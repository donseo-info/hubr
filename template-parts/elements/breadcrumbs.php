<?php

/**
 * @version 1.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\WPCommunity\Features\Breadcrumbs;
use function WPShop\WPCommunity\theme_container;

theme_container()->get( Breadcrumbs::class )->output_breadcrumbs();
