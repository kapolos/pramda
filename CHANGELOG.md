#### 0.10.0 (work-in-progress)

* Implemented support for more expressive methods to enhance usability. `P::countBy('P::identity')` can now be written as `P::countBy(P::identity)` or as `P::countBy('identity')` or as `countBy('identity')` (the last one with the respective `use function` statements) 
* Public functions are exposed as constants, so that 
use of 'P::someFunction' can be substituted by P::someFunction 
without the need for quotes - suggestion by [camspiers](htts://github.com/camspiers)
* Any string that ends up as an argument for `P::apply` is evaluated as a possible P class method and if it is, it is executed as such.
* All class methods are now available as namespaced functions (`Pramda` namespace).
* Added `partial`, `partialRight`
* Implemented private generic `_curry` based on [php-fp](https://github.com/camspiers/php-fp)'s implementation and refactored the `curry*` functions accordingly
* Added `curry4($callable)` as an alias to `curryN(4, $callable)`

#### 0.9.0

Initial Version
