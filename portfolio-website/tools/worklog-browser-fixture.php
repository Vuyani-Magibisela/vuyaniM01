<?php
if (PHP_SAPI!=='cli' || getenv('WORKLOG_TEST_FIXTURE')!=='1') exit(1);
require dirname(__DIR__).'/app/core/Session.php';
use App\Core\Session;
$id=bin2hex(random_bytes(24));session_id($id);Session::init();$id=session_id();
Session::set('user_id',(int)($argv[1]??1));Session::set('user_role',$argv[2]??'admin');
Session::set('username','Local workLOG verification');
Session::set('LAST_ACTIVITY',($argv[3]??'')==='expired'?time()-3600:time());Session::generateCsrfToken();
file_put_contents(dirname(__DIR__).'/logs/worklog-test-session.json',json_encode(['id'=>$id,'csrf'=>Session::get('csrf_token')]));
session_write_close();
$sessionFile=(session_save_path() ?: sys_get_temp_dir()).'/sess_'.$id;
if (is_file($sessionFile)) { chown($sessionFile,'www-data'); chmod($sessionFile,0600); }
echo "Temporary CLI-only test session created.\n";
