<?php

namespace Myerscode\Laravel\QueryStrategies;

use Illuminate\Pagination\LengthAwarePaginator;

class Paginated extends LengthAwarePaginator
{

    /**
     * Get the meta data of the paginated query
     *
     * @return array
     */
    public function getMeta()
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
        ];
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
