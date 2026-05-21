<?php

namespace Wpshop\SimpleHtmlDom;

require_once dirname( __FILE__ ) . '/../simple_html_dom.php';

class SimpleHtmlDom {
    /**
     * @return \simplehtmldom_source\simple_html_dom
     */
    static public function file_get_html() {
        return call_user_func_array ( '\simplehtmldom_source\file_get_html' , func_get_args() );
    }
    /**
     * get html dom from string
     * @return \simplehtmldom_source\simple_html_dom
     */
    static public function str_get_html() {
        return call_user_func_array ( '\simplehtmldom_source\str_get_html' , func_get_args() );
    }
}