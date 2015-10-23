<?php

namespace Draw\DrawBundle\HttpKernel\Bundle;

use Symfony\Component\HttpKernel\KernelInterface;

interface DependentBundleInterface
{
    /**
     * @param KernelInterface $kernel
     * @param \Symfony\Component\HttpKernel\Bundle\BundleInterface[] $currentBundles
     * @return \Symfony\Component\HttpKernel\Bundle\BundleInterface[]
     */
    public function registerDependentBundles(KernelInterface $kernel, array $currentBundles);
}