# Clauses

Clauses define how a filter value translates into a SQL condition. When a user sends `?name=John`, the clause determines whether that becomes `WHERE name = 'John'`, `WHERE name LIKE '%John%'`, or something else entirely.

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
| `OrEqualsClause` | `OR WHERE column = value` | `or`, `\|\|` |
| `ScopeClause` | Calls a model scope | — |
| `TrashedClause` | Soft delete filtering | — |

## How Clauses Are Resolved

When a request comes in, the package resolves which clause to use in this order:

1. If the parameter has a `callback` defined, that runs instead of any clause.
2. If the user specifies an operator override (e.g. `?name--contains=John`), the alias is looked up in the parameter's methods and the strategy's default methods.
3. If the parameter has a `filter` or `default` clause class set, that's used.
4. If multiple values are provided, the `multi` clause is used (defaults to `IsInClause`).
5. Otherwise, the global default `EqualsClause` is used.

## Operator Overrides

API consumers can override the default clause for any parameter by appending `--` and a clause alias:

```
?name--contains=lap        → WHERE name LIKE '%lap%'
?price--gte=100            → WHERE price >= 100
?price--between=10,500     → WHERE price BETWEEN 10 AND 500
?email--beginsWith=admin   → WHERE email LIKE 'admin%'
?status--isNull            → WHERE status IS NULL
```

This gives consumers flexibility without you needing to define separate parameters for each operation.

## Scope Clause

The `ScopeClause` calls an Eloquent local scope instead of applying a direct WHERE condition. The parameter name is converted to camelCase to match the scope method.

Say your model has:

```php
public function scopeStartsBefore(Builder $query, string $date): Builder
{
    return $query->where('starts_at', '<=', $date);
}
```

Configure it in your strategy:

```php
protected array $config = [
    'starts_before' => [
        'filter' => ScopeClause::class,
    ],
];
```

Then `?starts_before=2024-01-01` calls `scopeStartsBefore($query, '2024-01-01')`.

Scopes that accept multiple arguments work with comma-separated values:

```
?created_between=2024-01-01,2024-12-31
→ scopeCreatedBetween($query, '2024-01-01', '2024-12-31')
```

Empty values are ignored — the scope won't be called.

## Trashed Clause

For models using Laravel's `SoftDeletes` trait, the `TrashedClause` provides built-in soft delete control:

```php
protected array $config = [
    'trashed' => [
        'filter' => TrashedClause::class,
    ],
];
```

| Value | Behaviour |
|---|---|
| `?trashed=with` | Include soft-deleted records alongside active ones |
| `?trashed=only` | Return only soft-deleted records |
| Any other value | Default behaviour (exclude soft-deleted) |

## Callback Filters

For one-off filters that don't warrant a full clause class, use a closure:

```php
protected array $config = [
    // Simple boolean check
    'has_reviews' => [
        'callback' => fn ($builder, $value, $column) => $builder->whereHas('reviews'),
    ],

    // Value-dependent filter
    'min_score' => [
        'callback' => fn ($builder, $value, $column) => $builder->where('score', '>=', is_array($value) ? $value[0] : $value),
    ],
];
```

The closure receives `(Builder $builder, mixed $value, string $column)`. When a callback is defined, it takes priority over all other clause resolution.

## Creating a Custom Clause

Generate one with artisan:

```bash
php artisan make:clause ILikeClause
```

This creates `app/Queries/Clause/ILikeClause.php`. Implement the `filter` method:

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

Use it in your strategy:

```php
protected array $config = [
    // As the default clause for a parameter
    'name' => [
        'filter' => ILikeClause::class,
    ],

    // As a named method alias (usable via operator override)
    'email' => [
        'methods' => [
            'ilike' => ILikeClause::class,
        ],
    ],
];
```

With the methods approach, users can opt in: `?email--ilike=admin`.

You can also register a custom clause globally by overriding `$defaultMethods` in your strategy, making it available to all parameters:

```php
protected array $defaultMethods = [
    ...parent::$defaultMethods,
    ILikeClause::class => ['ilike', '~'],
];
```
