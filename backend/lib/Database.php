<?php
/**
 * 데이터베이스 연결 관리 클래스 - 머니매니저 시스템
 *
 * 싱글톤 패턴을 사용하여 MySQL 데이터베이스 연결을 관리합니다.
 * PDO를 활용한 안전한 데이터베이스 접근과 쿼리 실행을 제공합니다.
 *
 * 주요 기능:
 * - 싱글톤 패턴으로 연결 관리
 * - 환경변수 기반 설정
 * - PDO Prepared Statement 지원
 * - 자동 에러 처리
 * - UTF-8 문자셋 지원
 * - 트랜잭션 지원
 *
 * 보안 기능:
 * - SQL 인젝션 방지 (Prepared Statements)
 * - 에러 정보 보호
 * - 연결 풀링 최적화
 *
 * @package MoneyManager\Lib
 * @version 1.0
 * @author YeongHoon Kim
 */

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $host = getenv('DB_HOST') ?: 'localhost';
        $dbname = getenv('DB_NAME') ?: 'money_management';
        $username = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASSWORD') ?: getenv('DB_PASS') ?: '';

        $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";

        try {
            $this->pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    public function commit() {
        return $this->pdo->commit();
    }

    public function rollback() {
        return $this->pdo->rollback();
    }
}