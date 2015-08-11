<?php

namespace Draw\DrawBundle\Serializer;

use JMS\Serializer\Annotation as Serializer;

trait EntityMetaDataTrait
{
    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("__metaData")
     * @Serializer\Type("array")
     * @Serializer\Groups("all")
     *
     * @return array
     */
    public function getMetaData()
    {
        return [
            'class' => __CLASS__
        ];
    }
}