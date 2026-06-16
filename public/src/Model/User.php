<?php

declare(strict_types=1);

namespace App\Model;

use PDO;

// Users in the database
final class User
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->execute(['username' => $username]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findAll(): array
    {
        $stmt = $this->db->query('SELECT id, username, is_admin, created_at FROM users ORDER BY created_at ASC');

        return $stmt->fetchAll();
    }

    public function create(string $username, string $passwordHash, bool $isAdmin = false): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, password_hash, is_admin) VALUES (:username, :password_hash, :is_admin)',
        );
        $stmt->execute([
            'username' => $username,
            'password_hash' => $passwordHash,
            'is_admin' => $isAdmin ? 1 : 0,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');

        return $stmt->execute(['id' => $id]);
    }

    public function setAdmin(int $id, bool $isAdmin): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET is_admin = :is_admin WHERE id = :id');

        return $stmt->execute([
            'id' => $id,
            'is_admin' => $isAdmin ? 1 : 0,
        ]);
    }

    public function countAdmins(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) FROM users WHERE is_admin = 1');

        return (int) $stmt->fetchColumn();
    }

    public function verifyPassword(array $user, string $password): bool
    {
        return password_verify($password, $user['password_hash']);
    }
}
