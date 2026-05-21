<?php
/**
 * The sidebar containing the main widget area
 *
 * @link    https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WPCommunity
 */

use function WPShop\WPCommunity\is_sidebar_hidden;

?>
<?php if ( ! is_sidebar_hidden('sidebar-1' ) ): ?>
    <aside class="widget-area widget-area--one">
        <div class="widget-area-inner">
            <?php if ( is_active_sidebar( 'sidebar-top' ) ): ?>
                <div class="widget-area-scroll">
                    <?php dynamic_sidebar( 'sidebar-top' ) ?>
                </div>
            <?php endif ?>

            <?php if ( is_active_sidebar( 'sidebar-bottom' ) ): ?>
                <div class="widget-footer">
                    <?php dynamic_sidebar( 'sidebar-bottom' ) ?>
                </div>
            <?php endif ?>

            <?php /* ?>
            <div class="widget">
                <div style="height: 200px;margin-top: 60px;background: #fff;border-radius: 10px;"></div>
            </div>
            <?php */ ?>
        </div><!--.widget-area-inner-->
    </aside>
<?php endif ?>

<?php if ( ! is_sidebar_hidden('sidebar-2' ) ): ?>
    <aside id="secondary" class="widget-area widget-area--two">
        <div class="widget-area-inner">
            <?php dynamic_sidebar( 'sidebar-2' ); ?>
        </div>
    </aside><!-- #secondary -->
<?php endif ?>
