<?php

declare(strict_types=1);

namespace UrlShortener\Storage;

use PDO;
use PDOException;
use UrlShortener\Entity\UrlRecord;
use UrlShortener\Entity\AnalyticsRecord;
use UrlShortener\Exception\UrlShortenerException;
use DateTimeImmutable;

/**
 * Database storage implementation using PDO
 */
class DatabaseStorage implements StorageInterface
{
    public function __construct(private PDO $pdo)
    {
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->createTablesIfNotExists();
    }

    public function store(UrlRecord $record): bool
    {
        try {
            $sql = "INSERT INTO urls (short_code, original_url, created_at, expires_at, click_count, metadata) 
                    VALUES (:short_code, :original_url, :created_at, :expires_at, :click_count, :metadata)
                    ON DUPLICATE KEY UPDATE 
                    original_url = VALUES(original_url),
                    expires_at = VALUES(expires_at),
                    metadata = VALUES(metadata)";

            $stmt = $this->pdo->prepare($sql);
            
            return $stmt->execute([
                ':short_code' => $record->getShortCode(),
                ':original_url' => $record->getOriginalUrl(),
                ':created_at' => $record->getCreatedAt()->format('Y-m-d H:i:s'),
                ':expires_at' => $record->getExpiresAt()?->format('Y-m-d H:i:s'),
                ':click_count' => $record->getClickCount(),
                ':metadata' => json_encode($record->getMetadata()),
            ]);
        } catch (PDOException $e) {
            throw new UrlShortenerException("Database error: " . $e->getMessage(), 0, $e);
        }
    }

    public function retrieve(string $shortCode): ?UrlRecord
    {
        try {
            $sql = "SELECT * FROM urls WHERE short_code = :short_code";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':short_code' => $shortCode]);
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$row) {
                return null;
            }

            return UrlRecord::fromArray([
                'short_code' => $row['short_code'],
                'original_url' => $row['original_url'],
                'created_at' => $row['created_at'],
                'expires_at' => $row['expires_at'],
                'click_count' => (int) $row['click_count'],
                'metadata' => json_decode($row['metadata'] ?? '[]', true),
            ]);
        } catch (PDOException $e) {
            throw new UrlShortenerException("Database error: " . $e->getMessage(), 0, $e);
        }
    }

    public function exists(string $shortCode): bool
    {
        try {
            $sql = "SELECT 1 FROM urls WHERE short_code = :short_code";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':short_code' => $shortCode]);
            
            return $stmt->fetchColumn() !== false;
        } catch (PDOException $e) {
            throw new UrlShortenerException("Database error: " . $e->getMessage(), 0, $e);
        }
    }

    public function delete(string $shortCode): bool
    {
        try {
            $this->pdo->beginTransaction();

            // Delete analytics first (foreign key constraint)
            $analyticsStmt = $this->pdo->prepare("DELETE FROM analytics WHERE short_code = :short_code");
            $analyticsStmt->execute([':short_code' => $shortCode]);

            // Delete URL
            $urlStmt = $this->pdo->prepare("DELETE FROM urls WHERE short_code = :short_code");
            $result = $urlStmt->execute([':short_code' => $shortCode]);

            $this->pdo->commit();
            
            return $result && $urlStmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new UrlShortenerException("Database error: " . $e->getMessage(), 0, $e);
        }
    }

    public function storeAnalytics(AnalyticsRecord $record): bool
    {
        try {
            $sql = "INSERT INTO analytics (short_code, clicked_at, ip_address, user_agent, referrer, additional_data) 
                    VALUES (:short_code, :clicked_at, :ip_address, :user_agent, :referrer, :additional_data)";

            $stmt = $this->pdo->prepare($sql);
            
            return $stmt->execute([
                ':short_code' => $record->getShortCode(),
                ':clicked_at' => $record->getClickedAt()->format('Y-m-d H:i:s'),
                ':ip_address' => $record->getIpAddress(),
                ':user_agent' => $record->getUserAgent(),
                ':referrer' => $record->getReferrer(),
                ':additional_data' => json_encode($record->getAdditionalData()),
            ]);
        } catch (PDOException $e) {
            throw new UrlShortenerException("Database error: " . $e->getMessage(), 0, $e);
        }
    }

    public function getAnalytics(string $shortCode): array
    {
        try {
            $sql = "SELECT * FROM analytics WHERE short_code = :short_code ORDER BY clicked_at DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':short_code' => $shortCode]);
            
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(function (array $row): AnalyticsRecord {
                return AnalyticsRecord::fromArray([
                    'short_code' => $row['short_code'],
                    'clicked_at' => $row['clicked_at'],
                    'ip_address' => $row['ip_address'],
                    'user_agent' => $row['user_agent'],
                    'referrer' => $row['referrer'],
                    'additional_data' => json_decode($row['additional_data'] ?? '[]', true),
                ]);
            }, $rows);
        } catch (PDOException $e) {
            throw new UrlShortenerException("Database error: " . $e->getMessage(), 0, $e);
        }
    }

    private function createTablesIfNotExists(): void
    {
        $sqlUrls = "
            CREATE TABLE IF NOT EXISTS urls (
                short_code VARCHAR(255) PRIMARY KEY,
                original_url TEXT NOT NULL,
                created_at DATETIME NOT NULL,
                expires_at DATETIME NULL,
                click_count INT UNSIGNED NOT NULL DEFAULT 0,
                metadata JSON NULL,
                INDEX idx_created_at (created_at),
                INDEX idx_expires_at (expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";

        $sqlAnalytics = "
            CREATE TABLE IF NOT EXISTS analytics (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                short_code VARCHAR(255) NOT NULL,
                clicked_at DATETIME NOT NULL,
                ip_address VARCHAR(45) NULL,
                user_agent TEXT NULL,
                referrer TEXT NULL,
                additional_data JSON NULL,
                INDEX idx_short_code (short_code),
                INDEX idx_clicked_at (clicked_at),
                FOREIGN KEY (short_code) REFERENCES urls(short_code) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";

        try {
            $this->pdo->exec($sqlUrls);
            $this->pdo->exec($sqlAnalytics);
        } catch (PDOException $e) {
            throw new UrlShortenerException("Cannot create database tables: " . $e->getMessage(), 0, $e);
        }
    }
}
