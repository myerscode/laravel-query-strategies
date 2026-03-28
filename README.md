# Laravel Query Strategies

A package to help build queries with Eloquent Builder from URL parameters in a request.

[![Latest Stable Version](https://poser.pugx.org/myerscode/laravel-query-strategies/v/stable)](https://packagist.org/packages/myerscode/laravel-query-strategies)
[![Total Downloads](https://poser.pugx.org/myerscode/laravel-query-strategies/downloads)](https://packagist.org/packages/myerscode/laravel-query-strategies)
[![PHP Version Require](http://poser.pugx.org/myerscode/laravel-query-strategies/require/php)](https://packagist.org/packages/myerscode/laravel-query-strategies)
[![License](https://poser.pugx.org/myerscode/laravel-query-strategies/license)](https://github.com/myerscode/laravel-query-strategies/blob/main/LICENSE)
[![Tests](https://github.com/myerscode/laravel-query-strategies/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/myerscode/laravel-query-strategies/actions/workflows/tests.yml)
[![codecov](https://codecov.io/gh/myerscode/laravel-query-strategies/graph/badge.svg)](https://codecov.io/gh/myerscode/laravel-query-strategies)

## Requirements

- PHP ^8.5
- Laravel ^13.0

## Why this package is helpful?

If you want to apply query clauses to Eloquent Models using parameters passed by the user, then this package will allow you to create strategies that will enable them to be applied automatically.

Using query strategies you can define what properties a user can have access to offering a safer way for them interact with your data schemas.

Strategies can obfuscate the real column names, add aliases to them and enable/disable the query clauses that can be applied to the model.

You can work the builder before and after applying a strategy, so it can be easily integrated with existing code and queries.

## Installation

You can install the package via composer:

```bash
composer require myerscode/laravel-query-strategies
```

## Documentation

- [Usage](docs/usage.md) — Getting started, filter methods, query parameter syntax, and pagination
- [Configuration](docs/configuration.md) — Publishing and customising the config file
- [Strategies](docs/strategies.md) — Defining strategies, parameter config options, ordering, limiting, and eager loads
- [Clauses](docs/clauses.md) — Built-in clauses, aliases, and creating custom clauses
- [Transmutes](docs/transmutes.md) — Value transformation with built-in and custom transmutes

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
