<?php

namespace WPShop\WPCommunity\Metaboxes;

class MetaboxAchievements {

    const POST_TYPES = [ 'achievement' ];

    public function __construct() {

//		if ( is_admin() ) {
//			add_action( 'load-post.php',     array( $this, 'init_metabox' ) );
//			add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
//		}

        add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
        add_action( 'save_post', [ $this, 'save_meta_box' ], 10, 2 );

//		$this->set_settings( [
//			'prefix'         => 'invite_',
//			'post_type'      => [ 'invite' ],
//			'meta_box_title' => __( 'Invite', 'wpcommunity' ),
//		] );
    }

//	public function init_metabox() {
//
//		add_action( 'add_meta_boxes',        array( $this, 'add_metabox' )         );
//		add_action( 'save_post',             array( $this, 'save_metabox' ), 10, 2 );
//
//	}

    public function add_meta_boxes() {

        add_meta_box(
            'meta_box_achievements',
            __( 'Achievements', 'wpcommunity' ),
            [ $this, 'render_meta_box' ],
            self::POST_TYPES,
            'advanced',
            'default'
        );

    }

    public function render_meta_box( $post ) {

        // Retrieve an existing value from the database.
        $description = get_post_meta( $post->ID, 'description', true );

//		$days        = (int) get_post_meta( $post->ID, 'days', true );
//		$for_newbie  = get_post_meta( $post->ID, 'for_newbie', true );
//		$expired     = get_post_meta( $post->ID, 'expired', true );
//		$limit       = (int) get_post_meta( $post->ID, 'limit', true );
//		$description = get_post_meta( $post->ID, 'description', true );
//
//		$invite_history = get_post_meta( $post->ID, Invite::POST_META_INVITE_HISTORY, true );

        // Set default values.
//		if ( empty( $expired ) ) $expired = date( 'Y-m-d', strtotime( '+1 month' ) );

//		if( empty( $chimp_send_on ) ) $chimp_send_on = '';
//		if( empty( $chimp_send_at ) ) $chimp_send_at = '';
//		if( empty( $chimp_mailing_list ) ) $chimp_mailing_list = '';
//		if( empty( $chimp_from_name ) ) $chimp_from_name = '';
//		if( empty( $chimp_reply_to ) ) $chimp_reply_to = '';
//		if( empty( $chimp_subject ) ) $chimp_subject = '';

        // Form fields.
        echo '<table class="form-table">';

        echo '	<tr>';
        echo '		<th><label for="description">' . __( 'Description', 'wpcommunity' ) . '</label></th>';
        echo '		<td>';
        echo '			<label><textarea id="description" name="description" style="display: block;width: 100%;"> ' . $description . '</textarea></label>';
        echo '		</td>';
        echo '	</tr>';

//		echo '	<tr>';
//		echo '		<th><label for="days">' . __( 'Bonus days', 'wpcommunity' ) . '</label></th>';
//		echo '		<td>';
//		echo '			<input type="number" id="days" name="days" value="' . esc_attr( $days ) . '">';
//		echo '			<p class="description">' . __( 'Bonus days after activate invite', 'wpcommunity' ) . '</p>';
//		echo '		</td>';
//		echo '	</tr>';
//
//		echo '	<tr>';
//		echo '		<th><label for="expired">' . __( 'Expired', 'wpcommunity' ) . '</label></th>';
//		echo '		<td>';
//		echo '			<input type="date" id="expired" name="expired" value="' . esc_attr( $expired ) . '">';
//		echo '			<p class="description">' . __( 'Expired date included', 'wpcommunity' ) . '</p>';
//		echo '		</td>';
//		echo '	</tr>';
//
//		echo '	<tr>';
//		echo '		<th><label for="limit">' . __( 'Limit', 'wpcommunity' ) . '</label></th>';
//		echo '		<td>';
//		echo '			<input type="number" id="limit" name="limit" value="' . esc_attr( $limit ) . '">';
//		echo '			<p class="description">' . __( 'Usage limit', 'wpcommunity' ) . '</p>';
//		echo '		</td>';
//		echo '	</tr>';
//
//		echo '	<tr>';
//		echo '		<th><label for="description">' . __( 'Description', 'wpcommunity' ) . '</label></th>';
//		echo '		<td>';
//		echo '			<input type="text" id="description" name="description" value="' . esc_attr( $description ) . '">';
//		echo '			<p class="description">' . __( 'Only for admins', 'wpcommunity' ) . '</p>';
//		echo '		</td>';
//		echo '	</tr>';
//
//		echo '	<tr>';
//		echo '		<th><label for="description">' . __( 'Activations', 'wpcommunity' ) . '</label></th>';
//		echo '		<td>';
//		if ( ! empty( $invite_history ) ) {
//			echo '<table>';
//			foreach ( $invite_history as $invite_date => $invite_user_id ) {
//				$invite_user = get_user_by( 'ID', $invite_user_id );
//				echo '<tr>';
//				echo '<td>' . $invite_user_id . '</td>';
//				echo '<td>';
//				echo get_avatar( $invite_user_id, 30 );
//				echo '</td>';
//				echo '<td>';
//				echo '<a href="' . get_edit_user_link( $invite_user_id ) . '">' . $invite_user->user_email . '</a>';
//				echo '</td>';
//				echo '<td>';
//				echo date( 'd.m.Y H:i', $invite_date );
//				echo '</td>';
//				echo '</tr>';
//			}
//			echo '</table>';
//		}
//		echo '		</td>';
//		echo '	</tr>';


//
//		echo '	<tr>';
//		echo '		<th><label for="chimp_send_at" class="chimp_send_at_label">' . __( 'Time to Send', 'mailchimp-campaigns' ) . '</label></th>';
//		echo '		<td>';
//		echo '			<input type="time" id="chimp_send_at" name="chimp_send_at" class="chimp_send_at_field" placeholder="' . esc_attr__( '', 'mailchimp-campaigns' ) . '" value="' . esc_attr( $chimp_send_at ) . '">';
//		echo '		</td>';
//		echo '	</tr>';
//
//		echo '	<tr>';
//		echo '		<th><label for="chimp_mailing_list" class="chimp_mailing_list_label">' . __( 'Mailing List', 'mailchimp-campaigns' ) . '</label></th>';
//		echo '		<td>';
//		echo '			<select id="chimp_mailing_list" name="chimp_mailing_list" class="chimp_mailing_list_field">';
//		echo '			<option value="chimp_listv" ' . selected( $chimp_mailing_list, 'chimp_listv', false ) . '> ' . __( 'list 1', 'mailchimp-campaigns' ) . '</option>';
//		echo '			<option value="chimp_list2" ' . selected( $chimp_mailing_list, 'chimp_list2', false ) . '> ' . __( 'List 2', 'mailchimp-campaigns' ) . '</option>';
//		echo '			</select>';
//		echo '		</td>';
//		echo '	</tr>';
//
//		echo '	<tr>';
//		echo '		<th><label for="chimp_from_name" class="chimp_from_name_label">' . __( 'Campaign From', 'mailchimp-campaigns' ) . '</label></th>';
//		echo '		<td>';
//		echo '			<input type="text" id="chimp_from_name" name="chimp_from_name" class="chimp_from_name_field" placeholder="' . esc_attr__( '', 'mailchimp-campaigns' ) . '" value="' . esc_attr( $chimp_from_name ) . '">';
//		echo '		</td>';
//		echo '	</tr>';
//
//		echo '	<tr>';
//		echo '		<th><label for="chimp_reply_to" class="chimp_reply_to_label">' . __( 'Reply to E-Mail', 'mailchimp-campaigns' ) . '</label></th>';
//		echo '		<td>';
//		echo '			<input type="email" id="chimp_reply_to" name="chimp_reply_to" class="chimp_reply_to_field" placeholder="' . esc_attr__( '', 'mailchimp-campaigns' ) . '" value="' . esc_attr( $chimp_reply_to ) . '">';
//		echo '		</td>';
//		echo '	</tr>';
//
//		echo '	<tr>';
//		echo '		<th><label for="chimp_subject" class="chimp_subject_label">' . __( 'Subject Line', 'mailchimp-campaigns' ) . '</label></th>';
//		echo '		<td>';
//		echo '			<input type="text" id="chimp_subject" name="chimp_subject" class="chimp_subject_field" placeholder="' . esc_attr__( '', 'mailchimp-campaigns' ) . '" value="' . esc_attr( $chimp_subject ) . '">';
//		echo '		</td>';
//		echo '	</tr>';

        echo '</table>';

    }


    public function save_meta_box( $post_id, $post ) {

        // проверяем, может ли текущий юзер редактировать пост
        $post_type = get_post_type_object( $post->post_type );
        if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
            return $post_id;
        }

        // ничего не делаем для автосохранений
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }

        // проверяем тип записи
        if ( ! in_array( $post->post_type, self::POST_TYPES ) ) {
            return $post_id;
        }


        if ( isset( $_POST['description'] ) ) {
            update_post_meta( $post_id, 'description', sanitize_text_field( $_POST['description'] ) );
        } else {
            delete_post_meta( $post_id, 'description' );
        }

//		$for_newbie = isset( $_POST[ 'for_newbie' ] ) ? 'checked'  : '';
//		$days = isset( $_POST[ 'days' ] ) ? (int) $_POST[ 'days' ] : 0;
//		$expired = isset( $_POST[ 'expired' ] ) ? sanitize_text_field( $_POST[ 'expired' ] ) : '';
//		$limit = isset( $_POST[ 'limit' ] ) ? (int) $_POST[ 'limit' ] : 0;
//		$description = isset( $_POST[ 'description' ] ) ? sanitize_text_field( $_POST[ 'description' ] ) : '';
//
//		if ( empty( $expired ) ) $expired = date( 'Y-m-d', strtotime( '+1 month' ) );

//		$chimp_new_send_at = isset( $_POST[ 'chimp_send_at' ] ) ? sanitize_text_field( $_POST[ 'chimp_send_at' ] ) : '';
//		$chimp_new_mailing_list = isset( $_POST[ 'chimp_mailing_list' ] ) ? $_POST[ 'chimp_mailing_list' ] : '';
//		$chimp_new_from_name = isset( $_POST[ 'chimp_from_name' ] ) ? sanitize_text_field( $_POST[ 'chimp_from_name' ] ) : '';
//		$chimp_new_reply_to = isset( $_POST[ 'chimp_reply_to' ] ) ? sanitize_email( $_POST[ 'chimp_reply_to' ] ) : '';
//		$chimp_new_subject = isset( $_POST[ 'chimp_subject' ] ) ? sanitize_text_field( $_POST[ 'chimp_subject' ] ) : '';

        // Update the meta field in the database.
//		update_post_meta( $post_id, 'description', $description );
//		update_post_meta( $post_id, 'days', $days );
//		update_post_meta( $post_id, 'expired', $expired );
//		update_post_meta( $post_id, 'limit', $limit );
//		update_post_meta( $post_id, 'description', $description );

//		update_post_meta( $post_id, 'chimp_send_at', $chimp_new_send_at );
//		update_post_meta( $post_id, 'chimp_mailing_list', $chimp_new_mailing_list );
//		update_post_meta( $post_id, 'chimp_from_name', $chimp_new_from_name );
//		update_post_meta( $post_id, 'chimp_reply_to', $chimp_new_reply_to );
//		update_post_meta( $post_id, 'chimp_subject', $chimp_new_subject );

    }

//	public function render_fields() {
//		$this->field_number( 'days', 'Добавляет дней', '', '' );
//		$this->field_text( 'expired', 'Истекает', '', date('Y-m-d', strtotime('next month')) );
////		$this->field_select( 'access', 'Доступ', [
////			'default' => 'По умолчанию',
////			'public' => 'Public',
////			'private' => 'Private',
////		] );
//	}

}
