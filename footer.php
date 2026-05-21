<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link    https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WPCommunity
 */


?>

<footer id="colophon" class="site-footer">
    <?php

    /**
     * Hook for output footer elements
     *
     * [ru] Хук вывода элементов подвала
     *
     * @since 1.0
     *
     * @hooked \WPShop\WPCommunity\Layout\Layout::_output_footer_blocks()
     */
    do_action( 'wpcommunity/footer/footer' );
    ?>
</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
