<?php

namespace Myerscode\Laravel\QueryStrategies\Strategies;

use Closure;

readonly class Parameter
{
    /**
     * Default value use for exploding query parameters
     */
    public const string DEFAULT_EXPLODE_DELIMITER = ',';

    /**
     * Default value use for creating the operator override parameter
     */
    public const string DEFAULT_OPERATOR_OVERRIDE_SUFFIX = '--operator';

    private ?string $column;

    private ?Closure $callback;

    private ?string $default;

    private mixed $defaultValue;

    private bool $hasDefaultValue;

    /** @var array<int|string, string> */
    private array $disabled;

    private bool $explode;

    private string $explodeDelimiter;

    /** @var array<int, mixed> */
    private array $ignoredValues;

    /** @var array<string, string> */
    private array $methods;

    private ?string $multi;

    private string $overrideParameter;

    private ?string $transmute;

    /**
     * @param array<string, mixed> $configuration
     */
    public function __construct(private ?string $name, array $configuration)
    {
        $this->column = $configuration['column'] ?? $this->name;
        $this->callback = $configuration['callback'] ?? null;
        $this->default = $configuration['default'] ?? null;
        $this->hasDefaultValue = array_key_exists('defaultValue', $configuration);
        $this->defaultValue = $configuration['defaultValue'] ?? null;
        $this->multi = $configuration['multi'] ?? null;
        $this->transmute = $configuration['transmute'] ?? null;
        $this->methods = $configuration['methods'] ?? [];
        $this->disabled = isset($configuration['disabled']) ? array_filter(is_array($configuration['disabled']) ? $configuration['disabled'] : [$configuration['disabled']]) : [];
        $this->overrideParameter = $configuration['override'] ?? $this->name . ($configuration['overrideSuffix'] ?? self::DEFAULT_OPERATOR_OVERRIDE_SUFFIX);
        $this->explode = isset($configuration['explode']) && filter_var($configuration['explode'], FILTER_VALIDATE_BOOLEAN);
        $this->explodeDelimiter = $configuration['delimiter'] ?? self::DEFAULT_EXPLODE_DELIMITER;
        $this->ignoredValues = isset($configuration['ignore']) ? (is_array($configuration['ignore']) ? $configuration['ignore'] : [$configuration['ignore']]) : [];
    }

    /**
     * The column to use when interacting with this parameter
     */
    public function column(): ?string
    {
        return $this->column;
    }

    /**
     * The callback closure for inline filtering
     */
    public function callback(): ?Closure
    {
        return $this->callback;
    }

    /**
     * Default method class to use instead of Filter default
     */
    public function defaultMethod(): ?string
    {
        return $this->default;
    }

    /**
     * The default value to use when the parameter is not in the request
     */
    public function defaultValue(): mixed
    {
        return $this->defaultValue;
    }

    /**
     * Whether this parameter has a default value configured
     */
    public function hasDefaultValue(): bool
    {
        return $this->hasDefaultValue;
    }

    /**
     * What parameters are disabled
     *
     * @return array<int|string, string>
     */
    public function disabled(): array
    {
        return $this->disabled;
    }

    /**
     * Delimiter used for exploding values
     */
    public function explodeDelimiter(): string
    {
        return $this->explodeDelimiter;
    }

    /**
     * Values that should be ignored when filtering
     *
     * @return array<int, mixed>
     */
    public function ignoredValues(): array
    {
        return $this->ignoredValues;
    }

    /**
     * What custom methods can this parameter use
     *
     * @return array<string, string>
     */
    public function methods(): array
    {
        return $this->methods;
    }

    /**
     * Default multi method class to use instead of Filter default
     */
    public function multiMethod(): ?string
    {
        return $this->multi;
    }

    /**
     * The name of this parameter
     */
    public function name(): ?string
    {
        return $this->name;
    }

    /**
     * The operator override key
     */
    public function operatorOverride(): string
    {
        return $this->overrideParameter;
    }

    /**
     * Should this parameter explode its value to find multiple values
     */
    public function shouldExplode(): bool
    {
        return $this->explode;
    }

    /**
     * The transmute class to transform values
     */
    public function transmuteWith(): ?string
    {
        return $this->transmute;
    }
}
