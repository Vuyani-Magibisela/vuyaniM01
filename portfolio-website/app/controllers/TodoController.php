<?php
namespace App\Controllers;

use App\Core\Session;
use App\Core\WorklogMarkdown;
use App\Models\Worklog;

require_once dirname(__DIR__) . '/models/Worklog.php';
require_once dirname(__DIR__) . '/core/WorklogMarkdown.php';
require_once dirname(__DIR__) . '/core/WorklogRateLimit.php';
require_once dirname(__DIR__) . '/config/config.php';

class TodoController extends BaseController {
    private Worklog $worklog;

    public function __construct() {
        header('Cache-Control: no-store, private');
        if (!Worklog::allowed()) {
            $this->json(['error'=>'This workspace is private. Sign in with its owner account.'], Session::isAuthenticated()?403:401);
        }
        try { $this->worklog=new Worklog(); }
        catch (\Throwable $e) { $this->fail($e); }
    }

    public function index(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') $this->json(['error'=>'Method not allowed.'],405);
        $this->view('admin/todo',['baseUrl'=>BASE_URL,'csrf'=>Session::generateCsrfToken()]);
    }

    protected function json($body, int $status=200): never {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($body, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function fail(\Throwable $e): never {
        $code=(int)$e->getCode();
        if (!in_array($code,[400,401,403,404,405,409,413,422,429,503])) {
            error_log('workLOG: '.$e->getMessage()); $code=500;
        }
        $this->json(['error'=>$code===500?'Could not save workLOG. Your changes have not been discarded.':$e->getMessage()],$code);
    }

    private function body(): array {
        \App\Core\WorklogRateLimit::check('browser:'.Worklog::ownerId());
        $token=$_SERVER['HTTP_X_CSRF_TOKEN']??'';
        if (!$token || !Session::get('csrf_token') || !Session::verifyCsrfToken($token)) {
            $this->json(['error'=>'Session token missing or expired. Reload after signing in.'],403);
        }
        if ((int)($_SERVER['CONTENT_LENGTH']??0)>1100000) $this->json(['error'=>'Request too large.'],413);
        $raw=file_get_contents('php://input',false,null,0,1100001);
        if (strlen($raw)>1100000) $this->json(['error'=>'Request too large.'],413);
        $body=json_decode($raw,true);
        if (!is_array($body)) $this->json(['error'=>'Invalid JSON.'],400);
        return $body;
    }

    private function run(callable $callback): never {
        try { $this->json($callback()); } catch (\Throwable $e) { $this->fail($e); }
    }

    public function state(): void {
        if ($_SERVER['REQUEST_METHOD']!=='GET') $this->json(['error'=>'Method not allowed.'],405);
        $this->run(fn()=>array_merge($this->worklog->snapshot(),['conflicts'=>$this->worklog->conflicts(),'devices'=>$this->worklog->devices()]));
    }

    public function tasks(?string $id=null): void { $this->mutate('task',$id); }
    public function weeks(?string $id=null): void { $this->mutate('week',$id); }

    private function mutate(string $kind, ?string $id): void {
        $method=$_SERVER['REQUEST_METHOD'];
        if ($method==='GET') {
            $this->run(function() use($id,$kind) {
                if ($id) {
                    $record=$this->worklog->record($id);
                    if (!$record || $record['kind']!==$kind) throw new \RuntimeException('Not found.',404);
                    return ['record'=>$record];
                }
                return ['records'=>array_values(array_filter($this->worklog->snapshot()['records'],fn($r)=>$r['kind']===$kind))];
            });
        }
        if (!in_array($method,['POST','PATCH','DELETE'])) $this->json(['error'=>'Method not allowed.'],405);
        $this->run(function() use($id,$kind,$method) {
            $body=$this->body();
            if ($id && ($body['id']??null)!==$id) throw new \InvalidArgumentException('Record ID mismatch.',422);
            $body['kind']=$kind;
            $body['deleted']=$method==='DELETE' || ($body['deleted']??false)===true;
            return $this->worklog->apply($body);
        });
    }

    public function devices(?string $id=null): void {
        if (!in_array($_SERVER['REQUEST_METHOD'],['POST','DELETE'])) $this->json(['error'=>'Method not allowed.'],405);
        $this->run(function() use($id) {
            $body=$this->body();
            if ($_SERVER['REQUEST_METHOD']==='DELETE') {
                $this->worklog->revoke($id??''); return ['ok'=>true];
            }
            return $this->worklog->pair((string)($body['name']??'Windows bridge'));
        });
    }

    public function conflicts(?string $id=null): void {
        if ($_SERVER['REQUEST_METHOD']!=='POST') $this->json(['error'=>'Method not allowed.'],405);
        $this->run(function() use($id) {
            $body=$this->body();
            return $this->worklog->resolve($id??'', $body['choice']??'', (int)($body['version']??0));
        });
    }

    public function export(?string $id=null): void {
        if ($_SERVER['REQUEST_METHOD']!=='GET') $this->json(['error'=>'Method not allowed.'],405);
        try {
            $snapshot=$this->worklog->snapshot();
            $week=null; $tasks=[];
            foreach ($snapshot['records'] as $r) {
                if ($r['id']===$id && $r['kind']==='week' && !$r['deleted']) $week=$r;
                if ($r['kind']==='task' && $r['data']['week_id']===$id) $tasks[]=$r;
            }
            if (!$week) throw new \RuntimeException('Week not found.',404);
            header('Content-Type: text/markdown; charset=utf-8');
            header('Content-Disposition: attachment; filename="worklog-'.$week['data']['starts_on'].'.md"');
            echo WorklogMarkdown::render($week,$tasks);
        } catch (\Throwable $e) { $this->fail($e); }
    }

    public function import(): void {
        if ($_SERVER['REQUEST_METHOD']!=='POST') $this->json(['error'=>'Method not allowed.'],405);
        $this->run(function() {
            $body=$this->body();
            $parsed=WorklogMarkdown::parse((string)($body['markdown']??''));
            $start=$body['starts_on']??$parsed['starts_on'];
            $end=$body['ends_on']??$parsed['ends_on'];
            $stable=static function($text) {
                $hex=hash('sha256',$text);
                return substr($hex,0,8).'-'.substr($hex,8,4).'-'.substr($hex,12,4).'-'.substr($hex,16,4).'-'.substr($hex,20,12);
            };
            $snapshot=$this->worklog->snapshot();
            $existingWeek=null;
            foreach ($snapshot['records'] as $r) if ($r['kind']==='week' && $r['data']['starts_on']===$start) $existingWeek=$r;
            $weekId=$parsed['id']??$existingWeek['id']??$stable('worklog-week:'.Worklog::ownerId().':'.$start);
            if ($existingWeek && $existingWeek['id']!==$weekId) throw new \RuntimeException('This date already has a different weekly note.',409);
            $existingTasks=array_values(array_filter($snapshot['records'],fn($r)=>$r['kind']==='task' && $r['data']['week_id']===$weekId));
            $week=Worklog::validate('week',['title'=>$parsed['title'],'starts_on'=>$start,'ends_on'=>$end,'related'=>$parsed['related'],'review'=>$parsed['review'] ?: ($existingWeek['data']['review']??'')]);
            $operations=[['id'=>$weekId,'kind'=>'week','data'=>$week]];
            $nextPosition=$existingTasks ? max(array_column(array_column($existingTasks,'data'),'position'))+1 : 0;
            foreach ($parsed['tasks'] as $i=>$task) {
                $id=$task['id']??$stable($weekId.':row:'.$i);
                $existing=$this->worklog->record($id);
                $data=$task['data']; $data['week_id']=$weekId;
                $data['position']=$existing['data']['position']??$nextPosition++;
                $operations[]=['id'=>$id,'kind'=>'task','data'=>Worklog::validate('task',$data)];
            }
            // Refuse edits through bulk import: ongoing edits use the versioned bridge.
            foreach ($operations as $op) {
                $current=$this->worklog->record($op['id']);
                if ($current && $op['kind']!=='week' && ($current['data']!=$op['data'] || $current['deleted'])) throw new \RuntimeException('This note already has different data in workLOG. Use sync to reconcile it.',409);
            }
            if (($body['commit']??false)!==true) return ['preview'=>['week'=>$week,'tasks'=>count($parsed['tasks']),'records'=>$operations,'revision'=>$snapshot['revision']]];
            $this->worklog->importRecords($operations,(int)($body['expected_revision']??-1));
            return ['imported'=>count($parsed['tasks']),'week_id'=>$weekId];
        });
    }
}
