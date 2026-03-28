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

Each key maps an internal concept to the query parameter name your API exposes:

| Key | Default | Description |
|---|---|---|
| `order` | `order` | Parameter for specifying which column to order results by |
| `sort` | `sort` | Parameter for specifying sort direction (`asc` or `desc`) |
| `limit` | `limit` | Parameter for limiting the number of results returned |
| `page` | `page` | Parameter for pagination page number |
| `with` | `with` | Parameter for specifying relationships to eager load |
| `fields` | `fields` | Reserved for future use |
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
