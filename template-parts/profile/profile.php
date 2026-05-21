<?php

/**
 * @version 1.0
 */

use WPShop\WPCommunity\Layout\Profile\ProfileTabs;
use function WPShop\WPCommunity\theme_container;

$tabs = theme_container()->get( ProfileTabs::class );
$tabs->register_tabs();

$default_active = $tabs->get_default_active_tab();
?>
<div class="post-card__header">
    <h1 class="post-card__title"><?php _e( 'Profile', 'wpcommunity' ) ?></h1>
</div>

<div class="post-card__content">
    <div class="profile-form">
        <div class="tabs-nav-container">
            <button class="tabs-nav-scroll tabs-nav-scroll--backward js-wpcommunity-tabs-nav-scroll" data-direction="backward">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 14.542 5.846 8.32a1.073 1.073 0 0 0-1.53 0 1.102 1.102 0 0 0 0 1.546l6.614 6.686c.59.597 1.55.597 2.14 0l6.613-6.686a1.102 1.102 0 0 0 0-1.546 1.073 1.073 0 0 0-1.529 0L12 14.542Z" fill="currentColor"></path>
                </svg>
            </button>
            <button class="tabs-nav-scroll tabs-nav-scroll--forward js-wpcommunity-tabs-nav-scroll" data-direction="forward">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12 14.542 5.846 8.32a1.073 1.073 0 0 0-1.53 0 1.102 1.102 0 0 0 0 1.546l6.614 6.686c.59.597 1.55.597 2.14 0l6.613-6.686a1.102 1.102 0 0 0 0-1.546 1.073 1.073 0 0 0-1.529 0L12 14.542Z" fill="currentColor"></path>
                </svg>
            </button>
            <ul class="tabs-nav js-tabs-nav" role="tablist">
                <?php foreach ( $tabs->get_prepared_tabs() as $tab_key => $tab ): ?>
                    <li class="tabs-nav__item<?php echo $tab_key === $default_active ? ' active' : '' ?> js-tabs-nav-item" data-target="#tab-<?php echo esc_attr( $tab_key ) ?>">
                        <?php $tab->the_nav() ?>
                    </li>
                <?php endforeach ?>
            </ul>
        </div>

        <div class="tabs-content js-tabs-content">
            <?php foreach ( $tabs->get_prepared_tabs() as $tab_key => $tab ): ?>
                <div class="tabs-content__item<?php echo $tab_key === $default_active ? ' active' : '' ?>" id="tab-<?php echo esc_attr( $tab_key ) ?>" role="tabpanel">
                    <?php $tab->the_content() ?>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</div>
