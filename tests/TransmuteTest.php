<?php

declare(strict_types=1);

namespace Tests;

use Tests\Support\Models\Item;
use Tests\Support\Strategies\ComplexConfigQueryStrategy;

final class TransmuteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->simpleDatabase($this->app);
    }

    public function test_config_can_override_default_multi_clause(): void
    {
        $strategy = $this->strategyManager()->findStrategy(ComplexConfigQueryStrategy::class);
        $distill = $this->filter(Item::query(), $strategy, ['transmute_me' => 'no']);
        $distill->apply();

        $builder = $distill->builder();
        $expectedSql = 'select * from "items" where "transmute_me" = \'0\' limit 50';
        $this->assertSame($expectedSql, $this->getRawSqlFromBuilder($builder));
        $distill = $this->filter(Item::query(), $strategy, ['transmute_me' => 'yes']);
        $distill->apply();

        $builder = $distill->builder();
        $expectedSql = 'select * from "items" where "transmute_me" = \'1\' limit 50';
        $this->assertSame($expectedSql, $this->getRawSqlFromBuilder($builder));
    }
}
