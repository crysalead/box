# Box - Minimalist Dependency Injection Container

[![Build Status](https://travis-ci.org/crysalead/box.png?branch=master)](https://travis-ci.org/crysalead/box) [![Code Coverage](https://scrutinizer-ci.com/g/crysalead/box/badges/coverage.png?s=3bea5fc1b02c06e020d5545f9c7da103c4f7ecf4)](https://scrutinizer-ci.com/g/crysalead/box/)

Box is dependency injection container which manage dependencies based on closures definition only. This approch has the particularity to be simple, easy, flexible and powerful and doesn't need to rely on the `ReflectionClass` layer of PHP.

## API

### Creating a Dependency container

```php
$box = new Box();
```

### Setting up a service

To to share a unique service over your application use `Box::service()`. A service can be a class, an instance, a string or any kind of value.

Example:

```php
$box->service('foo', new MyClass());
```

Each shared service defined with `Box::service()` can be retreived using `Box::get()`.

Note: If a share is defined using a closure, the closure will be executed once and the result will be returned for all next `Box::get()` calls.

### Setting up a factory

Use `Box::factory()` to setup a factory. It can be a closure:

```php
use MyClass;

$box->factory('foo', function($options = []) {
	return new MyClass($options);
});
```

Or a fully-namespaced class name.

```php
Box::factory('foo', 'otherNamespace\MyClass');
```

`Box::factory()` will create an new instance of the definition when resolved using `Box::get()`.

### Resolving a dependency

To resolve a dependency, use `Box::get()`:

```php
$box->get('foo', $params);
```

The second parameter is the array of parameters passed to the closure or directly to the constructor if the definition is a fully-namespaced class name string.

### Returing a wrapped dependency

Wrapping a dependency has the advantage to allow to inject a dependency without resolving it directly. To be able to lazily resolve a dependency you need to use `Box::wrap()`:

```php
$box->wrap('foo', $params, 'box\Wrapper');
```

The second parameter is the array of parameters passed to pass to the closure dependency definition. And the third parameter is the class name used for wrapping up the dependency.

Then dependency is resolved by doing:

```php
$dependency = $wrapper->get($params);
```

The second parameter is the array of parameters passed to the closure or directly to the constructor if the definition is a fully-namespaced class name string (if set, it overrides the one setted at the wrapping step).

### Cleanup

Use `Box::remove('foo')` to remove a specific dependency or `Box::clear()` to remove all dependencies.

## Global API

You can use the `box()` function to get/set a DI anywhere in your code.

### Setter
```
$box = box('mynamespace', new Box());
```

### Getter
```
$box = box('mynamespace');
```

### Unsetting a DI
```
$box = box('mynamespace', false);
```

### Clear everything.
```
$box = box(false);
```