<?php
require_once dirname(__DIR__, 2).'/models/Worklog.php';
if (\App\Models\Worklog::allowed()):
    $dueCount=0;
    try {
        $snapshot=(new \App\Models\Worklog())->snapshot();
        $today=(new DateTimeImmutable('now',new DateTimeZone('Africa/Johannesburg')))->format('Y-m-d');
        foreach ($snapshot['records'] as $record) {
            if ($record['kind']!=='task' || $record['deleted'] || $record['data']['status']==='done') continue;
            $d=$record['data'];
            foreach (['planned_on','due_on','follow_up_on'] as $key) {
                if ($d[$key] && $d[$key]<=$today && ($key!=='follow_up_on' || $d['status']==='waiting')) { $dueCount++; break; }
            }
        }
    } catch (Throwable $e) { /* Navigation remains available while migrations are pending. */ }
?>
<a class="stat-card" href="<?= htmlspecialchars(BASE_URL,ENT_QUOTES) ?>/admin/todo" style="display:block;margin-bottom:24px;text-decoration:none;color:inherit">
    <div class="stat-card-header"><div><div class="stat-card-value">workLOG</div>
    <div class="stat-card-label"><?= $dueCount ?> outstanding today · Open your notebook</div></div>
    <div class="stat-card-icon blue"><i class="fas fa-check-square"></i></div></div>
</a>
<?php endif; ?>
