<?php

namespace Nucleus\Bundle\RestBundle\Validator;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ViolationListToArrayConverter extends \ArrayObject
{
    public function __get($key)
    {
        return $this->offsetGet($key);
    }

    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return true;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        if (!parent::offsetExists($offset)) {
            $this[$offset] = new static();
        }

        return parent::offsetGet($offset);
    }

    public function getArrayCopy()
    {
        $result = parent::getArrayCopy();
        foreach ($result as $key => $value) {
            if ($value instanceof static) {
                $result[$key] = $value->getArrayCopy();
            }
        }

        return $result;
    }

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

        $violationMap = new static();
        foreach ($errors as $path => $messages) {
            $propertyAccessor->setValue($violationMap, $path, $messages);
        }

        return $violationMap->getArrayCopy();
    }
}