<?php

namespace Tests;

use Tests\Support\Models\Item;
use Tests\Support\Strategies\ComplexConfigQueryStrategy;

class TransmuteTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->simpleDatabase($this->app);
    }

    public function testConfigCanOverrideDefaultMultiClause(): void
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
