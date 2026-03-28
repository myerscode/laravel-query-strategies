# Usage

## Getting Started

There are three ways to create a filter. All of them produce the same result — pick whichever fits your style.

### Helper Function

```php
use function Myerscode\Laravel\QueryStrategies\filter;

// In a controller
public function index()
{
    return filter(Product::class)->with(ProductStrategy::class)->apply();
}
```

### Facade

```php
use Myerscode\Laravel\QueryStrategies\Facades\Query;

public function index()
{
    return Query::filter(Product::class)->with(ProductStrategy::class)->apply();
}
```

### Direct Instantiation

Useful when you want full control over the query and request data:

```php
use Myerscode\Laravel\QueryStrategies\Filter;

$filter = new Filter(
    Product::query()->where('store_id', $storeId), // start from an existing builder
    new ProductStrategy(),
    $request->query->all(),
);

return $filter->apply();
```

## Model-Based Filtering

If a model always uses the same strategy, add the `IsFilterableTrait` and set the `$strategy` property:

```php
use Myerscode\Laravel\QueryStrategies\IsFilterableTrait;

class Product extends Model
{
    use IsFilterableTrait;

    public string $strategy = ProductStrategy::class;
}
```

Then filter directly from the model:

```php
// Uses the strategy defined on the model
$paginated = (new Product)->filter()->apply();
```

The `results()` method also auto-detects the strategy:

```php
// No need to pass a strategy — it reads $strategy from the model
$paginated = filter(Product::class)->results();
```

## Filter Methods

`apply()` runs everything in one call: field selection, filters, ordering, limiting, eager loads, accessor appending, and pagination. But you can also call each step individually:

| Method | What it does |
|---|---|
| `apply()` | Runs all steps and returns paginated results |
| `fields()` | Applies `SELECT` column restrictions |
| `filter()` | Applies `WHERE` clauses from request parameters |
| `order()` | Applies `ORDER BY` |
| `limit()` | Applies `LIMIT` |
| `with()` | Applies eager loads and aggregate includes |
| `paginate()` | Paginates and returns a `Paginated` instance |
| `builder()` | Returns the underlying Eloquent Builder |

Methods are chainable, so you can selectively apply what you need:

```php
$filter = filter(Product::class)->with(ProductStrategy::class);

// Only apply filters and ordering, then get the raw builder
$builder = $filter->filter()->order()->builder();
$products = $builder->get();

// Or apply everything and get paginated results
$paginated = $filter->apply();
```

## Query Parameters

All examples below assume the default parameter names. These can be [customised via config](configuration.md).

### Filtering

The simplest filter — pass a parameter name and value:

```
GET /products?name=Laptop
→ WHERE name = 'Laptop'
```

Multiple values for the same parameter use the multi-clause (defaults to `whereIn`):

```
GET /products?category_id=1&category_id=2
→ WHERE category_id IN (1, 2)
```

### Operator Overrides

Override the default clause for a parameter by appending `--` and the clause alias:

```
GET /products?name--contains=lap
→ WHERE name LIKE '%lap%'

GET /products?price--gte=100
→ WHERE price >= 100

GET /products?created_at--between=2024-01-01,2024-12-31
→ WHERE created_at BETWEEN '2024-01-01' AND '2024-12-31'
```

See [Clauses](clauses.md) for all available aliases.

### Relationship Filtering

Use dot notation to filter through relationships. This generates a `whereHas` query:

```
GET /products?category.name=Electronics
→ WHERE EXISTS (SELECT * FROM categories WHERE products.category_id = categories.id AND name = 'Electronics')
```

You can also alias relationship filters to keep your API clean:

```php
// In your strategy config
'author' => ['column' => 'reviews.author_name']

// Then: GET /products?author=John
// Generates: whereHas('reviews', fn($q) => $q->where('author_name', 'John'))
```

### Field Selection

Request only the columns you need:

```
GET /products?fields=id,name,price
→ SELECT id, name, price FROM products
```

Controlled by the strategy's `$allowedFields`. If a requested field isn't in the allowlist, it's silently dropped. If all fields are dropped, the default `SELECT *` is used.

### Ordering

```
GET /products?order=price&sort=desc
→ ORDER BY price DESC

GET /products?order[]=name&order[]=price
→ ORDER BY name ASC, price ASC
```

Sort by a relationship column using dot notation:

```
GET /products?order=category.name&sort=asc
→ ORDER BY (SELECT categories.name FROM categories WHERE ...) ASC
```

The relationship must exist on the model and the dot-notation column must be in the strategy's `$canOrderBy` array.

### Limiting

```
GET /products?limit=25
→ LIMIT 25
```

Capped at the strategy's `$maxLimit`. Values below 1 or non-numeric values fall back to the strategy's `$limitTo` default.

### Eager Loading

```
GET /products?with=category,reviews
→ Eager loads the category and reviews relationships
```

Controlled by the strategy's `$canWith`. If `$canWith` is empty, all relationships are allowed.

### Aggregate Includes

Request relationship counts and aggregates alongside eager loads:

```
GET /products?with=category,reviewsCount
→ Eager loads category + adds reviews_count column
```

Aggregates are defined in the strategy's `$aggregateIncludes`. Supported types: `count`, `exists`, `sum`, `avg`, `min`, `max`.

### Appending Accessors

Append computed model attributes to the response:

```
GET /products?append=discount_price,formatted_price
→ Each product in the response includes discount_price and formatted_price
```

Controlled by the strategy's `$allowedAppends`. Appends run after pagination, so they operate on the result collection — not the query.

### Pagination

```
GET /products?page=2
→ Returns page 2 of results
```

## Paginated Response

The `Paginated` class extends Laravel's `LengthAwarePaginator` and adds:

`getMeta()` returns comprehensive pagination metadata, useful for API responses:

```php
$paginated = filter(Product::class)->with(ProductStrategy::class)->apply();

$paginated->getMeta();
// [
//     'count'           => 10,
//     'firstItem'       => 1,
//     'lastItem'        => 10,
//     'total'           => 247,
//     'hasMorePage'     => true,
//     'currentPageUrl'  => 'http://example.com/products?page=1',
//     'previousPageUrl' => '',
//     'nextPageUrl'     => 'http://example.com/products?page=2',
//     'currentPage'     => 1,
//     'lastPage'        => 25,
//     'perPage'         => 10,
//     'appliedFilters'  => ['name' => ['Laptop']],
// ]
```

`getAppliedFilters()` returns just the filters that were applied, useful for debugging or displaying active filters in a UI.
