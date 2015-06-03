<?php

namespace Draw\DrawBundle\Request;

use LookLike\Bundle\LookLikeBundle\Exception\ConstraintViolationListException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class RequestBodyParamConverter extends  \FOS\RestBundle\Request\RequestBodyParamConverter
{
    public function apply(Request $request, ParamConverter $configuration)
    {
        $options = (array) $configuration->getOptions();

        if(isset($options['propertiesMap'])) {
            $content = json_decode($request->getContent(),true);
            foreach($options['propertiesMap'] as $source => $target) {
                $content[$target] = $request->attributes->get($source);
            }

            $property = new \ReflectionProperty(get_class($request), 'content');
            $property->setAccessible(true);
            $property->setValue($request, json_encode($content));
        }

        $result = $this->execute($request, $configuration);

        if($this->validationErrorsArgument && $request->attributes->has($this->validationErrorsArgument)) {
            if(count($errors = $request->attributes->get($this->validationErrorsArgument))) {
                $exception = new ConstraintViolationListException();
                $exception->setViolationList($errors);
                throw $exception;
            }
        }

        return $result;
    }
}