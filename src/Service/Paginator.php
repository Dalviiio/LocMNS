<?php

namespace App\Service;

final class Paginator
{
    public readonly int $nbPages;
    public readonly int $offset;

    public function __construct(
        public readonly int $total,
        public readonly int $page,
        public readonly int $perPage,
    ) {
        $this->nbPages = max(1, (int) ceil($total / $perPage));
        $this->offset  = ($page - 1) * $perPage;
    }

    public function hasPrev(): bool { return $this->page > 1; }
    public function hasNext(): bool { return $this->page < $this->nbPages; }

    /** Plage de numéros de pages à afficher (courant ±3) */
    public function pages(): array
    {
        $start = max(1, $this->page - 3);
        $end   = min($this->nbPages, $this->page + 3);
        return range($start, $end);
    }

    /** Construit un Paginator depuis la Request */
    public static function fromRequest(\Symfony\Component\HttpFoundation\Request $request, int $total): self
    {
        $page    = max(1, $request->query->getInt('page', 1));
        $perPage = $request->query->getInt('per_page', 25);
        if (!in_array($perPage, [25, 50, 100, 200], true)) {
            $perPage = 25;
        }
        return new self($total, $page, $perPage);
    }
}
