<?php

namespace Draw\DrawBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CompilerPass implements CompilerPassInterface
{

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        $requestBodyConverter = $container->getDefinition("fos_rest.converter.request_body");

        $requestBodyConverter->addMethodCall(
                "setGroupHierarchy",
                [new Reference("draw.serializer.group_hierarchy")]
            );

        if($container->hasDefinition('jms_serializer.doctrine_object_constructor')) {
            $container->getDefinition("jms_serializer.doctrine_object_constructor")
                ->setClass('Draw\DrawBundle\Serializer\Construction\DoctrineObjectConstructor');
        }
    }
}