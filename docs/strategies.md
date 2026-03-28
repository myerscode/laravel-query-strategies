# Strategies

A strategy defines what query parameters a user can interact with, what clauses they can apply, and how results are ordered, limited, and paginated.

## Creating a Strategy

Generate a strategy using the artisan command:

```bash
php artisan make:strategy ProductStrategy
```

This creates a class in `app/Queries/Strategy/`:

```php
use Myerscode\Laravel\QueryStrategies\Strategies\Strategy;

class ProductStrategy extends Strategy
{
    protected array $config = [
        //
    ];
}
```

## Strategy Properties

### `$config` — Parameter Definitions

Define which query parameters users can filter by. Keys are the parameter names, values configure their behaviour:

```php
protected array $config = [
    // Simple parameter — uses defaults for everything
    'name',

    // Parameter with configuration
    'price' => [
        'column'    => 'unit_price',       // Map to a different database column
        'default'   => GreaterThanClause::class, // Default clause for single values
        'multi'     => IsInClause::class,  // Clause for multiple values
        'explode'   => true,               // Split comma-separated values
        'delimiter' => ',',                // Delimiter for exploding (default: ',')
        'transmute' => BoolTransmute::class, // Transform values before filtering
        'disabled'  => ['contains'],       // Disable specific clause aliases
        'aliases'   => ['cost', 'amount'], // Alternative parameter names
        'methods'   => [                   // Custom clause aliases
            'between' => BetweenClause::class,
        ],
    ],
];
```

### Parameter Config Options

| Option | Type | Description |
|---|---|---|
| `column` | `string` | Database column name. Defaults to the parameter name. |
| `default` | `string` | Clause class for single values. Defaults to `EqualsClause`. |
| `multi` | `string` | Clause class for multiple values. Defaults to `IsInClause`. |
| `explode` | `bool` | Split values by delimiter into multiple values. |
| `delimiter` | `string` | Delimiter for exploding. Default: `,` |
| `transmute` | `string` | Transmute class to transform values before filtering. |
| `callback` | `Closure` | Inline closure filter — receives `($builder, $value, $column)`. |
| `ignore` | `mixed\|array` | Values to ignore when filtering. If all values are ignored, the filter is not applied. |
| `disabled` | `array` | Clause aliases to disable for this parameter. |
| `defaultValue` | `mixed` | Value applied when the parameter is absent from the request. Null values are ignored. |
| `aliases` | `array` | Alternative parameter names that map to this config. |
| `methods` | `array` | Custom clause alias-to-class mappings for this parameter. |
| `override` | `string` | Custom operator override parameter name. |
| `overrideSuffix` | `string` | Suffix for operator override. Default: `--operator` |

### `$canOrderBy` — Allowed Order Columns

Allowlist which columns can be used for ordering:

```php
protected array $canOrderBy = [
    'id',
    'name',
    'created_at',
];
```

Default: `['id']`

### `$allowedFields` — Selectable Columns

Allowlist which columns can be selected via the `fields` query parameter:

```php
protected array $allowedFields = [
    'id',
    'name',
    'email',
];
```

Default: `[]` (empty means all requested fields are allowed)

### `$allowedAppends` — Appendable Accessors

Allowlist which model accessors can be appended to results via the `append` query parameter:

```php
protected array $allowedAppends = [
    'full_name',
    'avatar_url',
];
```

When a user requests `?append=full_name,avatar_url`, the listed accessors are appended to each model in the paginated results. Accessors not in the allowlist are silently filtered out.

Default: `[]` (empty means all requested appends are allowed)

### `$canWith` — Allowed Eager Loads

Allowlist which relationships can be eager loaded:

```php
protected array $canWith = [
    'owner',
    'categories',
];
```

Default: `[]` (empty means all relationships are allowed)

### `$aggregateIncludes` — Aggregate Relationship Includes

Define aggregate includes that can be requested via the `with` parameter:

```php
protected array $aggregateIncludes = [
    'postsCount'    => ['type' => 'count', 'relationship' => 'posts'],
    'postsExists'   => ['type' => 'exists', 'relationship' => 'posts'],
    'postsViewsSum' => ['type' => 'sum', 'relationship' => 'posts', 'column' => 'views'],
    'postsViewsAvg' => ['type' => 'avg', 'relationship' => 'posts', 'column' => 'views'],
    'postsViewsMin' => ['type' => 'min', 'relationship' => 'posts', 'column' => 'views'],
    'postsViewsMax' => ['type' => 'max', 'relationship' => 'posts', 'column' => 'views'],
];
```

The key is the include name used in the query parameter. Supported types: `count`, `exists`, `sum`, `avg`, `min`, `max`. The `column` key is required for `sum`, `avg`, `min`, and `max`.

Default: `[]`

### `$defaultFilters` — Always-Applied Filters

Define closures that are always applied to the query, regardless of request parameters:

```php
protected array $defaultFilters = [];

public function __construct()
{
    $this->defaultFilters = [
        fn (Builder $builder) => $builder->where('active', true),
        fn (Builder $builder) => $builder->where('published', true),
    ];

    parent::__construct();
}
```

Default filters run before user filters. They're useful for scoping queries to only return relevant records (e.g. active, published, non-deleted) without requiring the API consumer to pass those parameters.

Default: `[]`

### `$limitTo` — Default Result Limit

```php
protected int $limitTo = 25;
```

Default: `50`

### `$maxLimit` — Maximum Result Limit

Caps the user-provided limit to prevent excessive queries:

```php
protected int $maxLimit = 100;
```

Default: `150`

## Full Example

```php
use Myerscode\Laravel\QueryStrategies\Strategies\Strategy;
use Myerscode\Laravel\QueryStrategies\Clause\ContainsClause;
use Myerscode\Laravel\QueryStrategies\Clause\GreaterThanOrEqualsClause;

class ProductStrategy extends Strategy
{
    protected array $canOrderBy = ['name', 'price', 'created_at'];

    protected array $canWith = ['category', 'reviews'];

    protected int $limitTo = 25;

    protected int $maxLimit = 100;

    protected array $config = [
        'name' => [
            'default' => ContainsClause::class,
        ],
        'price' => [
            'column'  => 'unit_price',
            'default' => GreaterThanOrEqualsClause::class,
        ],
        'category' => [
            'column'  => 'category_id',
            'explode' => true,
        ],
        'active' => [
            'transmute' => BoolTransmute::class,
        ],
        // Relationship filtering via dot notation
        'category.name' => [
            'column'  => 'category.name',
            'default' => ContainsClause::class,
        ],
        // Aliased relationship filter
        'reviewer' => [
            'column' => 'reviews.author_name',
        ],
    ];
}
```

Usage:

```
GET /products?name=laptop&price=500&category=1,2,3&order=price&sort=desc&limit=10&with=reviews
GET /products?category.name=electronics&reviewer=John
```
