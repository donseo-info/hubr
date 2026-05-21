<?php

/**
 * @version 1.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\WPCommunity\Customizer\Customizer;
use WPShop\WPCommunity\Features\ViewsCounter;
use function WPShop\WPCommunity\theme_container;

$views      = theme_container()->get( ViewsCounter::class );
$customizer = theme_container()->get( Customizer::class );
?>

<?php if ( $customizer->get_option( 'structure.post.show_views_count' ) ): ?>
    <div class="post-meta__views">
        <svg width="20" height="20">
            <use xlink:href="#ico-eye"></use>
        </svg>
        <?php $views->the_views(); ?>
    </div>
<?php endif ?>
