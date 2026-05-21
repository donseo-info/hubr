<?php

namespace Wpshop\Settings;

interface MaintenanceInterface {

    /**
     * @param string $license
     *
     * @return void
     */
    public function init_updates( $license );

    /**
     * @param string   $license
     * @param callable $cb
     *
     * @return bool|\WP_Error
     */
    public function activate( $license, $cb );

    /**
     * @return string
     */
    public function get_type();
}
