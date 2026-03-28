<?php

namespace Myerscode\Laravel\QueryStrategies;

use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends LengthAwarePaginator<int, mixed>
 */
class Paginated extends LengthAwarePaginator
{
    /** @var array<string, mixed>|null */
    protected ?array $appliedFilters = null;

    /**
     * Get query filters applied to the query
     *
     * @return array<string, mixed>|null
     */
    public function getAppliedFilters(): ?array
    {
        return $this->appliedFilters;
    }

    /**
     * Get the meta data of the paginated query
     *
     * @return array{count: int, firstItem: int|null, lastItem: int|null, total: int, hasMorePage: bool, currentPageUrl: string, previousPageUrl: string, nextPageUrl: string, currentPage: int, lastPage: int, perPage: int, appliedFilters: array<string, mixed>|null}
     */
    public function getMeta(): array
    {
        return [
            'count' => $this->count(),
            'firstItem' => $this->firstItem(),
            'lastItem' => $this->lastItem(),
            'total' => $this->total(),
            'hasMorePage' => $this->hasMorePages(),
            'currentPageUrl' => urldecode($this->url($this->currentPage())),
            'previousPageUrl' => urldecode((string) $this->previousPageUrl()),
            'nextPageUrl' => urldecode((string) $this->nextPageUrl()),
            'currentPage' => $this->currentPage(),
            'lastPage' => $this->lastPage(),
            'perPage' => $this->perPage(),
            'appliedFilters' => $this->getAppliedFilters(),
        ];
    }

    /**
     * The base path to assign to all URLs.
     */
    public function getPath(): string
    {
        return $this->path;
    }
}
