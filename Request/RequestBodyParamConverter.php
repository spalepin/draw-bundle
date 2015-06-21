<?php

namespace Draw\DrawBundle\Request;

use Draw\DrawBundle\PropertyAccess\DynamicArrayObject;
use LookLike\Bundle\LookLikeBundle\Exception\ConstraintViolationListException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

class RequestBodyParamConverter extends \FOS\RestBundle\Request\RequestBodyParamConverter
{
    public function apply(Request $request, ParamConverter $configuration)
    {
        $options = (array) $configuration->getOptions();

        if(isset($options['propertiesMap'])) {
            $content = new DynamicArrayObject(json_decode($request->getContent(),true));

            $propertyAccessor = PropertyAccess::createPropertyAccessor();

            foreach($options['propertiesMap'] as $target => $source) {
                $propertyAccessor->setValue($content, $target, $request->attributes->get($source));
            }

            $property = new \ReflectionProperty(get_class($request), 'content');
            $property->setAccessible(true);
            $property->setValue($request, json_encode($content->getArrayCopy()));
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