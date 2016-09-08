<?php

namespace Draw\DrawBundle\ErrorHandling;

use Exception;

interface ExceptionMessageFormatterInterface
{
    public function formatExceptionMessage(Exception $exception);
}