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

    /** @return list<array<string, mixed>> */
    public function findByQuoteIdWithVotes(int $quoteId, ?int $viewerUserId = null): array
    {
        $viewerId = $viewerUserId ?? 0;

        $stmt = $this->db->prepare(
            'SELECT c.*, u.username, u.avatar_path,
                    (SELECT COALESCE(SUM(cv.vote), 0) FROM comment_votes cv WHERE cv.comment_id = c.id) AS score,
                    (SELECT COUNT(*) FROM comment_votes cv WHERE cv.comment_id = c.id AND cv.vote = 1) AS upvotes,
                    (SELECT COUNT(*) FROM comment_votes cv WHERE cv.comment_id = c.id AND cv.vote = -1) AS downvotes,
                    (SELECT cv.vote FROM comment_votes cv
                     WHERE cv.comment_id = c.id AND cv.user_id = :viewer_id LIMIT 1) AS user_vote,
                    (
                        (SELECT COUNT(*) FROM comment_votes cv2
                         WHERE cv2.comment_id = c.id
                           AND cv2.created_at >= NOW() - INTERVAL 7 DAY)
                        + (SELECT COUNT(*) FROM comments ch
                           WHERE ch.parent_id = c.id
                             AND ch.created_at >= NOW() - INTERVAL 7 DAY)
                    ) AS trend_score
             FROM comments c
             LEFT JOIN users u ON u.id = c.user_id
             WHERE c.quote_id = :quote_id',
        );
        $stmt->execute(['quote_id' => $quoteId, 'viewer_id' => $viewerId]);

        return $stmt->fetchAll();
    }

    /** @return list<array<string, mixed>> */
    public function buildTree(int $quoteId, string $sort = 'new', ?int $viewerUserId = null): array
    {
        return self::nestAndSort($this->findByQuoteIdWithVotes($quoteId, $viewerUserId), null, $sort);
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
            'SELECT c.*, q.text AS quote_text, q.speaker AS quote_speaker,
                    (SELECT COALESCE(SUM(cv.vote), 0) FROM comment_votes cv WHERE cv.comment_id = c.id) AS score,
                    (SELECT COUNT(*) FROM comment_votes cv WHERE cv.comment_id = c.id AND cv.vote = 1) AS upvotes,
                    (SELECT COUNT(*) FROM comment_votes cv WHERE cv.comment_id = c.id AND cv.vote = -1) AS downvotes
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

    public function totalScoreByUserId(int $userId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COALESCE(SUM(cv.vote), 0)
             FROM comment_votes cv
             INNER JOIN comments c ON c.id = cv.comment_id
             WHERE c.user_id = :user_id',
        );
        $stmt->execute(['user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    public static function normalizeSort(?string $sort): string
    {
        return match ($sort) {
            'top', 'trending' => $sort,
            default => 'new',
        };
    }

    /**
     * @param list<array<string, mixed>> $flat
     * @return list<array<string, mixed>>
     */
    private static function nestAndSort(array $flat, ?int $parentId, string $sort): array
    {
        $siblings = [];
        foreach ($flat as $comment) {
            $pid = $comment['parent_id'] !== null ? (int) $comment['parent_id'] : null;
            if ($pid === $parentId) {
                $siblings[] = $comment;
            }
        }

        $siblings = self::sortSiblings($siblings, $sort);

        foreach ($siblings as &$comment) {
            $comment['children'] = self::nestAndSort($flat, (int) $comment['id'], $sort);
        }

        return $siblings;
    }

    /**
     * @param list<array<string, mixed>> $siblings
     * @return list<array<string, mixed>>
     */
    private static function sortSiblings(array $siblings, string $sort): array
    {
        usort($siblings, static function (array $a, array $b) use ($sort): int {
            return match ($sort) {
                'top' => ((int) $b['score'] <=> (int) $a['score'])
                    ?: (strtotime((string) $b['created_at']) <=> strtotime((string) $a['created_at'])),
                'trending' => ((int) $b['trend_score'] <=> (int) $a['trend_score'])
                    ?: ((int) $b['score'] <=> (int) $a['score']),
                default => strtotime((string) $b['created_at']) <=> strtotime((string) $a['created_at']),
            };
        });

        return $siblings;
    }
}
