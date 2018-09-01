<?php

namespace Myerscode\Laravel\QueryStrategies\Strategies;

class Parameter
{

    const COLUMN_KEY = 'column';

    const DEFAULT_KEY = 'default';

    const METHODS_KEY = 'methods';

    const DISABLED_KEY = 'disabled';

    const MASS_KEY = 'mass';

    /**
     * @var string
     */
    protected $massFilterSuffix = '--filter';

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
     * @var string
     */
    private $massFilter;

    public function __construct(string $name, array $configuration)
    {
        $this->setName($name);
        $this->bindConfig($configuration);
    }

    /**
     * @param array $configuration
     */
    private function bindConfig(array $configuration)
    {
        $this->setColumn($configuration['column'] ?? $this->name);
        $this->setDefault($configuration['default'] ?? null);
        $this->setMethods($configuration['methods'] ?? []);
        $this->setDisabled($configuration['disabled'] ?? []);
        $this->setMassFilter($configuration['mass'] ?? $this->name . ($configuration['massSuffix'] ?? $this->massFilterSuffix));
    }

    /**
     * @return null|string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param null|string $name
     * @return Parameter
     */
    public function setName(string $name): Parameter
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @param null|string $column
     * @return Parameter
     */
    public function setColumn($column): Parameter
    {
        $this->column = $column;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param null|string $default
     * @return Parameter
     */
    public function setDefault($default): Parameter
    {
        $this->default = $default;
        return $this;
    }

    /**
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @param array $methods
     * @return Parameter
     */
    public function setMethods(array $methods): Parameter
    {
        $this->methods = $methods;
        return $this;
    }

    /**
     * @return array
     */
    public function getDisabled(): array
    {
        return $this->disabled;
    }

    /**
     * @param array $disabled
     * @return Parameter
     */
    public function setDisabled(array $disabled): Parameter
    {
        $this->disabled = $disabled;
        return $this;
    }

    /**
     * @return string
     */
    public function getMassFilter()
    {
        return $this->massFilter;
    }

    /**
     * @param string $massFilter
     * @return Parameter
     */
    public function setMassFilter(string $massFilter): Parameter
    {
        $this->massFilter = $massFilter;
        return $this;
    }
}
