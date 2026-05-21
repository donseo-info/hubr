<?php

namespace Wpshop\Core\Customizer;
use Wpshop\Core\Customizer\Control\Multicheck;
use Wpshop\Core\Customizer\Control\PageConstructor;
use Wpshop\Core\Customizer\Control\SortableCheckboxes;
use Wpshop\Core\Customizer\Control\RadioImage;
use Wpshop\Core\Customizer\Control\Typography;
use Wpshop\Core\ThemeOptions;

/**
 * Class Customizer
 *
 * @version 1.0.4
 *
 * Changelog
 *
 * 1.0.3    2019-12-03      Add ability to add custom control type
 * 1.0.3    2019-04-16      Fix: standard section colors, etc. don't work
 * 1.0.2    2019-04-11      Add PageConstructor control
 * 1.0.1    2019-01-27      Change `sortable_checkboxes` to `sortable-checkboxes`, change `radio_image` to `radio-image`
 * 1.0.0    2018-10-24      init
 */
class Customizer {

    /**
     * Theme options
     */
    protected $options;
    protected $controls;
    protected $defaults;

    /**
     * @var \WP_Customize_Manager
     */
    protected $wp_customize;


    public function __construct( ThemeOptions $options ) {
        $this->options = $options;
    }


    public function add_controls( $controls ) {
        $this->controls = $controls;
    }


    /**
     * @param \WP_Customize_Manager $wp_customize
     */
    public function init( $wp_customize ) {
        $this->wp_customize = $wp_customize;

        foreach ( $this->controls as $key => $item ) {

            // if section
            if ( isset( $item['controls'] ) ) {

                $this->add_section( $key, $item );

                // if panel
            } else if ( isset( $item['sections'] ) ) {

                $this->add_panel( $key, $item );

                foreach ( $item['sections'] as $section_name => $section ) {
                    $this->add_section( $section_name, $section, $key );
                }

            }
        }
    }

	/**
	 * @param string $name
	 * @param array  $args
	 * @param string $section_id
	 *
	 * @return void
	 */
	public function add_control( $name, $args, $section_id ) {

		$id = $this->options->option_name . '[' . $name . ']';

		$setting_type = 'option';
		if ( ! empty( $args['setting_type'] ) ) {
			$setting_type = $args['setting_type'];
			unset( $args['setting_type'] );
		}

		$default = ( isset( $this->defaults[ $name ] ) ) ? $this->defaults[ $name ] : '';

		// add setting
		$this->wp_customize->add_setting( $id, [
			'default' => $default,
			'type'    => $setting_type,
		] );

		// set id for add_control
		$args['settings'] = $id;

		// set section
		$args['section'] = $section_id;

		// custom controls
		if ( isset( $args['type'] ) ) {
			switch ( $args['type'] ) {
				case 'image':
					$this->wp_customize->add_control( new \WP_Customize_Image_Control( $this->wp_customize, $id, $args ) );
					break;
				case 'color':
					$this->wp_customize->add_control( new \WP_Customize_Color_Control( $this->wp_customize, $id, $args ) );
					break;
				case 'multicheck':
					$this->wp_customize->add_control( new Multicheck( $this->wp_customize, $id, $args ) );
					break;
				case 'sortable-checkboxes':
					$this->wp_customize->add_control( new SortableCheckboxes( $this->wp_customize, $id, $args ) );
					break;
				case 'radio-image':
					$this->wp_customize->add_control( new RadioImage( $this->wp_customize, $id, $args ) );
					break;
				case 'typography':
					$this->wp_customize->add_control( $control = new Typography( $this->wp_customize, $id, $args ) );
					$control->set_options( $this->options );
					break;
				case 'page-constructor':
					$this->wp_customize->add_control( $control = new PageConstructor( $this->wp_customize, $id, $args ) );
					$control->set_options( $this->options );
					break;
				default:
					if ( class_exists( $args['type'] ) ) {
						$class = $args['type'];
						$this->wp_customize->add_control( new $class( $this->wp_customize, $id, $args ) );
					} else {
						$this->wp_customize->add_control( $id, $args );
					}
					break;
			}
		} else {
			$this->wp_customize->add_control( $id, $args );
		}
	}


	public function add_section( $name, $section, $panel = '' ) {

        $args = $section;

        // remove controls
        unset( $args['controls'] );

        // set panel if not empty
        if ( ! empty( $panel ) ) $args['panel'] = $this->options->theme_slug . '_' . $panel;

        // add section
        if ( $name == 'colors' || $name == 'background_image' || $name == 'tagline' ) {
            $id = $name;
        } else {
            $id = implode( '_', [ $this->options->theme_slug, $panel, $name ] );
            $this->wp_customize->add_section( $id, $args );
        }

        foreach ( $section['controls'] as $control_id => $control ) {
            $this->add_control( $control_id, $control, $id );
        }

    }


    public function add_panel( $name, $args ) {
        $id = $this->options->theme_slug . '_' . $name;

        // remove sections
        unset( $args['sections'] );

        $this->wp_customize->add_panel( $id, $args );
    }


    /**
     * @param mixed $defaults
     */
    public function set_defaults( $defaults ) {
        $this->defaults = $defaults;
    }

}
