<?php
namespace App\Models;

use App\Core\Env;
use App\Core\Session;
use PDO;

require_once __DIR__ . '/BaseModel.php';
require_once dirname(__DIR__) . '/core/Env.php';
require_once dirname(__DIR__) . '/core/Session.php';

/** Durable records and one serialized change stream per private workspace. */
class Worklog extends BaseModel {
    private int $owner;

    public static function ownerId(): int {
        return (int) Env::get('WORKLOG_OWNER_ID', 0);
    }

    public static function allowed(): bool {
        return self::ownerId() > 0 && Session::isAdmin()
            && (int) Session::getUserId() === self::ownerId();
    }

    public static function uuid(): string {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 15) | 64);
        $bytes[8] = chr((ord($bytes[8]) & 63) | 128);
        $hex = bin2hex($bytes);
        return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4)
            . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20);
    }

    public static function validId($id): bool {
        return is_string($id) && preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/D', $id);
    }

    public function __construct(?int $owner = null) {
        parent::__construct();
        $this->owner = $owner ?? self::ownerId();
        if (!$this->db || $this->owner < 1) {
            throw new \RuntimeException('workLOG is not configured.', 503);
        }
        $this->executeSql('INSERT IGNORE INTO worklog_workspaces (owner_id) VALUES (?)', [$this->owner]);
    }

    private function executeSql(string $sql, array $values = []): \PDOStatement {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        return $stmt;
    }

    private function decode(array $row): array {
        return ['id' => $row['id'], 'kind' => $row['kind'], 'version' => (int) $row['version'],
            'deleted' => $row['deleted_at'] !== null, 'data' => json_decode($row['data'], true),
            'updated_at' => $row['updated_at']];
    }

    public function record(string $id): ?array {
        $row = $this->executeSql('SELECT * FROM worklog_records WHERE owner_id=? AND id=?', [$this->owner, $id])->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->decode($row) : null;
    }

    public function ensureCurrentWeek(): void {
        $today=(new \DateTimeImmutable('now',new \DateTimeZone('Africa/Johannesburg')))->format('Y-m-d');
        foreach ($this->snapshot()['records'] as $r) {
            if ($r['kind']==='week' && !$r['deleted'] && $r['data']['starts_on']<=$today && $r['data']['ends_on']>=$today) return;
        }
        $start=(new \DateTimeImmutable($today))->modify('monday this week');
        try {
            $this->apply(['id'=>self::uuid(),'kind'=>'week','version'=>0,'operation_id'=>self::uuid(),'data'=>[
                'title'=>'Week of '.$start->format('j F Y'),'starts_on'=>$start->format('Y-m-d'),'ends_on'=>$start->modify('+6 days')->format('Y-m-d')
            ]]);
        } catch (\RuntimeException|\InvalidArgumentException $e) {
            if ($e->getCode()!==409) throw $e; // Another client may have created this week.
        }
    }

    public function snapshot(): array {
        // The revision and records must represent the same committed database snapshot.
        $this->db->beginTransaction();
        try {
            $revision = (int) $this->executeSql('SELECT revision FROM worklog_workspaces WHERE owner_id=?', [$this->owner])->fetchColumn();
            $rows = $this->executeSql('SELECT * FROM worklog_records WHERE owner_id=? ORDER BY kind,id', [$this->owner])->fetchAll(PDO::FETCH_ASSOC);
            $this->db->commit();
            return ['revision' => $revision, 'records' => array_map([$this, 'decode'], $rows)];
        } catch (\Throwable $error) {
            $this->db->rollBack();
            throw $error;
        }
    }

    private static function date($value): ?string {
        if ($value === null || $value === '') return null;
        if (!is_string($value)) throw new \InvalidArgumentException('Invalid date.', 422);
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if (!$date || $date->format('Y-m-d') !== $value) throw new \InvalidArgumentException('Use YYYY-MM-DD dates.', 422);
        return $value;
    }

    public static function validate(string $kind, array $data): array {
        $fields = $kind === 'task'
            ? ['week_id','position','status','priority','area','description','schedule','started_on','finished_on','planned_on','due_on','waiting_on','follow_up_on','time_log','timer_started_at']
            : ['title','starts_on','ends_on','related','review'];
        if (!in_array($kind, ['task','week'], true) || array_diff(array_keys($data), $fields)) {
            throw new \InvalidArgumentException('Unknown record field or type.', 422);
        }
        $out = $kind === 'task'
            ? ['week_id'=>null,'position'=>0,'status'=>'open','priority'=>'medium','area'=>'','description'=>'','schedule'=>'','started_on'=>null,'finished_on'=>null,'planned_on'=>null,'due_on'=>null,'waiting_on'=>'','follow_up_on'=>null,'time_log'=>[],'timer_started_at'=>null]
            : ['title'=>'','starts_on'=>null,'ends_on'=>null,'related'=>'## Related Notes' . "\n\n" . '- [[Work]]','review'=>''];
        foreach ($data as $key => $value) {
            if (in_array($key, ['started_on','finished_on','planned_on','due_on','follow_up_on','starts_on','ends_on'], true)) $out[$key] = self::date($value);
            elseif ($key === 'time_log') $out[$key] = self::timeLog($value);
            elseif ($key === 'timer_started_at') {
                // Website-only running timer, stored as UTC ISO-8601 so every device agrees.
                if ($value !== null && (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d{1,3})?Z$/D', $value) || strtotime($value) === false)) {
                    throw new \InvalidArgumentException('Invalid timer start.', 422);
                }
                $out[$key] = $value;
            } elseif ($key === 'week_id') {
                if ($value !== null && !self::validId($value)) throw new \InvalidArgumentException('Invalid week.', 422);
                $out[$key] = $value;
            } elseif ($key === 'position') {
                if (!is_int($value) || $value < 0 || $value > 1000000) throw new \InvalidArgumentException('Invalid order.', 422);
                $out[$key] = $value;
            } else {
                if (!is_string($value) || strlen($value) > (in_array($key, ['description','related','review']) ? 20000 : 500)) {
                    throw new \InvalidArgumentException('Text is invalid or too long.', 422);
                }
                $out[$key] = trim($value);
            }
        }
        if ($kind === 'task') {
            if (!$out['description']) throw new \InvalidArgumentException('A task needs a description.', 422);
            if (!in_array($out['status'], ['open','waiting','done'], true) || !in_array($out['priority'], ['high','medium','low'], true)) {
                throw new \InvalidArgumentException('Invalid status or priority.', 422);
            }
        } elseif (!$out['title'] || !$out['starts_on'] || !$out['ends_on'] || $out['starts_on'] > $out['ends_on']) {
            throw new \InvalidArgumentException('A week needs a title and valid date boundaries.', 422);
        }
        return $out;
    }

    private static function normalize(string $kind, array $data): array {
        return $data ? self::validate($kind, $data) : [];
    }

    /** Website-only time entries; the Obsidian table never carries them. */
    private static function timeLog($value): array {
        if (!is_array($value) || !array_is_list($value) || count($value) > 1000) throw new \InvalidArgumentException('Invalid time log.', 422);
        $out = [];
        foreach ($value as $entry) {
            if (!is_array($entry) || array_diff(array_keys($entry), ['id','on','minutes','note'])) throw new \InvalidArgumentException('Invalid time entry.', 422);
            $minutes = $entry['minutes'] ?? null;
            $note = $entry['note'] ?? '';
            if (!self::validId($entry['id'] ?? null) || !is_int($minutes) || $minutes < 1 || $minutes > 1440) throw new \InvalidArgumentException('Time entries need 1 to 1440 minutes.', 422);
            if (!is_string($note) || strlen($note) > 200) throw new \InvalidArgumentException('Time entry notes are limited to 200 characters.', 422);
            $on = self::date($entry['on'] ?? null);
            if (!$on) throw new \InvalidArgumentException('Time entries need a date.', 422);
            $out[] = ['id'=>$entry['id'],'on'=>$on,'minutes'=>$minutes,'note'=>trim($note)];
        }
        return $out;
    }

    private static function areaKey(string $name): string {
        return mb_strtolower(trim($name));
    }

    /** Saved areas plus any typed elsewhere (e.g. Obsidian), one entry per name ignoring case. */
    public function areas(): array {
        $saved = json_decode((string) $this->executeSql('SELECT areas FROM worklog_workspaces WHERE owner_id=?', [$this->owner])->fetchColumn(), true) ?: [];
        $areas = [];
        foreach ($saved as $name) $areas[self::areaKey($name)] ??= ['name'=>$name,'tasks'=>0];
        foreach ($this->snapshot()['records'] as $r) {
            if ($r['kind'] !== 'task' || $r['deleted'] || trim($r['data']['area']) === '') continue;
            $areas[self::areaKey($r['data']['area'])] ??= ['name'=>trim($r['data']['area']),'tasks'=>0];
            $areas[self::areaKey($r['data']['area'])]['tasks']++;
        }
        uasort($areas, fn($a, $b) => strcasecmp($a['name'], $b['name']));
        return array_values($areas);
    }

    private function saveAreas(array $names): void {
        $this->executeSql('UPDATE worklog_workspaces SET areas=? WHERE owner_id=?', [json_encode(array_values($names), JSON_UNESCAPED_UNICODE), $this->owner]);
    }

    private function savedAreas(): array {
        return json_decode((string) $this->executeSql('SELECT areas FROM worklog_workspaces WHERE owner_id=? FOR UPDATE', [$this->owner])->fetchColumn(), true) ?: [];
    }

    private static function areaName($name): string {
        if (!is_string($name) || trim($name) === '' || mb_strlen(trim($name)) > 100) throw new \InvalidArgumentException('Area names need 1 to 100 characters.', 422);
        return trim($name);
    }

    /** Returns the existing spelling when the name already exists in any case. */
    public function addArea($name): string {
        $name = self::areaName($name);
        foreach ($this->areas() as $area) if (self::areaKey($area['name']) === self::areaKey($name)) return $area['name'];
        $this->db->beginTransaction();
        try {
            $saved = $this->savedAreas();
            $saved[] = $name;
            $this->saveAreas($saved);
            $this->db->commit();
            return $name;
        } catch (\Throwable $error) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $error;
        }
    }

    /** Renaming into an existing name merges the two areas. Task edits sync to Obsidian. */
    public function renameArea($from, $to): int {
        $from = self::areaName($from);
        $to = self::areaName($to);
        foreach ($this->areas() as $area) if (self::areaKey($area['name']) === self::areaKey($to)) $to = $area['name'];
        $this->db->beginTransaction();
        try {
            $this->executeSql('SELECT revision FROM worklog_workspaces WHERE owner_id=? FOR UPDATE', [$this->owner]);
            $changed = 0;
            $rows = $this->executeSql("SELECT * FROM worklog_records WHERE owner_id=? AND kind='task' AND deleted_at IS NULL", [$this->owner])->fetchAll(PDO::FETCH_ASSOC);
            foreach (array_map([$this, 'decode'], $rows) as $task) {
                if (self::areaKey($task['data']['area']) !== self::areaKey($from) || $task['data']['area'] === $to) continue;
                $this->write($task['id'], 'task', array_merge($task['data'], ['area'=>$to]), false, $task);
                $changed++;
            }
            $saved = array_filter($this->savedAreas(), fn($n) => !in_array(self::areaKey($n), [self::areaKey($from), self::areaKey($to)], true));
            $saved[] = $to;
            $this->saveAreas($saved);
            $this->db->commit();
            return $changed;
        } catch (\Throwable $error) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $error;
        }
    }

    public function deleteArea($name): void {
        $name = self::areaName($name);
        foreach ($this->areas() as $area) {
            if (self::areaKey($area['name']) === self::areaKey($name) && $area['tasks'] > 0) {
                throw new \RuntimeException('Tasks still use this area. Merge it into another area instead.', 409);
            }
        }
        $this->db->beginTransaction();
        try {
            $this->saveAreas(array_filter($this->savedAreas(), fn($n) => self::areaKey($n) !== self::areaKey($name)));
            $this->db->commit();
        } catch (\Throwable $error) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $error;
        }
    }

    /** Three-way merge. Null is a real field value, not an absent field. */
    public static function merge(array $base, array $remote, array $local): array {
        $merged = $remote;
        $conflicts = [];
        foreach (array_unique(array_merge(array_keys($base), array_keys($local))) as $field) {
            $before = $base[$field] ?? null;
            $ours = $local[$field] ?? null;
            $theirs = $remote[$field] ?? null;
            if ($ours === $before) continue;
            if ($theirs !== $before && $theirs !== $ours) $conflicts[] = $field;
            $merged[$field] = $ours;
        }
        return ['data'=>$merged, 'fields'=>$conflicts];
    }

    /** All writes lock the workspace first, so revisions follow commit order. */
    public function apply(array $operation, bool $sync = false): array {
        $opId = $operation['operation_id'] ?? '';
        $id = $operation['id'] ?? '';
        if (!self::validId($opId) || !self::validId($id)) throw new \InvalidArgumentException('Invalid operation or record ID.', 422);
        $hash = hash('sha256', json_encode([$operation, $sync]));
        $this->db->beginTransaction();
        try {
            $this->executeSql('SELECT revision FROM worklog_workspaces WHERE owner_id=? FOR UPDATE', [$this->owner]);
            $seen = $this->executeSql('SELECT * FROM worklog_operations WHERE owner_id=? AND operation_id=?', [$this->owner, $opId])->fetch(PDO::FETCH_ASSOC);
            if ($seen) {
                if ($seen['request_hash'] !== $hash) throw new \InvalidArgumentException('Operation ID already used.', 409);
                $this->db->commit();
                return json_decode($seen['result'], true);
            }
            $current = $this->record($id);
            $kind = $operation['kind'] ?? '';
            $data = self::validate($kind, $operation['data'] ?? []);
            $deleted = ($operation['deleted'] ?? false) === true;
            if ($current && $current['kind'] !== $kind) throw new \InvalidArgumentException('Record type cannot change.', 422);
            $expected = $operation['version'] ?? 0;
            if (!is_int($expected) || $expected < 0) throw new \InvalidArgumentException('Invalid version.', 422);
            $conflictFields = [];
            if ($sync && $current && isset($operation['base'])) {
                // The supplied base must be an actual server revision for this record.
                $base = $operation['base'];
                $historical = $this->executeSql('SELECT snapshot FROM worklog_changes WHERE owner_id=? AND record_id=?', [$this->owner, $id])->fetchAll(PDO::FETCH_COLUMN);
                $validBase = false;
                foreach ($historical as $snapshot) {
                    $old = json_decode($snapshot, true);
                    if ($old['version'] === $expected && $old['data'] === ($base['data'] ?? []) && $old['deleted'] === ($base['deleted'] ?? false)) $validBase = true;
                }
                if (!$validBase) throw new \InvalidArgumentException('Unknown synchronization base; refresh required.', 409);
                // Normalize first: records saved before a field existed must not read as changing it.
                $merge = self::merge(self::normalize($kind, $base['data'] ?? []), self::normalize($kind, $current['data']), $data);
                $data = $merge['data'];
                $conflictFields = $merge['fields'];
                if ($current['deleted'] !== $base['deleted'] || $deleted !== $base['deleted']) {
                    if ($current['version'] !== $expected || $current['deleted'] !== $deleted) $conflictFields[] = 'deleted';
                }
                if ($deleted && !$current['deleted']) $conflictFields[] = 'confirm_deletion';
            } elseif (($current['version'] ?? 0) !== $expected) {
                throw new \RuntimeException('This task changed. Reload it before saving.', 409);
            }
            if ($sync && $deleted && !in_array('confirm_deletion', $conflictFields, true)) $conflictFields[] = 'confirm_deletion';
            if ($conflictFields) {
                $conflictId = self::uuid();
                $detail = ['current'=>$current,'incoming'=>['id'=>$id,'kind'=>$kind,'data'=>$data,'deleted'=>$deleted],
                    'fields'=>array_values(array_unique($conflictFields)), 'base'=>$operation['base'] ?? null];
                $this->executeSql('INSERT INTO worklog_conflicts (id,owner_id,record_id,detail) VALUES (?,?,?,?)',
                    [$conflictId,$this->owner,$id,json_encode($detail)]);
                $result = ['conflict'=>$conflictId,'record'=>$current];
            } else {
                $result = ['record'=>$this->write($id, $kind, $data, $deleted, $current)];
            }
            $this->executeSql('INSERT INTO worklog_operations (owner_id,operation_id,request_hash,result) VALUES (?,?,?,?)',
                [$this->owner,$opId,$hash,json_encode($result)]);
            $this->db->commit();
            return $result;
        } catch (\Throwable $error) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $error;
        }
    }

    private function write(string $id, string $kind, array $data, bool $deleted, ?array $current): array {
        if (!self::validId($id) || ($current && $current['kind'] !== $kind)) throw new \InvalidArgumentException('Invalid record ID or type.', 422);
        $data = self::validate($kind, $data);
        if ($kind === 'task' && $data['week_id']) {
            $week = $this->record($data['week_id']);
            if (!$week || $week['kind'] !== 'week' || $week['deleted']) throw new \InvalidArgumentException('Week does not exist.', 422);
        }
        if ($kind === 'week' && $deleted) throw new \InvalidArgumentException('Archive weeks instead of deleting them.', 422);
        if ($kind === 'week') {
            $weeks = $this->executeSql("SELECT id,data FROM worklog_records WHERE owner_id=? AND kind='week' AND deleted_at IS NULL", [$this->owner])->fetchAll(PDO::FETCH_ASSOC);
            foreach ($weeks as $week) {
                if ($week['id'] !== $id && json_decode($week['data'], true)['starts_on'] === $data['starts_on']) {
                    throw new \InvalidArgumentException('A week already exists for this start date.', 409);
                }
            }
        }
        if ($current && $current['data'] === $data && $current['deleted'] === $deleted) return $current;
        $version = ($current['version'] ?? 0) + 1;
        $this->executeSql('INSERT INTO worklog_records (owner_id,id,kind,version,data,deleted_at) VALUES (?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE version=VALUES(version),data=VALUES(data),deleted_at=VALUES(deleted_at),updated_at=UTC_TIMESTAMP()',
            [$this->owner,$id,$kind,$version,json_encode($data, JSON_UNESCAPED_UNICODE),$deleted ? gmdate('Y-m-d H:i:s') : null]);
        $this->executeSql('UPDATE worklog_workspaces SET revision=revision+1 WHERE owner_id=?', [$this->owner]);
        $revision = $this->executeSql('SELECT revision FROM worklog_workspaces WHERE owner_id=?', [$this->owner])->fetchColumn();
        $record = $this->record($id);
        $this->executeSql('INSERT INTO worklog_changes (owner_id,revision,record_id,snapshot) VALUES (?,?,?,?)',
            [$this->owner,$revision,$id,json_encode($record, JSON_UNESCAPED_UNICODE)]);
        return $record;
    }

    public function importRecords(array $records, int $expectedRevision): array {
        $this->db->beginTransaction();
        try {
            $revision=(int)$this->executeSql('SELECT revision FROM worklog_workspaces WHERE owner_id=? FOR UPDATE',[$this->owner])->fetchColumn();
            if ($revision!==$expectedRevision) throw new \RuntimeException('Workspace changed after preview. Preview the import again.',409);
            $result=[];
            foreach ($records as $record) {
                $current=$this->record($record['id']);
                $result[]=$this->write($record['id'],$record['kind'],$record['data'],false,$current);
            }
            $this->db->commit();
            return $result;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function conflicts(): array {
        $rows = $this->executeSql('SELECT id,detail,created_at FROM worklog_conflicts WHERE owner_id=? AND resolved_at IS NULL ORDER BY created_at', [$this->owner])->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) $row['detail'] = json_decode($row['detail'], true);
        return $rows;
    }

    public function resolve(string $id, string $choice, int $version): array {
        if (!in_array($choice, ['website','note'], true)) throw new \InvalidArgumentException('Choose website or note.', 422);
        $this->db->beginTransaction();
        try {
            $this->executeSql('SELECT revision FROM worklog_workspaces WHERE owner_id=? FOR UPDATE', [$this->owner]);
            $row = $this->executeSql('SELECT * FROM worklog_conflicts WHERE owner_id=? AND id=? AND resolved_at IS NULL', [$this->owner,$id])->fetch(PDO::FETCH_ASSOC);
            if (!$row) throw new \RuntimeException('Conflict already resolved or missing.', 404);
            $detail = json_decode($row['detail'], true);
            $current = $this->record($row['record_id']);
            if (($current['version'] ?? 0) !== $version) throw new \RuntimeException('Task changed again. Refresh before resolving.', 409);
            if ($choice === 'note') {
                $incoming = $detail['incoming'];
                // Keep subsequent website changes to fields untouched by the original local edit.
                $kind = $incoming['kind'];
                $merge = self::merge(self::normalize($kind, $detail['current']['data'] ?? []), self::normalize($kind, $current['data'] ?? []), self::normalize($kind, $incoming['data']));
                $current = $this->write($row['record_id'], $incoming['kind'], $merge['data'], $incoming['deleted'], $current);
            }
            $this->executeSql('UPDATE worklog_conflicts SET resolved_at=UTC_TIMESTAMP() WHERE id=? AND owner_id=?', [$id,$this->owner]);
            $this->db->commit();
            return ['record'=>$current];
        } catch (\Throwable $error) {
            $this->db->rollBack();
            throw $error;
        }
    }

    public function devices(): array {
        return $this->executeSql('SELECT id,name,last_seen,acknowledged_revision,revoked_at FROM worklog_devices WHERE owner_id=? ORDER BY created_at DESC', [$this->owner])->fetchAll(PDO::FETCH_ASSOC);
    }

    public function pair(string $name): array {
        $id = self::uuid();
        $token = bin2hex(random_bytes(32));
        $this->executeSql('INSERT INTO worklog_devices (id,owner_id,name,token_hash) VALUES (?,?,?,?)',
            [$id,$this->owner,substr(trim($name) ?: 'Windows bridge',0,100),hash('sha256',$token)]);
        return ['id'=>$id,'token'=>$token];
    }

    public function revoke(string $id): void {
        $this->executeSql('UPDATE worklog_devices SET revoked_at=UTC_TIMESTAMP() WHERE id=? AND owner_id=?', [$id,$this->owner]);
    }

    public function authenticateDevice(string $token): string {
        if (!preg_match('/^[a-f0-9]{64}$/D', $token)) throw new \RuntimeException('Invalid device credential.', 401);
        $id = $this->executeSql('SELECT id FROM worklog_devices WHERE owner_id=? AND token_hash=? AND revoked_at IS NULL',
            [$this->owner,hash('sha256',$token)])->fetchColumn();
        if (!$id) throw new \RuntimeException('Device credential revoked or invalid.', 401);
        $this->executeSql('UPDATE worklog_devices SET last_seen=UTC_TIMESTAMP() WHERE id=?', [$id]);
        return $id;
    }

    public function acknowledge(string $id, int $revision): void {
        $max = (int) $this->executeSql('SELECT revision FROM worklog_workspaces WHERE owner_id=?', [$this->owner])->fetchColumn();
        if ($revision < 0 || $revision > $max) throw new \InvalidArgumentException('Invalid revision.', 422);
        $this->executeSql('UPDATE worklog_devices SET acknowledged_revision=GREATEST(acknowledged_revision,?) WHERE owner_id=? AND id=?', [$revision,$this->owner,$id]);
    }
}
