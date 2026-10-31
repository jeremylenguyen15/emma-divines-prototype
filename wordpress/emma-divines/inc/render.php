<?php
/**
 * The "Emma Divines Home" page template: a full-width page drawn by this plugin, so the theme and
 * Elementor can't change its look. Pick it under Page → Template on any page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'theme_page_templates', 'ed_page_templates' );
function ed_page_templates( $templates ) {
	$templates[ ED_TEMPLATE ]      = 'Emma Divines Home';
	$templates[ ED_BLOG_TEMPLATE ] = 'Emma Divines Blog';
	return $templates;
}

function ed_is_home_template() {
	return is_singular( 'page' ) && ED_TEMPLATE === get_page_template_slug( get_queried_object_id() );
}

add_filter( 'template_include', 'ed_template_include', 99 );
function ed_template_include( $template ) {
	$views = array( 'home' => 'home.php', 'blog' => 'blog.php', 'post' => 'single.php' );
	$view  = ed_view();
	return isset( $views[ $view ] ) ? ED_DIR . 'templates/' . $views[ $view ] : $template;
}

/** The page that uses the template (the front page if it does, else the first one found). */
function ed_home_page_id() {
	$front = (int) get_option( 'page_on_front' );
	if ( $front && ED_TEMPLATE === get_page_template_slug( $front ) ) {
		return $front;
	}
	$pages = get_posts(
		array(
			'post_type'   => 'page',
			'post_status' => array( 'publish', 'private', 'draft' ),
			'meta_key'    => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'  => ED_TEMPLATE, // phpcs:ignore WordPress.DB.SlowDBQuery
			'numberposts' => 1,
			'fields'      => 'ids',
		)
	);
	return $pages ? (int) $pages[0] : 0;
}

/* ---------- Assets: only ours on this page, so theme/Elementor styles can't collide ---------- */

add_action( 'wp_enqueue_scripts', 'ed_enqueue', 100 );
function ed_enqueue() {
	$view = ed_view();
	if ( '' === $view ) {
		return;
	}
	global $wp_styles;
	foreach ( (array) $wp_styles->queue as $h ) {
		if ( ! ed_keep_style( $h ) ) {
			wp_dequeue_style( $h );
		}
	}
	global $wp_scripts;
	foreach ( (array) $wp_scripts->queue as $h ) {
		if ( ! ed_keep_script( $h ) ) {
			wp_dequeue_script( $h );
		}
	}
	wp_enqueue_style( 'ed-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Philosopher:ital,wght@0,400;0,700;1,400&display=swap', array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceVersion
	wp_enqueue_style( 'emma-divines', ED_URL . 'assets/emma.css', array( 'ed-fonts' ), ED_VERSION );
	if ( 'home' !== $view ) {
		$s = ed_settings();
		wp_enqueue_style( 'ed-blog', ED_URL . 'assets/blog.css', array( 'emma-divines' ), ED_VERSION );
		wp_enqueue_script( 'ed-blog', ED_URL . 'assets/blog.js', array(), ED_VERSION, true );
		wp_add_inline_script(
			'ed-blog',
			'window.ED=' . wp_json_encode(
				array(
					'book'      => $s['book_url'],
					'ajax'      => admin_url( 'admin-ajax.php' ),
					'thanks'    => $s['thanks'],
					'formError' => 'Sorry, that didn’t send. Please call or text Emma at ' . $s['phone'] . '.',
					'view'      => $view,
				)
			) . ';',
			'before'
		);
		return;
	}
	wp_enqueue_script( 'ed-arcana', ED_URL . 'assets/arcana.js', array(), ED_VERSION, true );
	wp_enqueue_script( 'emma-divines', ED_URL . 'assets/emma.js', array( 'ed-arcana' ), ED_VERSION, true );
	wp_add_inline_script( 'ed-arcana', 'window.ED=' . wp_json_encode( ed_front_data() ) . ';', 'before' );
}

/**
 * Last line of defence at print time: whatever another plugin or the theme enqueues later (or in the footer)
 * never reaches the page, so SiteGround's "Combine CSS" has nothing of the theme's to bundle back in.
 */
add_filter( 'print_styles_array', 'ed_only_our_styles', 999 );
function ed_only_our_styles( $handles ) {
	if ( is_admin() || ! ed_is_ours() ) {
		return $handles;
	}
	global $wp_styles;
	$keep = array();
	foreach ( (array) $handles as $h ) {
		if ( ed_keep_style( $h ) ) {
			$keep[] = $h;
			$keep   = array_merge( $keep, isset( $wp_styles->registered[ $h ] ) ? (array) $wp_styles->registered[ $h ]->deps : array() );
		}
	}
	return array_values( array_intersect( (array) $handles, $keep ) );
}

/** Ours, plus the small stylesheets plugins add for their admin-bar items (SEO counter, Elementor, SiteGround purge). */
function ed_keep_style( $h ) {
	global $wp_styles;
	if ( in_array( $h, array( 'admin-bar', 'dashicons', 'ed-fonts', 'emma-divines', 'ed-blog' ), true ) ) {
		return true;
	}
	$src = isset( $wp_styles->registered[ $h ] ) ? (string) $wp_styles->registered[ $h ]->src : '';
	return is_admin_bar_showing() && (bool) preg_match( '/admin[-_]?bar|toolbar/i', $h . ' ' . $src );
}

/* ---------- Content ---------- */

function ed_tel( $phone ) {
	$d = preg_replace( '/\D+/', '', $phone );
	if ( 10 === strlen( $d ) ) {
		$d = '1' . $d;
	}
	return 'tel:+' . $d;
}

function ed_img_url( $id, $size = 'large' ) {
	$id = absint( $id );
	return $id ? (string) wp_get_attachment_image_url( $id, $size ) : '';
}

function ed_categories() {
	$terms = get_terms( array( 'taxonomy' => 'ed_category', 'hide_empty' => false ) );
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	usort( $terms, 'ed_term_order_cmp' );
	return $terms;
}

function ed_sessions_in( $term_id ) {
	return get_posts(
		array(
			'post_type'   => 'ed_session',
			'numberposts' => -1,
			'orderby'     => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			'tax_query'   => array( array( 'taxonomy' => 'ed_category', 'terms' => $term_id ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
}

function ed_session_data( $p ) {
	$tag  = get_post_meta( $p->ID, '_ed_tag', true );
	$link = get_post_meta( $p->ID, '_ed_link', true );
	$cta  = get_post_meta( $p->ID, '_ed_cta', true );
	$o    = array(
		'n'   => html_entity_decode( get_the_title( $p ), ENT_QUOTES ),
		'm'   => (string) get_post_meta( $p->ID, '_ed_meta', true ),
		'p'   => (string) get_post_meta( $p->ID, '_ed_price', true ),
		't'   => (string) get_post_meta( $p->ID, '_ed_desc', true ),
		'img' => (string) get_the_post_thumbnail_url( $p, 'large' ),
	);
	if ( $link ) {
		$o['href'] = $link;
	}
	if ( $cta ) {
		$o['cta'] = $cta;
	}
	if ( 'start' === $tag ) {
		$o['start'] = 1;
	} elseif ( 'quick' === $tag ) {
		$o['quick'] = 1;
	}
	return $o;
}

/** Everything the page script needs, as window.ED. */
function ed_front_data() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$s      = ed_settings();
	$offers = array();
	$cat    = array();
	$short  = array();
	$events = array();
	$events_key = '';
	foreach ( ed_categories() as $t ) {
		$list   = array_map( 'ed_session_data', ed_sessions_in( $t->term_id ) );
		$name   = html_entity_decode( $t->name, ENT_QUOTES );
		$as_tab = '0' !== (string) get_term_meta( $t->term_id, 'as_tab', true );
		if ( $as_tab ) {
			if ( ! $list ) {
				continue;
			}
			$offers[ $name ] = $list;
			$label           = get_term_meta( $t->term_id, 'tab_label', true );
			if ( $label ) {
				$short[ $name ] = $label;
			}
		} elseif ( ! $events_key ) {
			$events_key = $name;
			$events     = $list;
		}
		$ids   = array_filter( array_map( 'absint', explode( ',', (string) get_term_meta( $t->term_id, 'photos', true ) ) ) );
		$entry = array(
			'eb'    => (string) get_term_meta( $t->term_id, 'eyebrow', true ),
			'intro' => (string) get_term_meta( $t->term_id, 'intro', true ),
			'note'  => (string) get_term_meta( $t->term_id, 'note', true ),
		);
		if ( count( $ids ) > 1 ) {
			$entry['gallery'] = array_values( array_filter( array_map( 'ed_img_url', $ids ) ) );
		} elseif ( $ids ) {
			$entry['photo'] = ed_img_url( reset( $ids ) );
			$entry['alt']   = (string) get_post_meta( reset( $ids ), '_wp_attachment_image_alt', true );
		}
		$cat[ $name ] = $entry;
	}
	return $cache = array(
		'book'      => $s['book_url'],
		'tel'       => ed_tel( $s['phone'] ),
		'offers'    => (object) $offers,
		'short'     => (object) $short,
		'cat'       => (object) $cat,
		'events'    => $events,
		'eventsKey' => $events_key,
		'fallback'  => 'Classic Reading',
		'reviews'   => ed_reviews(),
		'arcana'    => ED_URL . 'assets/images/arcana/',
		'arcEdits'  => ed_arcana_front_edits(),
		'ajax'      => admin_url( 'admin-ajax.php' ),
		'thanks'    => $s['thanks'],
		'policy'    => $s['policy'],
		'inPerson'  => $s['in_person'],
		'formError' => 'Sorry, that didn’t send. Please call or text Emma at ' . $s['phone'] . '.',
	);
}

function ed_reviews() {
	$out = array();
	foreach ( get_posts( array( 'post_type' => 'ed_review', 'numberposts' => -1, 'orderby' => array( 'menu_order' => 'ASC', 'date' => 'DESC' ) ) ) as $p ) {
		$out[] = array(
			'n' => html_entity_decode( get_the_title( $p ), ENT_QUOTES ),
			'd' => (string) get_post_meta( $p->ID, '_ed_date', true ),
			't' => (string) get_post_meta( $p->ID, '_ed_text', true ),
		);
	}
	return $out;
}

/* ---------- Small markup helpers for the template ---------- */

function ed_e( $k ) {
	echo esc_html( ed_opt( $k ) );
}

/** Headline + accent words in pink italic. */
function ed_headline( $title_key, $accent_key ) {
	echo esc_html( ed_opt( $title_key ) );
	$accent = ed_opt( $accent_key );
	if ( $accent ) {
		echo ' <em>' . esc_html( $accent ) . '</em>';
	}
}

function ed_eyebrow( $k, $class = 'eyebrow' ) {
	printf( '<p class="%s"><svg><use href="#sp"/></svg>%s</p>', esc_attr( $class ), esc_html( ed_opt( $k ) ) );
}

function ed_paragraphs( $text, $class = 'lede' ) {
	foreach ( preg_split( '/\n\s*\n/', trim( (string) $text ) ) as $para ) {
		if ( '' !== trim( $para ) ) {
			printf( '<p class="%s">%s</p>', esc_attr( $class ), wp_kses_post( nl2br( trim( $para ) ) ) );
		}
	}
}

function ed_logo( $which = 'white' ) {
	return ED_URL . 'assets/images/logo_' . ( 'color' === $which ? 'color' : 'white' ) . '.png';
}

function ed_social_links( $class, $label ) {
	$s     = ed_settings();
	$icons = array(
		'instagram' => array( 'Instagram', '<rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r=".6" fill="currentColor"/>' ),
		'tiktok'    => array( 'TikTok', '<path d="M14 3.5v11.2a3.6 3.6 0 1 1-3.6-3.6"/><path d="M14 3.5c.4 2.6 2.2 4.4 5 4.7"/>' ),
		'youtube'   => array( 'YouTube', '<rect x="2.8" y="5.8" width="18.4" height="12.4" rx="4"/><path d="M10.4 9.3v5.4l4.6-2.7z"/>' ),
		'facebook'  => array( 'Facebook', '<path d="M14.6 20.5v-7.3h2.6l.4-3h-3V8.4c0-.9.3-1.5 1.6-1.5h1.5V4.2a20 20 0 0 0-2.3-.1c-2.3 0-3.8 1.4-3.8 3.9v2.2H9v3h2.6v7.3"/>' ),
	);
	printf( '<div class="%s" aria-label="%s">', esc_attr( $class ), esc_attr( $label ) );
	foreach ( $icons as $k => $icon ) {
		if ( empty( $s[ $k ] ) ) {
			continue;
		}
		printf(
			'<a href="%s" target="_blank" rel="noopener" aria-label="%2$s" title="%2$s"><svg viewBox="0 0 24 24" aria-hidden="true">%3$s</svg><svg class="tw" aria-hidden="true"><use href="#sp"/></svg></a>',
			esc_url( $s[ $k ] ),
			esc_attr( $icon[0] ),
			$icon[1] // phpcs:ignore WordPress.Security.EscapeOutput -- fixed SVG markup above
		);
	}
	echo '</div>';
}

/**
 * Nav links: one per card tab, then the menu-only category (Events), then sections, then the blog.
 * On the blog pages ($away) they point at the homepage, which opens the matching section (/#readings).
 */
function ed_nav_links( $extra_draw = false, $away = false ) {
	$data = ed_front_data();
	$href = function ( $hash, $name = '' ) use ( $away ) {
		return $away ? ed_home_link( $name ? sanitize_title( $name ) : $hash ) : '#' . $hash;
	};
	foreach ( array_keys( (array) $data['offers'] ) as $name ) {
		$short = isset( $data['short']->$name ) ? $data['short']->$name : $name;
		printf( '<a href="%s" data-tab="%s">%s</a>', esc_url( $href( 'top', $name ) ), esc_attr( $name ), esc_html( $short ) );
	}
	if ( $data['eventsKey'] ) {
		printf( '<a href="%1$s" data-sheet="%2$s">%2$s</a>', esc_url( $href( 'top', $data['eventsKey'] ) ), esc_html( $data['eventsKey'] ) );
	}
	if ( $extra_draw ) {
		printf( '<a href="%s">Draw a card</a><a href="%s">About Emma</a>', esc_url( $href( 'draw' ) ), esc_url( $href( 'emma' ) ) );
	} else {
		printf( '<a href="%s">About</a>', esc_url( $href( 'emma' ) ) );
	}
	if ( ed_blog_url() ) {
		printf( '<a href="%s"%s>Blog</a>', esc_url( ed_blog_url() ), $away ? ' aria-current="page"' : '' );
	}
	printf( '<a href="%s">FAQs</a>', esc_url( $href( 'faq' ) ) );
}

/** Elementor adds its Google Fonts to every page; this one doesn't use them. */
add_filter( 'elementor/frontend/print_google_fonts', 'ed_no_elementor_fonts' );
function ed_no_elementor_fonts( $print ) {
	return ed_is_ours() ? false : $print;
}

/**
 * SiteGround Optimizer strips ?ver= from asset URLs and serves them with a one-year cache, so after an
 * update browsers would keep the old CSS/JS. Keep the version on this plugin's files so each update busts it.
 */
add_filter( 'sgo_rqs_exclude', 'ed_keep_asset_versions' );
function ed_keep_asset_versions( $list ) {
	$list   = (array) $list;
	$list[] = 'plugins/emma-divines/';
	return $list;
}

/** …and leave this plugin's stylesheets out of SiteGround's Combine/Minify CSS, which can't read a URL that keeps its ?ver=. */
add_filter( 'sgo_css_combine_exclude', 'ed_sgo_exclude_styles' );
add_filter( 'sgo_css_minify_exclude', 'ed_sgo_exclude_styles' );
function ed_sgo_exclude_styles( $list ) {
	return array_merge( (array) $list, array( 'emma-divines', 'ed-fonts', 'ed-blog' ) );
}

/**
 * Scripts get the same treatment: Elementor and its add-ons have nothing to run on this page, and when
 * SiteGround bundles them together one failing script would take the cards down with it.
 */
add_filter( 'print_scripts_array', 'ed_only_our_scripts', 999 );
function ed_only_our_scripts( $handles ) {
	if ( is_admin() || ! ed_is_ours() ) {
		return $handles;
	}
	global $wp_scripts;
	$keep = array();
	foreach ( (array) $handles as $h ) {
		if ( ed_keep_script( $h ) ) {
			$keep[] = $h;
			$keep   = array_merge( $keep, isset( $wp_scripts->registered[ $h ] ) ? (array) $wp_scripts->registered[ $h ]->deps : array() );
		}
	}
	return array_values( array_intersect( (array) $handles, $keep ) );
}

function ed_keep_script( $h ) {
	global $wp_scripts;
	if ( in_array( $h, array( 'ed-arcana', 'emma-divines', 'ed-blog', 'admin-bar', 'hoverintent-js' ), true ) ) {
		return true;
	}
	$src = isset( $wp_scripts->registered[ $h ] ) ? (string) $wp_scripts->registered[ $h ]->src : '';
	// Google's tag, e.g. from Site Kit, so the homepage is counted in Analytics too
	if ( 0 === strpos( $h, 'google_gtagjs' ) || false !== strpos( $src, 'googletagmanager.com/' ) ) {
		return true;
	}
	return is_admin_bar_showing() && (bool) preg_match( '/admin[-_]?bar|toolbar/i', $h . ' ' . $src );
}

/** Our scripts (and the data printed before them) load as they are, outside SiteGround's Combine/Minify JS. */
add_filter( 'sgo_javascript_combine_exclude', 'ed_sgo_exclude_scripts' );
add_filter( 'sgo_js_minify_exclude', 'ed_sgo_exclude_scripts' );
add_filter( 'sgo_js_async_exclude', 'ed_sgo_exclude_scripts' );
function ed_sgo_exclude_scripts( $list ) {
	return array_merge( (array) $list, array( 'ed-arcana', 'emma-divines', 'ed-blog' ) );
}
add_filter( 'sgo_javascript_combine_exclude_ids', 'ed_sgo_exclude_script_ids' );
function ed_sgo_exclude_script_ids( $list ) {
	return array_merge( (array) $list, array( 'ed-arcana-js-before', 'ed-arcana-js', 'emma-divines-js', 'ed-blog-js-before', 'ed-blog-js' ) );
}
