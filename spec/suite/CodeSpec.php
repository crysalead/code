<?php
namespace code\spec\suite;

use Exception;
use InvalidArgumentException;
use code\TimeoutException;
use code\Code;

describe("Code", function() {

    declare(ticks = 1) {

        describe("::run()", function() {

            it("runs the passed closure", function () {

                $start = microtime(true);

                expect(Code::run(function() {return true;}, 1))->toBe(true);

                $end = microtime(true);
                expect($end - $start)->toBeLessThan(1);

            });

            it("throws an exception if an invalid closure is provided", function() {

                $closure = function() {
                    Code::run("invalid", 1);
                };

                expect($closure)->toThrow(new InvalidArgumentException());

            });

            it("throws an exception on timeout", function() {

                $start = microtime(true);

                $closure = function() {
                    Code::run(function() {
                        while(true) sleep(1);
                    }, 1);
                };

                expect($closure)->toThrow(new TimeoutException('Timeout reached, execution aborted after 1 second(s).'));

                $end = microtime(true);
                expect($end - $start)->toBeGreaterThan(1);

            });

            it("throws all unexpected exceptions", function() {

                $closure = function() {
                    Code::run(function() {
                        throw new Exception("Error Processing Request");
                    }, 1);
                };

                expect($closure)->toThrow(new Exception("Error Processing Request"));

            });

            it("throws timeout exceptions even on ignore mode", function() {

                $start = microtime(true);

                $closure = function() {
                    Code::run(function() {
                        while(true) sleep(1);
                    }, 1, true);
                };

                expect($closure)->toThrow(new TimeoutException('Timeout reached, execution aborted after 1 second(s).'));

                $end = microtime(true);
                expect($end - $start)->toBeGreaterThan(1);

            });

            it("ignores exceptions when the third parameter is true", function() {

                $closure = function() {
                    Code::run(function() {
                        throw new Exception("Error Processing Request");
                    }, 1, true);
                };

                expect($closure)->not->toThrow(new Exception("Error Processing Request"));

            });

        });

    }

    describe("::spin()", function() {

        it("runs the passed closure", function () {

            $start = microtime(true);

            expect(Code::spin(function() {return true;}, 1))->toBe(true);

            $end = microtime(true);
            expect($end - $start)->toBeLessThan(1);

        });

        it("throws an exception if an invalid closure is provided", function() {

            $closure = function() {
                Code::spin("invalid", 1);
            };

            expect($closure)->toThrow(new InvalidArgumentException());

        });

        it("throws an exception on timeout", function() {

            $start = microtime(true);

            $closure = function() {
                Code::spin(function() {}, 1);
            };

            expect($closure)->toThrow(new TimeoutException('Timeout reached, execution aborted after 1 second(s).'));

            $end = microtime(true);
            expect($end - $start)->toBeGreaterThan(1);

        });

        it("throws timeout exceptions even on ignore mode", function() {

            $start = microtime(true);

            $closure = function() {
                Code::spin(function() {}, 1, true);
            };

            expect($closure)->toThrow(new TimeoutException('Timeout reached, execution aborted after 1 second(s).'));

            $end = microtime(true);
            expect($end - $start)->toBeGreaterThan(1);

        });

        it("ignores exceptions when the third parameter is true", function() {

            $closure = function() {
                Code::spin(function() {
                    throw new Exception("Error Processing Request");
                }, 1, true);
            };

            expect($closure)->not->toThrow(new Exception("Error Processing Request"));

        });

    });

});