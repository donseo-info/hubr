<?php

namespace WPShop\WPCommunity\Data\ElementAttributes;

class PostContext extends AbstractElementContext {

    /**
     * @param string $name
     */
    public function __construct( $name ) {
        parent::__construct( $name );
        $this->type = 'post';
    }
}
