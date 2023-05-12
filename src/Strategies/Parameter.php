<?php

namespace Myerscode\Laravel\QueryStrategies\Strategies;

class Parameter
{

    /**
     * Default value use for creating the operator override parameter
     * @var string
     */
    final public const DEFAULT_OPERATOR_OVERRIDE_SUFFIX = '--operator';

    /**
     * Default value use for exploding query parameters
     * @var string
     */
    final public const DEFAULT_EXPLODE_DELIMITER = ',';

    private ?string $column = null;

    private ?string $default = null;

    private ?string $multi = null;

    private ?string $transmute = null;

    /**
     * @var array
     */
    private $methods = [];

    private array $disabled = [];

    private bool $explode = false;

    /**
     * @var string
     */
    private $overrideParameter;

    private string $explodeDelimiter;


    public function __construct(private readonly ?string $name, array $configuration)
    {
        $this->bindConfig($configuration);
    }

    private function bindConfig(array $configuration): void
    {
        $this->column = $configuration['column'] ?? $this->name;
        $this->default = $configuration['default'] ?? null;
        $this->multi = $configuration['multi'] ?? null;
        $this->transmute = $configuration['transmute'] ?? null;
        $this->methods = $configuration['methods'] ?? [];
        $this->disabled = isset($configuration['disabled']) ? array_filter(is_array($configuration['disabled']) ? $configuration['disabled'] : [$configuration['disabled']]) : [];
        $this->overrideParameter = $configuration['override'] ?? $this->name . ($configuration['overrideSuffix'] ?? Parameter::DEFAULT_OPERATOR_OVERRIDE_SUFFIX);
        $this->explode = isset($configuration['explode']) && filter_var($configuration['explode'], FILTER_VALIDATE_BOOLEAN);
        $this->explodeDelimiter = $configuration['delimiter'] ?? Parameter::DEFAULT_EXPLODE_DELIMITER;
    }

    /**
     * The name of this parameter
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * The column to use when interacting with this parameter
     */
    public function column(): ?string
    {
        return $this->column;
    }

    /**
     * Default method class to use instead of Filter default
     */
    public function defaultMethod(): ?string
    {
        return $this->default;
    }

    /**
     * Default multi method class to use instead of Filter default
     */
    public function multiMethod(): ?string
    {
        return $this->multi;
    }

    /**
     * Default multi method class to use instead of Filter default
     */
    public function transmuteWith(): ?string
    {
        return $this->transmute;
    }

    /**
     * What custom methods can this parameter use
     */
    public function methods(): array
    {
        return $this->methods;
    }

    /**
     * What parameters are disabled
     */
    public function disabled(): array
    {
        return $this->disabled;
    }

    /**
     * Should this parameter explode its value to find multiple values
     */
    public function shouldExplode(): bool
    {
        return $this->explode;
    }

    /**
     * Delimiter used for exploding values
     */
    public function explodeDelimiter(): string
    {
        return $this->explodeDelimiter;
    }

    /**
     * The operator override key
     *
     * @return string
     */
    public function operatorOverride()
    {
        return $this->overrideParameter;
    }

}
