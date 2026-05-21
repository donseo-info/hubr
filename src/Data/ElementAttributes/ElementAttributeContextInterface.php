<?php

namespace WPShop\WPCommunity\Data\ElementAttributes;

interface ElementAttributeContextInterface {

    /**
     * @return string
     */
    public function get_type();

    /**
     * @return string
     */
    public function get_name();

    /**
     * @return string
     */
    public function __toString();
}
