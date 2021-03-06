<?php
namespace Lead\Box\Spec\Suite;

use stdClass;
use Lead\Box\Box;
use Lead\Box\BoxException;

class MyTestClass
{
    public $params = [];

    public function __construct()
    {
        $this->params = func_get_args();
    }
}

describe("Box", function() {

    beforeEach(function() {
        $this->box = new Box();
    });

    describe("->factory()", function() {

        it("binds a closure", function() {

            $this->box->factory('spec.stdClass', function() { return new stdClass; });
            expect($this->box->get('spec.stdClass'))->toBeAnInstanceOf("stdClass");

        });

        it("binds a classname", function() {

            $this->box->factory('spec.stdClass', "stdClass");
            expect($this->box->get('spec.stdClass'))->toBeAnInstanceOf("stdClass");

        });

        it("passes all arguments to the Closure", function() {

            $this->box->factory('spec.arguments', function() { return func_get_args(); });
            $params = [
                'params1',
                'params2'
            ];
            expect($this->box->get('spec.arguments', $params[0],  $params[1]))->toBe($params);

        });

        it("passes all arguments to the constructor", function() {

            $this->box->factory('spec.arguments', 'Lead\Box\Spec\Suite\MyTestClass');
            $params = [
                'params1',
                'params2'
            ];
            expect($this->box->get('spec.arguments', $params[0],  $params[1])->params)->toBe($params);

        });

        it("creates different instances", function() {

            $this->box->factory('spec.stdClass', "stdClass");

            $instance1 = $this->box->get('spec.stdClass');
            $instance2 = $this->box->get('spec.stdClass');
            expect($instance1)->not->toBe($instance2);

        });

        it("returns itself", function() {

            expect($this->box->factory('spec.stdClass', "stdClass"))->toBe($this->box);

        });

        it("throws an exception if the definition is not a string or a Closure", function() {

            $expected = new BoxException("Error `spec.instance` is not a closure definition dependency can't use it as a factory definition.");

            $closure = function(){
                $this->box->factory('spec.instance', new stdClass);
            };
            expect($closure)->toThrow($expected);

            $closure = function(){
                $this->box->factory('spec.instance', []);
            };
            expect($closure)->toThrow($expected);

        });

    });

    describe("->service()", function() {

        it("shares a string", function() {

            $this->box->service('spec.stdClass', "stdClass");
            expect($this->box->get('spec.stdClass'))->toBe("stdClass");

        });

        it("shares an instance", function() {

            $instance = new stdClass;
            $this->box->service('spec.instance', $instance);
            expect($this->box->get('spec.instance'))->toBe($instance);

        });

        it("gets the same instance", function() {

            $this->box->service('spec.stdClass', new stdClass);
            $instance1 = $this->box->get('spec.stdClass');
            $instance2 = $this->box->get('spec.stdClass');
            expect($instance1)->toBe($instance2);

        });

        it("shares a singleton using the closure syntax", function() {

            $this->box->service('spec.stdClass', function() { return new stdClass; });
            $instance1 = $this->box->get('spec.stdClass');
            $instance2 = $this->box->get('spec.stdClass');
            expect($instance1)->toBe($instance2);
            expect($instance1)->toBeAnInstanceOf("stdClass");

        });

        it("shares a closure", function() {

            $closure = function() {
                return "Hello World!";
            };
            $this->box->service('spec.closure', function() use ($closure) { return $closure; });

            $closure1 = $this->box->get('spec.closure');
            $closure2 = $this->box->get('spec.closure');
            expect($closure1)->toBe($closure2);
            expect($closure1)->toBeAnInstanceOf("Closure");
            expect($closure1())->toBe("Hello World!");

        });

        it("returns itself", function() {

            expect($this->box->service('spec.stdClass', "stdClass"))->toBe($this->box);

        });

    });

    describe("has", function() {

        it("returns `false` if the Box is empty", function() {
            expect($this->box->has('spec.hello'))->toBe(false);
        });

        it("returns `true` if the Box contain the bind dependency", function() {
            $this->box->factory('spec.stdClass', function() { return new stdClass; });
            expect($this->box->has('spec.stdClass'))->toBe(true);
        });

        it("returns `true` if the Box contain the share dependency", function() {
            $this->box->service('spec.hello', "Hello World!");
            expect($this->box->has('spec.hello'))->toBe(true);
        });
    });

    describe("->get()", function() {

        it("throws an exception if the dependency doesn't exists", function() {

            $closure = function(){
                $this->box->get('spec.stdUnexistingClass');
            };
            expect($closure)->toThrow(new BoxException("Unexisting `spec.stdUnexistingClass` definition dependency."));

        });
    });


    describe("->__get()/->__set()", function() {

        it("gets/sets a service", function() {

            $this->box->stdClass = 'stdClass';
            expect($this->box->stdClass)->toBe('stdClass');

        });

    });

    describe("->wrap()", function() {

        it("returns a dependency container", function() {

            $this->box->factory('spec.stdClass', function() { return new stdClass; });
            $wrapper = $this->box->wrap('spec.stdClass');
            expect($wrapper)->toBeAnInstanceOf('Lead\Box\Wrapper');

            $dependency = $wrapper->get();
            expect($dependency)->toBeAnInstanceOf("stdClass");

            expect($wrapper->get())->toBe($dependency);

        });

        it("throws an exception if the dependency definition is not a closure doesn't exists", function() {

            $closure = function() {
                $this->box->service('spec.stdClass', new stdClass);
                $wrapper = $this->box->wrap('spec.stdClass');
            };

            expect($closure)->toThrow(new BoxException("Error `spec.stdClass` is not a closure definition dependency can't be wrapped."));

        });

        it("throws an exception if the dependency doesn't exists", function() {

            $closure = function() {
                $this->box->wrap('spec.stdUnexistingClass');
            };

            expect($closure)->toThrow(new BoxException("Unexisting `spec.stdUnexistingClass` definition dependency."));

        });
    });

    describe("->remove()", function() {

        it("remove a bind", function() {

            $this->box->factory('spec.stdClass', function() { return new stdClass; });
            expect($this->box->has('spec.stdClass'))->toBe(true);

            $this->box->remove('spec.stdClass');
            expect($this->box->has('spec.stdClass'))->toBe(false);

        });

    });

    describe("->clear()", function() {

        it("clears all binds & shares", function() {

            $this->box->factory('spec.stdClass', "stdClass");
            $this->box->service('spec.hello', "Hello World!");
            expect($this->box->has('spec.stdClass'))->toBe(true);
            expect($this->box->has('spec.hello'))->toBe(true);

            $this->box->clear();
            expect($this->box->has('spec.stdClass'))->toBe(false);
            expect($this->box->has('spec.hello'))->toBe(false);

            $closure = function() {
                $this->box->get('spec.stdClass');
            };
            expect($closure)->toThrow(new BoxException("Unexisting `spec.stdClass` definition dependency."));

            $closure = function() {
                $this->box->get('spec.hello');
            };
            expect($closure)->toThrow(new BoxException("Unexisting `spec.hello` definition dependency."));

        });

    });

});

describe("box()", function() {

    beforeEach(function() {
        box(false);
    });

    it("adds a box", function() {

        $box = new Box();
        $actual = box('box.spec', $box);

        expect($actual)->toBe($box);
    });

    it("gets a box", function() {

        $box = new Box();
        box('box.spec', $box);
        $actual = box('box.spec');

        expect($actual)->toBe($box);
    });

    it("adds a default box", function() {

        $box = new Box();

        expect(box($box))->toBe($box);
        expect(box())->toBe($box);

    });

    it("gets a default box", function() {

        $box = box();
        expect($box)->toBeAn('object');
        expect(box())->toBe($box);

    });

    it("removes a box", function() {

        $box = new Box();
        box('box.spec', $box);
        box('box.spec', false);

        $closure = function() {
            box('box.spec');
        };
        expect($closure)->toThrow("Unexisting box `'box.spec'`.");
    });

    it("removes all boxes", function() {

        $box = new Box();
        box('box.spec1', $box);
        box('box.spec2', $box);
        box(false);

        $closure = function() {
            box('box.spec1');
        };
        expect($closure)->toThrow("Unexisting box `'box.spec1'`.");

        $closure = function() {
            box('box.spec2');
        };
        expect($closure)->toThrow("Unexisting box `'box.spec2'`.");
    });

    it("throws an exception when trying to get an unexisting box", function() {
        $closure = function() {
            box('box.spec');
        };
        expect($closure)->toThrow("Unexisting box `'box.spec'`.");
    });

});
