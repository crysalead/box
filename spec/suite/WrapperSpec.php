<?php
namespace box\spec\suite;

use kahlan\plugin\Stub;

use stdClass;
use box\Box;
use box\Wrapper;
use box\BoxException;

describe("Wrapper", function() {

    beforeEach(function() {
            $this->box = new Box();
    });

    describe("->__construct()", function() {

        it("throws an exception if the `'box'` parameter is empty", function() {

            $closure = function(){
                $wrapper = new Wrapper(['box' => null, 'name' => 'spec.stdClass']);
            };

            expect($closure)->toThrow(new BoxException("Error, the wrapper require at least `'box'` & `'name'` to not be empty."));

        });

        it("throws an exception if the `'name'` parameter is empty", function() {

            $this->box->factory('spec.stdClass', function() { return new stdClass; });

            $closure = function(){
                $wrapper = new Wrapper(['box' => $this->box, 'name' => '']);
            };

            expect($closure)->toThrow(new BoxException("Error, the wrapper require at least `'box'` & `'name'` to not be empty."));

        });

    });

    describe("->get()", function() {

        it("resolve a dependency", function() {

            $this->box->factory('spec.stdClass', function() { return new stdClass; });
            $wrapper = new Wrapper(['box' => $this->box, 'name' => 'spec.stdClass']);

            $dependency = $wrapper->get();
            expect($dependency)->toBeAnInstanceOf("stdClass");

            expect($wrapper->get())->toBe($dependency);

        });

        it("throws an exception if the dependency doesn't exists", function() {

            $wrapper = new Wrapper(['box' => $this->box, 'name' => 'spec.stdUnexistingClass']);
            expect(function() use ($wrapper) { $wrapper->get(); })->toThrow(new BoxException());

        });

        it("passes parameters to the Closure", function() {

            $this->box->factory('spec.arguments', function($options) { return $options; });
            $options = [
                'options1' => 'value1',
                'options2' => 'value2'
            ];
            $wrapper = new Wrapper([
                'box'    => $this->box,
                'name'   => 'spec.arguments',
                'params' => [$options]
            ]);
            expect($wrapper->get())->toBe($options);

        });

        it("override passed parameters to the Closure", function() {

            $this->box->factory('spec.arguments', function($options) { return $options; });
            $options = [
                'options1' => 'value1',
                'options2' => 'value2'
            ];
            $wrapper = new Wrapper([
                'box'    => $this->box,
                'name'   => 'spec.arguments',
                'params' => [$options]
            ]);
            $overrided = [
                'options3' => 'value3',
                'options4' => 'value4'
            ];
            expect($wrapper->get([$overrided]))->toBe($overrided);

        });

    });

});