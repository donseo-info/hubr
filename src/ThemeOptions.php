<?php

namespace WPShop\WPCommunity;

/**
 * @deprecated
 */
class ThemeOptions {

	protected $option_menu_slug = 'wpcommunity';
	protected $settings_page = 'wpcommunity_page';
	protected $option_group = 'wpcommunity_option';

	public $option_name = 'wpcommunity_options';
	public $defaults = [];

	public function __construct() {
		$this->defaults = [
			'page_profile'   => '',
			'page_join'      => '',
			'page_about'     => '',
			'page_popular'   => '',
			'page_subs'      => '',
			'page_top'       => '',
			'page_bookmarks' => '',
			'page_publish'   => '',
			'page_order'     => '',

			'vote_method' => 'users',
		];
	}

	public function init() {

		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'admin_init', array( $this, 'init_settings' ) );

	}


	public function get_options() {
		$options = get_option( $this->option_name, [] );
		if ( empty( $options ) ) {
			$options = $this->defaults;
		}

		return $options;
	}

	public function get_option( $option ) {
		$options = $this->get_options();

		if ( isset( $options[ $option ] ) ) {
			return $options[ $option ];
		}

		if ( isset( $this->defaults[ $option ] ) ) {
			return $this->defaults[ $option ];
		}

		return null;
	}


	public function add_admin_menu() {
		add_options_page(
			'WPCommunity',
			'WPCommunity',
			'manage_options',
			$this->option_menu_slug,
			[ $this, 'page_callback' ]
		);
	}

	public function page_callback() {

		// Check required user capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wpcommunity' ) );
		}

		// Admin Page Layout
		echo '<div class="wrap">' . PHP_EOL;
		echo '	<h1>' . get_admin_page_title() . '</h1>' . PHP_EOL;

		echo '	<form action="options.php" method="post">' . PHP_EOL;

		settings_fields( $this->option_group );
		do_settings_sections( $this->settings_page );
		submit_button();

		echo '	</form>' . PHP_EOL;

		echo '</div>';
	}

	public function init_settings() {

		// todo 4.7 минимальная тк третий аргумент тут массив
		// todo вынести настройки по дефолту
		register_setting(
			$this->option_group,
			$this->option_name, // $this->options->settings_name
			[
				'sanitize_callback' => [ $this, 'sanitize_callback' ],
				'default'           => $this->defaults,
			]
		);


		// Section
		$section_id    = THEME_SLUG . '_section_pages';
        $section_title = __( 'Pages', 'wpcommunity' );
		add_settings_section( $section_id, $section_title, '', $this->settings_page );

		// Fields
		$name = 'page_profile';
		add_settings_field( THEME_SLUG . '_' . $name, __( 'Profile', 'wpcommunity' ),
			[ $this, 'field_select_pages' ], $this->settings_page, $section_id, [ 'name' => $name ],
		);

		$name = 'page_join';
		add_settings_field( THEME_SLUG . '_' . $name, __( 'Join', 'wpcommunity' ),
			[ $this, 'field_select_pages' ], $this->settings_page, $section_id, [ 'name' => $name ],
		);

		$name = 'page_about';
		add_settings_field( THEME_SLUG . '_' . $name, __( 'About', 'wpcommunity' ),
			[ $this, 'field_select_pages' ], $this->settings_page, $section_id, [ 'name' => $name ],
		);

		$name = 'page_popular';
		add_settings_field( THEME_SLUG . '_' . $name, __( 'Popular', 'wpcommunity' ),
			[ $this, 'field_select_pages' ], $this->settings_page, $section_id, [ 'name' => $name ],
		);

		$name = 'page_subs';
		add_settings_field( THEME_SLUG . '_' . $name, __( 'Subscriptions', 'wpcommunity' ),
			[ $this, 'field_select_pages' ], $this->settings_page, $section_id, [ 'name' => $name ],
		);

		$name = 'page_top';
		add_settings_field( THEME_SLUG . '_' . $name, __( 'Top', 'wpcommunity' ),
			[ $this, 'field_select_pages' ], $this->settings_page, $section_id, [ 'name' => $name ],
		);

		$name = 'page_bookmarks';
		add_settings_field( THEME_SLUG . '_' . $name, __( 'Bookmarks', 'wpcommunity' ),
			[ $this, 'field_select_pages' ], $this->settings_page, $section_id, [ 'name' => $name ],
		);

		$name = 'page_publish';
		add_settings_field( THEME_SLUG . '_' . $name, __( 'Publish post', 'wpcommunity' ),
			[ $this, 'field_select_pages' ], $this->settings_page, $section_id, [ 'name' => $name ],
		);

		$name = 'page_order';
		add_settings_field( THEME_SLUG . '_' . $name, __( 'Order', 'wpcommunity' ),
			[ $this, 'field_select_pages' ], $this->settings_page, $section_id, [ 'name' => $name ],
		);


		// Section
		$section_id    = THEME_SLUG . '_section_vote';
		$section_title = __( 'Votes', 'wpcommunity' );
		add_settings_section( $section_id, $section_title, '', $this->settings_page );

		// Fields
		$name = 'vote_method';
		$items = [
			'users' => __( 'Users', 'wpcommunity' ),
			'ips' => __( 'IPs', 'wpcommunity' ),
//			'cookies' => __( 'Cookies', 'wpcommunity' ), // пока выпиливаем, усложняет логику
		];
		add_settings_field( THEME_SLUG . '_' . $name, __( 'Method', 'wpcommunity' ),
			[ $this, 'field_select' ], $this->settings_page, $section_id, [ 'name' => $name, 'items' => $items ],
		);
	}

	public function sanitize_callback( $value ) {
		return $value;
	}

	public function field_select( $args ) {
		$options = get_option( $this->option_name );
		$args    = wp_parse_args( $args, [
			'name'  => '',
			'items' => [],
		] );
		$name = $args['name'];
		$value = ( isset( $options[ $name ] ) ) ? $options[ $name ] : '';

		echo '<select name="' . $this->option_name . '[' . $name . ']">';
		foreach ( $args['items'] as $k => $v ) {
			echo '<option value="' . $k . '" ' . selected( $k, $value, false ) . '>' . $v . '</option>';
		}
		echo '</select>';

	}

	public function field_select_pages( $args ) {

            $get_pages = get_pages( [] );

		$pages[0] = '';
		foreach ( $get_pages as $page ) {
			$pages[ $page->ID ] = $page->post_title;
		}

		$args['items'] = $pages;

		$this->field_select( $args );
	}
}
