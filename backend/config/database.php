<?php
/**
 * Database Configuration
 * 데이터베이스 설정 및 연결 관리
 */

class Database {
    private static $connection = null;

    /**
     * 데이터베이스 연결 가져오기
     */
    public static function getConnection() {
        if (self::$connection === null) {
            try {
                // 환경변수에서 DB 설정 읽기
                $useDockerMysql = getenv('USE_DOCKER_MYSQL') === 'true';

                if ($useDockerMysql) {
                    // Docker MySQL 사용
                    $host = getenv('DB_HOST') ?: 'mysql';
                    $port = getenv('DB_PORT') ?: '3306';
                    $dbname = getenv('DB_NAME') ?: 'money_management';
                    $username = getenv('DB_USER') ?: 'root';
                    $password = getenv('DB_PASSWORD') ?: '';
                } else {
                    // 외부 DB 사용
                    $host = getenv('PROD_DB_HOST');
                    $port = getenv('PROD_DB_PORT') ?: '3306';
                    $dbname = getenv('DB_NAME');
                    $username = getenv('PROD_DB_USER');
                    $password = getenv('PROD_DB_PASSWORD');
                }

                $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

                self::$connection = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ]);

            } catch (PDOException $e) {
                error_log('Database Connection Error: ' . $e->getMessage());
                throw new Exception('Database connection failed');
            }
        }

        return self::$connection;
    }

    /**
     * 데이터베이스 연결 테스트
     */
    public static function testConnection() {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->query('SELECT 1');
            return $stmt ? 'connected' : 'failed';
        } catch (Exception $e) {
            return 'failed: ' . $e->getMessage();
        }
    }

    /**
     * 쿼리 실행 (SELECT)
     */
    public static function query($sql, $params = []) {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Query Error: ' . $e->getMessage() . ' SQL: ' . $sql);
            throw new Exception('Query execution failed');
        }
    }

    /**
     * 쿼리 실행 (INSERT, UPDATE, DELETE)
     */
    public static function execute($sql, $params = []) {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute($params);
            return [
                'success' => $result,
                'affected_rows' => $stmt->rowCount(),
                'last_insert_id' => $pdo->lastInsertId()
            ];
        } catch (PDOException $e) {
            error_log('Execute Error: ' . $e->getMessage() . ' SQL: ' . $sql);
            throw new Exception('Query execution failed');
        }
    }
}
?>