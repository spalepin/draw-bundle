<?php

namespace Draw\DrawBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class RestControllerGenerator extends Generator
{
    private $filesystem;

    /**
     * Constructor.
     *
     * @param Filesystem $filesystem A Filesystem instance
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function generate(BundleInterface $bundle, $controller, $entity, $override)
    {
        $dir = $bundle->getPath();
        $controllerFile = $dir.'/Controller/'.$controller.'Controller.php';
        if (!$override && file_exists($controllerFile)) {
           throw new \RuntimeException(sprintf('Controller "%s" already exists', $controller));
        }

        $sections = explode('\\', $entity);
        $shortName = end($sections);

        $underscored = strtolower(
            preg_replace(
                ["/([A-Z]+)/", "/_([A-Z]+)([A-Z][a-z])/"],
                ["_$1", "_$1_$2"],
                lcfirst($shortName)
            )
        );

        $parameters = array(
            'namespace'       => $bundle->getNamespace(),
            'bundle'          => $bundle->getName(),
            'entityShortName' => $shortName,
            'entityClass'     => $entity,
            'entityUnderScore'=> $underscored,
            'entityDash'      => str_replace('_','-', $underscored),
            'controller'      => $controller,
        );

        $this->renderFile('controller/rest/Controller.php.twig', $controllerFile, $parameters);
        $this->renderFile('controller/rest/ControllerTest.php.twig', $dir.'/Tests/Controller/'.$controller.'ControllerTest.php', $parameters);
    }
}