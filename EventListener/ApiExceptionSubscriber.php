<?php

namespace Draw\DrawBundle\EventListener;

use Draw\DrawBundle\Validator\Exception\ConstraintViolationListException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    private $debug;

    private $exceptionCodes;

    const DEFAULT_STATUS_CODE           = 500;

    public function __construct(
        $debug,
        $exceptionCodes
    ) {
        $this->debug = $debug;
        $this->exceptionCodes = $exceptionCodes;
        $this->exceptionCodes[ConstraintViolationListException::class] = 400;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => array('onKernelException', 255)
        );
    }

    /**
     * @param $exception
     * @return int
     */
    protected function getStatusCode($exception)
    {
        return $this->isSubclassOf($exception, $this->exceptionCodes) ?: self::DEFAULT_STATUS_CODE;
    }

    /**
     * @param $exception
     * @param $exceptionMap
     * @return bool|string
     */
    protected function isSubclassOf($exception, $exceptionMap)
    {
        $exceptionClass = get_class($exception);
        $reflectionExceptionClass = new \ReflectionClass($exceptionClass);

        foreach ($exceptionMap as $exceptionMapClass => $value) {
            if ($value
                && ($exceptionClass === $exceptionMapClass || $reflectionExceptionClass->isSubclassOf($exceptionMapClass))
            ) {
                return $value;
            }
        }

        return false;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $request   = $event->getRequest();

        if($this->getFormat($request) != 'json'){
            return;
        }

        $statusCode = $this->getStatusCode($exception);

        $data = array(
            "code" => $statusCode,
            "message" => $exception->getMessage(),
        );

        if($exception instanceof ConstraintViolationListException) {
            $errors = array();
            foreach ($exception->getViolationList() as $constraintViolation) {
                /* @var $constraintViolation \Symfony\Component\Validator\ConstraintViolationInterface */
                $error = array(
                    'propertyPath' => $constraintViolation->getPropertyPath(),
                    'message' => $constraintViolation->getMessage(),
                    'invalidValue' => $constraintViolation->getInvalidValue(),
                    'code' => $constraintViolation->getCode()
                );

                if(!is_null($payload = $constraintViolation->getConstraint()->payload)) {
                    $error['payload'] = $payload;
                }

                $errors[] = $error;
            }

            $data['errors'] = $errors;
        }

        if($this->debug) {
            $data['detail'] = $this->getExceptionDetail($exception);
        }

        $event->stopPropagation();
        $event->setResponse(new JsonResponse($data, $statusCode));
    }

    /**
     * @param Request $request
     * @return string
     */
    private function getFormat(Request $request){
        $acceptKey = null;
        if($request->headers->has('Accept')){
            $acceptKey = 'Accept';
        }
        if($request->headers->has('accept')){
            $acceptKey = 'accept';
        }
        if(strstr($request->headers->get($acceptKey), 'json')){
            return 'json';
        }
        return 'other';
    }

    public function getExceptionDetail(\Exception $e)
    {
        $result = array(
            'class' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        );

        foreach(explode("\n",$e->getTraceAsString()) as $line) {
            $result['stack'][] = $line;
        }

        if($previous = $e->getPrevious()) {
            $result['previous'] = $this->getExceptionDetail($previous);
        }

        return $result;
    }
}