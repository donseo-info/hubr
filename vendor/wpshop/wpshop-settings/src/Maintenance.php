<?php

namespace Wpshop\Settings;

use Puc_v4_Factory;
use WP_Error;

/**
 * @updated 2025-07-25 добавлена поддержка verify_urls
 * @updated 2025-08-04 добавлена поддержка is_legacy для проверки возвращаемого результата по старому урлу
 */
#[AllowDynamicProperties] 
class Maintenance implements MaintenanceInterface {
public ?string $verify_url = null;
    /**
     * @var string[]
     */
    protected $verify_urls;

    /**
     * @var bool
     */
    protected $is_legacy = false;

    /**
     * @var array
     */
    protected $update_cnf;

    /**
     * @var string
     */
    protected $slug;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $textdomain;

    /**
     * @var string
     */
    protected $file_path;

    /**
     * @param array{'verify_url':string, 'update':array} $config
     * @param string                                     $type
     * @param string                                     $slug
     * @param string                                     $file_path plugin file or theme name
     * @param string                                     $textdomain
     */
    public function __construct( $config, $type, $slug, $file_path, $textdomain ) {
        $this->verify_url = $config['verify_url'] ?? '';
        $this->update_cnf = $config['update'] ?? '';

        $this->type       = in_array( $type, [ 'plugin', 'theme' ] ) ? $type : 'unknown';
        $this->slug       = $slug;
        $this->textdomain = $textdomain;
        $this->file_path  = $file_path;
    }

    /**
     * @return void
     */
    public function init_updates( $license, $license_token = null ) {
        Puc_v4_Factory::buildUpdateChecker(
            $this->update_cnf['url'],
            $this->file_path,
            $this->update_cnf['slug'],
            $this->update_cnf['check_period'],
            $this->update_cnf['opt_name']
        )->addQueryArgFilter( function ( $query_args ) use ( $license, $license_token ) {
            if ( $license ) {
                $query_args['license_key'] = $license;
            }
            if ( $license_token ) {
                $query_args['token'] = $license_token;
            }

            return $query_args;
        } );
    }

    /**
     * @param string   $license
     * @param callable $cb
     *
     * @return bool|WP_Error
     */
    public function activate( $license, $cb ) {
		return true;
    }

    /**
     * @return string
     */
    public function get_type() {
        return $this->type;
    }

    /**
     * @return string|null
     */
    protected function get_version_from_metadata() {
        switch ( $this->type ) {
            case 'plugin':
                require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

                return get_plugin_data( $this->file_path, false, false )['Version'] ?? '';
            case 'theme':
                return wp_get_theme( basename( $this->file_path ) )->get( 'Version' );
            default:
                break;
        }

        return null;
    }

    /**
     * @return string
     */
    protected function get_ip() {
        return null;
    }
}
