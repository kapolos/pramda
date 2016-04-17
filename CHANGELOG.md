#### 0.10.0 (unfinished)

* Public functions are now also exposed as constants, so that 
use of 'P::someFunction' can be substituted by P::someFunction 
without the need for quotes - suggestion by [camspiers](https://github.com/camspiers)
* Added `partial`, `partialRight`
* Implemented private generic `_curry` based on [php-fp](https://github.com/camspiers/php-fp)'s implementation and refactored the `curry*` functions accordingly
* Added `curry4($callable)` as an alias to `curryN(4, $callable)`

#### 0.9.0

Initial Version
