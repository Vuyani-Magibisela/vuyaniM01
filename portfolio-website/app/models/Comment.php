<?php

namespace App\Models;

class Comment extends BaseModel {

    private $table = 'blog_comments';

    /**
     * Fetch approved comments for a post (public view), oldest first.
     */
    public function getApproved($postId) {
        if (!$this->isConnected()) {
            return [];
        }

        return $this->query(
            "SELECT id, author_name, content, created_at
             FROM {$this->table}
             WHERE post_id = :post_id AND status = 'approved'
             ORDER BY created_at ASC",
            ['post_id' => (int)$postId]
        );
    }

    /**
     * Fetch all comments for a post regardless of status (admin view).
     */
    public function getAllForAdmin($postId) {
        if (!$this->isConnected()) {
            return [];
        }

        return $this->query(
            "SELECT id, author_name, author_email, content, status, ip_address, created_at
             FROM {$this->table}
             WHERE post_id = :post_id
             ORDER BY created_at DESC",
            ['post_id' => (int)$postId]
        );
    }

    /**
     * Count comments for a post filtered by status.
     */
    public function getCount($postId, $status = 'approved') {
        if (!$this->isConnected()) {
            return 0;
        }

        $row = $this->query(
            "SELECT COUNT(*) as cnt FROM {$this->table}
             WHERE post_id = :post_id AND status = :status",
            ['post_id' => (int)$postId, 'status' => $status],
            false
        );

        return (int)($row['cnt'] ?? 0);
    }

    /**
     * Count recent comments from an IP within a time window (spam rate-limit).
     */
    public function countByIp($ip, $windowSeconds = 300) {
        if (!$this->isConnected()) {
            return 0;
        }

        $row = $this->query(
            "SELECT COUNT(*) as cnt FROM {$this->table}
             WHERE ip_address = :ip
               AND created_at >= DATE_SUB(NOW(), INTERVAL :secs SECOND)",
            ['ip' => $ip, 'secs' => (int)$windowSeconds],
            false
        );

        return (int)($row['cnt'] ?? 0);
    }

    /**
     * Insert a new comment (always starts as 'pending').
     * Returns new comment ID or false on failure.
     */
    public function createComment($data) {
        if (!$this->isConnected()) {
            return false;
        }

        return $this->create($this->table, [
            'post_id'      => (int)$data['post_id'],
            'author_name'  => $data['author_name'],
            'author_email' => $data['author_email'],
            'content'      => $data['content'],
            'status'       => 'pending',
            'ip_address'   => $data['ip_address'],
            'user_agent'   => $data['user_agent'] ?? '',
        ]);
    }

    /**
     * Update comment status (approved / spam).
     */
    public function updateStatus($id, $status) {
        if (!$this->isConnected()) {
            return false;
        }

        $allowed = ['pending', 'approved', 'spam'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        return $this->update($this->table, (int)$id, ['status' => $status]);
    }

    /**
     * Hard-delete a comment by ID.
     */
    public function deleteComment($id) {
        if (!$this->isConnected()) {
            return false;
        }

        return $this->delete($this->table, (int)$id);
    }

    /**
     * Get pending comment count across all posts (for sidebar badge).
     */
    public function getTotalPending() {
        if (!$this->isConnected()) {
            return 0;
        }

        $row = $this->query(
            "SELECT COUNT(*) as cnt FROM {$this->table} WHERE status = 'pending'",
            [],
            false
        );

        return (int)($row['cnt'] ?? 0);
    }
}
