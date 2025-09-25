<?php

require_once __DIR__ . '/../lib/Database.php';

abstract class BaseModel {
    protected $db;
    protected $table;
    protected $fillable = [];
    protected $defaults = [];

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findAll($limit = null, $offset = null) {
        $sql = "SELECT * FROM {$this->table} WHERE deleted_at IS NULL ORDER BY created_at DESC";

        if ($limit) {
            $sql .= " LIMIT {$limit}";
            if ($offset) {
                $sql .= " OFFSET {$offset}";
            }
        }

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? AND deleted_at IS NULL";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        // 기본값과 입력 데이터 병합
        $dataWithDefaults = array_merge($this->defaults, $data);
        $filteredData = $this->filterFillable($dataWithDefaults);
        $columns = implode(',', array_keys($filteredData));
        $placeholders = ':' . implode(', :', array_keys($filteredData));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";

        $this->db->query($sql, $filteredData);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $filteredData = $this->filterFillable($data);
        $setPairs = [];

        foreach ($filteredData as $column => $value) {
            $setPairs[] = "{$column} = :{$column}";
        }

        $setClause = implode(', ', $setPairs);
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE id = :id AND deleted_at IS NULL";

        $filteredData['id'] = $id;
        $stmt = $this->db->query($sql, $filteredData);

        return $stmt->rowCount() > 0;
    }

    public function softDelete($id) {
        $sql = "UPDATE {$this->table} SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->rowCount() > 0;
    }

    public function restore($id) {
        $sql = "UPDATE {$this->table} SET deleted_at = NULL WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->rowCount() > 0;
    }

    public function forceDelete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->rowCount() > 0;
    }

    public function getDeleted($limit = null, $offset = null) {
        $sql = "SELECT * FROM {$this->table} WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC";

        if ($limit) {
            $sql .= " LIMIT {$limit}";
            if ($offset) {
                $sql .= " OFFSET {$offset}";
            }
        }

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function count($includeDeleted = false) {
        $whereClause = $includeDeleted ? '' : 'WHERE deleted_at IS NULL';
        $sql = "SELECT COUNT(*) as count FROM {$this->table} {$whereClause}";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['count'];
    }

    protected function filterFillable($data) {
        if (empty($this->fillable)) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }

    public function exists($id) {
        $sql = "SELECT 1 FROM {$this->table} WHERE id = ? AND deleted_at IS NULL";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch() !== false;
    }

    // PATCH용 부분 업데이트 메서드
    public function partialUpdate($id, $data) {
        // 빈 필드는 제외하고 업데이트
        $filteredData = array_filter($this->filterFillable($data), function($value) {
            return $value !== null && $value !== '';
        });

        if (empty($filteredData)) {
            return false;
        }

        $setPairs = [];
        foreach ($filteredData as $column => $value) {
            $setPairs[] = "{$column} = :{$column}";
        }

        $setClause = implode(', ', $setPairs);
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE id = :id AND deleted_at IS NULL";

        $filteredData['id'] = $id;
        $stmt = $this->db->query($sql, $filteredData);

        return $stmt->rowCount() > 0;
    }

    // 별칭 메서드들 추가
    public function getAll($limit = null, $offset = null) {
        return $this->findAll($limit, $offset);
    }

    public function getById($id) {
        return $this->findById($id);
    }
}