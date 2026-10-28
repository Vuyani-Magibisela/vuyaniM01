<?php
$title = 'workLOG';
$pageTitle = 'Your working notebook';
$additionalCSS = [BASE_URL . '/css/worklog.css?v=' . filemtime(dirname(__DIR__, 3) . '/public/css/worklog.css')];
$additionalJS = [BASE_URL . '/js/worklog.js?v=' . filemtime(dirname(__DIR__, 3) . '/public/js/worklog.js')];
ob_start();
?>
<section id="worklog" class="wl" data-base="<?= htmlspecialchars(BASE_URL, ENT_QUOTES) ?>" data-csrf="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
    <div class="wl-masthead">
        <div><p class="wl-eyebrow">PRIVATE WORKSPACE / FIELD NOTES</p><h2>work<span>LOG</span><em>Make the next move clear.</em></h2></div>
        <div class="wl-stamp"><span id="wl-today"></span><strong>One thing<br>at a time.</strong><small id="wl-save" role="status" aria-live="polite">Opening your notebook…</small></div>
    </div>
    <div class="wl-stats" id="wl-stats" aria-label="Task counts"></div>
    <nav class="wl-tabs" aria-label="Notebook views">
        <button data-view="weeks" aria-pressed="true">01 / Weeks</button>
        <button data-view="today">02 / Today</button>
        <button data-view="inbox">03 / Inbox</button>
        <button data-view="review">04 / Review</button>
        <button data-view="sync">05 / Obsidian</button>
        <button data-view="trash">Archive bin</button>
    </nav>
    <form id="wl-capture" class="wl-capture">
        <label class="wl-grow"><span>Catch a thought. Give it a next action.</span><input id="wl-quick" required maxlength="20000" placeholder="What needs to happen?" autocomplete="off"></label>
        <label><span>Place it in</span><select id="wl-capture-week"><option value="">Inbox</option></select></label>
        <button type="submit" class="wl-primary">+ Capture task</button>
    </form>
    <div class="wl-tools" id="wl-tools">
        <label class="wl-grow"><span class="wl-sr">Search</span><input id="wl-search" type="search" placeholder="Find a task, area, or person…"></label>
        <label><span class="wl-sr">Status</span><select id="wl-filter"><option value="all">All states</option><option value="open">Open</option><option value="waiting">Waiting</option><option value="done">Done</option></select></label>
        <button id="wl-new-week">+ New week</button>
        <button id="wl-import">Import note</button>
        <button id="wl-refresh">Refresh</button>
    </div>
    <p id="wl-error" class="wl-error" role="alert" hidden></p>
    <div id="wl-content"></div>
    <p class="wl-footnote">Open: your next move. Waiting: someone else’s. Done: room for what matters.</p>
    <dialog id="wl-dialog"><form method="dialog"><button class="wl-close" aria-label="Close dialog">×</button></form><div id="wl-dialog-content"></div></dialog>
</section>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/admin.php';
