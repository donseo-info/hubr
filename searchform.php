<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<form role="search" method="get" class="search-form" action="<?php echo home_url( '/' ); ?>">
    <label class="search-form__label">
        <span class="screen-reader-text"><!--noindex--><?php echo _x( 'Search for:', 'wpcommunity' ) ?><!--/noindex--></span>
        <input type="search" class="search-field" placeholder="<?php echo esc_html__( 'Search…', 'wpcommunity' ) ?>" value="<?php echo get_search_query() ?>" name="s">
    </label>
    <button type="submit" class="btn search-form__submit"></button>
</form>
