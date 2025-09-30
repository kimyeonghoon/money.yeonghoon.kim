<?php

class Database {
    private static $instance = null;
    private $pdo;

    /**
     * 환경변수 로드 (.env 파일에서)
     */
    private static function loadEnv() {
        static $loaded = false;
        if ($loaded) {
            return;
        }

        // 프로젝트 루트의 .env 파일 경로
        $envFile = __DIR__ . '/../../.env';

        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                // 주석 제거
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }

                // KEY=VALUE 파싱
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);

                    // 따옴표 제거
                    $value = trim($value, "'\"");

                    // 환경변수에 설정 (이미 없는 경우만)
                    if (!getenv($key)) {
                        putenv("$key=$value");
                    }
                }
            }
        }

        $loaded = true;
    }

    private function __construct() {
        // .env 파일 로드
        self::loadEnv();

        // 프로덕션 환경인 경우 PROD_ 접두사 사용
        $useDocker = getenv('USE_DOCKER_MYSQL');
        if ($useDocker === 'false') {
            $host = getenv('PROD_DB_HOST') ?: getenv('DB_HOST') ?: 'localhost';
            $dbname = getenv('PROD_DB_NAME') ?: getenv('DB_NAME') ?: 'money_management';
            $username = getenv('PROD_DB_USER') ?: getenv('DB_USER') ?: 'root';
            $password = getenv('PROD_DB_PASSWORD') ?: getenv('DB_PASSWORD') ?: '';
        } else {
            $host = getenv('DB_HOST') ?: 'localhost';
            $dbname = getenv('DB_NAME') ?: 'money_management';
            $username = getenv('DB_USER') ?: 'root';
            $password = getenv('DB_PASSWORD') ?: getenv('DB_PASS') ?: '';
        }

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