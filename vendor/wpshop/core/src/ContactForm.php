<?php

namespace Wpshop\Core;

/**
 * Class ContactForm
 *
 * 1.0.9    2022-11-29    Add filters 'wpshop_contact_form_errors', 'wpshop_contact_form_do_success_redirect' add success POST-GET redirect
 * 1.0.8    2022-04-13    Add filter 'wpshop_contact_form_text_from'
 * 1.0.7    2022-04-13    Add filter 'wpshop_contact_form_subject'
 * 1.0.6    2021-09-27    Add filter 'wpshop_contact_form_body'
 * 1.0.5    2021-09-17    Add filter 'render_callback' to field args
 * 1.0.5    2021-05-14    Add filter 'wpshop_contact_form_verify_nonce'
 * 1.0.4    2021-03-01    Add filter 'wpshop_contact_form_url_from' for url from
 * 1.0.3    2021-01-20    Add filter 'wpshop_contact_form_button_text' for button text
 * 1.0.2    2020-09-08    Add filter 'wpshop_contact_form_email_to' for email to
 * 1.0.1    2020-05-25    Add filter for message sent text
 */
class ContactForm {

    protected $options;

    public $fields;
    protected $fields_out = '';

    protected $nonce_action = 'wpshop_nonce_action';
    protected $nonce_name = 'wpshop_nonce_name';

    protected $success_query_var = 'wpshop-contact-form-success';

    protected $errors;

    /**
     * @var bool
     * @deprecated
     */
    protected $success = false;

    public $admin_email;


    /**
     * Contact_Form constructor.
     *
     * @param ThemeOptions $options
     */
    public function __construct( ThemeOptions $options ) {

        $this->options = $options;

        $this->nonce_action = $this->options->theme_slug . '_nonce_action';
        $this->nonce_name = $this->options->theme_slug . '_nonce_name';

        add_shortcode( 'contactform', array( $this, 'display_form' ) );
        add_action( 'init', array( $this, 'send_form' ) );

        $this->admin_email = apply_filters( 'wpshop_contact_form_email_to', get_option( 'admin_email' ) );

    }


    public function send_form() {

        if ( ! empty( $_POST['email'] ) ) {
            return;
        }

        $body = '';

        if ( ! empty( $_POST['submitted'] ) && $_POST['submitted'] == 'submitted' ) {

            $valid = true;
            if ( apply_filters( 'wpshop_contact_form_verify_nonce', true ) ) {
                $valid = wp_verify_nonce( $_POST[ $this->nonce_name ], $this->nonce_action );
            }

            if ( $valid ) {

                if ( empty( $_POST['fields'] ) ) $this->errors[] = __( 'Sorry, but we can\'t send form. Error #1', $this->options->text_domain );

                $fields = json_decode( base64_decode( $_POST['fields'] ), true );

                if ( empty( $fields ) ) $this->errors[] = __( 'Sorry, but we can\'t send form. Error #2', $this->options->text_domain );

                foreach ( $fields as $n => $field ) {

                    $field_name = $field['name'];
                    if ( ! empty( $field['placeholder'] ) ) $field_name = $field['placeholder'];
                    if ( ! empty( $field['label'] ) ) $field_name = $field['label'];

                    if ( isset( $field['required'] ) && $field['required'] == 'required' && empty( $_POST[ $field['name'] ] ) ) {
                        $this->errors[] = __( 'Fill field ' . $field_name, $this->options->text_domain );
                        $fields[$n]['error'] = 'error';
                    }

                    if ( ! empty( $_POST[ $field['name'] ] ) ) {

                        $body .= $field_name . ': ' . PHP_EOL . sanitize_text_field( $_POST[ $field['name'] ] ) . PHP_EOL . PHP_EOL;

                    }
                }

                $name = ( ! empty( $_POST['contact-name'] ) ) ? sanitize_text_field( $_POST['contact-name'] ) : 'Contact Form' ;
                $from_email = ( ! empty( $_POST['contact-email'] ) ) ? sanitize_text_field( $_POST['contact-email'] ) : $this->admin_email ;
                $subject = ( ! empty( $_POST['contact-subject'] ) ) ? sanitize_text_field( $_POST['contact-subject'] ) : apply_filters( 'wpshop_contact_form_subject', __( 'Message from contact form', $this->options->text_domain ) );

                if ( ! is_email( $from_email ) ) {
                    $this->errors[] = __( 'Wrong e-mail format', $this->options->text_domain );
                }

                $errors = (array) apply_filters( 'wpshop_contact_form_errors', [] );
                foreach ( $errors as $error ) {
                    $this->errors[] = $error;
                }

                $body .= apply_filters( 'wpshop_contact_form_text_from', __( 'Message from', $this->options->text_domain ) ) . ' ' . apply_filters( 'wpshop_contact_form_url_from', get_site_url() );

                $body = apply_filters( 'wpshop_contact_form_body', $body );

                if ( empty( $this->errors ) ) {

                    $mail_from = apply_filters( 'wpshop_contact_form_email_from' , $from_email );
                    $headers = 'From: '.$name.' <'.$mail_from.'>' . "\r\n" . 'Reply-To: ' . $from_email;

                    wp_mail( $this->admin_email, $subject, $body, $headers );

                    $this->success = true;
                    if ( $_REQUEST['do_redirect'] ) {
                        wp_redirect( add_query_arg( $this->success_query_var, 'true', $_REQUEST['redirect_url'] ) );
                        die;
                    }
                }
            }
        }
    }

    public function display_form( $atts, $content, $shortcode ) {
        $atts = shortcode_atts( [
            'do_redirect'  => apply_filters( 'wpshop_contact_form_do_success_redirect', true ),
            'redirect_url' => $this->get_redirect_url(),
        ], $atts, $shortcode );

        $get = wp_parse_args($_GET, [
            $this->success_query_var => ''
        ]);

        $success = $this->success || $get[ $this->success_query_var ];

        $out = '';

        $out .= '<div class="contact-form">';

        if ( $success ) {
            $out .= '<div class="contact-form__success">';
            $out .= apply_filters( 'wpshop_contact_form_message_sent', __( 'Message sent successfully!', $this->options->text_domain ) );
            $out .= '</div>';
        }

        if ( ! empty( $this->errors ) ) {
            $out .= '<ul class="contact-form__errors">';
            foreach ( $this->errors as $error ) {
                $out .= '<li>' . $error . '</li>';
            }
            $out .= '</ul>';
        }

        if ( $success ) {
            if ( $atts['do_redirect'] ) {
                $out .= '<script>';
                $out .= 'window.history.replaceState && window.history.replaceState(null, null, "' . $this->get_redirect_url() . '" + window.location.hash)';
                $out .= '</script>';
            }
        } else {
            $out .= $this->form_start();
            $out .= $this->fields_out;
            $out .= $this->form_end( $atts );
        }

        $out .= '</div>';

        return $out;
    }

    /**
     * @return false|string
     */
    protected function get_redirect_url() {
        return remove_query_arg( $this->success_query_var, add_query_arg( $_GET, get_the_permalink() ) );
    }

    protected function form_start() {
        $out = '';
        $out .= '<form action="" method="post">';
        $out .= '<input type="email" name="email" style="display:none;">';

        return $out;
    }


    protected function form_end( $atts ) {
        $out = '';

        $fields_out = base64_encode( json_encode($this->fields) );

        $out .= wp_nonce_field( $this->nonce_action, $this->nonce_name, true, false );
        $out .= '<input type="hidden" name="redirect_url" value="' . $this->get_redirect_url() . '">';
        $out .= '<input type="hidden" name="do_redirect" value="' . intval( $atts['do_redirect'] ) . '">';
        $out .= '<input type="hidden" name="submitted" value="submitted">';
        $out .= '<input type="hidden" name="fields" value="' . $fields_out . '">';
        $out .= apply_filters( 'wpshop_text_before_submit', '' );
        $out .= '<button type="submit" name="submit" class="btn btn-primary">' . apply_filters( 'wpshop_contact_form_button_text', __( 'Send', $this->options->text_domain ) ) . '</button>';
        $out .= '</form>';

        return $out;
    }


    protected function create_field( $args = array() ) {

        $out = '';
        $atts = array();
        $attributes = '';

        if ( empty( $args['name'] ) ) return '<!-- name required -->';

        $tag         = ( ! empty( $args['tag'] ) ) ? $args['tag'] : 'input';

        $type        = ( ! empty( $args['type'] ) ) ? $args['type'] : 'text';
        $placeholder = ( ! empty( $args['placeholder'] ) ) ? $args['placeholder'] : '';
        $name        = ( ! empty( $args['name'] ) ) ? $args['name'] : '';
        $label       = ( ! empty( $args['label'] ) ) ? $args['label'] : '';
        $value       = ( ! empty( $_POST[ $name ] ) ) ? trim( $_POST[ $name ] ) : '';
        $required    = ( ! empty( $args['required'] ) ) ? true : false;

        if ( ! empty( $type ) )         $atts['type'] = $type;
        if ( ! empty( $placeholder ) )  $atts['placeholder'] = $placeholder;
        if ( ! empty( $name ) )         $atts['name'] = $name;
        if ( ! empty( $name ) )         $atts['id'] = $name;
        if ( ! empty( $value ) )        $atts['value'] = $value;
        if ( $required )                $atts['required'] = 'required';

        $atts['class'] = 'field-' . $name;
        if ( $required ) $atts['class'] .= ' field-required';

        if ( $tag == 'textarea' ) {
            unset( $atts['type'] );
            unset( $atts['value'] );
        }

        foreach ( $atts as $k => $v ) {
            $attributes .= ' ' . $k . '="' . esc_attr( $v ) . '"';
        }

        $out .= '<div class="contact-form__field contact-form--type-' . $type . '">';

        if ( isset( $args['render_callback'] ) && is_callable( $args['render_callback'] ) ) {
            $callback = $args['render_callback'];
            unset( $args['render_callback'] );
            $out .= $callback( $atts, $args );
        } else {
            if ( ! empty( $label ) ) $out .= '<label for="' . $name . '">' . $label . '</label>';
            if ( $tag == 'textarea' ) {
                $out .= '<textarea' . $attributes . '>' . $value . '</textarea>';
            } else {
                $out .= '<input' . $attributes . '>';
            }
        }

        $out .= '</div>';

        return $out;
    }


    public function create_fields( $fields = array() ) {
        if ( ! is_array( $fields ) ) return;
        $this->fields = $fields;

        foreach ( $fields as $field ) {
            $this->fields_out .= $this->create_field( $field );
        }
    }

}
