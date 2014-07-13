<?php

/**
 * Nothing significant here yet, but I'm working on it...
 */
class PolicyTraitTest extends PHPUnit_Framework_TestCase {

    private $policyTraitObject;

    public function setUp()
    {
        $this->policyTraitObject = $this->getObjectForTrait('Rookwood\\Turnstile\\User\\PolicyTrait');
    }

    public function testThatPolicyIsExecutedCorrectly()
    {
        $stub = $this->getMock('TestPolicy');

        $stub->expects($this->any())
             ->method('execute')
             ->will($this->returnValue(TRUE));
    }
} 