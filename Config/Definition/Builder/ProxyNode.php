<?php

namespace Draw\DrawBundle\Config\Definition\Builder;
use Symfony\Component\Config\Definition\NodeInterface;

/**
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 */
class ProxyNode implements NodeInterface
{
    /**
     * @var NodeInterface
     */
    protected $node;

    /**
     * @param NodeInterface $nodeToProxy
     */
    public function __construct(NodeInterface $nodeToProxy)
    {
        $this->node = $nodeToProxy;
    }

    /**
     * Returns the name of the node.
     *
     * @return string The name of the node
     */
    public function getName()
    {
        return $this->node->getName();
    }

    /**
     * Returns the path of the node.
     *
     * @return string The node path
     */
    public function getPath()
    {
        return $this->node->getPath();
    }

    /**
     * Returns true when the node is required.
     *
     * @return bool    If the node is required
     */
    public function isRequired()
    {
        return $this->node->isRequired();
    }

    /**
     * Returns true when the node has a default value.
     *
     * @return bool    If the node has a default value
     */
    public function hasDefaultValue()
    {
        return $this->node->hasDefaultValue();
    }

    /**
     * Returns the default value of the node.
     *
     * @return mixed The default value
     * @throws \RuntimeException if the node has no default value
     */
    public function getDefaultValue()
    {
        return $this->node->getDefaultValue();
    }

    /**
     * Normalizes the supplied value.
     *
     * @param mixed $value The value to normalize
     *
     * @return mixed The normalized value
     */
    public function normalize($value)
    {
        return $this->node->normalize($value);
    }

    /**
     * Merges two values together.
     *
     * @param mixed $leftSide
     * @param mixed $rightSide
     *
     * @return mixed The merged values
     */
    public function merge($leftSide, $rightSide)
    {
        return $this->node->merge($leftSide, $rightSide);
    }

    /**
     * Finalizes a value.
     *
     * @param mixed $value The value to finalize
     *
     * @return mixed The finalized value
     */
    public function finalize($value)
    {
        return $this->node->finalize($value);
    }

    public function __call($method, $arguments)
    {
        return call_user_func_array(array($this->node,$method), $arguments);
    }
}