<?php

namespace Draw\DrawBundle\Config\Definition\Builder;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 */
class AllowExtraPropertiesNodeBuilder extends NodeBuilder
{
    public function __construct()
    {
        parent::__construct();
        $this->nodeMapping['array'] = __NAMESPACE__.'\\ArrayNodeDefinition';
    }
} 