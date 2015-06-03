<?php

namespace Draw\DrawBundle\Config\Definition\Builder;

/**
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 */
class ArrayNodeDefinition extends \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
{
    protected $acceptExtraKeys = false;

    /**
     * @param $value
     * @return $this
     */
    public function acceptExtraKeys($value)
    {
        $this->acceptExtraKeys = (bool)$value;
        return $this;
    }

    public function createNode()
    {
        $node = parent::createNode();
        if($this->acceptExtraKeys) {
            $node = new ExtraPropertiesProxyNode($node);
        }

        return $node;
    }
} 