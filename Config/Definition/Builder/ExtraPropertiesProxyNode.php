<?php

namespace Draw\DrawBundle\Config\Definition\Builder;

use Symfony\Component\Config\Definition\ArrayNode;

/**
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 */
class ExtraPropertiesProxyNode extends ProxyNode
{
    public function normalize($value)
    {
        if ($this->node instanceof ArrayNode) {
            $extraValue = array_diff_key($value, $this->node->getChildren());
            $value = array_intersect_key($value, $this->node->getChildren());
            return array_merge($this->node->normalize($value), $extraValue);
        } else {
            return $this->node->normalize($value);
        }
    }
} 