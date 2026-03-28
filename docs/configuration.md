# Configuration

## Publishing the Config

```bash
php artisan vendor:publish --tag=config --provider="Myerscode\Laravel\QueryStrategies\ServiceProvider"
```

This creates `config/query-strategies.php`. If you don't publish it, the package uses sensible defaults.

## Options

```php
return [
    'strict' => false,
    'parameters' => [
        'order'  => 'order',
        'sort'   => 'sort',
        'limit'  => 'limit',
        'page'   => 'page',
        'with'   => 'with',
        'fields' => 'fields',
        'append' => 'append',
    ],
];
```

## Strict Mode

When `strict` is `true`, any query parameter that isn't a recognised filter, system key, or operator override throws an `InvalidFilterException`. This is useful for APIs where you want to catch typos or prevent consumers from guessing at filter names.

```php
'strict' => true,
```

When `strict` is `false` (the default), unknown parameters are silently ignored.

You can also enable strict mode per-request by passing it in the config array:

```php
$filter = new Filter($builder, $strategy, $request->query->all(), ['strict' => true]);
```

## Parameter Names

Each key maps an internal concept to the query parameter name your API exposes. Change these if your API conventions differ from the defaults:

| Key | Default | What it controls |
|---|---|---|
| `order` | `order` | Column to sort by |
| `sort` | `sort` | Sort direction (`asc` / `desc`) |
| `limit` | `limit` | Results per page |
| `page` | `page` | Page number |
| `with` | `with` | Relationships to eager load |
| `fields` | `fields` | Columns to select |
| `append` | `append` | Model accessors to append |

## Example: Custom Parameter Names

If your API uses different conventions:

```php
return [
    'parameters' => [
        'order'  => 'sort_by',
        'sort'   => 'direction',
        'limit'  => 'per_page',
        'page'   => 'page',
        'with'   => 'include',
        'fields' => 'select',
        'append' => 'append',
    ],
];
```

Consumers would then query with:

```
GET /products?sort_by=name&direction=desc&per_page=25&include=category&select=id,name
```

The strategy and filter logic stays the same — only the URL parameter names change.
