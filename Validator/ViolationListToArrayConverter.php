<?php

namespace Draw\DrawBundle\Validator;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ViolationListToArrayConverter
{
    static public function convert(ConstraintViolationListInterface $constraintViolationList)
    {
        if (!$constraintViolationList->count()) {
            return array();
        }

        $errors = array();

        foreach ($constraintViolationList as $constraintViolation) {
            /* @var $constraintViolation \Symfony\Component\Validator\ConstraintViolationInterface */
            $errors[$constraintViolation->getPropertyPath()] = array(
                'propertyPath' => $constraintViolation->getPropertyPath(),
                'message' => $constraintViolation->getMessage(),
                'invalidValue' => $constraintViolation->getInvalidValue()
            );
        }

        return $errors;
    }
}