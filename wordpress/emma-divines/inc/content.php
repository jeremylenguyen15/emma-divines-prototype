<?php
/**
 * Sessions (with categories), Reviews, FAQs and Messages, all under one "Emma Divines" menu.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ed_session_fields() {
	return array(
		'_ed_meta'  => array( 'label' => 'Length & format', 'placeholder' => 'e.g. 30 min · Phone or video', 'help' => 'The small line under the name on the card.' ),
		'_ed_price' => array( 'label' => 'Price', 'placeholder' => 'e.g. $88', 'help' => 'Type it as you want it shown, e.g. <code>$88</code> or <code>Free</code>. Leave empty to hide.' ),
		'_ed_desc'  => array( 'label' => 'Short description', 'type' => 'textarea', 'rows' => 2, 'help' => 'One or two sentences, shown when the card is turned over.' ),
		'_ed_link'  => array( 'label' => 'Booking link', 'type' => 'url', 'placeholder' => 'e.g. https://calendly.com/emmadivinesbook/classic', 'help' => 'Paste the Calendly link for this session (or any page). Leave empty to use the main booking link.' ),
		'_ed_cta'   => array( 'label' => 'Button label', 'placeholder' => 'e.g. Book', 'help' => 'Optional. Defaults to <em>Book</em>.' ),
		'_ed_tag'   => array(
			'label'   => 'Highlight',
			'type'    => 'select',
			'options' => array(
				''      => 'None',
				'start' => '✦ Start here',
				'quick' => 'Quick check-in',
			),
			'help'    => '<em>Start here</em> adds a badge on the card. <em>Quick check-in</em> groups it under that heading in the menu.',
		),
	);
}

function ed_category_fields() {
	return array(
		'tab_label' => array( 'label' => 'Short tab name', 'help' => 'Optional shorter name for the tab on phones, e.g. <em>Readings</em>.' ),
		'eyebrow'   => array( 'label' => 'Small heading', 'help' => 'The little line above the category name in its menu.' ),
		'intro'     => array( 'label' => 'Intro', 'type' => 'textarea', 'rows' => 3 ),
		'photos'    => array( 'label' => 'Photo(s)', 'type' => 'images', 'help' => 'One photo shows large beside the list. Choose several to show a gallery.' ),
		'as_tab'    => array(
			'label'   => 'Show as cards on the homepage',
			'type'    => 'select',
			'options' => array(
				'1' => 'Yes, as a tab of cards',
				'0' => 'No, only in the menu (like Events)',
			),
		),
		'order'     => array( 'label' => 'Order', 'type' => 'number', 'default' => '0', 'help' => 'Lower numbers come first.' ),
		'note'      => array( 'label' => 'Note under the list', 'type' => 'textarea', 'rows' => 2, 'help' => 'Optional. Replaces the usual “All sessions by phone or video…” note.' ),
	);
}

add_action( 'init', 'ed_register_content' );
function ed_register_content() {
	$common = array(
		'public'             => false,
		'show_ui'            => true,
		'show_in_menu'       => 'emma-divines',
		'show_in_rest'       => false,
		'publicly_queryable' => false,
		'exclude_from_search' => true,
	);

	register_post_type(
		'ed_session',
		$common + array(
			'labels'   => array(
				'name'               => 'Sessions',
				'singular_name'      => 'Session',
				'add_new_item'       => 'Add a session',
				'edit_item'          => 'Edit session',
				'all_items'          => 'Sessions & events',
				'featured_image'     => 'Card art',
				'set_featured_image' => 'Set card art',
				'remove_featured_image' => 'Remove card art',
				'not_found'          => 'No sessions yet.',
			),
			'supports' => array( 'title', 'thumbnail', 'page-attributes' ),
		)
	);

	register_taxonomy(
		'ed_category',
		'ed_session',
		array(
			'labels'            => array(
				'name'          => 'Categories',
				'singular_name' => 'Category',
				'edit_item'     => 'Edit category',
				'add_new_item'  => 'Add a category',
				'menu_name'     => 'Categories',
			),
			'public'            => false,
			'show_ui'           => true,
			'show_in_menu'      => false,
			'show_admin_column' => true,
			'hierarchical'      => true,
			'show_in_rest'      => false,
			'meta_box_cb'       => 'ed_category_radio_box',
		)
	);

	register_post_type(
		'ed_review',
		$common + array(
			'labels'   => array(
				'name'          => 'Reviews',
				'singular_name' => 'Review',
				'add_new_item'  => 'Add a review',
				'edit_item'     => 'Edit review',
				'enter_title_here' => 'Client name, e.g. Terry Y.',
			),
			'supports' => array( 'title', 'page-attributes' ),
		)
	);

	register_post_type(
		'ed_faq',
		$common + array(
			'labels'   => array(
				'name'          => 'FAQs',
				'singular_name' => 'FAQ',
				'add_new_item'  => 'Add a question',
				'edit_item'     => 'Edit question',
			),
			'supports' => array( 'title', 'page-attributes' ),
		)
	);

	register_post_type(
		'ed_message',
		$common + array(
			'labels'       => array(
				'name'          => 'Messages',
				'singular_name' => 'Message',
				'edit_item'     => 'Message',
				'not_found'     => 'No messages yet. Notes sent from the “Let’s get connected” form land here (and in your email).',
			),
			'supports'     => array( 'title' ),
			'capabilities' => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap' => true,
		)
	);
}

add_filter( 'enter_title_here', 'ed_title_placeholder', 10, 2 );
function ed_title_placeholder( $text, $post ) {
	$map = array(
		'ed_session' => 'Session name, e.g. Classic Reading',
		'ed_review'  => 'Client name, e.g. Terry Y.',
		'ed_faq'     => 'The question',
	);
	return isset( $map[ $post->post_type ] ) ? $map[ $post->post_type ] : $text;
}

/** One category per session, as radio buttons. */
function ed_category_radio_box( $post ) {
	$terms   = get_terms( array( 'taxonomy' => 'ed_category', 'hide_empty' => false ) );
	$current = wp_get_object_terms( $post->ID, 'ed_category', array( 'fields' => 'ids' ) );
	$current = $current ? (int) $current[0] : 0;
	usort( $terms, 'ed_term_order_cmp' );
	foreach ( $terms as $t ) {
		printf( '<label style="display:block;margin:4px 0"><input type="radio" name="tax_input[ed_category][]" value="%d"%s> %s</label>', (int) $t->term_id, checked( $current, $t->term_id, false ), esc_html( $t->name ) );
	}
	echo '<p class="description"><a href="' . esc_url( admin_url( 'edit-tags.php?taxonomy=ed_category&post_type=ed_session' ) ) . '">Edit categories</a></p>';
}

function ed_term_order_cmp( $a, $b ) {
	return (int) get_term_meta( $a->term_id, 'order', true ) - (int) get_term_meta( $b->term_id, 'order', true );
}

/* ---------- Meta boxes ---------- */

add_action( 'add_meta_boxes', 'ed_meta_boxes' );
function ed_meta_boxes() {
	add_meta_box( 'ed_session_details', 'Card details', 'ed_session_box', 'ed_session', 'normal', 'high' );
	add_meta_box( 'ed_review_details', 'Review', 'ed_review_box', 'ed_review', 'normal', 'high' );
	add_meta_box( 'ed_faq_details', 'Answer', 'ed_faq_box', 'ed_faq', 'normal', 'high' );
	add_meta_box( 'ed_message_details', 'Message', 'ed_message_box', 'ed_message', 'normal', 'high' );
	remove_meta_box( 'pageparentdiv', 'ed_session', 'side' );
	remove_meta_box( 'pageparentdiv', 'ed_review', 'side' );
	remove_meta_box( 'pageparentdiv', 'ed_faq', 'side' );
	add_meta_box( 'ed_order', 'Order', 'ed_order_box', array( 'ed_session', 'ed_review', 'ed_faq' ), 'side', 'default' );
}

function ed_order_box( $post ) {
	printf( '<p><input type="number" name="menu_order" value="%d" class="small-text"></p><p class="description">Lower numbers come first.</p>', (int) $post->menu_order );
}

function ed_post_values( $post_id, $fields ) {
	$v = array();
	foreach ( $fields as $k => $f ) {
		$v[ $k ] = get_post_meta( $post_id, $k, true );
	}
	return $v;
}

function ed_session_box( $post ) {
	wp_nonce_field( 'ed_save', 'ed_nonce' );
	echo '<table class="form-table" role="presentation">';
	ed_field_rows( ed_session_fields(), ed_post_values( $post->ID, ed_session_fields() ), '' );
	echo '</table><p class="description">Card art: set it in the <b>Card art</b> box on the right. Category: pick it in the <b>Categories</b> box.</p>';
}

function ed_review_fields() {
	return array(
		'_ed_text' => array( 'label' => 'What they said', 'type' => 'textarea', 'rows' => 6 ),
		'_ed_date' => array( 'label' => 'When', 'placeholder' => 'e.g. Aug 2026', 'help' => 'Optional, e.g. <em>Aug 2026</em>.' ),
	);
}
function ed_review_box( $post ) {
	wp_nonce_field( 'ed_save', 'ed_nonce' );
	echo '<table class="form-table" role="presentation">';
	ed_field_rows( ed_review_fields(), ed_post_values( $post->ID, ed_review_fields() ), '' );
	echo '</table>';
}

function ed_faq_fields() {
	return array(
		'_ed_answer' => array( 'label' => 'Answer', 'type' => 'textarea', 'rows' => 5, 'help' => 'Links are fine: <code>&lt;a href="#draw"&gt;Draw a card&lt;/a&gt;</code>' ),
	);
}
function ed_faq_box( $post ) {
	wp_nonce_field( 'ed_save', 'ed_nonce' );
	echo '<table class="form-table" role="presentation">';
	ed_field_rows( ed_faq_fields(), ed_post_values( $post->ID, ed_faq_fields() ), '' );
	echo '</table>';
}

function ed_message_box( $post ) {
	$email = get_post_meta( $post->ID, '_ed_email', true );
	printf(
		'<p><b>From:</b> %s &lt;<a href="mailto:%s">%s</a>&gt;<br><b>Sent:</b> %s</p><div style="white-space:pre-wrap;font-size:14px;line-height:1.6">%s</div>',
		esc_html( get_post_meta( $post->ID, '_ed_name', true ) ),
		esc_attr( $email ),
		esc_html( $email ),
		esc_html( get_the_date( 'M j, Y g:ia', $post ) ),
		esc_html( get_post_meta( $post->ID, '_ed_msg', true ) )
	);
}

add_action( 'save_post', 'ed_save_post', 10, 2 );
function ed_save_post( $post_id, $post ) {
	if ( ! isset( $_POST['ed_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['ed_nonce'] ), 'ed_save' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$map = array(
		'ed_session' => ed_session_fields(),
		'ed_review'  => ed_review_fields(),
		'ed_faq'     => ed_faq_fields(),
	);
	if ( ! isset( $map[ $post->post_type ] ) ) {
		return;
	}
	foreach ( $map[ $post->post_type ] as $k => $f ) {
		if ( isset( $_POST[ $k ] ) ) {
			update_post_meta( $post_id, $k, ed_sanitize_field( $f, $_POST[ $k ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		}
	}
}

/* ---------- Category screen fields ---------- */

add_action( 'ed_category_add_form_fields', 'ed_category_add_fields' );
function ed_category_add_fields() {
	foreach ( ed_category_fields() as $k => $f ) {
		echo '<div class="form-field">';
		echo '<label>' . esc_html( $f['label'] ) . '</label>';
		ed_field_input( 'ed_term[' . $k . ']', $f, isset( $f['default'] ) ? $f['default'] : '' );
		echo '</div>';
	}
}

add_action( 'ed_category_edit_form_fields', 'ed_category_edit_fields' );
function ed_category_edit_fields( $term ) {
	$v = array();
	foreach ( ed_category_fields() as $k => $f ) {
		$v[ $k ] = get_term_meta( $term->term_id, $k, true );
	}
	ed_field_rows( ed_category_fields(), $v, 'ed_term' );
}

add_action( 'created_ed_category', 'ed_save_term' );
add_action( 'edited_ed_category', 'ed_save_term' );
function ed_save_term( $term_id ) {
	if ( empty( $_POST['ed_term'] ) || ! current_user_can( 'manage_categories' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}
	$in = (array) $_POST['ed_term']; // phpcs:ignore
	foreach ( ed_category_fields() as $k => $f ) {
		if ( isset( $in[ $k ] ) ) {
			update_term_meta( $term_id, $k, ed_sanitize_field( $f, $in[ $k ] ) );
		}
	}
}

/* ---------- List screens ---------- */

add_filter( 'manage_ed_session_posts_columns', 'ed_session_columns' );
function ed_session_columns( $cols ) {
	$out = array();
	foreach ( $cols as $k => $label ) {
		if ( 'title' === $k ) {
			$out['ed_art'] = '';
		}
		$out[ $k ] = $label;
		if ( 'title' === $k ) {
			$out['ed_price'] = 'Price';
			$out['ed_order'] = 'Order';
		}
	}
	unset( $out['date'] );
	return $out;
}

add_action( 'manage_ed_session_posts_custom_column', 'ed_session_column', 10, 2 );
function ed_session_column( $col, $post_id ) {
	if ( 'ed_art' === $col ) {
		echo get_the_post_thumbnail( $post_id, array( 40, 50 ), array( 'style' => 'width:40px;height:50px;object-fit:cover;border-radius:4px' ) );
	} elseif ( 'ed_price' === $col ) {
		echo esc_html( get_post_meta( $post_id, '_ed_price', true ) );
	} elseif ( 'ed_order' === $col ) {
		echo (int) get_post_field( 'menu_order', $post_id );
	}
}

add_filter( 'manage_ed_message_posts_columns', 'ed_message_columns' );
function ed_message_columns( $cols ) {
	return array(
		'cb'      => $cols['cb'],
		'title'   => 'From',
		'ed_msg'  => 'Message',
		'date'    => 'Sent',
	);
}
add_action( 'manage_ed_message_posts_custom_column', 'ed_message_column', 10, 2 );
function ed_message_column( $col, $post_id ) {
	if ( 'ed_msg' === $col ) {
		echo esc_html( wp_trim_words( get_post_meta( $post_id, '_ed_msg', true ), 24 ) );
	}
}

/** Sort sessions, reviews and FAQs by Order in the dashboard, like on the site. */
add_action( 'pre_get_posts', 'ed_admin_order' );
function ed_admin_order( $q ) {
	if ( is_admin() && $q->is_main_query() && in_array( $q->get( 'post_type' ), array( 'ed_session', 'ed_review', 'ed_faq' ), true ) && ! $q->get( 'orderby' ) ) {
		$q->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
	}
}

/* ---------- Menu ---------- */

add_action( 'admin_menu', 'ed_admin_menu', 9 );
function ed_admin_menu() {
	add_menu_page( 'Emma Divines', 'Emma Divines', 'edit_posts', 'emma-divines', 'ed_settings_page', 'dashicons-star-filled', 3 );
	add_submenu_page( 'emma-divines', 'Site content', 'Site content', 'manage_options', 'emma-divines', 'ed_settings_page' );
}

add_action( 'admin_menu', 'ed_admin_menu_late', 20 );
function ed_admin_menu_late() {
	add_submenu_page( 'emma-divines', 'Categories', 'Categories', 'manage_categories', 'edit-tags.php?taxonomy=ed_category&post_type=ed_session' );
}

add_filter( 'parent_file', 'ed_parent_file' );
function ed_parent_file( $parent ) {
	$screen = get_current_screen();
	if ( $screen && 'ed_category' === $screen->taxonomy ) {
		return 'emma-divines';
	}
	return $parent;
}

/** Sessions list: grouped by category (in category order), then by Order. */
add_filter( 'posts_clauses', 'ed_session_list_clauses', 10, 2 );
function ed_session_list_clauses( $c, $q ) {
	global $wpdb;
	if ( ! is_admin() || ! $q->is_main_query() || 'ed_session' !== $q->get( 'post_type' ) || isset( $_GET['orderby'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return $c;
	}
	$c['join']   .= " LEFT JOIN {$wpdb->term_relationships} ed_tr ON ed_tr.object_id = {$wpdb->posts}.ID"
		. " LEFT JOIN {$wpdb->term_taxonomy} ed_tt ON ed_tt.term_taxonomy_id = ed_tr.term_taxonomy_id AND ed_tt.taxonomy = 'ed_category'"
		. " LEFT JOIN {$wpdb->termmeta} ed_tm ON ed_tm.term_id = ed_tt.term_id AND ed_tm.meta_key = 'order'";
	$c['groupby'] = "{$wpdb->posts}.ID";
	$c['orderby'] = "MIN( CAST( ed_tm.meta_value AS SIGNED ) ) ASC, {$wpdb->posts}.menu_order ASC, {$wpdb->posts}.post_title ASC";
	return $c;
}
