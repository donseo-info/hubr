<?php

/**
 * @version 1.0
 */

use WPShop\WPCommunity\FrontendPublish;
use function WPShop\WPCommunity\theme_container;

$frontend_publish = theme_container()->get( FrontendPublish::class );

$drafts_query = $frontend_publish->get_drafts_query();
if ( $drafts_query->have_posts() ): ?>
    <h3><?php echo __( 'Drafts', 'wpcommunity' ) ?></h3>
    <ul>
        <?php while ( $drafts_query->have_posts() ):
            $drafts_query->the_post(); ?>
            <li>
                <a href="<?php echo $frontend_publish->get_edit_link( get_the_ID() ) ?>"><?php the_title() ?></a>
            </li>
        <?php endwhile;
        wp_reset_postdata();
        ?>
    </ul>
<?php endif ?>

<?php
$pending_query = $frontend_publish->get_pending_query();
if ( $pending_query->have_posts() ): ?>
    <h3><?php echo __( 'Pending' ) ?></h3>
    <ul>
        <?php while ( $pending_query->have_posts() ):
            $pending_query->the_post(); ?>
            <li>
                <a href="<?php echo $frontend_publish->get_edit_link( get_the_ID() ) ?>"><?php the_title() ?></a>
            </li>
        <?php endwhile;
        wp_reset_postdata();
        ?>
    </ul>
<?php endif ?>

<?php
$published_query = $frontend_publish->get_published_query();
if ( $published_query->have_posts() ): ?>
    <h3><?php echo __( 'Published', 'wpcommunity' ) ?></h3>
    <ul>
        <?php while ( $published_query->have_posts() ):
            $published_query->the_post(); ?>
            <li>
                <a href="<?php the_permalink(); ?>"><?php the_title() ?></a>
                <a href="<?php echo $frontend_publish->get_edit_link( get_the_ID() ) ?>" style="color: var(--wpsc-text-color)"><?php echo __( 'edit', 'wpcommunity' ) ?></a>
            </li>
        <?php endwhile;
        wp_reset_postdata();
        ?>
    </ul>
<?php endif ?>
