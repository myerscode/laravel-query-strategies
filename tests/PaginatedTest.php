<?php

declare(strict_types=1);

namespace Tests;

use Tests\Support\Models\Item;
use Tests\Support\Strategies\BasicConfigQueryStrategy;

final class PaginatedTest extends TestCase
{
    private array $metaShape = [
        'count',
        'firstItem',
        'lastItem',
        'total',
        'hasMorePage',
        'currentPageUrl',
        'previousPageUrl',
        'nextPageUrl',
        'currentPage',
        'lastPage',
        'perPage',
        'appliedFilters',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->simpleDatabase($this->app);
    }

    public function test_get_meta(): void
    {
        $filter = $this->filter(Item::query(), BasicConfigQueryStrategy::class, []);

        $paginated = $filter->paginate();

        $this->assertEquals($this->metaShape, array_keys($paginated->getMeta()));
    }
}
