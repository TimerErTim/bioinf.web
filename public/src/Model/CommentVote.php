<?php

declare(strict_types=1);

namespace App\Model;

use PDO;

final class CommentVote
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function setVote(int $userId, int $commentId, int $vote): bool
    {
        // Allow only upvotes (+1) or downvotes (-1)
        if ($vote !== 1 && $vote !== -1) {
            return false;
        }

        // Insert a new vote or update existing one; update timestamp when updating
        $stmt = $this->db->prepare(
            'INSERT INTO comment_votes (user_id, comment_id, vote)
             VALUES (:user_id, :comment_id, :vote)
             ON DUPLICATE KEY UPDATE vote = VALUES(vote), created_at = CURRENT_TIMESTAMP',
        );

        return $stmt->execute([
            'user_id' => $userId,
            'comment_id' => $commentId,
            'vote' => $vote,
        ]);
    }

    public function removeVote(int $userId, int $commentId): bool
    {
        // Delete user's vote for a specific comment
        $stmt = $this->db->prepare(
            'DELETE FROM comment_votes WHERE user_id = :user_id AND comment_id = :comment_id',
        );

        return $stmt->execute(['user_id' => $userId, 'comment_id' => $commentId]);
    }
}
