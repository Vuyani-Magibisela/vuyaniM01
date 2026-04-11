<?php

namespace App\Models;

class Reaction extends BaseModel {

    private $table = 'blog_reactions';

    private $allowed = ['like', 'love', 'fire', 'clap', 'wow'];

    public function isAllowed($emoji) {
        return in_array($emoji, $this->allowed, true);
    }

    /**
     * Get all reaction counts for a post keyed by emoji.
     * Returns e.g. ['like' => 3, 'love' => 1, ...]
     */
    public function getCounts($postId) {
        if (!$this->isConnected()) {
            return [];
        }

        $rows = $this->query(
            "SELECT emoji, COUNT(*) as cnt FROM {$this->table}
             WHERE post_id = :post_id GROUP BY emoji",
            ['post_id' => (int)$postId]
        );

        $counts = [];
        foreach ($this->allowed as $e) {
            $counts[$e] = 0;
        }
        foreach ($rows as $row) {
            $counts[$row['emoji']] = (int)$row['cnt'];
        }
        return $counts;
    }

    /**
     * Toggle a reaction. Returns ['action' => 'added'|'removed', 'count' => N].
     */
    public function toggle($postId, $emoji, $ipHash) {
        if (!$this->isConnected()) {
            return ['action' => 'error', 'count' => 0];
        }

        $postId = (int)$postId;

        // Check if it already exists
        $exists = $this->query(
            "SELECT id FROM {$this->table}
             WHERE post_id = :post_id AND emoji = :emoji AND ip_hash = :ip_hash",
            ['post_id' => $postId, 'emoji' => $emoji, 'ip_hash' => $ipHash],
            false
        );

        if ($exists) {
            $this->query(
                "DELETE FROM {$this->table}
                 WHERE post_id = :post_id AND emoji = :emoji AND ip_hash = :ip_hash",
                ['post_id' => $postId, 'emoji' => $emoji, 'ip_hash' => $ipHash]
            );
            $action = 'removed';
        } else {
            // INSERT IGNORE handles any race-condition duplicate
            $this->query(
                "INSERT IGNORE INTO {$this->table} (post_id, emoji, ip_hash)
                 VALUES (:post_id, :emoji, :ip_hash)",
                ['post_id' => $postId, 'emoji' => $emoji, 'ip_hash' => $ipHash]
            );
            $action = 'added';
        }

        $row = $this->query(
            "SELECT COUNT(*) as cnt FROM {$this->table}
             WHERE post_id = :post_id AND emoji = :emoji",
            ['post_id' => $postId, 'emoji' => $emoji],
            false
        );

        return ['action' => $action, 'count' => (int)($row['cnt'] ?? 0)];
    }

    /**
     * Get all reactions for multiple posts (used in admin postStats).
     * Returns ['like' => N, 'love' => N, ...] for a single post.
     */
    public function getTotalForPost($postId) {
        return $this->getCounts($postId);
    }
}
