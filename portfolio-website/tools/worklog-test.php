<?php
if (PHP_SAPI!=='cli') exit(1);
require dirname(__DIR__).'/vendor/autoload.php';
require dirname(__DIR__).'/app/core/Env.php';
require dirname(__DIR__).'/app/core/Database.php';
require dirname(__DIR__).'/app/models/Worklog.php';
require dirname(__DIR__).'/app/core/WorklogMarkdown.php';
use App\Core\Database;
use App\Core\Env;
use App\Core\WorklogMarkdown;
use App\Models\Worklog;
if (Env::get('DB_NAME') !== 'worklog_test') {fwrite(STDERR,"Tests require the isolated worklog_test database.\n");exit(1);}
$pdo=Database::connect();
$pdo->exec(file_get_contents(dirname(__DIR__).'/database/migrations/010_create_worklog_tables.sql'));
foreach (['conflicts','operations','changes','devices','records','workspaces'] as $table) $pdo->exec('DELETE FROM worklog_'.$table);
$count=0;
function check($condition,$message) {global $count;if(!$condition)throw new RuntimeException('FAIL: '.$message);$count++;}
function rejects(callable $fn,int $code) {try{$fn();}catch(Throwable $e){check((int)$e->getCode()===$code,'error code: '.$e->getMessage());return;}throw new RuntimeException('Expected failure '.$code);}
check(Worklog::validate('task',['description'=>'Task','waiting_on'=>'Reviewer'])['waiting_on']==='Reviewer','waiting-on is text, not a date');
$model=new Worklog(1);
$week=$model->apply(['operation_id'=>Worklog::uuid(),'id'=>Worklog::uuid(),'kind'=>'week','version'=>0,'data'=>[
    'title'=>'Week of 26 October','starts_on'=>'2026-10-26','ends_on'=>'2026-11-01','related'=>"## Related Notes\n\n- [[Work|Alias]]",'review'=>'Reflect.'
]])['record'];
$op=['operation_id'=>Worklog::uuid(),'id'=>Worklog::uuid(),'kind'=>'task','version'=>0,'data'=>[
    'week_id'=>$week['id'],'description'=>"**Keep** [[Note|alias]] | pipes\nNext: café ✅",'priority'=>'high','planned_on'=>'2026-10-27'
]];
$task=$model->apply($op)['record'];
check($task['version']===1,'creation version');
check($model->apply($op)['record']===$task,'idempotent retry');
rejects(fn()=>$model->apply(array_replace($op,['data'=>['description'=>'different']])),409);
$updated=$model->apply(['operation_id'=>Worklog::uuid(),'id'=>$task['id'],'kind'=>'task','version'=>1,'data'=>array_replace($task['data'],['area'=>'Website'])])['record'];
rejects(fn()=>$model->apply(['operation_id'=>Worklog::uuid(),'id'=>$task['id'],'kind'=>'task','version'=>1,'data'=>$task['data']]),409);
$merged=$model->apply(['operation_id'=>Worklog::uuid(),'id'=>$task['id'],'kind'=>'task','version'=>1,'base'=>$task,'data'=>array_replace($task['data'],['schedule'=>'Morning'])],true)['record'];
check($merged['data']['area']==='Website'&&$merged['data']['schedule']==='Morning','non-overlapping field merge');
$conflict=$model->apply(['operation_id'=>Worklog::uuid(),'id'=>$task['id'],'kind'=>'task','version'=>1,'base'=>$task,'data'=>array_replace($task['data'],['area'=>'Obsidian'])],true);
check(isset($conflict['conflict']),'same field conflict');
check(count($model->conflicts())===1,'conflict persisted');
$resolved=$model->resolve($conflict['conflict'],'note',$merged['version'])['record'];
check($resolved['data']['area']==='Obsidian'&&$resolved['data']['schedule']==='Morning','resolution preserves other changes');
$deletion=$model->apply(['operation_id'=>Worklog::uuid(),'id'=>$task['id'],'kind'=>'task','version'=>$resolved['version'],'base'=>$resolved,'data'=>$resolved['data'],'deleted'=>true],true);
check(isset($deletion['conflict'])&&!$model->record($task['id'])['deleted'],'note deletion requires confirmation');
$model->resolve($deletion['conflict'],'website',$resolved['version']);
$second=new Worklog(2);check($second->record($task['id'])===null,'workspace isolation');
$device=$model->pair('test');check($model->authenticateDevice($device['token'])===$device['id'],'device token');
$model->acknowledge($device['id'],$model->snapshot()['revision']);$model->revoke($device['id']);
rejects(fn()=>$model->authenticateDevice($device['token']),401);
rejects(fn()=>Worklog::validate('task',['description'=>'x','due_on'=>'2026-02-31']),422);
rejects(fn()=>Worklog::validate('task',['description'=>'x','owner_id'=>2]),422);
$markdown=WorklogMarkdown::render($week,[$resolved]);$parsed=WorklogMarkdown::parse($markdown);
check($parsed['tasks'][0]['data']['description']===$resolved['data']['description'],'Markdown rich text round trip');
check($parsed['related']===$week['data']['related'],'related links preserved');
check($parsed['review']==='Reflect.','review round trip');
check($parsed['tasks'][0]['id']===$task['id'],'stable IDs');
$pdo->exec(file_get_contents(dirname(__DIR__).'/database/migrations/010_create_worklog_tables.sql'));
check(count($model->snapshot()['records'])===2,'migration repeat is non-destructive');
$revision=$model->snapshot()['revision'];
rejects(fn()=>$model->importRecords([],$revision-1),409);
$importId=Worklog::uuid();
rejects(fn()=>$model->importRecords([
    ['id'=>$importId,'kind'=>'task','data'=>['description'=>'Atomic import']],
    ['id'=>Worklog::uuid(),'kind'=>'task','data'=>['description'=>'Invalid week','week_id'=>Worklog::uuid()]]
],$revision),422);
check($model->record($importId)===null && $model->snapshot()['revision']===$revision,'failed import rolls back every record and revision');
rejects(fn()=>$model->importRecords([['id'=>$task['id'],'kind'=>'week','data'=>$week['data']]],$revision),422);
echo "$count workLOG assertions passed.\n";
