<?php
/**
 * The old Elementor pages send visitors (and Google) to the matching part of the new homepage with a
 * permanent redirect. Works whether the old page is still published, in Drafts or deleted.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Old address (path) => place on the new homepage. */
function ed_redirect_map() {
	return apply_filters(
		'ed_redirect_map',
		array(
			'psychic-readings' => '#readings',
			'mediumship'       => '#mediumship',
			'reiki'            => '#reiki',
			'classes'          => '#classes',
			'events'           => '#events',
			'about-me'         => '#emma',
			'about-me-copy'    => '#emma',
			'faqs'             => '#faq',
			'sessions'         => '',
			'sessions-copy'    => '',
			'home'             => '',
		)
	);
}

add_action( 'template_redirect', 'ed_redirect_old_pages', 1 );
function ed_redirect_old_pages() {
	// Only once the new homepage is live, and never while Elementor is previewing a page
	if ( ! ed_home_page_id() || (int) get_option( 'page_on_front' ) !== ed_home_page_id() || is_preview() || isset( $_GET['elementor-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}
	$path = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$home = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	if ( '' !== $home && '/' !== $home && 0 === strpos( $path, $home ) ) {
		$path = substr( $path, strlen( $home ) );
	}
	$path = strtolower( trim( $path, '/' ) );
	$map  = ed_redirect_map();
	if ( '' === $path || ! array_key_exists( $path, $map ) ) {
		return;
	}
	wp_safe_redirect( home_url( '/' ) . $map[ $path ], 301, 'Emma Divines' );
	exit;
}
