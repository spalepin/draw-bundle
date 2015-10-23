<?php

namespace Draw\DrawBundle\HttpKernel\Bundle;

use Symfony\Component\HttpKernel\KernelInterface;

trait DependentBundleTrait
{
    /**
     * Return a array of bundle class that is needed for the dependency
     *
     * @return array[]
     */
    abstract public function getBundleDependencyClasses();

    /**
     * @param KernelInterface $kernel
     * @param \Symfony\Component\HttpKernel\Bundle\BundleInterface[] $currentBundles
     * @return \Symfony\Component\HttpKernel\Bundle\BundleInterface[]
     */
    public function registerDependentBundles(KernelInterface $kernel, array $currentBundles)
    {
        $bundles = array();

        $dependencies = $this->getBundleDependencyClasses();

        foreach ($dependencies as $dependency) {
            if (!$this->hasBundle($currentBundles, $dependency)) {
                $bundles[] = new $dependency();
            }
        }

        return $bundles;
    }

    protected function hasBundle(array $currentBundles, $class)
    {
        foreach ($currentBundles as $bundle) {
            if ($bundle instanceof $class) {
                return true;
            }
        }

        return false;
    }
}