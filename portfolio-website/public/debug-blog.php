<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "<pre>";

require_once dirname(__DIR__) . '/app/config/database.php';
require_once dirname(__DIR__) . '/app/core/Database.php';

try {
    $db = \App\Core\Database::connect();

    // Fix: set published_at for any published posts that have it NULL
    $fix = $db->prepare("UPDATE blog_posts SET published_at = created_at WHERE status = 'published' AND published_at IS NULL");
    $fix->execute();
    $affected = $fix->rowCount();
    echo "Fixed $affected post(s) with NULL published_at\n\n";

    // Verify
    $stmt = $db->query("SELECT id, title, status, published_at, created_at FROM blog_posts ORDER BY created_at DESC");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "All posts:\n";
    foreach ($rows as $r) {
        echo "  id:{$r['id']} [{$r['status']}] published_at:{$r['published_at']} — {$r['title']}\n";
    }

    // Test the model query
    require_once dirname(__DIR__) . '/app/models/BaseModel.php';
    require_once dirname(__DIR__) . '/app/models/BlogPost.php';
    $m = new \App\Models\BlogPost();
    $posts = $m->getRecentPosts(3);
    echo "\ngetRecentPosts(3): " . count($posts) . " results\n";
    foreach ($posts as $p) {
        echo "  [{$p['id']}] {$p['title']}\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\nDone. Delete this file after confirming.\n";
echo "</pre>";
