<?php

namespace App\Service;

class PaginationService
{
    private int $currentPage = 1;
    private int $limit = 10;
    private int $total = 0;

    public function paginate(array $data, int $page, int $limit): array
    {
        $this->currentPage = max(1, $page);
        $this->limit       = $limit;
        $this->total       = count($data);

        $offset = ($this->currentPage - 1) * $this->limit;

        return array_slice($data, $offset, $this->limit);
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getTotalPages(): int
    {
        return (int) ceil($this->total / $this->limit);
    }

    public function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }

    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->getTotalPages();
    }
}
