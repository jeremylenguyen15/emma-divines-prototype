<?php
/**
 * Plugin Name: Emma Divines
 * Description: Emma's homepage (tarot-card sessions, Draw a card, reviews, FAQs) and blog with everything editable from the dashboard: Sessions, Reviews, FAQs and Emma Divines → Site Content.
 * Version: 1.4.0
 * Author: Jeremy Le Nguyen
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Text Domain: emma-divines
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ED_VERSION', '1.4.0' );
define( 'ED_FILE', __FILE__ );
define( 'ED_DIR', plugin_dir_path( __FILE__ ) );
define( 'ED_URL', plugin_dir_url( __FILE__ ) );
define( 'ED_TEMPLATE', 'emma-divines-home' );
define( 'ED_BLOG_TEMPLATE', 'emma-divines-blog' );

require_once ED_DIR . 'inc/fields.php';
require_once ED_DIR . 'inc/content.php';
require_once ED_DIR . 'inc/settings.php';
require_once ED_DIR . 'inc/render.php';
require_once ED_DIR . 'inc/blog.php';
require_once ED_DIR . 'inc/contact.php';
require_once ED_DIR . 'inc/tarot.php';
require_once ED_DIR . 'inc/redirects.php';
require_once ED_DIR . 'inc/tracking.php';
require_once ED_DIR . 'inc/seed.php';

register_activation_hook( __FILE__, 'ed_activate' );
function ed_activate() {
	ed_register_content();
	ed_seed();
	flush_rewrite_rules();
}
