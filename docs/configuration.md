# Configuration

## Publishing the Config

Publish the configuration file to customise query parameter names:

```bash
php artisan vendor:publish --tag=config --provider="Myerscode\Laravel\QueryStrategies\ServiceProvider"
```

This creates `config/query-strategies.php`.

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

### Strict Mode

| Key | Default | Description |
|---|---|---|
| `strict` | `false` | When `true`, throws `InvalidFilterException` if a query parameter is not a recognised filter, system key, or operator override |

When strict mode is enabled, any unknown query parameter will throw an exception with a message listing the allowed filters. This is useful for APIs where you want to prevent typos or unauthorised filter attempts from silently being ignored.

### Parameter Keys

Each key maps an internal concept to the query parameter name your API exposes:

| Key | Default | Description |
|---|---|---|
| `order` | `order` | Parameter for specifying which column to order results by |
| `sort` | `sort` | Parameter for specifying sort direction (`asc` or `desc`) |
| `limit` | `limit` | Parameter for limiting the number of results returned |
| `page` | `page` | Parameter for pagination page number |
| `with` | `with` | Parameter for specifying relationships to eager load |
| `fields` | `fields` | Parameter for selecting specific columns |
| `append` | `append` | Reserved for future use |

## Example

If your API uses `sort_by` instead of `order` and `per_page` instead of `limit`:

```php
return [
    'parameters' => [
        'order'  => 'sort_by',
        'sort'   => 'direction',
        'limit'  => 'per_page',
        'page'   => 'page',
        'with'   => 'include',
        'fields' => 'fields',
        'append' => 'append',
    ],
];
```

Users would then query with `?sort_by=name&direction=desc&per_page=25&include=owner`.
