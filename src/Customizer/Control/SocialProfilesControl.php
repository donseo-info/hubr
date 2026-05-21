<?php

namespace WPShop\WPCommunity\Customizer\Control;

class SocialProfilesControl extends SortableCheckboxesControl {

    protected static $list_item_template_added__profiles = false;

    /**
     * @var string
     */
    public $type = 'wpcommunity_social_profiles';

    /**
     * @return void
     */
    public function _print_items_template() {
        if ( static::$list_item_template_added__profiles ) {
            return;
        }

        static::$list_item_template_added__profiles = true;

        ?>
        <script type="text/html" id="tmpl-customize-control-<?php echo $this->type ?>-item">
            <# var checked = data.enabled ? ' checked' : '' #>
            <# var disabled = data._editable ? '' : ' disabled' #>
            <li class="wpcommunity-customize-sortable-checkboxes-control__list-item wpcommunity-customize-social-profiles-control__list-item ui-sortable"
                title="<?php echo esc_html_x( 'Selector', 'customize control', 'wpcommunity' ) ?>: {{ data.selector }}">
                <input data-role="name"
                       type="checkbox"
                       name="{{ data.name }}"
                       data-selector="{{ data.selector }}"
                       data-display_prop="{{data.display_prop}}" {{{ checked }}}{{{disabled}}}>
                <input data-role="url"
                       type="text"
                       value="{{data.url}}"
                       placeholder="{{ data.label }}"
                       title="{{ data.label }}">
                <# if (data._sortable) { #>
                <i class="dashicons dashicons-menu wpcommunity-customize-sortable-checkboxes-control__sortable_handle js-wpcommunity-customize-sortable-checkboxes-handle ui-sortable-handle"></i>
                <# } #>
            </li>
        </script>
        <?php
    }

    /**
     * @inheritDoc
     */
    public function enqueue() {
        $scripts_suffix = wp_scripts_get_suffix();
        wp_enqueue_script(
            'wpcommunity-customize-social-profiles',
            get_template_directory_uri() . "/assets/admin/js/customizer/social-profiles-control{$scripts_suffix}.js",
            [
                'customize-controls',
                'wpcommunity-customize-sortable-checkboxes',
                'jquery',
                'wpcommunity-customizer-utils',
            ],
            '1.0',
            true
        );
        wp_enqueue_style(
            'wpcommunity-customize-social-profiles',
            get_template_directory_uri() . "/assets/admin/css/customizer/social-profiles-control.min.css",
            [ 'wpcommunity-control-devices', 'wpcommunity-customize-sortable-checkboxes' ],
            '1.0'
        );
    }
}
