<?php

namespace Draw\DrawBundle\Request;

use Draw\DrawBundle\PropertyAccess\DynamicArrayObject;
use Draw\DrawBundle\Serializer\GroupHierarchy;
use JMS\Serializer\DeserializationContext;
use Draw\DrawBundle\Validator\Exception\ConstraintViolationListException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

class RequestBodyParamConverter extends \FOS\RestBundle\Request\RequestBodyParamConverter
{
    /**
     * @var GroupHierarchy
     */
    private $groupHierarchy;

    public function setGroupHierarchy(GroupHierarchy $groupHierarchy)
    {
        $this->groupHierarchy = $groupHierarchy;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $options = (array)$configuration->getOptions();

        if (isset($options['propertiesMap'])) {
            $content = new DynamicArrayObject(json_decode($request->getContent(), true));

            $propertyAccessor = PropertyAccess::createPropertyAccessor();

            foreach ($options['propertiesMap'] as $target => $source) {
                $propertyAccessor->setValue($content, $target, $request->attributes->get($source));
            }

            $property = new \ReflectionProperty(get_class($request), 'content');
            $property->setAccessible(true);
            $property->setValue($request, json_encode($content->getArrayCopy()));
        }

        $result = $this->execute($request, $configuration);

        if ($this->validationErrorsArgument && $request->attributes->has($this->validationErrorsArgument)) {
            if (count($errors = $request->attributes->get($this->validationErrorsArgument))) {
                $this->convertValidationErrorsToException($errors);
            }
        }

        return $result;
    }

    protected function convertValidationErrorsToException($errors)
    {
        $exception = new ConstraintViolationListException();
        $exception->setViolationList($errors);
        throw $exception;
    }

    public function configureDeserializationContext(DeserializationContext $context, array $options)
    {
        if (!isset($options['groups'])) {
            $options['groups'] = ['Default'];
        }

        $options['groups'] = $this->groupHierarchy->getReachableGroups($options['groups']);

        return parent::configureDeserializationContext($context, $options);
    }
}