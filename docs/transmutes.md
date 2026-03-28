# Transmutes

Transmutes transform parameter values before they reach the clause. This is useful when the value a user sends doesn't match what the database expects.

## Built-in Transmutes

### BoolTransmute

Converts boolean-like strings to `1` or `0`:

| Input | Output |
|---|---|
| `'ok'`, `'true'`, `'yes'`, `'1'`, `true` | `1` |
| Everything else | `0` |

```php
protected array $config = [
    'active' => [
        'transmute' => BoolTransmute::class,
    ],
];
```

`?active=yes` becomes `WHERE active = 1`.
`?active=no` becomes `WHERE active = 0`.

## Creating a Custom Transmute

Generate one with artisan:

```bash
php artisan make:transmute DateTransmute
```

This creates `app/Queries/Transmute/DateTransmute.php`. Implement the `transmute` method:

```php
use Carbon\Carbon;
use Myerscode\Laravel\QueryStrategies\Strategies\Property;
use Myerscode\Laravel\QueryStrategies\Transmute\TransmuteInterface;

class DateTransmute implements TransmuteInterface
{
    public function transmute(Property $property): Property
    {
        $date = Carbon::parse($property->getOriginalValue());
        $property->setValue($date->toDateString());

        return $property;
    }
}
```

The `Property` object holds both the original value (`getOriginalValue()`) and the transformed value (`getValue()` / `setValue()`). The original is preserved so you can reference it if needed.

Use it in your strategy:

```php
protected array $config = [
    'created_after' => [
        'column'    => 'created_at',
        'transmute' => DateTransmute::class,
        'filter'    => GreaterThanOrEqualsClause::class,
    ],
];
```

`?created_after=last+monday` becomes `WHERE created_at >= '2024-03-25'` (or whatever date Carbon parses).

## Other Transmute Ideas

- **SlugTransmute** — convert user input to a slug before filtering (`My Product` → `my-product`)
- **LowercaseTransmute** — normalise case for case-sensitive columns
- **CurrencyTransmute** — convert `$19.99` to `1999` for integer cent columns
- **EnumTransmute** — map human-readable labels to enum values
