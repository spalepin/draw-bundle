<?php

namespace Draw\DrawBundle\Validator;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Draw\DrawBundle\Validator\Exception\ConstraintViolationListException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ValidationEntitySubscriber implements EventSubscriber
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::prePersist
        );
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws ConstraintViolationListException
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        $violations = $this->container->get('validator')->validate($entity, array('persist'));

        if ($violations->count() > 0) {
            $exception = new ConstraintViolationListException("" . $violations);
            $exception->setViolationList($violations);
            throw $exception;
        }
    }
}