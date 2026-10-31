<?php
/**
 * "Let's get connected" form: saves each note under Emma Divines → Messages and emails it.
 * No nonce, so it keeps working on cached pages; a hidden honeypot field and a rate limit stop spam.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_ajax_ed_contact', 'ed_contact' );
add_action( 'wp_ajax_nopriv_ed_contact', 'ed_contact' );
function ed_contact() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing
	if ( ! empty( $_POST['website'] ) ) {
		wp_send_json_success(); // bots fill the hidden field; pretend it worked
	}
	$name  = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$msg   = isset( $_POST['msg'] ) ? sanitize_textarea_field( wp_unslash( $_POST['msg'] ) ) : '';
	// phpcs:enable

	if ( ! is_email( $email ) ) {
		wp_send_json_error( 'Please check your email address.' );
	}
	if ( '' === trim( $msg ) ) {
		wp_send_json_error( 'Please add a short message.' );
	}

	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key = 'ed_rl_' . md5( $ip );
	$n   = (int) get_transient( $key );
	if ( $n >= 5 ) {
		wp_send_json_error( 'Thanks! We’ve got your notes. Please call or text for anything urgent.' );
	}
	set_transient( $key, $n + 1, HOUR_IN_SECONDS );

	$id = wp_insert_post(
		array(
			'post_type'   => 'ed_message',
			'post_status' => 'private',
			'post_title'  => ( $name ? $name : 'Someone' ) . ' — ' . $email,
		)
	);
	if ( $id && ! is_wp_error( $id ) ) {
		update_post_meta( $id, '_ed_name', $name );
		update_post_meta( $id, '_ed_email', $email );
		update_post_meta( $id, '_ed_msg', $msg );
	}

	$to = ed_opt( 'email_to' );
	if ( ! is_email( $to ) ) {
		$to = get_option( 'admin_email' );
	}
	$body = "Name: {$name}\nEmail: {$email}\n\n{$msg}\n\n— Sent from the contact form on " . home_url( '/' );
	wp_mail( $to, 'New message from ' . ( $name ? $name : $email ), $body, array( 'Reply-To: ' . ( $name ? $name . ' ' : '' ) . '<' . $email . '>' ) );

	wp_send_json_success();
}
