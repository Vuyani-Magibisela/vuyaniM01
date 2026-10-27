<?php
require_once dirname(__DIR__, 2) . '/models/Worklog.php';
if (\App\Models\Worklog::allowed()):
?>
<a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES) ?>/admin/todo" class="nav-link">
    <i class="fas fa-check-square"></i><span>To Do / workLOG</span>
</a>
<?php endif; ?>
