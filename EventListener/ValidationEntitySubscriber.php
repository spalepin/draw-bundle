<?php

namespace Draw\DrawBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use LookLike\Bundle\LookLikeBundle\Exception\ConstraintViolationListException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidationEntitySubscriber implements EventSubscriber
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
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

        $violations = $this->validator->validate($entity, array('persist'));

        if ($violations->count() > 0) {
            $exception = new ConstraintViolationListException("" . $violations);
            $exception->setViolationList($violations);
            throw $exception;
        }
    }
}