<?php
/**
 * First activation only: load the approved prototype content (sessions, events, reviews, FAQs,
 * photos and art) and create a private "Home (new)" page using the Emma Divines Home template.
 * Nothing existing is changed, and the live homepage stays as it is until it's switched in Settings → Reading.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ed_seed() {
	if ( get_option( 'ed_seeded' ) ) {
		return;
	}
	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	}

	// The page comes first, so there's something to preview even if the photo import is cut short
	if ( ! ed_home_page_id() ) {
		$page = wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_status' => 'private',
				'post_title'  => 'Home (new)',
				'post_name'   => 'home-new',
			)
		);
		if ( $page && ! is_wp_error( $page ) ) {
			update_post_meta( $page, '_wp_page_template', ED_TEMPLATE );
		}
	}


	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$seed = json_decode( (string) file_get_contents( ED_DIR . 'assets/seed/seed.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( ! $seed ) {
		return;
	}

	// Card art, imported once per file into the media library
	$art = array();
	$art_id = function ( $slug ) use ( &$art ) {
		if ( ! isset( $art[ $slug ] ) ) {
			$art[ $slug ] = ed_seed_image( 'tarot/' . $slug . '.jpg', '' );
		}
		return $art[ $slug ];
	};

	foreach ( $seed['categories'] as $c ) {
		$term = term_exists( $c['name'], 'ed_category' );
		if ( ! $term ) {
			$term = wp_insert_term( $c['name'], 'ed_category' );
		}
		if ( is_wp_error( $term ) ) {
			continue;
		}
		$tid = (int) $term['term_id'];
		foreach ( array( 'tab_label', 'eyebrow', 'intro', 'as_tab', 'order', 'note' ) as $k ) {
			if ( isset( $c[ $k ] ) ) {
				update_term_meta( $tid, $k, (string) $c[ $k ] );
			}
		}
		update_term_meta( $tid, 'photos', implode( ',', array_filter( array_map( $art_id, $c['photos'] ) ) ) );

		foreach ( $c['sessions'] as $i => $o ) {
			$found = get_posts( array( 'post_type' => 'ed_session', 'title' => $o['title'], 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids' ) );
			if ( $found ) {
				if ( ! has_post_thumbnail( $found[0] ) && $art_id( $o['art'] ) ) {
					set_post_thumbnail( $found[0], $art_id( $o['art'] ) );
				}
				continue;
			}
			$pid = wp_insert_post(
				array(
					'post_type'   => 'ed_session',
					'post_status' => 'publish',
					'post_title'  => $o['title'],
					'menu_order'  => $i + 1,
				)
			);
			if ( ! $pid || is_wp_error( $pid ) ) {
				continue;
			}
			wp_set_object_terms( $pid, $tid, 'ed_category' );
			update_post_meta( $pid, '_ed_meta', $o['meta'] );
			update_post_meta( $pid, '_ed_price', $o['price'] );
			update_post_meta( $pid, '_ed_desc', $o['desc'] );
			update_post_meta( $pid, '_ed_link', $o['link'] );
			update_post_meta( $pid, '_ed_cta', $o['cta'] );
			update_post_meta( $pid, '_ed_tag', $o['tag'] );
			$aid = $art_id( $o['art'] );
			if ( $aid ) {
				set_post_thumbnail( $pid, $aid );
			}
		}
	}

	foreach ( $seed['reviews'] as $i => $r ) {
		if ( get_posts( array( 'post_type' => 'ed_review', 'title' => $r['n'], 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids' ) ) ) {
			continue;
		}
		$pid = wp_insert_post( array( 'post_type' => 'ed_review', 'post_status' => 'publish', 'post_title' => $r['n'], 'menu_order' => $i + 1 ) );
		if ( $pid && ! is_wp_error( $pid ) ) {
			update_post_meta( $pid, '_ed_text', $r['t'] );
			update_post_meta( $pid, '_ed_date', $r['d'] );
		}
	}

	foreach ( $seed['faqs'] as $i => $f ) {
		if ( get_posts( array( 'post_type' => 'ed_faq', 'title' => html_entity_decode( $f['q'], ENT_QUOTES ), 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids' ) ) ) {
			continue;
		}
		$pid = wp_insert_post( array( 'post_type' => 'ed_faq', 'post_status' => 'publish', 'post_title' => html_entity_decode( $f['q'], ENT_QUOTES ), 'menu_order' => $i + 1 ) );
		if ( $pid && ! is_wp_error( $pid ) ) {
			update_post_meta( $pid, '_ed_answer', $f['a'] );
		}
	}

	// Photos of Emma
	$life = array(
		array( 'life-everyday.jpg', 'Emma relaxing outdoors with her eyes closed', 'Everyday me' ),
		array( 'life-wedding.jpg', 'Emma and her wife dancing at their wedding', 'Our wedding day' ),
		array( 'life-pups.jpg', 'A signature-drinks sign at Emma’s wedding illustrated with their four dogs', 'Our four pups' ),
		array( 'life-little-joys.jpg', 'A homemade ratatouille fresh out of the oven', 'Little joys' ),
	);
	$settings = get_option( 'ed_settings', array() );
	$settings = is_array( $settings ) ? $settings : array();
	$settings['hero_photo']  = (string) ed_seed_image( 'photos/emma-avatar.jpg', 'Emma Divines, smiling' );
	$settings['emma_photo']  = (string) ed_seed_image( 'photos/meet-emma.jpg', 'Portrait of Emma Divines, smiling' );
	$settings['life_photos'] = implode( ',', array_filter( array_map( function ( $l ) {
		return ed_seed_image( 'photos/' . $l[0], $l[1], $l[2] );
	}, $life ) ) );
	update_option( 'ed_settings', $settings );

	update_option( 'ed_seeded', ED_VERSION );
}

/** Copy a bundled image into the media library and return its attachment ID. */
function ed_seed_image( $rel, $alt, $caption = '' ) {
	$src = ED_DIR . 'assets/seed/' . $rel;
	if ( ! file_exists( $src ) ) {
		return 0;
	}
	$done = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_ed_seed_src', 'meta_value' => $rel, 'numberposts' => 1, 'fields' => 'ids' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	if ( ! $done ) {
		// Imported by an earlier, interrupted setup (before files were tagged): match the file name
		$done = get_posts(
			array(
				'post_type'   => 'attachment',
				'post_status' => 'inherit',
				'numberposts' => 1,
				'fields'      => 'ids',
				'orderby'     => 'ID',
				'order'       => 'ASC',
				'meta_query'  => array( array( 'key' => '_wp_attached_file', 'value' => '/emma-divines-' . pathinfo( $src, PATHINFO_FILENAME ) . '.', 'compare' => 'LIKE' ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);
		if ( $done ) {
			update_post_meta( $done[0], '_ed_seed_src', $rel );
		}
	}
	if ( $done ) {
		return (int) $done[0];
	}
	$tmp = wp_tempnam( basename( $src ) );
	if ( ! $tmp || ! copy( $src, $tmp ) ) {
		return 0;
	}
	$id = media_handle_sideload(
		array(
			'name'     => 'emma-divines-' . basename( $src ),
			'tmp_name' => $tmp,
		),
		0,
		null,
		array( 'post_excerpt' => $caption )
	);
	if ( is_wp_error( $id ) ) {
		wp_delete_file( $tmp );
		return 0;
	}
	update_post_meta( $id, '_ed_seed_src', $rel );
	if ( $alt ) {
		update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	}
	return (int) $id;
}

/* ---------- If setup was cut short (slow hosts time out on the photo import), finish it from a button ---------- */

add_action( 'admin_notices', 'ed_setup_notice' );
function ed_setup_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! get_option( 'ed_seeded' ) ) {
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=ed_seed' ), 'ed_seed' );
		printf( '<div class="notice notice-warning"><p><b>Emma Divines</b> didn’t finish loading its content and photos. <a class="button button-primary" href="%s">Finish setup</a> <span class="description">Takes up to a minute. Safe to click again if it stops.</span></p></div>', esc_url( $url ) );
		return;
	}
	if ( isset( $_GET['ed_ready'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$home = ed_home_page_id();
		printf( '<div class="notice notice-success is-dismissible"><p><b>Emma Divines is set up.</b> <a href="%s" target="_blank" rel="noopener">Preview the new homepage ↗</a></p></div>', esc_url( $home ? get_permalink( $home ) : home_url( '/' ) ) );
	}
}

add_action( 'admin_post_ed_seed', 'ed_seed_action' );
function ed_seed_action() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Not allowed.' );
	}
	check_admin_referer( 'ed_seed' );
	ed_seed();
	wp_safe_redirect( admin_url( 'admin.php?page=emma-divines&ed_ready=1' ) );
	exit;
}

/** "Preview homepage" link on the Plugins screen. */
add_filter( 'plugin_action_links_' . plugin_basename( ED_FILE ), 'ed_plugin_links' );
function ed_plugin_links( $links ) {
	$home = ed_home_page_id();
	if ( $home ) {
		array_unshift( $links, '<a href="' . esc_url( get_permalink( $home ) ) . '" target="_blank" rel="noopener">Preview homepage</a>' );
	}
	array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=emma-divines' ) ) . '">Site content</a>' );
	return $links;
}
