<?php

declare(strict_types=1);

namespace App\Model;

use PDO;

final class Quote
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /** @return list<array<string, mixed>> */
    public function findAll(int $limit, int $offset, string $sort = 'new', ?int $viewerUserId = null): array
    {
        // Resolve the viewer ID, defaulting to 0 if not provided
        $viewerId = $viewerUserId ?? 0;

        // Determine the ORDER BY clause based on the sort parameter
        $orderBy = match ($sort) {
            'top' => 'like_count DESC, q.created_at DESC',
            'trending' => 'trend_score DESC, q.created_at DESC',
            default => 'q.created_at DESC',
        };

        // The query fetches quotes and metadata (comment/like/trend counts, user_liked)
        // The stats subquery aggregates comment/like/trend data for each quote
        // The LEFT JOIN on quote_likes finds if the viewer has liked each quote
        $sql = "SELECT q.*,
                   stats.comment_count,
                   stats.like_count,
                   stats.trend_score,
                   CASE WHEN vl.id IS NOT NULL THEN 1 ELSE 0 END AS user_liked
            FROM quotes q
            INNER JOIN (
                SELECT qo.id,
                       (SELECT COUNT(*) FROM comments c WHERE c.quote_id = qo.id) AS comment_count,
                       (SELECT COUNT(*) FROM quote_likes ql WHERE ql.quote_id = qo.id) AS like_count,
                       (
                           (SELECT COUNT(*) FROM quote_likes ql2
                            WHERE ql2.quote_id = qo.id
                              AND ql2.created_at >= NOW() - INTERVAL 7 DAY)
                           + (SELECT COUNT(*) FROM comments c2
                              WHERE c2.quote_id = qo.id
                                AND c2.created_at >= NOW() - INTERVAL 7 DAY)
                       ) AS trend_score
                FROM quotes qo
            ) stats ON stats.id = q.id
            LEFT JOIN quote_likes vl ON vl.quote_id = q.id AND vl.user_id = :viewer_id
            ORDER BY {$orderBy}
            LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('viewer_id', $viewerId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findById(int $id, ?int $viewerUserId = null): ?array
    {
        $viewerId = $viewerUserId ?? 0;

        // Aggregates detailed information and metrics for a single quote,
        // including counts and whether the viewer has liked it
        $stmt = $this->db->prepare(
            'SELECT q.*,
                    stats.comment_count,
                    stats.like_count,
                    stats.trend_score,
                    CASE WHEN vl.id IS NOT NULL THEN 1 ELSE 0 END AS user_liked
             FROM quotes q
             INNER JOIN (
                 SELECT qo.id,
                        (SELECT COUNT(*) FROM comments c WHERE c.quote_id = qo.id) AS comment_count,
                        (SELECT COUNT(*) FROM quote_likes ql WHERE ql.quote_id = qo.id) AS like_count,
                        (
                            (SELECT COUNT(*) FROM quote_likes ql2
                             WHERE ql2.quote_id = qo.id
                               AND ql2.created_at >= NOW() - INTERVAL 7 DAY)
                            + (SELECT COUNT(*) FROM comments c2
                               WHERE c2.quote_id = qo.id
                                 AND c2.created_at >= NOW() - INTERVAL 7 DAY)
                        ) AS trend_score
                 FROM quotes qo
                 WHERE qo.id = :quote_id
             ) stats ON stats.id = q.id
             LEFT JOIN quote_likes vl ON vl.quote_id = q.id AND vl.user_id = :viewer_id
             WHERE q.id = :quote_id_lookup',
        );
        $stmt->execute(['quote_id' => $id, 'quote_id_lookup' => $id, 'viewer_id' => $viewerId]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM quotes')->fetchColumn();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO quotes (text, speaker, season, episode, image_path)
             VALUES (:text, :speaker, :season, :episode, :image_path)',
        );
        $stmt->execute([
            'text' => $data['text'],
            'speaker' => $data['speaker'],
            'season' => $data['season'] ?: null,
            'episode' => $data['episode'] ?: null,
            'image_path' => $data['image_path'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE quotes
             SET text = :text, speaker = :speaker, season = :season, episode = :episode,
                 image_path = :image_path
             WHERE id = :id',
        );

        return $stmt->execute([
            'id' => $id,
            'text' => $data['text'],
            'speaker' => $data['speaker'],
            'season' => $data['season'] ?: null,
            'episode' => $data['episode'] ?: null,
            'image_path' => $data['image_path'] ?? null,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM quotes WHERE id = :id');

        return $stmt->execute(['id' => $id]);
    }

    public static function normalizeSort(?string $sort): string
    {
        // Only allow certain sort values
        return match ($sort) {
            'top', 'trending' => $sort,
            default => 'new',
        };
    }
}
