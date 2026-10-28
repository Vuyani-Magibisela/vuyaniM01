<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__).'/vendor/autoload.php';
require dirname(__DIR__).'/app/core/Env.php';
require dirname(__DIR__).'/app/core/Database.php';
require dirname(__DIR__).'/app/models/Worklog.php';
require dirname(__DIR__).'/app/core/WorklogMarkdown.php';
use App\Core\Database;
use App\Models\Worklog;
use App\Core\WorklogMarkdown;
$command=$argv[1]??'info';
try {
    $pdo=Database::connect();
    if (!$pdo) throw new RuntimeException('Database unavailable.');
    if ($command==='info') {
        echo json_encode(['admins'=>$pdo->query("SELECT id,username FROM users WHERE role='admin'")->fetchAll(PDO::FETCH_ASSOC),
            'configured_owner'=>Worklog::ownerId()],JSON_PRETTY_PRINT)."\n";exit;
    }
    $model=new Worklog();
    if ($command==='snapshot') {echo json_encode($model->snapshot(),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)."\n";exit;}
    if ($command==='pair') {echo json_encode($model->pair($argv[2]??'Windows bridge'))."\n";exit;}
    throw new RuntimeException('Commands: info, snapshot, pair. Import notes through the web preview.');
} catch (Throwable $e) {fwrite(STDERR,$e->getMessage()."\n");exit(1);}
