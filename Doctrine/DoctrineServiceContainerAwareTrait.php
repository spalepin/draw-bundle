<?php

namespace Draw\DrawBundle\Doctrine;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;

trait DoctrineServiceContainerAwareTrait
{
    use ContainerAwareTrait;
    use DoctrineServiceTrait;

    /**
     * @return \Doctrine\Bundle\DoctrineBundle\Registry
     */
    public function getDoctrine()
    {
        return $this->container->get('doctrine');
    }
}