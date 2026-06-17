<?php

declare(strict_types=1);

namespace App\Model;

use PDO;

final class QuoteLike
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function like(int $userId, int $quoteId): bool
    {
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO quote_likes (user_id, quote_id) VALUES (:user_id, :quote_id)',
        );

        return $stmt->execute(['user_id' => $userId, 'quote_id' => $quoteId]);
    }

    public function unlike(int $userId, int $quoteId): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM quote_likes WHERE user_id = :user_id AND quote_id = :quote_id',
        );

        return $stmt->execute(['user_id' => $userId, 'quote_id' => $quoteId]);
    }

    public function hasLiked(int $userId, int $quoteId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM quote_likes WHERE user_id = :user_id AND quote_id = :quote_id',
        );
        $stmt->execute(['user_id' => $userId, 'quote_id' => $quoteId]);

        return (bool) $stmt->fetchColumn();
    }

    public function countByUserId(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM quote_likes WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    /** @return list<array<string, mixed>> */
    public function findQuotesByUserId(int $userId, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            'SELECT q.*, ql.created_at AS liked_at,
                    (SELECT COUNT(*) FROM comments c WHERE c.quote_id = q.id) AS comment_count,
                    (SELECT COUNT(*) FROM quote_likes ql2 WHERE ql2.quote_id = q.id) AS like_count
             FROM quote_likes ql
             INNER JOIN quotes q ON q.id = ql.quote_id
             WHERE ql.user_id = :user_id
             ORDER BY ql.created_at DESC
             LIMIT :limit',
        );
        $stmt->bindValue('user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
