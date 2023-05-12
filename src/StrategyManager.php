<?php

namespace Myerscode\Laravel\QueryStrategies;

use Myerscode\Laravel\QueryStrategies\Exceptions\FilterStrategyNotFoundException;
use Myerscode\Laravel\QueryStrategies\Exceptions\InvalidStrategyException;
use Myerscode\Laravel\QueryStrategies\Strategies\StrategyInterface;

class StrategyManager
{
    /**
     * @var StrategyInterface[]
     */
    private array $cache = [];

    /**
     * @param  $possibleStrategy
     * @throws FilterStrategyNotFoundException
     * @throws InvalidStrategyException
     */
    public function findStrategy($possibleStrategy): StrategyInterface
    {
        $possibleStrategyName = is_object($possibleStrategy) ? $possibleStrategy::class : $possibleStrategy;

        $cacheName = $this->getCacheName($possibleStrategyName);

        if (isset($this->cache[$cacheName])) {
            return $this->cache[$cacheName];
        }

        if (is_string($possibleStrategy)) {
            $this->cache[$cacheName] = $this->getStrategy($possibleStrategy);
        } elseif ($possibleStrategy instanceof StrategyInterface) {
            $this->cache[$cacheName] = $possibleStrategy;
        } else {
            throw new InvalidStrategyException('Cannot find strategy: ' . $possibleStrategyName);
        }

        return $this->cache[$cacheName];
    }

    /**
     * Create a safe slug for caching the strategy
     *
     * @param $strategyName
     */
    private function getCacheName($strategyName): string
    {
        return preg_replace('#[^A-Za-z0-9-]+#', '-', strtolower(trim((string) $strategyName)));
    }

    /**
     * Get a built strategy from its class name
     *
     * @throws FilterStrategyNotFoundException
     * @throws InvalidStrategyException
     */
    private function getStrategy(string $strategy): StrategyInterface
    {
        if (class_exists($strategy) && ($strategyClass = new $strategy)) {
            if ($strategyClass instanceof StrategyInterface) {
                $this->cache[$strategy] = new $strategyClass;
                return $this->cache[$strategy];
            }

            throw new InvalidStrategyException($strategy . ' does not implement StrategyInterface');
        }

        throw new FilterStrategyNotFoundException('Strategy not found' . $strategy);
    }
}
