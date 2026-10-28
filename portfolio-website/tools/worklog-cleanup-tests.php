<?php
if (PHP_SAPI!=='cli' || getenv('WORKLOG_TEST_FIXTURE')!=='1') exit(1);
require dirname(__DIR__).'/app/core/Env.php';
require dirname(__DIR__).'/app/core/Database.php';
require dirname(__DIR__).'/app/models/Worklog.php';
$pdo=\App\Core\Database::connect();$owner=\App\Models\Worklog::ownerId();
$ids=json_decode(@file_get_contents(dirname(__DIR__).'/logs/worklog-test-ids.json'),true)?:[];
$rows=$pdo->prepare("SELECT id,data FROM worklog_records WHERE owner_id=? AND kind='task'");$rows->execute([$owner]);
foreach ($rows->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $description=json_decode($row['data'],true)['description'];
    if ($description==='workLOG verification task') $ids[]=$row['id'];
}
$pdo->beginTransaction();
foreach (array_unique($ids) as $id) {
    $row=$pdo->prepare('SELECT data FROM worklog_records WHERE owner_id=? AND id=?');$row->execute([$owner,$id]);
    $data=json_decode($row->fetchColumn()?:'{}',true);
    if (!isset($data['description']) || (!str_starts_with($data['description'],'[worklog-test]') && $data['description']!=='workLOG verification task')) throw new RuntimeException('Refusing to remove non-test data.');
    foreach (['worklog_conflicts'=>'record_id','worklog_changes'=>'record_id','worklog_records'=>'id'] as $table=>$field) {
        $stmt=$pdo->prepare("DELETE FROM $table WHERE owner_id=? AND $field=?");$stmt->execute([$owner,$id]);
    }
    $stmt=$pdo->prepare("DELETE FROM worklog_operations WHERE owner_id=? AND JSON_UNQUOTE(JSON_EXTRACT(result,'$.record.id'))=?");$stmt->execute([$owner,$id]);
}
$stmt=$pdo->prepare("DELETE FROM worklog_devices WHERE owner_id=? AND name LIKE '[worklog-test]%'");$stmt->execute([$owner]);
$pdo->commit();
echo "Removed ".count(array_unique($ids))." explicitly identified test tasks.\n";
