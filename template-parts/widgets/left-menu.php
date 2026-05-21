<?php

/**
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\WPCommunity\Widgets\MenuWidget;
use function WPShop\WPCommunity\the_attributes;

/**
 * @var array{'widget_args':array, 'items':array, 'enabled':callable, 'instance':MenuWidget} $args
 */

$widget = $args['instance'];

?>

<div class="widget widget-navigation">
    <?php foreach ( $args['items'] as $key => $item ): ?>
        <?php
        if ( ! $args['enabled']( $key ) ) {
            continue;
        }
        $is_item_active = $widget->is_active( $key, $item );
        ?>
        <div <?php the_attributes( 'menu-widget-item', [
            'classes' => [
                'widget-navigation__item',
                $is_item_active ? 'widget-navigation__item--active' : '',
            ],
        ] ); ?>>
            <?php if ( $is_item_active ): ?>
                <span class="removed-link current-menu-item">
                    <span class="widget-navigation__item-wrap">
                        <span class="widget-navigation__icon"><?php echo $item['icon'] ?></span>
                        <?php echo $item['title'] ?>
                    </span>
                </span>
            <?php else: ?>
                <a href="<?php echo $item['link'] ?>">
                    <span class="widget-navigation__item-wrap">
                        <span class="widget-navigation__icon"><?php echo $item['icon'] ?></span>
                        <?php echo $item['title'] ?>
                    </span>
                </a>
            <?php endif ?>
        </div>
    <?php endforeach ?>
</div>
