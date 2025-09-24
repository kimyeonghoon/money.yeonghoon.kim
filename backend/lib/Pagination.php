<?php

class Pagination {
    private $page;
    private $limit;
    private $total;
    private $offset;

    public function __construct($page = 1, $limit = 20, $total = 0) {
        $this->page = max(1, (int)$page);
        $this->limit = max(1, min(100, (int)$limit));
        $this->total = max(0, (int)$total);
        $this->offset = ($this->page - 1) * $this->limit;
    }

    public function getLimit() {
        return $this->limit;
    }

    public function getOffset() {
        return $this->offset;
    }

    public function getPage() {
        return $this->page;
    }

    public function getTotal() {
        return $this->total;
    }

    public function getTotalPages() {
        return $this->limit > 0 ? ceil($this->total / $this->limit) : 1;
    }

    public function hasNextPage() {
        return $this->page < $this->getTotalPages();
    }

    public function hasPreviousPage() {
        return $this->page > 1;
    }

    public function toArray() {
        return [
            'total' => $this->total,
            'page' => $this->page,
            'limit' => $this->limit,
            'pages' => $this->getTotalPages(),
            'has_next' => $this->hasNextPage(),
            'has_previous' => $this->hasPreviousPage()
        ];
    }

    public static function fromRequest($params, $defaultLimit = 20) {
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? $defaultLimit;

        return new self($page, $limit);
    }
}