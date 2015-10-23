<?php

namespace Draw\DrawBundle\Controller;

trait DoctrineControllerTrait
{
    /**
     * @return \Doctrine\Bundle\DoctrineBundle\Registry
     */
    abstract public function getDoctrine();

    public function persistAndFlush($entity)
    {
        $manager = $this->getDoctrine()->getManagerForClass(get_class($entity));
        $manager->persist($entity);
        $manager->flush($entity);
        return $entity;
    }

    public function flush($entity)
    {
        $this->getDoctrine()->getManagerForClass(get_class($entity))->flush($entity);
        return $entity;
    }

    public function persist($entity)
    {
        $this->getDoctrine()->getManagerForClass(get_class($entity))->persist($entity);
        return $entity;
    }

    public function removeAndFlush($entity)
    {
        $manager = $this->getDoctrine()->getManagerForClass(get_class($entity));
        $manager->remove($entity);
        $manager->flush($entity);
        return $entity;
    }

    /**
     * @param $class
     * @param $alias
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createOrmQueryBuilder($class, $alias)
    {
        return $this->getDoctrine()->getRepository($class)->createQueryBuilder($alias);
    }
}