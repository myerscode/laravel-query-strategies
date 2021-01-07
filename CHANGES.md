# CHANGE LOG

### 0.1.0

* docs: added license information, updated wording, fixed spelling mistakes. Thanks to [IckleChris](https://github.com/IckleChris) for helping/proof reading
* added: feature to find multiple values by exploding parameter value
* refactor: updated how the override parameter is setup, and how Parameter properties are used


## 0.1.0
Created package


## 6.0.0
* Updated dependencies
* Version now follows Laravel


## 7.0.0
* Updated dependencies
* Fixed bug where default filter override got applied as filter


# 8.0.0

### Refactor 
* Updated dependencies for PHP 8 compatibility
    * Min requirement reverted to PHP 7.3 as per Laravel

### Fix
* Ensure named queries can be exploded
* Ensure that custom multi overrides do not have priority over named overrides

### Feat    
* Added ability to get the list of prepared query values that will be used in the builder
* Added ability to transmute query properties
* Added ability to override the default multi filter


