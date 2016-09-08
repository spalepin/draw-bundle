<?php

namespace Draw\DrawBundle\ErrorHandling;


use Exception;

class DefaultExceptionMessageFormatter implements ExceptionMessageFormatterInterface
{
    public function formatExceptionMessage(Exception $exception)
    {
        return $exception->getMessage();
    }

}