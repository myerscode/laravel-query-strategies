# Laravel Query Strategies
a package to help build queries with Eloquent Builder from URL parameters in a request

[![Latest Stable Version](https://poser.pugx.org/myerscode/laravel-query-strategies/v/stable)](https://packagist.org/packages/myerscode/laravel-query-strategies)
[![Total Downloads](https://poser.pugx.org/myerscode/laravel-query-strategies/downloads)](https://packagist.org/packages/myerscode/laravel-query-strategies)
[![License](https://poser.pugx.org/myerscode/laravel-query-strategies/license)](https://packagist.org/packages/myerscode/laravel-query-strategies)

## Why this package is helpful?
If you want to apply query clauses to Eloquent Models using parameters passed by the user, then this package will  allow you to create strategies that will enable them to be applied automatically.

Using query strategies you can define what properties a user can have access to offering a safer way for them interact with your data schemas. 

Strategies can obfuscate the real column names, add aliases to them and enable/disable the query clauses that can be applied to the model.

You can work the builder before and after applying a strategy, so it can be easily integrated with existing code and queries.

## Installation

You can install the package via composer:

```bash
composer require myerscode/laravel-query-strategies
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

## Strategy structure

### $config

Fill in the `$config` property with the query parameters that you want use on the model.

`$config` should contain the allowed queries keys and the values `aliases` `column` `default` `disabled` `methods`

```php
// basic implamentation, that will just enable all default clauses for the strategy and will not mask the column name
$config = [
  'foo',
  'bar'  
];
```

```php
// advance with custom methods, disabling clauses and changing the default clause
$config = [
    'name' => [
        'column' => 'first_name',
        'methods' => [
            'hello' => HelloClause::class,
            'world' => WorldClause::class,
        ]
    ],
    'surname' => [
        'column' => 'last_name',
        'disabled' => [
            'equals' => EqualsClause::class,
        ],
    ],
    'dob' => [
        'aliases' => [
            'date_of_birth',
            'birthday',
        ]
        'default' => FooBarClause::class,
    ],
    'address' => [
        'methods' => [
            'distance' => DistanceClause::class,
        ],
    ],
];
```

### $defaultMethods
The `$defaultMethods` property contains all the clauses against an collection of `aliases` that properties can use. 

Overriding this property will enable you to control what default methods a query can apply to a property of `Model`.

```php
protected $defaultMethods = [
    ...
    LessThanClause::class => ['lessThan', '<', 'lt'],
    GreaterThanOrEqualsClause::class => ['greaterThanOrEquals', '>=', 'gte'],
    ...
];
```

### $limitTo
The `$limitTo` property sets what the default select limit is by default.

```php
// default value
protected $limitTo = 50;
```

### $maxLimit
The `$maxLimit` property sets what the max limit is, to help prevent people selecting all records form you database and degrading performance.

```php
// default value
protected $maxLimit = 150;
```

### $orderBy
The `$orderBy` property is an array which sets what columns the `Model` can be ordered by.

```php
// default value
protected $canOrderBy = [
    'id',
];
```


### Applying strategies

Getting a filter instance by using one of the following methods:


Using the global helper
```
filter(Item::class)->with(MyStrategy::class);
```

Use the facade
```
Query::filter(Item::class)->with(MyStrategy::class);
```

Building it yourself
```
new Filter(Item::query(), new MyStrategy, $request->query->all());
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


### Filters

By default parameters will have access to all the query filters in the `$defaultMethods`. 
You can create custom a `Clause` to do more complex or domain specific actions and add them to `$defaultMethods` or a single parameter.

### Where Clauses

| Type          | Aliases   | Query   | Eloquent   |
| ------------- |---------- | ------- | ---------- |
| begins with | `beginsWith` `*%` | `?name[beginsWith]=Fr` `?name[*%]=Fr` | Record::where('name', '=', 'Fr%') |
| contains | `contains` `%%` | `?name[contains]=Fr` `?name[%%]=Fr` | Record::where('name', '=', '%Fr%') |
| ends with | `endsWith` `%*` | `?name[endsWith]=ed` `?name[%*]=ed` | Record::where('name', '=', '%ed') |
| equals | `is` `=` | `?name=Fred` `?name[is]=Fred` `?name[is]=Fred` | Record::where('name', '=', 'Fred') |
| less than | `lessThan` `<` `lt` | `?hello[lessThan]=world` `?hello[<]=world` `?hello[lt]=world` | Record::where('hello', '<', 'world') |
| less than or equals | `lessThanOrEquals` `<=` `lte` | `?hello[lessThanOrEquals]=world` `?hello[<=]=world` `?hello[lte]=world` | Record::where('hello', '<=', 'world') |
| greater than | `greaterThan` `>` `gt` | `?hello[greaterThan]=world` `?hello[>]=world` `?hello[gt]=world` | Record::where('hello', '>', 'world') |
| greater than or equals | `greaterThanOrEquals` `>=` `gte` | `?hello[greaterThanOrEquals]=world` `?hello[>=]=world` `?hello[gte]=world` | Record::where('hello', '>=', 'world') |
| not equals | `not` `!` | `?name[not]=Fred` `?name[!]=Fred` | Record::where('hello', '!=', 'world') |
| is in | `isIn` `in` | `?name[isIn]=Fred,Tor` `?name[in]=Fred,Tor` `?name[]=Fred&name[]=Tor` | Record::whereIn('name', ['Fred', 'Tor']) |
| is not in | `notIn` `!in` | `?name[notIn]=Fred,Tor` `?name[!in]=Fred,Tor`  | Record::whereNotIn('name', ['Fred', 'Tor']) |
| or | `or` <code>&#124;&#124;</code>  | `?name[is]=Fred&name[or]=Tor` | Record::where('name', '=', 'Fred')->orWhere('name', '=', 'Tor') |


### Overriding the clause

You can use a special parameter to set a clause to all properties with that name in a query.

The following example would apply the `not` clause to the `name` properties.

```php
?name[]=Fred&name[]=Tor&name[]=Chris&name--operator=not
```

By default the special parameter is `$paramName` with a default suffix of `--operator`. e.g. `name--operator`

The parameter can be either fully renamed or the suffix changed in the strategy config.

```php
// a strategy config with operator override properties
$config = [
    'name' => [
        'override' => 'name_override',
    ],
    'date' => [
        'overrideSuffix' => '--filter',
    ],
];
// name=Fred&name_override=like
// date=31/12/1987&date--filter=before
```


### Ordering and Sorting
Sorting is ascending by default. The only available options for sorting is `asc` and `desc` - if a value other than those is past, it will resort to the default.
`?order=name&sort=desc`

`?order[asc]=name&order[desc]=id`

### Limiting

`?limit=10`

`?order[asc]=name&order[desc]=id`


### Using the config
Run the publish command, to create the config file in `/config`
```
> php artisan vendor:publish --provider="Myerscode\Laravel\QueryStrategies\ServiceProvider" --tag=config
```
This will create `config/query-strategies.php` which contains the default settings for things such as reserved parameter keys (limit, page, with etc.)


### Creating a new strategy
To quickly create a new `Strategy` class in `Queries/Strategies` run:
```
> php artisan make:strategy $name
```

### Creating a new query clause
To quickly create a new `Clause` class in `Queries/Clause` run:
```
> php artisan make:clause $name
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.