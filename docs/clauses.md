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
| `ContainsClause` | `WHERE column LIKE %value%` | `contains`, `%%` |
| `BeginsWithClause` | `WHERE column LIKE value%` | `beginsWith`, `*%` |
| `EndsWithClause` | `WHERE column LIKE %value` | `endsWith`, `%*` |
| `IsInClause` | `WHERE column IN (...)` | `isIn`, `in` |
| `IsNotInClause` | `WHERE column NOT IN (...)` | `notIn`, `!in` |
| `OrEqualsClause` | `OR WHERE column = value` | `or`, `\|\|` |

Aliases are used in operator overrides. For example, `?name--contains=John` uses the `ContainsClause`.

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
