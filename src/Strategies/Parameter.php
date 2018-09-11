<?php

namespace Myerscode\Laravel\QueryStrategies\Strategies;

class Parameter
{

    /**
     * Default value use for creating the override parameter
     */
    const DEFAULT_OVERRIDE_SUFFIX = '--operator';

    /**
     * Default value use for exploding query parameters
     */
    const DEFAULT_EXPLODE_DELIMITER = ',';

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $column;

    /**
     * @var string|null
     */
    private $default;

    /**
     * @var array
     */
    private $methods = [];

    /**
     * @var array
     */
    private $disabled = [];

    /**
     * @var bool
     */
    private $explode = false;

    /**
     * @var string
     */
    private $overrideParameter;

    /**
     * @var string
     */
    private $explodeDelimiter;

    
    public function __construct(string $name, array $configuration)
    {
        $this->name = $name;
        $this->bindConfig($configuration);
    }

    /**
     * @param array $configuration
     */
    private function bindConfig(array $configuration)
    {
        $this->column = $configuration['column'] ?? $this->name;
        $this->default = $configuration['default'] ?? null;
        $this->methods = $configuration['methods'] ?? [];
        $this->disabled = isset($configuration['disabled']) ? array_filter(is_array($configuration['disabled']) ? $configuration['disabled'] : [$configuration['disabled']]) : [];
        $this->overrideParameter = $configuration['override'] ?? $this->name . ($configuration['overrideSuffix'] ?? Parameter::DEFAULT_OVERRIDE_SUFFIX);
        $this->explode = isset($configuration['explode']) ? filter_var($configuration['explode'], FILTER_VALIDATE_BOOLEAN) : false;
        $this->explodeDelimiter = $configuration['delimiter'] ?? Parameter::DEFAULT_EXPLODE_DELIMITER;
    }

    /**
     * The name of this parameter
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * The column to use when interacting with this parameter
     *
     * @return null|string
     */
    public function column()
    {
        return $this->column;
    }

    /**
     * Default method class to use instead of Filter default
     *
     * @return string|null
     */
    public function defaultMethod()
    {
        return $this->default;
    }

    /**
     * What custom methods can this parameter use
     *
     * @return array
     */
    public function methods(): array
    {
        return $this->methods;
    }

    /**
     * What parameters are disabled
     *
     * @return array
     */
    public function disabled(): array
    {
        return $this->disabled;
    }

    /**
     * Should this parameter explode its value to find multiple values
     *
     * @return bool
     */
    public function shouldExplode(): bool
    {
        return $this->explode;
    }

    /**
     * Delimiter used for exploding values
     *
     * @return string
     */
    public function explodeDelimiter(): string
    {
        return $this->explodeDelimiter;
    }

    /**
     * The override parameter key to set the filter clause
     *
     * @return string
     */
    public function overrideParameter()
    {
        return $this->overrideParameter;
    }

}
