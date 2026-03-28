# Transmutes

Transmutes transform parameter values before they are applied as filter clauses. This is useful when the user-facing value differs from the database value.

## Built-in Transmutes

### BoolTransmute

Converts boolean-like strings to `1` or `0`:

- Truthy values: `'ok'`, `'true'`, `'yes'`, `'1'`, `true`
- Everything else becomes `0`

```php
protected array $config = [
    'active' => [
        'transmute' => BoolTransmute::class,
    ],
];
```

With this config, `?active=yes` becomes `WHERE active = 1`.

## Creating a Custom Transmute

Generate a transmute using the artisan command:

```bash
php artisan make:transmute DateTransmute
```

This creates a class in `app/Queries/Transmute/`. Implement the `transmute` method:

```php
use Myerscode\Laravel\QueryStrategies\Strategies\Property;
use Myerscode\Laravel\QueryStrategies\Transmute\TransmuteInterface;

class DateTransmute implements TransmuteInterface
{
    public function transmute(Property $property): Property
    {
        $date = \Carbon\Carbon::parse($property->getOriginalValue());
        $property->setValue($date->toDateString());

        return $property;
    }
}
```

The `Property` object holds both the original value (`getOriginalValue()`) and the transformed value (`getValue()` / `setValue()`).

### Using a Custom Transmute

```php
protected array $config = [
    'created_after' => [
        'column'    => 'created_at',
        'transmute' => DateTransmute::class,
        'default'   => GreaterThanOrEqualsClause::class,
    ],
];
```
