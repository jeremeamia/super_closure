<?php

require __DIR__ . '/../vendor/autoload.php';

class IncreMaker
{
    private $step = 1;

    public function setStep($step)
    {
        $this->step = $step;

        return $this;
    }

    public function getIncrementFn()
    {
        return function ($value) {
            return $value + $this->step;
        };
    }
}

$serializer = new SuperClosure\Serializer;
$increMaker = (new IncreMaker)->setStep(5);
$increment = $increMaker->getIncrementFn();

echo $increment(6) . "\n";
//> 11

$data = $serializer->getClosureData($increment);
assert($data['binding'] === $increMaker);
assert($data['scope'] === 'IncreMaker');

$serialized = $serializer->serialize($increment);
echo $serialized . "\n";
$unserialized = $serializer->unserialize($serialized);

echo $unserialized(6) . "\n";
//> 11
