<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link    https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package WPCommunity
 */

get_header();
?>

    <div class="site-content">
        <div class="content-area">
            <main id="primary" class="site-main">

                <div class="post-card">

                    <?php get_template_part( 'template-parts/elements/breadcrumbs' ) ?>

                    <div class="error404-lead">404</div>

                    <h1 class="page-title"><?php esc_html_e( 'Not found', 'wpcommunity' ); ?></h1>

                    <p><?php esc_html_e( 'It looks like this page has been deleted or never existed.', 'wpcommunity' ); ?></p>
                </div>

            </main><!-- #main -->
        </div>

        <?php get_sidebar(); ?>

    </div><!--.site-content-->

<?php
get_footer();
