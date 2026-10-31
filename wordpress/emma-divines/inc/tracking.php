<?php
/**
 * Google Analytics 4 on every page, from the Measurement ID in Site content → Tracking.
 * Logged-in editors aren't counted. The homepage's events (book_click, draw_card, …) are sent by emma.js.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ed_ga_id() {
	$id = strtoupper( trim( (string) ed_opt( 'ga_id' ) ) );
	return preg_match( '/^G-[A-Z0-9]{4,20}$/', $id ) ? $id : '';
}

add_action( 'wp_head', 'ed_ga_tag', 1 );
function ed_ga_tag() {
	$id = ed_ga_id();
	// Site Kit already adds Google's tag: don't count every visit twice
	if ( ! $id || is_admin() || current_user_can( 'edit_posts' ) || wp_script_is( 'google_gtagjs', 'enqueued' ) ) {
		return;
	}
	// phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedScript -- printed directly so the homepage's script clean-up leaves it alone
	printf( '<script async id="ed-gtag-js" src="%s"></script>' . "\n", esc_url( 'https://www.googletagmanager.com/gtag/js?id=' . $id ) );
	printf( "<script id=\"ed-gtag-config\">window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config',%s);</script>\n", wp_json_encode( $id ) );
	// phpcs:enable
}

/** Keep the tag out of SiteGround's Combine JS, so it loads straight away on every page. */
add_filter( 'sgo_javascript_combine_exclude_ids', 'ed_ga_exclude_ids' );
function ed_ga_exclude_ids( $ids ) {
	return array_merge( (array) $ids, array( 'ed-gtag-js', 'ed-gtag-config' ) );
}

/** Settings screen: show whether tracking is on. */
add_action( 'admin_notices', 'ed_ga_notice' );
function ed_ga_notice() {
	$screen = get_current_screen();
	if ( ! $screen || 'toplevel_page_emma-divines' !== $screen->id || ! isset( $_GET['tab'] ) || 'tracking' !== $_GET['tab'] ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}
	$raw = trim( (string) ed_opt( 'ga_id' ) );
	if ( ed_ga_id() ) {
		echo '<div class="notice notice-success"><p><b>Tracking is on</b> with ' . esc_html( ed_ga_id() ) . '. Purge the SG cache after changing it so every page picks it up.</p></div>';
	} elseif ( '' !== $raw ) {
		echo '<div class="notice notice-error"><p>That doesn’t look like a Google Analytics Measurement ID. It starts with <b>G-</b>, e.g. G-AB12CD34EF.</p></div>';
	}
}
