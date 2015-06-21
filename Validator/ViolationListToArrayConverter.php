<?php

namespace Draw\DrawBundle\Validator;

use Draw\DrawBundle\PropertyAccess\DynamicArrayObject;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ViolationListToArrayConverter
{
    static public function convert(ConstraintViolationListInterface $constraintViolationList)
    {
        if (!$constraintViolationList->count()) {
            return array();
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        $errors = array();
        foreach ($constraintViolationList as $constraintViolation) {
            /* @var $constraintViolation \Symfony\Component\Validator\ConstraintViolationInterface */
            $errors[$constraintViolation->getPropertyPath()][] = $constraintViolation->getMessage();
        }

        $violationMap = new DynamicArrayObject(array());
        foreach ($errors as $path => $messages) {
            $propertyAccessor->setValue($violationMap, $path, $messages);
        }

        return $violationMap->getArrayCopy();
    }
}