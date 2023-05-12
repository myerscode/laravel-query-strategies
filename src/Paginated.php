<?php

namespace Myerscode\Laravel\QueryStrategies;

use Illuminate\Pagination\LengthAwarePaginator;

class Paginated extends LengthAwarePaginator
{

    /**
     * Get the meta data of the paginated query
     *
     * @return array{count: int, firstItem: int, lastItem: int, total: int, hasMorePage: bool, currentPageUrl: string, previousPageUrl: string, nextPageUrl: string, currentPage: int, lastPage: int, perPage: int, appliedFilters: mixed[]|null}
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
            'previousPageUrl' => urldecode($this->previousPageUrl()),
            'nextPageUrl' => urldecode($this->nextPageUrl()),
            'currentPage' => $this->currentPage(),
            'lastPage' => $this->lastPage(),
            'perPage' => $this->perPage(),
            'appliedFilters' => $this->getAppliedFilters(),
        ];
    }

    /**
     * Get query filters applied to the query
     */
    public function getAppliedFilters(): ?array
    {
        return $this->appliedFilters;
    }

    /**
     * The base path to assign to all URLs.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}
