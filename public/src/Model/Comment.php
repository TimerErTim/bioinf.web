<?php

declare(strict_types=1);

namespace App\Model;

use PDO;

/*
 * Comment table access.
 *
 * LEFT JOIN users: keep comments even when user_id is NULL (deleted author).
 * username comes from join; NULL username means show <deleted> in the view.
 */
final class Comment
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findByQuoteId(int $quoteId): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, u.username
             FROM comments c
             LEFT JOIN users u ON u.id = c.user_id
             WHERE c.quote_id = :quote_id
             ORDER BY c.created_at ASC',
        );
        $stmt->execute(['quote_id' => $quoteId]);

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, u.username
             FROM comments c
             LEFT JOIN users u ON u.id = c.user_id
             WHERE c.id = :id',
        );
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(int $quoteId, int $userId, string $content): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO comments (quote_id, user_id, content) VALUES (:quote_id, :user_id, :content)',
        );
        $stmt->execute([
            'quote_id' => $quoteId,
            'user_id' => $userId,
            'content' => $content,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $content): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE comments SET content = :content, updated_at = NOW() WHERE id = :id',
        );

        return $stmt->execute(['id' => $id, 'content' => $content]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM comments WHERE id = :id');

        return $stmt->execute(['id' => $id]);
    }
}
