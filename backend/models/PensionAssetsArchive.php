<?php

require_once __DIR__ . '/BaseModel.php';

class PensionAssetsArchive extends BaseModel {
    protected $table = 'pension_assets_archive';
    protected $fillable = [
        'snapshot_month',
        'type',
        'account_name',
        'item_name',
        'current_value',
        'deposit_amount',
        'display_order'
    ];

    public function getByMonth($year, $month) {
        $snapshotMonth = sprintf('%04d-%02d-01', $year, $month);
        $sql = "SELECT * FROM {$this->table} WHERE snapshot_month = ? ORDER BY display_order";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$snapshotMonth]);
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function update($id, $data) {
        $filteredData = $this->filterFillable($data);
        $setPairs = [];

        foreach ($filteredData as $column => $value) {
            $setPairs[] = "{$column} = :{$column}";
        }

        $setClause = implode(', ', $setPairs);
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE id = :id";

        $filteredData['id'] = $id;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($filteredData);

        return $stmt->rowCount() > 0;
    }
}