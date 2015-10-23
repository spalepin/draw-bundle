<?php

namespace Draw\DrawBundle\HttpKernel;

use Symfony\Component\HttpKernel\Kernel as BaseHttpKernel;
use Draw\DrawBundle\HttpKernel\Bundle\DependentBundleInterface;

abstract class Kernel extends BaseHttpKernel
{
    public function registerBundles()
    {
        $bundles = $this->loadBundles();

        do {
            $last = $bundles;
            foreach ($bundles as $bundle) {
                if ($bundle instanceof DependentBundleInterface) {
                    $bundleDependencies = $bundle->registerDependentBundles($this, $bundles);
                    $bundles = array_merge($bundles, $bundleDependencies);
                }
            }
        } while (count($last) != count($bundles));

        return $bundles;
    }

    /**
     * @return \Symfony\Component\HttpKernel\Bundle\BundleInterface
     */
    public function loadBundles()
    {
        return array();
    }
}