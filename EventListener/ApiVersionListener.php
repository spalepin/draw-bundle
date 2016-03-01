<?php
namespace Draw\DrawBundle\EventListener;

use JMS\Serializer\Serializer;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class ApiVersionListener
{
    private $serializer;

    public function setSerializer(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $acceptedMimeType = $request->headers->get('Accept');

        // look for: application/vnd.matthias-v{version}+xml
        $versionAndFormat = str_replace('application/vnd.matthias', '', $acceptedMimeType);

        if (preg_match('/(\-v[0-9\.]+)?\+xml/', $versionAndFormat, $matches)) {
            $version = str_replace('-v', '', $matches[1]);

            $this->serializer->setVersion($version);
        }
    }
}