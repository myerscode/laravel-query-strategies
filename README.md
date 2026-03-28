# Laravel Query Strategies

Build safe, flexible API endpoints by turning URL query parameters into Eloquent queries. Define a strategy that controls exactly which filters, sorts, fields, and relationships your API consumers can use.

[![Latest Stable Version](https://poser.pugx.org/myerscode/laravel-query-strategies/v/stable)](https://packagist.org/packages/myerscode/laravel-query-strategies)
[![Total Downloads](https://poser.pugx.org/myerscode/laravel-query-strategies/downloads)](https://packagist.org/packages/myerscode/laravel-query-strategies)
[![PHP Version Require](http://poser.pugx.org/myerscode/laravel-query-strategies/require/php)](https://packagist.org/packages/myerscode/laravel-query-strategies)
[![License](https://poser.pugx.org/myerscode/laravel-query-strategies/license)](https://github.com/myerscode/laravel-query-strategies/blob/main/LICENSE)
[![Tests](https://github.com/myerscode/laravel-query-strategies/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/myerscode/laravel-query-strategies/actions/workflows/tests.yml)
[![codecov](https://codecov.io/gh/myerscode/laravel-query-strategies/graph/badge.svg)](https://codecov.io/gh/myerscode/laravel-query-strategies)

## Requirements

- PHP ^8.5
- Laravel ^13.0

## Quick Example

Given a `ProductStrategy` that defines which parameters are allowed:

```php
class ProductStrategy extends Strategy
{
    protected array $canOrderBy = ['name', 'price', 'created_at'];
    protected array $canWith = ['category', 'reviews'];
    protected array $allowedFields = ['id', 'name', 'price', 'category_id'];
    protected array $allowedAppends = ['discount_price'];

    protected array $config = [
        'name'     => ['filter' => ContainsClause::class],
        'price'    => ['column' => 'unit_price'],
        'category' => ['column' => 'category_id', 'explode' => true],
    ];
}
```

Your API consumers can now query like this:

```
GET /products?name=laptop&price=500&category=1,2,3&order=price&sort=desc&limit=10&with=reviews&fields=id,name,price&append=discount_price
```

And in your controller:

```php
use function Myerscode\Laravel\QueryStrategies\filter;

public function index()
{
    return filter(Product::class)->with(ProductStrategy::class)->apply();
}
```

That single line handles filtering, sorting, field selection, eager loading, accessor appending, limiting, and pagination — all controlled by the strategy.

## Why Use This?

- **Safe by default** — only parameters defined in your strategy are applied. Unknown parameters are ignored (or rejected in strict mode).
- **Column obfuscation** — map public parameter names to real database columns so your schema stays private.
- **Flexible clauses** — 17 built-in filter clauses (equals, contains, between, greater than, etc.) with operator overrides via URL.
- **Relationship support** — filter through relationships with dot notation, sort by relationship columns, and control eager loading.
- **Composable** — chain individual methods (`filter()`, `order()`, `fields()`, etc.) or call `apply()` to run everything at once.

## Installation

```bash
composer require myerscode/laravel-query-strategies
```

The package auto-discovers its service provider. No manual registration needed.

## Documentation

- [Usage](docs/usage.md) — Getting started, creating filters, query parameter syntax, and pagination
- [Strategies](docs/strategies.md) — Defining strategies, parameter options, ordering, limiting, eager loads, and default filters
- [Clauses](docs/clauses.md) — Built-in clauses, scope and trashed filtering, callbacks, and custom clauses
- [Transmutes](docs/transmutes.md) — Transforming values before filtering
- [Configuration](docs/configuration.md) — Customising parameter names and strict mode

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
