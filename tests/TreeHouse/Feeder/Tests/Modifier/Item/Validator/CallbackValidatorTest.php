<?php

namespace TreeHouse\Feeder\Tests\Modifier\Item\Filter;

use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Exception\ValidationException;
use TreeHouse\Feeder\Modifier\Item\Validator\CallbackValidator;

class CallbackValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $validator = new CallbackValidator(function () {});

        $this->assertInstanceOf(CallbackValidator::class, $validator);
    }

    /**
     * @expectedException \TreeHouse\Feeder\Exception\UnexpectedTypeException
     */
    public function testInvalidConstructor()
    {
        new CallbackValidator(true);
    }

    /**
     * @expectedException \TreeHouse\Feeder\Exception\ValidationException
     */
    public function testFilter()
    {
        $validator = new CallbackValidator(function () {
            throw new ValidationException();
        });

        $validator->validate(new ParameterBag());
    }
}
