<?php

namespace Draw\DrawBundle\Test;

use Symfony\Component\PropertyAccess\PropertyAccess;

class PropertyHelper
{
    public $propertyPath;

    public $type;

    public $checkIsSameValue = false;

    public $value;

    public $mustReplaceValue = false;

    public $replaceWithValue;

    /**
     * @var RequestHelper
     */
    public $requestHelper;

    public function __construct(RequestHelper $requestHelper, $propertyPath)
    {
        $this->requestHelper = $requestHelper;
        $this->propertyPath = $propertyPath;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()->getPropertyAccessor();
    }

    public function isOfType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function isSameAs($value)
    {
        $this->checkIsSameValue = true;
        $this->value = $value;

        return $this;
    }

    public function replace($with = 'checked by Draw\DrawBundle\Test\PropertyHelper')
    {
        $this->mustReplaceValue = true;
        $this->replaceWithValue = $with;

        return $this;
    }

    public function attach()
    {
        $this->requestHelper->contentFilters[] = array($this, 'assert');

        return $this->requestHelper;
    }

    public function assert($data)
    {
        $decodedData = json_decode($data);

        $testCase = $this->requestHelper->testCase;

        $testCase->assertTrue(
            $this->propertyAccessor->isReadable($decodedData, $this->propertyPath),
            "Property does not exists.\nProperty path: " . $this->propertyPath . "\nData:" .
            json_encode($decodedData, JSON_PRETTY_PRINT)
        );

        $value = $this->propertyAccessor->getValue($decodedData, $this->propertyPath);
        if ($this->type) {
            $testCase->assertInternalType($this->type, $value, 'Property path: ' . $this->propertyPath);
        }

        if ($this->checkIsSameValue) {
            $testCase->assertSame($this->value, $value);
        }

        if ($this->mustReplaceValue) {
            $this->propertyAccessor->setValue($decodedData, $this->propertyPath, $this->replaceWithValue);
        }

        return json_encode($decodedData);
    }
}