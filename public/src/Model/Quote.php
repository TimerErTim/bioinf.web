<?php

declare(strict_types=1);

namespace App\Model;

use PDO;

// Read and save quotes from the database
final class Quote
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findAll(int $limit = 100, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            'SELECT q.*, COUNT(c.id) AS comment_count
             FROM quotes q
             LEFT JOIN comments c ON c.quote_id = q.id
             GROUP BY q.id
             ORDER BY q.created_at DESC
             LIMIT :limit OFFSET :offset',
        );
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM quotes')->fetchColumn();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM quotes WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO quotes (text, speaker, season, episode)
             VALUES (:text, :speaker, :season, :episode)',
        );
        $stmt->execute([
            'text' => $data['text'],
            'speaker' => $data['speaker'],
            'season' => $data['season'] ?: null,
            'episode' => $data['episode'] ?: null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE quotes
             SET text = :text, speaker = :speaker, season = :season, episode = :episode
             WHERE id = :id',
        );

        return $stmt->execute([
            'id' => $id,
            'text' => $data['text'],
            'speaker' => $data['speaker'],
            'season' => $data['season'] ?: null,
            'episode' => $data['episode'] ?: null,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM quotes WHERE id = :id');

        return $stmt->execute(['id' => $id]);
    }
}
