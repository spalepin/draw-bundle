<?php

namespace Draw\DrawBundle\EventListener;

use JMS\Serializer\Exclusion\GroupsExclusionStrategy;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

class ViewResponseListener extends \FOS\RestBundle\EventListener\ViewResponseListener
{
    /**
     * Renders the parameters and template and initializes a new response object with the
     * rendered content.
     *
     * @param GetResponseForControllerResultEvent $event
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();
        /** @var \FOS\RestBundle\Controller\Annotations\View $configuration */
        $configuration = $request->attributes->get('_view');

        if($configuration) {
            if(!$configuration->getSerializerGroups()) {
                $configuration->setSerializerGroups(array(GroupsExclusionStrategy::DEFAULT_GROUP));
            }
        }

        parent::onKernelView($event);
    }
}