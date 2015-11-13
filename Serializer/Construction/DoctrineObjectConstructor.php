<?php

namespace Draw\DrawBundle\Serializer\Construction;

use Doctrine\Common\Persistence\ManagerRegistry;
use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\VisitorInterface;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\DeserializationContext;

/**
 * Doctrine object constructor for new (or existing) objects during deserialization.
 */
class DoctrineObjectConstructor implements ObjectConstructorInterface
{
    private $managerRegistry;
    private $fallbackConstructor;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $managerRegistry Manager registry
     * @param ObjectConstructorInterface $fallbackConstructor Fallback object constructor
     */
    public function __construct(ManagerRegistry $managerRegistry, ObjectConstructorInterface $fallbackConstructor)
    {
        $this->managerRegistry = $managerRegistry;
        $this->fallbackConstructor = $fallbackConstructor;
    }

    /**
     * {@inheritdoc}
     */
    public function construct(
        VisitorInterface $visitor,
        ClassMetadata $metadata,
        $data,
        array $type,
        DeserializationContext $context
    ) {
        // Locate possible ObjectManager
        $objectManager = $this->managerRegistry->getManagerForClass($metadata->name);

        if (!$objectManager) {
            // No ObjectManager found, proceed with normal deserialization
            return $this->fallbackConstructor->construct($visitor, $metadata, $data, $type, $context);
        }

        //If the object is not found we relay on the fallback constructor
        if (is_null($object = $this->loadObject($metadata->name, $data))) {
            return $this->fallbackConstructor->construct($visitor, $metadata, $data, $type, $context);
        }

        return $object;
    }

    private function loadObject($class, $data)
    {
        $objectManager = $this->managerRegistry->getManagerForClass($class);
        $classMetadataFactory = $objectManager->getMetadataFactory();

        if ($classMetadataFactory->isTransient($class)) {
            return null;
        }

        if(!is_array($data)) {
            return null;
        }

        $classMetadata = $objectManager->getClassMetadata($class);
        $identifierList = array();

        foreach ($classMetadata->getIdentifierFieldNames() as $name) {
            if (!array_key_exists($name, $data)) {
                return null;
            }

            if ($classMetadata->hasAssociation($name)) {
                $data[$name] = $this->loadObject($classMetadata->getAssociationTargetClass($name), $data[$name]);
            }

            $identifierList[$name] = $data[$name];
        }

        return $objectManager->find($class, $identifierList);
    }
}