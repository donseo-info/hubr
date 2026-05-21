<?php

namespace WPShop\WPCommunity\Layout;

use WPShop\WPCommunity\Context;

class LoopContext {

    /**
     * @var int
     */
    protected $n = 0;

    /**
     * @var Context|null
     */
    protected $context;

    /**
     * @param int $current_count
     *
     * @return void
     */
    public function init( $current_count ) {
        $this->n = $current_count;
    }

    /**
     * @return void
     */
    public function increase_counter() {
        $this->n ++;
    }

    /**
     * @return int
     */
    public function get_counter() {
        return $this->n;
    }

    /**
     * @param Context $context
     *
     * @return void
     */
    public function set_context( Context $context ) {
        $this->context = $context;
    }

    /**
     * @return Context
     */
    public function get_context() {
        if ( ! $this->context ) {
            $this->context = Context::createFromWpQuery();
        }

        return $this->context;
    }
}
