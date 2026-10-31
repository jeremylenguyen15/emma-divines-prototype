<?php
/**
 * Small field kit shared by the session/review/FAQ forms, category screens and Site Content page.
 * Types: text, textarea, url, number, select, image (one attachment ID), images (comma-separated IDs).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ed_field_input( $name, $f, $value ) {
	$id   = 'ed-' . sanitize_html_class( str_replace( array( '[', ']' ), '-', $name ) );
	$type = isset( $f['type'] ) ? $f['type'] : 'text';
	$ph   = isset( $f['placeholder'] ) ? ' placeholder="' . esc_attr( $f['placeholder'] ) . '"' : '';

	switch ( $type ) {
		case 'textarea':
			$rows = isset( $f['rows'] ) ? (int) $f['rows'] : 3;
			printf( '<textarea class="large-text" id="%s" name="%s" rows="%d"%s>%s</textarea>', esc_attr( $id ), esc_attr( $name ), $rows, $ph, esc_textarea( $value ) );
			break;
		case 'select':
			printf( '<select id="%s" name="%s">', esc_attr( $id ), esc_attr( $name ) );
			foreach ( $f['options'] as $k => $label ) {
				printf( '<option value="%s"%s>%s</option>', esc_attr( $k ), selected( $value, $k, false ), esc_html( $label ) );
			}
			echo '</select>';
			break;
		case 'image':
		case 'images':
			$multi = 'images' === $type;
			$ids   = array_filter( array_map( 'absint', explode( ',', (string) $value ) ) );
			printf( '<div class="ed-media" data-multiple="%d"><input type="hidden" name="%s" value="%s"><div class="ed-media-thumbs">', $multi ? 1 : 0, esc_attr( $name ), esc_attr( implode( ',', $ids ) ) );
			foreach ( $ids as $aid ) {
				$src = wp_get_attachment_image_url( $aid, 'thumbnail' );
				if ( $src ) {
					printf( '<img src="%s" alt="">', esc_url( $src ) );
				}
			}
			printf(
				'</div><button type="button" class="button ed-media-pick">%s</button> <button type="button" class="button-link ed-media-clear"%s>Remove</button></div>',
				$multi ? 'Choose photos' : 'Choose photo',
				$ids ? '' : ' hidden'
			);
			break;
		default:
			$input_type = in_array( $type, array( 'url', 'number', 'email' ), true ) ? $type : 'text';
			printf( '<input class="%s" type="%s" id="%s" name="%s" value="%s"%s>', 'number' === $type ? 'small-text' : 'regular-text', esc_attr( $input_type ), esc_attr( $id ), esc_attr( $name ), esc_attr( $value ), $ph );
	}
	if ( ! empty( $f['help'] ) ) {
		printf( '<p class="description">%s</p>', wp_kses( $f['help'], array( 'em' => array(), 'b' => array(), 'code' => array() ) ) );
	}
}

/** Render rows for a <table class="form-table">. $values is key => value, $prefix wraps names (e.g. "ed_settings"). */
function ed_field_rows( $fields, $values, $prefix ) {
	foreach ( $fields as $key => $f ) {
		$name  = $prefix ? $prefix . '[' . $key . ']' : $key;
		$value = isset( $values[ $key ] ) ? $values[ $key ] : ( isset( $f['default'] ) ? $f['default'] : '' );
		echo '<tr><th scope="row"><label for="' . esc_attr( 'ed-' . sanitize_html_class( str_replace( array( '[', ']' ), '-', $name ) ) ) . '">' . esc_html( $f['label'] ) . '</label></th><td>';
		ed_field_input( $name, $f, $value );
		echo '</td></tr>';
	}
}

function ed_sanitize_field( $f, $raw ) {
	$type = isset( $f['type'] ) ? $f['type'] : 'text';
	$raw  = is_string( $raw ) ? wp_unslash( $raw ) : '';
	switch ( $type ) {
		case 'textarea':
			return wp_kses( $raw, array( 'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ), 'em' => array(), 'strong' => array(), 'b' => array(), 'br' => array() ) );
		case 'url':
			return esc_url_raw( trim( $raw ) );
		case 'number':
			return (string) intval( $raw );
		case 'select':
			return array_key_exists( $raw, $f['options'] ) ? $raw : (string) key( $f['options'] );
		case 'image':
			return (string) absint( $raw );
		case 'images':
			return implode( ',', array_filter( array_map( 'absint', explode( ',', $raw ) ) ) );
		default:
			return sanitize_text_field( $raw );
	}
}

add_action( 'admin_enqueue_scripts', 'ed_admin_assets' );
function ed_admin_assets() {
	$screen = get_current_screen();
	if ( ! $screen || ( false === strpos( $screen->id, 'ed_' ) && false === strpos( $screen->id, 'emma-divines' ) ) ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script( 'ed-admin', ED_URL . 'assets/admin.js', array( 'jquery' ), ED_VERSION, true );
	wp_enqueue_style( 'ed-admin', ED_URL . 'assets/admin.css', array(), ED_VERSION );
}
