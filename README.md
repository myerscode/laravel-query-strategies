# Laravel Query Strategies

A package to help build queries with Eloquent Builder from URL parameters in a request.

[![Latest Stable Version](https://poser.pugx.org/myerscode/laravel-query-strategies/v/stable)](https://packagist.org/packages/myerscode/laravel-query-strategies)
[![Total Downloads](https://poser.pugx.org/myerscode/laravel-query-strategies/downloads)](https://packagist.org/packages/myerscode/laravel-query-strategies)
[![PHP Version Require](http://poser.pugx.org/myerscode/laravel-query-strategies/require/php)](https://packagist.org/packages/myerscode/laravel-query-strategies)
[![License](https://poser.pugx.org/myerscode/laravel-query-strategies/license)](https://github.com/myerscode/laravel-query-strategies/blob/main/LICENSE)
[![Tests](https://github.com/myerscode/laravel-query-strategies/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/myerscode/laravel-query-strategies/actions/workflows/tests.yml)
[![codecov](https://codecov.io/gh/myerscode/laravel-query-strategies/graph/badge.svg)](https://codecov.io/gh/myerscode/laravel-query-strategies)

## Requirements

- PHP ^8.5
- Laravel ^13.0

## Why this package is helpful?

If you want to apply query clauses to Eloquent Models using parameters passed by the user, then this package will allow you to create strategies that will enable them to be applied automatically.

Using query strategies you can define what properties a user can have access to offering a safer way for them interact with your data schemas.

Strategies can obfuscate the real column names, add aliases to them and enable/disable the query clauses that can be applied to the model.

You can work the builder before and after applying a strategy, so it can be easily integrated with existing code and queries.

## Installation

You can install the package via composer:

```bash
composer require myerscode/laravel-query-strategies
```

## Applying strategies

Getting a filter instance by using one of the following methods:

Using the global helper
```php
filter(Item::class)->with(MyStrategy::class);
```

Use the facade
```php
Query::filter(Item::class)->with(MyStrategy::class);
```

Building it yourself
```php
new Filter(Item::query(), new MyStrategy, $request->query->all());
```

Using the `IsFilterable` trait

```php
class Foo extends Model
{
    use IsFilterableTrait;

    public $strategy = BarStrategy::class;
}
```

You can then use the model itself to apply the filter

```php
$filter = (new Foo)->filter();
```

You can apply query filters, ordering, limits, includes, pagination.

```php
$filter->apply(); // Applies filter, order, limit, with methods and returns the paginated query
$filter->filter(); // Only applies filters and returns the Filter class
$filter->order(); // Only applies ordering and returns the Filter class
$filter->limit(); // Only applies limiting and returns the Filter class
$filter->with(); // Only applies includes and returns the Filter class
$filter->paginate(); // Applies pagination and returns a LengthAwarePaginator class
$filter->builder(); // Return the builder
```

## Strategies

With strategies you can:
* Have a set disable "default" clauses parameters can use
* Set what query clauses a parameter can do
    * You can create custom clauses
    * Disable clauses from a parameter
    * Set default clauses the parameter uses
* Add aliases to your columns
* Alias clauses to allow better for API experiences
* Automatically apply `with` the builder can eager load
* Set query limiting which can be capped to prevent service degradation
* Set columns the query can be ordered by
* Paginate the results

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
