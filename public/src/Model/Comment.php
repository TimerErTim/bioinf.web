<?php

declare(strict_types=1);

namespace App\Model;

use PDO;

/*
 * Comment table access with threaded replies (parent_id self-FK).
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
            'SELECT c.*, u.username, u.avatar_path
             FROM comments c
             LEFT JOIN users u ON u.id = c.user_id
             WHERE c.quote_id = :quote_id
             ORDER BY c.created_at ASC',
        );
        $stmt->execute(['quote_id' => $quoteId]);

        return $stmt->fetchAll();
    }

    /** @return list<array<string, mixed>> */
    public function buildTree(int $quoteId): array
    {
        return self::nestComments($this->findByQuoteId($quoteId));
    }

    public function countByQuoteId(int $quoteId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM comments WHERE quote_id = :quote_id');
        $stmt->execute(['quote_id' => $quoteId]);

        return (int) $stmt->fetchColumn();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, u.username, u.avatar_path
             FROM comments c
             LEFT JOIN users u ON u.id = c.user_id
             WHERE c.id = :id',
        );
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(int $quoteId, int $userId, string $content, ?int $parentId = null): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO comments (quote_id, user_id, parent_id, content)
             VALUES (:quote_id, :user_id, :parent_id, :content)',
        );
        $stmt->execute([
            'quote_id' => $quoteId,
            'user_id' => $userId,
            'parent_id' => $parentId,
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

    /** @return list<array<string, mixed>> */
    public function findByUserId(int $userId, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, q.text AS quote_text, q.speaker AS quote_speaker
             FROM comments c
             INNER JOIN quotes q ON q.id = c.quote_id
             WHERE c.user_id = :user_id
             ORDER BY c.created_at DESC
             LIMIT :limit',
        );
        $stmt->bindValue('user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @param list<array<string, mixed>> $flat
     * @return list<array<string, mixed>>
     */
    private static function nestComments(array $flat, ?int $parentId = null): array
    {
        $branch = [];
        foreach ($flat as $comment) {
            $pid = $comment['parent_id'] !== null ? (int) $comment['parent_id'] : null;
            if ($pid === $parentId) {
                $comment['children'] = self::nestComments($flat, (int) $comment['id']);
                $branch[] = $comment;
            }
        }

        return $branch;
    }
}
