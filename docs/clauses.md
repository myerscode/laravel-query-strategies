# Clauses

Clauses define how a filter value is applied to the Eloquent query builder. Each clause maps to a SQL WHERE condition.

## Built-in Clauses

| Clause | SQL | Aliases |
|---|---|---|
| `EqualsClause` | `WHERE column = value` | `is`, `=` |
| `DoesNotEqualClause` | `WHERE column != value` | `not`, `!` |
| `GreaterThanClause` | `WHERE column > value` | `greaterThan`, `>`, `gt` |
| `GreaterThanOrEqualsClause` | `WHERE column >= value` | `greaterThanOrEquals`, `>=`, `gte` |
| `LessThanClause` | `WHERE column < value` | `lessThan`, `<`, `lt` |
| `LessThanOrEqualsClause` | `WHERE column <= value` | `lessThanOrEquals`, `<=`, `lte` |
| `BetweenClause` | `WHERE column BETWEEN a AND b` | `between`, `><` |
| `ContainsClause` | `WHERE column LIKE %value%` | `contains`, `%%` |
| `BeginsWithClause` | `WHERE column LIKE value%` | `beginsWith`, `*%` |
| `EndsWithClause` | `WHERE column LIKE %value` | `endsWith`, `%*` |
| `IsInClause` | `WHERE column IN (...)` | `isIn`, `in` |
| `IsNotInClause` | `WHERE column NOT IN (...)` | `notIn`, `!in` |
| `IsNullClause` | `WHERE column IS NULL` | `isNull`, `null` |
| `IsNotNullClause` | `WHERE column IS NOT NULL` | `isNotNull`, `!null`, `notNull` |
| `TrashedClause` | Soft delete filtering (`with`, `only`) | — |
| `OrEqualsClause` | `OR WHERE column = value` | `or`, `\|\|` |
| `ScopeClause` | Calls a model scope | — |

Aliases are used in operator overrides. For example, `?name--contains=John` uses the `ContainsClause`.

## Scope Clause

The `ScopeClause` calls an Eloquent local scope on the model instead of applying a direct WHERE condition. The parameter name is converted to camelCase to match the scope method name.

```php
// Model scope
public function scopeStartsBefore(Builder $query, string $date): Builder
{
    return $query->where('starts_at', '<=', $date);
}

// Strategy config
protected array $config = [
    'starts_before' => [
        'default' => ScopeClause::class,
    ],
];
```

Usage: `?starts_before=2024-01-01`

Multiple parameters can be passed to a scope using comma-separated values:

```
?created_between=2024-01-01,2024-12-31
```

This calls `scopeCreatedBetween($query, '2024-01-01', '2024-12-31')`.

Empty values are ignored — the scope won't be called.

## Trashed Clause

The `TrashedClause` provides built-in soft delete filtering for models using Laravel's `SoftDeletes` trait.

```php
protected array $config = [
    'trashed' => [
        'default' => TrashedClause::class,
    ],
];
```

Accepted values:

| Value | Behaviour |
|---|---|
| `with` | Include soft-deleted records in results |
| `only` | Return only soft-deleted records |
| Any other value | Default behaviour (exclude soft-deleted) |

Usage:

```
?trashed=with    → Include trashed records
?trashed=only    → Only trashed records
```

## Callback Filters

For one-off filters that don't warrant a full clause class, use the `callback` config option:

```php
protected array $config = [
    'has_posts' => [
        'callback' => static function ($builder, $value, $column): void {
            $builder->whereHas('posts');
        },
    ],
    'min_score' => [
        'callback' => static function ($builder, $value, $column): void {
            $val = is_array($value) ? $value[0] : $value;
            $builder->where('score', '>=', $val);
        },
    ],
];
```

The closure receives `(Builder $builder, mixed $value, string $column)`. When a callback is defined, it takes priority over all other clause resolution.

## Creating a Custom Clause

Generate a clause using the artisan command:

```bash
php artisan make:clause ILikeClause
```

This creates a class in `app/Queries/Clause/`. Implement the `filter` method:

```php
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Myerscode\Laravel\QueryStrategies\Clause\AbstractClause;

class ILikeClause extends AbstractClause
{
    /**
     * @param Builder<Model> $builder
     * @return Builder<Model>
     */
    public function filter(Builder $builder, mixed $value, string $column): Builder
    {
        if (!empty($value)) {
            $values = is_array($value) ? $value : [$value];
            foreach ($values as $v) {
                $builder->where($column, 'ILIKE', '%' . $v . '%');
            }
        }

        return $builder;
    }
}
```

### Using a Custom Clause

Register it in your strategy's `$config`:

```php
protected array $config = [
    'name' => [
        'default' => ILikeClause::class,
    ],
    // Or as a named method alias
    'email' => [
        'methods' => [
            'ilike' => ILikeClause::class,
        ],
    ],
];
```

You can also add it to the strategy's `$defaultMethods` to make it available to all parameters:

```php
protected array $defaultMethods = [
    // Include parent defaults
    ...parent::$defaultMethods,
    ILikeClause::class => ['ilike', '~'],
];
```
