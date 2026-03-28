<?php

namespace Myerscode\Laravel\QueryStrategies\Strategies;

class Parameter
{
    /**
     * Default value use for exploding query parameters
     */
    final public const string DEFAULT_EXPLODE_DELIMITER = ',';

    /**
     * Default value use for creating the operator override parameter
     */
    final public const string DEFAULT_OPERATOR_OVERRIDE_SUFFIX = '--operator';

    private ?string $column = null;

    private ?\Closure $callback = null;

    private ?string $default = null;

    private mixed $defaultValue = null;

    private bool $hasDefaultValue = false;

    /** @var array<int|string, string> */
    private array $disabled = [];

    private bool $explode = false;

    private string $explodeDelimiter;

    /** @var array<string, string> */
    private array $methods = [];

    private ?string $multi = null;

    private string $overrideParameter;

    private ?string $transmute = null;


    /**
     * @param array<string, mixed> $configuration
     */
    public function __construct(private readonly ?string $name, array $configuration)
    {
        $this->bindConfig($configuration);
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
    public function callback(): ?\Closure
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
     * Default multi method class to use instead of Filter default
     */
    public function transmuteWith(): ?string
    {
        return $this->transmute;
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function bindConfig(array $configuration): void
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
        $this->overrideParameter = $configuration['override'] ?? $this->name . ($configuration['overrideSuffix'] ?? Parameter::DEFAULT_OPERATOR_OVERRIDE_SUFFIX);
        $this->explode = isset($configuration['explode']) && filter_var($configuration['explode'], FILTER_VALIDATE_BOOLEAN);
        $this->explodeDelimiter = $configuration['delimiter'] ?? Parameter::DEFAULT_EXPLODE_DELIMITER;
    }

}
