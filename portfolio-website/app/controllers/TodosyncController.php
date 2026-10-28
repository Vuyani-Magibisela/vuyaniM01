<?php
namespace App\Controllers;
use App\Models\Worklog;
require_once dirname(__DIR__).'/models/Worklog.php';
require_once dirname(__DIR__).'/core/WorklogRateLimit.php';

/** Device-only protocol. Browser sessions are deliberately not accepted here. */
class TodosyncController extends BaseController {
    private Worklog $worklog;
    private string $device;
    public function __construct() {
        header('Cache-Control: no-store, private');
        header('Content-Type: application/json; charset=utf-8');
        try {
            $this->worklog=new Worklog();
            \App\Core\WorklogRateLimit::check('sync-ip:'.($_SERVER['REMOTE_ADDR']??'unknown'),600);
            $auth=$_SERVER['HTTP_AUTHORIZATION']??$_SERVER['REDIRECT_HTTP_AUTHORIZATION']??'';
            $this->device=$this->worklog->authenticateDevice(preg_replace('/^Bearer /','',$auth));
        } catch (\Throwable $e) { $this->fail($e); }
    }
    private function reply($data,int $code=200): never {
        http_response_code($code); echo json_encode($data,JSON_UNESCAPED_UNICODE); exit;
    }
    private function fail(\Throwable $e): never {
        $code=(int)$e->getCode();
        if (!in_array($code,[400,401,403,404,409,413,422,429,503])) {
            error_log('workLOG sync: '.$e->getMessage()); $code=500;
        }
        $this->reply(['error'=>$code===500?'Sync failed; retry later.':$e->getMessage()],$code);
    }
    private function body(): array {
        if ($_SERVER['REQUEST_METHOD']!=='POST') $this->reply(['error'=>'Method not allowed.'],405);
        $raw=file_get_contents('php://input',false,null,0,1100001);
        if (strlen($raw)>1100000) $this->reply(['error'=>'Request too large.'],413);
        $body=json_decode($raw,true);
        if (!is_array($body)) $this->reply(['error'=>'Invalid JSON.'],400);
        return $body;
    }
    public function pull(): void {
        if ($_SERVER['REQUEST_METHOD']!=='GET') $this->reply(['error'=>'Method not allowed.'],405);
        try { $this->worklog->ensureCurrentWeek(); $this->reply($this->worklog->snapshot()+['conflicts'=>$this->worklog->conflicts()]); }
        catch (\Throwable $e) { $this->fail($e); }
    }
    public function push(): void {
        try { $this->reply($this->worklog->apply($this->body(),true)); }
        catch (\Throwable $e) { $this->fail($e); }
    }
    public function ack(): void {
        try {
            $body=$this->body();
            $this->worklog->acknowledge($this->device,(int)($body['revision']??-1));
            $this->reply(['ok'=>true]);
        } catch (\Throwable $e) { $this->fail($e); }
    }
}
