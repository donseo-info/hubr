<?php

namespace WPShop\WPCommunity\Customizer\Control;

use WP_Customize_Control;

class SortableCheckboxesControl extends WP_Customize_Control {

    use DevicesTrait;

    public $type = 'wpcommunity_sortable_checkboxes';

    /**
     * @var string
     */
    public $list_name = null;

    /**
     * @var string
     */
    public $devices = 'desktop,tablet,mobile';

    /**
     * @var string
     */
    public $connect_with = null;

    /**
     * @var array|null
     */
    public $exclude_connect = null;

    /**
     * @var array
     */
    public $items = [];

    /**
     * @var bool
     */
    protected static $list_item_template_added = false;

    /**
     * SortableCheckboxesControl constructor.
     *
     * @param \WP_Customize_Manager $manager
     * @param string                $id
     * @param array                 $args
     */
    public function __construct( $manager, $id, $args = [] ) {
        add_action( 'customize_controls_print_footer_scripts', [ $this, '_print_items_template' ] );
        parent::__construct( $manager, $id, $args );
    }

    /**
     * @return void
     */
    public function _print_items_template() {
        if ( static::$list_item_template_added ) {
            return;
        }

        static::$list_item_template_added = true;

        ?>
        <script type="text/html" id="tmpl-customize-control-<?php echo $this->type ?>-item">
            <# var checked = data.enabled ? ' checked' : '' #>
            <# var disabled = data._editable ? '' : ' disabled' #>
            <li class="wpcommunity-customize-sortable-checkboxes-control__list-item ui-sortable"
                title="<?php echo esc_html_x( 'Selector', 'customize control', 'wpcommunity' ) ?>: {{ data.selector }}">
                <label>
                    <input type="checkbox"
                           name="{{ data.name }}"
                           data-selector="{{ data.selector }}"
                           data-display_prop="{{data.display_prop}}" {{{ checked }}}{{{disabled}}}>
                    <span>{{ data.label }}</span>

                    <# if (data.name === 'structure.header_elements.social') { #>
                    <a href="#" class="js-wpcommunity-customize-sortable-checkboxes--open-section" data-section="social_profiles"><?php echo __( 'configure', 'wpcommunity' ) ?></a>
                    <# } #>

                </label>
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
    public function content_template() {
        ?>
        <div class="wpcommunity-customize-sortable-checkboxes-control">
            <div class="wpcommunity-customize-sortable-checkboxes-control__header">
                <# if ( data.label ) { #>
                <span class="customize-control-title">{{{ data.label }}}</span>
                <# } #>
                <?php $this->render_devices_html( 'wpcommunity-customize-sortable-checkboxes-control' ); ?>
            </div>
            <div class="wpcommunity-customize-sortable-checkboxes-control__row">
                <ul class="wpcommunity-customize-sortable-checkboxes-control__list js-wpcommunity-customize-sortable-checkboxes-list"
                    data-devices="{{data.devices}}"
                    data-list_name="{{data.listName}}"
                    data-connect_with="{{data.connectWith}}"
                    data-exclude_connect="{{data.excludeConnect}}"
                >
                </ul>
            </div>
            <# if ( data.description ) { #>
            <span class="description customize-control-description">{{{ data.description }}}</span>
            <# } #>
        </div>
        <?php
    }

    /**
     * @inheritDoc
     */
    public function to_json() {
        parent::to_json();
        $this->json['items'] = array_map( function ( $item ) {
            return wp_parse_args( $item, [
                'label'     => '',
                '_editable' => true,
                '_sortable' => true,
            ] );
        }, $this->items );

        $this->json['devices']        = $this->devices;
        $this->json['listName']       = $this->list_name;
        $this->json['connectWith']    = $this->connect_with;
        $this->json['excludeConnect'] = is_array( $this->exclude_connect ) ? implode( ',', $this->exclude_connect ) : $this->exclude_connect;
    }

    /**
     * @inheritDoc
     */
    public function enqueue() {
        $scripts_suffix = wp_scripts_get_suffix();
        wp_enqueue_script(
            'wpcommunity-customize-sortable-checkboxes',
            get_template_directory_uri() . "/assets/admin/js/customizer/sortable-checkboxes-control{$scripts_suffix}.js",
            [ 'customize-controls', 'jquery', 'wpcommunity-customizer-utils' ],
            '1.0',
            true
        );
        wp_enqueue_style(
            'wpcommunity-customize-sortable-checkboxes',
            get_template_directory_uri() . "/assets/admin/css/customizer/sortable-checkboxes-control.min.css",
            [ 'wpcommunity-control-devices' ],
            '1.0'
        );
    }

    /**
     * @inheritDoc
     */
    public function render_content() {
    }
}
