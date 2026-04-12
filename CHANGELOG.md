# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [13.0.0] - 2026-04-12
### New Features
- [`192f0c6`](https://github.com/myerscode/laravel-query-strategies/commit/192f0c693955557ea0d3f0b4a6c78c4480e143bf) - **laravel**: update to Laravel 13 and Testbench 11 *(commit by [@oniice](https://github.com/oniice))*
- [`31e1961`](https://github.com/myerscode/laravel-query-strategies/commit/31e19619f3eaf823706cac1d4c8324151bf58fee) - add default model strategy *(commit by [@oniice](https://github.com/oniice))*
- [`29b1421`](https://github.com/myerscode/laravel-query-strategies/commit/29b14218f095b3fc49556e6c20eeaae41511d966) - **filter**: add field selection support *(commit by [@oniice](https://github.com/oniice))*
- [`adbb148`](https://github.com/myerscode/laravel-query-strategies/commit/adbb148f809820365fde0049e32fd3bf74e47127) - **filter**: add relationship filtering *(commit by [@oniice](https://github.com/oniice))*
- [`74a0d97`](https://github.com/myerscode/laravel-query-strategies/commit/74a0d975e791118aed38a9a14908a1b654441a45) - **clause**: add ScopeClause for model scopes *(commit by [@oniice](https://github.com/oniice))*
- [`5a69f53`](https://github.com/myerscode/laravel-query-strategies/commit/5a69f53b9a0674b56c0267b4abd55b073f448cf0) - **parameter**: add default filter values *(commit by [@oniice](https://github.com/oniice))*
- [`704a14b`](https://github.com/myerscode/laravel-query-strategies/commit/704a14b0628ca4783806da0703ca0a2cc7fcf0c9) - **clause**: add IsNull and IsNotNull clauses *(commit by [@oniice](https://github.com/oniice))*
- [`51d2a8b`](https://github.com/myerscode/laravel-query-strategies/commit/51d2a8befc6f06692b6f5ce38ac1d1dd0b4838aa) - **clause**: add TrashedClause for soft deletes *(commit by [@oniice](https://github.com/oniice))*
- [`047d93e`](https://github.com/myerscode/laravel-query-strategies/commit/047d93e9eca9856e0e8d8e155a58e3cddf79880b) - **filter**: add callback/inline filters *(commit by [@oniice](https://github.com/oniice))*
- [`a01bedc`](https://github.com/myerscode/laravel-query-strategies/commit/a01bedc838138ca716f559247f81dbad15962b63) - **parameter**: add ignored filter values *(commit by [@oniice](https://github.com/oniice))*
- [`e003fb2`](https://github.com/myerscode/laravel-query-strategies/commit/e003fb22b18948bb68e9a75a182180818d15b337) - **filter**: add aggregate relationship includes *(commit by [@oniice](https://github.com/oniice))*
- [`6378d10`](https://github.com/myerscode/laravel-query-strategies/commit/6378d10138f790eea1a9257c4d8621244757eeb0) - **filter**: add opt-in strict mode *(commit by [@oniice](https://github.com/oniice))*
- [`98e5cee`](https://github.com/myerscode/laravel-query-strategies/commit/98e5ceebaaef5ff9b752c7eeaa5c2a1e3a4cc422) - **clause**: add BetweenClause for ranges *(commit by [@oniice](https://github.com/oniice))*
- [`d8bb728`](https://github.com/myerscode/laravel-query-strategies/commit/d8bb728b990f43616bf0b19cb4dc007cbeccb247) - **filter**: add append support for accessors *(commit by [@oniice](https://github.com/oniice))*
- [`645dc95`](https://github.com/myerscode/laravel-query-strategies/commit/645dc95770af2f8ace77f3ca635d38193f400e8c) - **filter**: add default filters and model strategy auto-pickup *(commit by [@oniice](https://github.com/oniice))*
- [`a23785b`](https://github.com/myerscode/laravel-query-strategies/commit/a23785ba322195382d100b67db46e0934aef2bcf) - **parameter**: add filter shorthand for clause class *(commit by [@oniice](https://github.com/oniice))*
- [`7b1fbaa`](https://github.com/myerscode/laravel-query-strategies/commit/7b1fbaaf99b79f75302319330c42a82a130114d3) - **filter**: add nested relationship sorting *(commit by [@oniice](https://github.com/oniice))*

### Bug Fixes
- [`0430961`](https://github.com/myerscode/laravel-query-strategies/commit/043096169e164e399921a6e0848142f34e691fbb) - resolve all Larastan level 8 errors *(commit by [@oniice](https://github.com/oniice))*
- [`539ddde`](https://github.com/myerscode/laravel-query-strategies/commit/539ddde6cee82330c5fbaa15454cf188816fdbe4) - **strategy**: remove double instantiation in StrategyManager *(commit by [@oniice](https://github.com/oniice))*
- [`026ab08`](https://github.com/myerscode/laravel-query-strategies/commit/026ab08c01ac6f771cddb3c2e2d5c4e6f0621849) - **filter**: remove redundant limit before paginate *(commit by [@oniice](https://github.com/oniice))*
- [`65f9c98`](https://github.com/myerscode/laravel-query-strategies/commit/65f9c98b58ab1bff435e52ca920481b1ecae2203) - **filter**: validate eager load relationships *(commit by [@oniice](https://github.com/oniice))*

### Performance Improvements
- [`14e0a33`](https://github.com/myerscode/laravel-query-strategies/commit/14e0a338b4e569fef61082946b9eafcc4e7287c0) - **strategy**: cache compiled defaultMethods map *(commit by [@oniice](https://github.com/oniice))*
- [`be4cccc`](https://github.com/myerscode/laravel-query-strategies/commit/be4cccc6762f454d5c4ca8b525ced36ecf8a6a03) - **filter**: direct instantiation for clauses *(commit by [@oniice](https://github.com/oniice))*
- [`f3ece05`](https://github.com/myerscode/laravel-query-strategies/commit/f3ece0529f0b84b7d82fe4c1c811dcf9e3cbb94c) - **filter**: replace collect() with array ops *(commit by [@oniice](https://github.com/oniice))*

### Refactors
- [`5dc7b26`](https://github.com/myerscode/laravel-query-strategies/commit/5dc7b2646ba73209037e7957953953fcad6f23fd) - modernise codebase with Rector *(commit by [@oniice](https://github.com/oniice))*
- [`84eb7f2`](https://github.com/myerscode/laravel-query-strategies/commit/84eb7f284c9c7118b7e1927b4ef0de691048afb5) - **php**: second modernisation pass *(commit by [@oniice](https://github.com/oniice))*
- [`0c623fe`](https://github.com/myerscode/laravel-query-strategies/commit/0c623fecd92cd19a919ba0665b54e677cf91da31) - **parameter**: make Parameter readonly *(commit by [@oniice](https://github.com/oniice))*
- [`06bfb77`](https://github.com/myerscode/laravel-query-strategies/commit/06bfb77c3d14a515ee5bafda794b0166c2e034d7) - modernise codebase with Rector *(commit by [@oniice](https://github.com/oniice))*
- [`d891800`](https://github.com/myerscode/laravel-query-strategies/commit/d89180099aae08c8866c20ac25054a105e3d3b38) - improve DX and clean up codebase *(commit by [@oniice](https://github.com/oniice))*

### Tests
- [`7d36405`](https://github.com/myerscode/laravel-query-strategies/commit/7d3640561ffe6b20b82764cfcc560069c32923cc) - improve test coverage *(commit by [@oniice](https://github.com/oniice))*

[13.0.0]: https://github.com/myerscode/laravel-query-strategies/compare/10.0.0...13.0.0
