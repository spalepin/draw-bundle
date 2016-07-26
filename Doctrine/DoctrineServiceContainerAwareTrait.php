<?php

namespace Draw\DrawBundle\Doctrine;

trait DoctrineServiceContainerAwareTrait
{
    use DoctrineServiceTrait;

    /**
     * @return \Doctrine\Bundle\DoctrineBundle\Registry
     */
    public function getDoctrine()
    {
        return $this->container->get('doctrine');
    }
}