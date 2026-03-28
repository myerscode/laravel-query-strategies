# Strategies

A strategy is the core concept of this package. It defines the rules for how API consumers can interact with your data: which parameters they can filter by, which columns they can sort and select, which relationships they can load, and what limits apply.

## Creating a Strategy

Generate one with artisan:

```bash
php artisan make:strategy ProductStrategy
```

This creates `app/Queries/Strategies/ProductStrategy.php`:

```php
use Myerscode\Laravel\QueryStrategies\Strategies\Strategy;

class ProductStrategy extends Strategy
{
    protected array $config = [
        //
    ];
}
```

## Parameter Config

The `$config` array defines which query parameters users can filter by. Each entry maps a parameter name to its behaviour.

### Simple Parameters

List parameter names as strings for basic equals filtering:

```php
protected array $config = [
    'name',        // ?name=Laptop → WHERE name = 'Laptop'
    'category_id', // ?category_id=5 → WHERE category_id = 5
];
```

### Configured Parameters

Use an array to customise how a parameter behaves:

```php
protected array $config = [
    'search' => [
        'column' => 'name',                    // Maps ?search= to the 'name' column
        'filter' => ContainsClause::class,      // Uses LIKE %value% instead of equals
    ],
    'price' => [
        'column'  => 'unit_price',              // Maps ?price= to 'unit_price' column
        'filter'  => GreaterThanOrEqualsClause::class,
    ],
    'category' => [
        'column'  => 'category_id',
        'explode' => true,                      // ?category=1,2,3 splits into [1, 2, 3]
    ],
    'active' => [
        'transmute' => BoolTransmute::class,    // ?active=yes becomes WHERE active = 1
    ],
];
```

### All Parameter Options

| Option | Type | Description |
|---|---|---|
| `column` | `string` | Database column to filter on. Defaults to the parameter name. Use dot notation for relationships (e.g. `author.name`). |
| `filter` | `string` | Clause class for single values. Shorthand alias for `default`. |
| `default` | `string` | Clause class for single values. Defaults to `EqualsClause`. |
| `multi` | `string` | Clause class when multiple values are provided. Defaults to `IsInClause`. |
| `callback` | `Closure` | Inline closure filter. Receives `($builder, $value, $column)`. Takes priority over clause resolution. |
| `explode` | `bool` | Split comma-separated values into an array. Default: `false`. |
| `delimiter` | `string` | Delimiter for exploding. Default: `,` |
| `transmute` | `string` | [Transmute](transmutes.md) class to transform values before filtering. |
| `defaultValue` | `mixed` | Value applied when the parameter is absent from the request. Null values are not applied. |
| `ignore` | `mixed\|array` | Values to silently ignore. If all values are ignored, the filter is skipped entirely. |
| `disabled` | `array` | Clause aliases to disable for this parameter. |
| `aliases` | `array` | Alternative parameter names that map to this config. |
| `methods` | `array` | Custom clause alias-to-class mappings for this parameter. |
| `override` | `string` | Custom operator override parameter name. |
| `overrideSuffix` | `string` | Suffix for operator override. Default: `--operator` |

## Ordering

Control which columns can be used for sorting with `$canOrderBy`:

```php
protected array $canOrderBy = [
    'id',
    'name',
    'created_at',
    'category.name', // Sort by a relationship column
];
```

Relationship columns use dot notation. When a user requests `?order=category.name`, the package generates a subquery to sort by the related table's column.

Default: `['id']`

## Field Selection

Control which columns can be selected with `$allowedFields`:

```php
protected array $allowedFields = [
    'id',
    'name',
    'price',
    'category_id',
];
```

When empty, all requested fields are allowed. When populated, only listed columns get through. If every requested field is disallowed, the default `SELECT *` is used.

Default: `[]` (all allowed)

## Appending Accessors

Control which model accessors can be appended to results with `$allowedAppends`:

```php
protected array $allowedAppends = [
    'full_name',
    'discount_price',
    'avatar_url',
];
```

When a user requests `?append=full_name,discount_price`, those accessors are appended to each model in the paginated results. Accessors not in the allowlist are silently filtered out.

When empty, all requested appends are allowed.

Default: `[]` (all allowed)

## Eager Loading

Control which relationships can be eager loaded with `$canWith`:

```php
protected array $canWith = [
    'category',
    'reviews',
    'reviews.author',
];
```

When empty, all relationships are allowed.

Default: `[]` (all allowed)

## Aggregate Includes

Define aggregate includes that can be requested via the `with` parameter:

```php
protected array $aggregateIncludes = [
    'reviewsCount'    => ['type' => 'count', 'relationship' => 'reviews'],
    'reviewsExists'   => ['type' => 'exists', 'relationship' => 'reviews'],
    'reviewsAvgScore' => ['type' => 'avg', 'relationship' => 'reviews', 'column' => 'score'],
];
```

The key is the name used in the query parameter. Supported types: `count`, `exists`, `sum`, `avg`, `min`, `max`. The `column` key is required for `sum`, `avg`, `min`, and `max`.

Usage: `?with=category,reviewsCount` eager loads the category and adds a `reviews_count` column.

Default: `[]`

## Default Filters

Define closures that always apply to the query, regardless of what the user requests. Useful for scoping results to active records, the current tenant, or any other baseline condition:

```php
use Illuminate\Database\Eloquent\Builder;

protected array $defaultFilters = [];

public function __construct()
{
    $this->defaultFilters = [
        fn (Builder $builder) => $builder->where('active', true),
        fn (Builder $builder) => $builder->where('store_id', auth()->user()->store_id),
    ];

    parent::__construct();
}
```

Default filters run before user filters. They're always applied — there's no way for the API consumer to bypass them.

Default: `[]` (none)

## Result Limits

Control pagination defaults:

```php
protected int $limitTo = 25;   // Default results per page
protected int $maxLimit = 100; // Maximum results per page (caps user input)
```

If a user requests `?limit=500` but `$maxLimit` is `100`, they get 100. Values below 1 or non-numeric values fall back to `$limitTo`.

Defaults: `$limitTo = 50`, `$maxLimit = 150`

## Real-World Example

Here's a complete strategy for a product API:

```php
use Illuminate\Database\Eloquent\Builder;
use Myerscode\Laravel\QueryStrategies\Clause\BetweenClause;
use Myerscode\Laravel\QueryStrategies\Clause\ContainsClause;
use Myerscode\Laravel\QueryStrategies\Clause\GreaterThanOrEqualsClause;
use Myerscode\Laravel\QueryStrategies\Strategies\Strategy;
use Myerscode\Laravel\QueryStrategies\Transmute\BoolTransmute;

class ProductStrategy extends Strategy
{
    protected array $canOrderBy = ['name', 'price', 'created_at', 'category.name'];

    protected array $canWith = ['category', 'reviews', 'reviews.author'];

    protected array $allowedFields = ['id', 'name', 'price', 'category_id', 'created_at'];

    protected array $allowedAppends = ['discount_price', 'formatted_price'];

    protected array $aggregateIncludes = [
        'reviewsCount' => ['type' => 'count', 'relationship' => 'reviews'],
        'reviewsAvg'   => ['type' => 'avg', 'relationship' => 'reviews', 'column' => 'score'],
    ];

    protected int $limitTo = 25;

    protected int $maxLimit = 100;

    protected array $defaultFilters = [];

    protected array $config = [
        // Simple text search
        'name' => [
            'filter' => ContainsClause::class,
        ],

        // Price filtering with column mapping
        'price' => [
            'column' => 'unit_price',
            'methods' => [
                'between' => BetweenClause::class,
            ],
        ],

        // Multi-value category filter
        'category' => [
            'column'  => 'category_id',
            'explode' => true,
        ],

        // Boolean filter with value transformation
        'active' => [
            'transmute' => BoolTransmute::class,
        ],

        // Relationship filter with alias
        'author' => [
            'column'  => 'reviews.author_name',
            'filter'  => ContainsClause::class,
        ],

        // Default value when parameter is absent
        'in_stock' => [
            'transmute'    => BoolTransmute::class,
            'defaultValue' => true,
        ],
    ];

    public function __construct()
    {
        $this->defaultFilters = [
            fn (Builder $builder) => $builder->where('published', true),
        ];

        parent::__construct();
    }
}
```

Example requests:

```
# Search products containing "laptop", sorted by price
GET /products?name=laptop&order=price&sort=asc&limit=10

# Filter by category and price range, include reviews
GET /products?category=1,2,3&price--between=100,500&with=reviews,reviewsCount

# Select specific fields, append computed attributes
GET /products?fields=id,name,price&append=discount_price

# Filter by review author, sort by category name
GET /products?author=John&order=category.name&sort=desc

# Override the default operator for a parameter
GET /products?name--beginsWith=Mac&active=yes
```
