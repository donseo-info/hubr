<?php

namespace WPShop\WPCommunity\Data\ElementAttributes;

abstract class AbstractElementContext implements ElementAttributeContextInterface {

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type = 'default';

    /**
     * @param string $name
     */
    public function __construct( $name ) {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function __toString() {
        return "{$this->type}:{$this->name}";
    }

    /**
     * @return string
     */
    public function get_type() {
        return $this->type;
    }

    /**
     * @return string
     */
    public function get_name() {
        return $this->name;
    }
}
