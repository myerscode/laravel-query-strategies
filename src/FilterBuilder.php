<?php

namespace Myerscode\Laravel\QueryStrategies;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Myerscode\Laravel\QueryStrategies\Exceptions\BuilderNotFoundException;
use Myerscode\Laravel\QueryStrategies\Exceptions\BuilderNotSetException;
use Myerscode\Laravel\QueryStrategies\Exceptions\FilterStrategyNotFoundException;
use Myerscode\Laravel\QueryStrategies\Exceptions\InvalidStrategyException;

class FilterBuilder
{

    /**
     * @var StrategyManager
     */
    private $strategyManager;

    /**
     *
     * @var Request
     */
    private $request;

    /**
     * @var Builder
     */
    private $builder;


    public function __construct(Request $request, StrategyManager $policyManager)
    {
        $this->request = $request;
        $this->strategyManager = $policyManager;
    }

    /**
     * Get a query builder via the passed by checking if its a model or already a builder
     *
     * @param $builderOrModel
     * @throws BuilderNotFoundException
     * @throws BuilderNotSetException
     */
    private function setBuilder($builderOrModel)
    {
        if (empty($builderOrModel)) {
            throw new BuilderNotSetException();
        }

        if ($builderOrModel instanceof Builder) {
            $this->builder = $builderOrModel;
        } elseif ($builderOrModel instanceof Model) {
            $this->builder = $builderOrModel->newQuery();
        } elseif (is_string($builderOrModel) && class_exists($builderOrModel) && ($model = app($builderOrModel)) instanceof Model) {
            $this->builder = $model->query();
        } else {
            throw new BuilderNotFoundException();
        }
    }

    /**
     * @return Builder
     */
    public function builder()
    {
        return $this->builder;
    }

    /**
     * Set the builder which we will be used for querying
     *
     * @param $builderOrModel
     * @return FilterBuilder
     * @throws BuilderNotFoundException
     * @throws BuilderNotSetException
     */
    public function filter($builderOrModel): FilterBuilder
    {
        $this->setBuilder($builderOrModel);

        return $this;
    }

    /**
     * Apply a possible strategy by name or a given class
     *
     * @param  $possibleStrategy
     * @return Filter
     * @throws FilterStrategyNotFoundException
     * @throws InvalidStrategyException
     * @throws BuilderNotSetException
     */
    public function with($possibleStrategy): Filter
    {
        if (empty($this->builder)) {
            throw new BuilderNotSetException();
        }
        $strategy = $this->strategyManager->findStrategy($possibleStrategy);
        return new Filter($this->builder, $strategy, $this->request->query->all(), $this->config());
    }

    /**
     * The config the will be built eiyh
     * @return array
     */
    public function config()
    {
        return [
            'order' => config('query-strategies.parameters.order', 'order'),
            'sort' => config('query-strategies.parameters.sort', 'sort'),
            'limit' => config('query-strategies.parameters.limit', 'limit'),
            'page' => config('query-strategies.parameters.page', 'page'),
            'with' => config('query-strategies.parameters.with', 'with'),
        ];
    }
}
