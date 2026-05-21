<?php

/**
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\WPCommunity\Admin\Settings;
use WPShop\WPCommunity\DefaultPages;
use function WPShop\WPCommunity\theme_container;

/**
 * @var array{'label':string} $args
 */

$settings      = theme_container()->get( Settings::class );
$default_pages = theme_container()->get( DefaultPages::class );

$page_options = [ '' => __( '(select page)', 'wpcommunity' ) ];
if ( $pages = get_pages() ) {
    foreach ( get_pages() as $page ) {
        $page_options[ $page->ID ] = "[{$page->ID}] {$page->post_title}";
    }
}
?>

<div class="wpshop-settings-header">
    <?php $settings->render_header( __( 'Pages', 'wpcommunity' ), null, $settings->doc_link( 'doc' ) . '/settings#pages' ); ?>
</div>

<?php foreach ( $default_pages->get_pages() as $page_name => $page ): ?>
    <div class="wpshop-settings-form-row">
        <?php $settings->render_select( "page.{$page_name}", $page['title'], $page_options ); ?>
    </div>
<?php endforeach; ?>

<div class="wpshop-settings-form-row">
    <div>
        <button class="wpshop-settings-button js-wpcommunity-create-pages"><?php echo __( 'Create And Setup The Pages', 'wpcommunity' ) ?></button>
    </div>
</div>
