<?php
/**
 * Seed Project Categories
 * Run this once to populate the project_categories table
 */

// Set HTTP_HOST for CLI usage
$_SERVER['HTTP_HOST'] = 'localhost';

require_once __DIR__ . '/app/core/Database.php';

use App\Core\Database;

echo "Seeding Project Categories...\n\n";

try {
    $db = Database::connect();

    if (!$db) {
        throw new Exception("Failed to connect to database. Check your database credentials.");
    }

    // Check if categories already exist
    $stmt = $db->query("SELECT COUNT(*) FROM project_categories");
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo "Categories already exist ($count found). Skipping seed.\n";
        echo "Delete existing categories first if you want to re-seed.\n";
        exit;
    }

    // Project categories to seed
    $categories = [
        [
            'name' => 'Web Development',
            'slug' => 'web-dev',
            'description' => 'Web applications, websites, and web-based solutions'
        ],
        [
            'name' => 'App Development',
            'slug' => 'app-dev',
            'description' => 'Mobile and desktop applications'
        ],
        [
            'name' => 'Game Development',
            'slug' => 'game-dev',
            'description' => 'Games and interactive experiences'
        ],
        [
            'name' => 'Digital Design',
            'slug' => 'digital-design',
            'description' => '3D modeling, animation, and digital art'
        ],
        [
            'name' => 'Maker Projects',
            'slug' => 'maker',
            'description' => 'Hardware projects, IoT, and physical computing'
        ]
    ];

    $sql = "INSERT INTO project_categories (name, slug, description, created_at, updated_at)
            VALUES (:name, :slug, :description, NOW(), NOW())";

    $stmt = $db->prepare($sql);

    foreach ($categories as $category) {
        $stmt->execute([
            ':name' => $category['name'],
            ':slug' => $category['slug'],
            ':description' => $category['description']
        ]);
        echo "✓ Created category: {$category['name']} ({$category['slug']})\n";
    }

    echo "\n✅ Successfully seeded " . count($categories) . " project categories!\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
