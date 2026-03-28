# Usage

## Getting a Filter Instance

There are several ways to create a filter and apply a strategy:

### Global Helper

```php
filter(Item::class)->with(MyStrategy::class)->apply();
```

### Facade

```php
use Myerscode\Laravel\QueryStrategies\Facades\Query;

Query::filter(Item::class)->with(MyStrategy::class)->apply();
```

### Direct Instantiation

```php
use Myerscode\Laravel\QueryStrategies\Filter;

$filter = new Filter(Item::query(), new MyStrategy(), $request->query->all());
$filter->apply();
```

### IsFilterable Trait

Add the trait to your model and set the `$strategy` property:

```php
use Myerscode\Laravel\QueryStrategies\IsFilterableTrait;

class Item extends Model
{
    use IsFilterableTrait;

    public string $strategy = ItemStrategy::class;
}
```

Then filter directly from the model:

```php
$paginated = (new Item)->filter()->apply();
```

## Filter Methods

Once you have a `Filter` instance, you can call these methods:

| Method | Description |
|---|---|
| `apply()` | Applies field selection, filters, ordering, limiting, eager loads, and returns paginated results |
| `fields()` | Applies only field selection (SELECT columns) and returns the Filter |
| `filter()` | Applies only WHERE clauses and returns the Filter |
| `order()` | Applies only ORDER BY and returns the Filter |
| `limit()` | Applies only LIMIT and returns the Filter |
| `with()` | Applies only eager loads and returns the Filter |
| `paginate()` | Paginates the query and returns a `Paginated` instance |
| `builder()` | Returns the underlying Eloquent Builder |

Methods are chainable, so you can selectively apply what you need:

```php
$filter = filter(Item::class)->with(MyStrategy::class);

// Only apply filters and ordering, then get the builder
$builder = $filter->filter()->order()->builder();

// Or apply everything and get paginated results
$paginated = $filter->apply();
```

## Query Parameter Syntax

### Field Selection

```
?fields=id,name,email       → Select only specific columns
```

Only columns listed in the strategy's `$allowedFields` array are permitted. If `$allowedFields` is empty, all requested fields are allowed. If all requested fields are disallowed, the default `SELECT *` is used.

### Filtering

```
?name=John              → Applies default clause (equals) to 'name'
?name=John&name=Jane    → Multiple values (uses multi-clause, e.g. whereIn)
```

### Relationship Filtering

Use dot notation in your strategy config to filter through relationships via `whereHas`:

```
?owner.name=John        → WHERE EXISTS (SELECT * FROM owners WHERE name = 'John')
```

You can also alias relationship filters to hide the dot notation from API consumers:

```php
// In your strategy config:
'author_name' => ['column' => 'author.name']

// Then: ?author_name=John → whereHas('author', fn($q) => $q->where('name', 'John'))
```

### Operator Override

```
?name--contains=John        → Use 'contains' clause instead of default
?name--operator=contains    → Alternative syntax for operator override
```

### Ordering

```
?order=id                   → Order by 'id' ascending
?order=id&sort=desc         → Order by 'id' descending
?order[]=id&order[]=name    → Multiple order by columns
```

### Limiting

```
?limit=10                   → Return 10 results (capped at strategy's maxLimit)
```

### Eager Loading

```
?with=owner,categories      → Eager load relationships
```

Only relationships listed in the strategy's `$canWith` array are allowed. If `$canWith` is empty, all relationships are permitted.

### Aggregate Includes

Include relationship counts and aggregates alongside eager loads:

```
?with=ownerCount            → withCount('owner')
?with=owner,ownerCount      → Eager load + count
```

Aggregates are defined in the strategy's `$aggregateIncludes` property. Supported types: `count`, `exists`, `sum`, `avg`, `min`, `max`.

### Pagination

```
?page=2                     → Get page 2 of results
```

## Paginated Response

The `Paginated` class extends Laravel's `LengthAwarePaginator` and adds:

- `getAppliedFilters()` — returns the filters that were applied to the query
- `getMeta()` — returns comprehensive pagination metadata:

```php
[
    'count'           => 10,
    'firstItem'       => 1,
    'lastItem'        => 10,
    'total'           => 100,
    'hasMorePage'     => true,
    'currentPageUrl'  => 'http://...',
    'previousPageUrl' => 'http://...',
    'nextPageUrl'     => 'http://...',
    'currentPage'     => 1,
    'lastPage'        => 10,
    'perPage'         => 10,
    'appliedFilters'  => ['name' => ['John']],
]
```
