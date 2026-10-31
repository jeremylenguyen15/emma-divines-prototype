<?php
/**
 * Emma Divines → Site content: every line of homepage copy, grouped by section.
 * Headlines have an "accent" field: those words show in pink italic after the headline.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ed_settings_schema() {
	return array(
		'contact' => array(
			'title'  => 'Contact & booking',
			'fields' => array(
				'book_url'    => array( 'label' => 'Main booking link', 'type' => 'url', 'default' => 'https://calendly.com/emmadivinesbook', 'help' => 'Used by the “Book a Reading” buttons. Each session has its own link too.' ),
				'phone'       => array( 'label' => 'Phone (as shown)', 'default' => '(916) 248-6213' ),
				'email_to'    => array( 'label' => 'Send contact form notes to', 'type' => 'email', 'default' => '', 'help' => 'Leave empty to use the site’s admin email. Every note is also saved under <b>Emma Divines → Messages</b>.' ),
				'thanks'      => array( 'label' => 'Thank-you message', 'default' => 'Thank you! Your note is on its way. For the quickest reply, call or text Emma at (916) 248-6213.' ),
				'instagram'   => array( 'label' => 'Instagram link', 'type' => 'url', 'default' => 'https://www.instagram.com/emmadivines/' ),
				'tiktok'      => array( 'label' => 'TikTok link', 'type' => 'url', 'default' => 'https://www.tiktok.com/@emmadivines' ),
				'youtube'     => array( 'label' => 'YouTube link', 'type' => 'url', 'default' => 'https://www.youtube.com/@emmadivines' ),
				'facebook'    => array( 'label' => 'Facebook link', 'type' => 'url', 'default' => 'https://www.facebook.com/diviningsoul' ),
				'policy'      => array( 'label' => 'Booking note', 'default' => 'All sessions by phone or video · Reschedule free 24h+ ahead', 'help' => 'Shown under the cards and in the menus.' ),
				'in_person'   => array( 'label' => 'In-person note', 'default' => 'In the Sacramento area? In-person sessions are possible in special circumstances.' ),
			),
		),
		'hero'    => array(
			'title'  => 'Top of page',
			'fields' => array(
				'hero_eyebrow' => array( 'label' => 'Small heading', 'default' => 'Psychic · Medium · Reiki · Teacher & Guide' ),
				'hero_title'   => array( 'label' => 'Headline', 'default' => 'Readings, mediumship & Reiki —' ),
				'hero_accent'  => array( 'label' => 'Headline accent', 'default' => 'from wherever you are' ),
				'hero_photo'   => array( 'label' => 'Your photo (round)', 'type' => 'image' ),
				'hero_intro'   => array( 'label' => 'Hello line', 'type' => 'textarea', 'rows' => 2, 'default' => 'Hi, I’m Emma — honest, compassionate guidance by phone, video or email.' ),
				'hero_vibe'    => array( 'label' => 'Call line', 'default' => 'Want to feel my vibe before you book?', 'help' => 'Your phone number is added after it.' ),
				'hero_book'    => array( 'label' => 'Main button', 'default' => 'Book a Reading' ),
				'hero_draw'    => array( 'label' => 'Second button', 'default' => 'Not sure? Draw a card' ),
			),
		),
		'how'     => array(
			'title'  => 'How it works',
			'fields' => array(
				'how_eyebrow' => array( 'label' => 'Small heading', 'default' => 'Booking is simple' ),
				'how_title'   => array( 'label' => 'Headline', 'default' => 'How it works' ),
				'how_1_title' => array( 'label' => 'Step I', 'default' => 'Choose a session' ),
				'how_1_text'  => array( 'label' => 'Step I text', 'type' => 'textarea', 'rows' => 2, 'default' => 'Pick the card that fits your question.' ),
				'how_2_title' => array( 'label' => 'Step II', 'default' => 'Pick a time' ),
				'how_2_text'  => array( 'label' => 'Step II text', 'type' => 'textarea', 'rows' => 2, 'default' => 'Book and pay securely without leaving the site.' ),
				'how_3_title' => array( 'label' => 'Step III', 'default' => 'Talk by phone or video' ),
				'how_3_text'  => array( 'label' => 'Step III text', 'type' => 'textarea', 'rows' => 2, 'default' => 'Join from anywhere, no app needed. Email readings arrive within 1 business day.' ),
			),
		),
		'draw'    => array(
			'title'  => 'Draw a card',
			'fields' => array(
				'draw_eyebrow' => array( 'label' => 'Small heading', 'default' => 'A little guidance' ),
				'draw_title'   => array( 'label' => 'Headline', 'default' => 'Not sure where to start?' ),
				'draw_accent'  => array( 'label' => 'Headline accent', 'default' => 'Draw a card.' ),
				'draw_lede'    => array( 'label' => 'Intro', 'type' => 'textarea', 'rows' => 2, 'default' => 'Choose what’s on your heart, then pick from the 22 cards of the Major Arcana for a little guidance.' ),
			),
		),
		'emma'    => array(
			'title'  => 'Meet Emma',
			'fields' => array(
				'emma_photo'   => array( 'label' => 'Photo', 'type' => 'image' ),
				'emma_eyebrow' => array( 'label' => 'Small heading', 'default' => 'Meet Emma' ),
				'emma_title'   => array( 'label' => 'Headline', 'default' => 'Welcome — I’m so glad you were' ),
				'emma_accent'  => array( 'label' => 'Headline accent', 'default' => 'guided here' ),
				'emma_bio'     => array(
					'label'   => 'Your story',
					'type'    => 'textarea',
					'rows'    => 8,
					'help'    => 'Leave an empty line between paragraphs.',
					'default' => "My spiritual journey began in 2018, when I left my job and moved to Thailand to study. During my very first Reiki course my psychic gifts came to light: clairvoyant visions, feeling what my client felt, knowing where her struggles were rooted.\n\nSince then I’ve trained with Masters and teachers in readings, energy work, Tarot and evidential mediumship, and I now teach Tarot and Reiki. It’s an honor to walk beside you with honest, compassionate guidance on your own path. “Know thyself.”",
				),
				'emma_roles'   => array( 'label' => 'What you offer', 'type' => 'textarea', 'rows' => 6, 'help' => 'One per line.', 'default' => "Psychic medium\nTarot reader\nReiki practitioner & teacher\nSpiritual teacher & guide\nSound healer\nEnergy healer" ),
				'emma_btn'     => array( 'label' => 'Button label', 'default' => 'Read my story', 'help' => 'Leave empty to hide the button.' ),
				'emma_btn_url' => array( 'label' => 'Button link', 'type' => 'url', 'default' => '', 'help' => 'e.g. your About page.' ),
			),
		),
		'life'    => array(
			'title'  => 'Off the clock',
			'fields' => array(
				'life_eyebrow' => array( 'label' => 'Small heading', 'default' => 'Off the clock' ),
				'life_title'   => array( 'label' => 'Headline', 'default' => 'The person behind' ),
				'life_accent'  => array( 'label' => 'Headline accent', 'default' => 'the readings' ),
				'life_lede'    => array( 'label' => 'Intro', 'type' => 'textarea', 'rows' => 2, 'default' => 'Here’s a peek at my everyday life: the people, places and pups I love. If something’s on your mind, just say hi.' ),
				'life_photos'  => array( 'label' => 'Photos', 'type' => 'images', 'help' => 'Choose 4 (or 8). Each photo’s <b>Caption</b> in the media library is the label under it, and its <b>Alt text</b> describes it for screen readers.' ),
			),
		),
		'more'    => array(
			'title'  => 'Reviews, FAQs & footer',
			'fields' => array(
				'rev_eyebrow' => array( 'label' => 'Reviews small heading', 'default' => 'Kind words' ),
				'rev_title'   => array( 'label' => 'Reviews headline', 'default' => 'What clients say' ),
				'rev_url'     => array( 'label' => '“Read all reviews” link', 'type' => 'url', 'default' => 'https://www.google.com/search?q=Emma+Divines+reviews' ),
				'faq_eyebrow' => array( 'label' => 'FAQs small heading', 'default' => 'Good to know' ),
				'faq_title'   => array( 'label' => 'FAQs headline', 'default' => 'Questions' ),
				'faq_lede'    => array( 'label' => 'FAQs intro', 'default' => 'Anything else on your mind? There’s more in the full FAQ.' ),
				'faq_url'     => array( 'label' => '“All FAQs” link', 'type' => 'url', 'default' => '', 'help' => 'Leave empty to hide.' ),
				'foot_title'  => array( 'label' => 'Footer headline', 'default' => 'Let’s get connected' ),
				'foot_prompt' => array( 'label' => 'Message box prompt', 'default' => 'What drew you here today?' ),
			),
		),
		'tracking' => array(
			'title'  => 'Tracking',
			'fields' => array(
				'ga_id' => array( 'label' => 'Google Analytics Measurement ID', 'placeholder' => 'e.g. G-AB12CD34EF', 'help' => 'From Google Analytics → Admin → Data streams → your website. Paste it and save: every page is tracked, plus Book clicks, cards drawn, categories opened, calls and contact-form messages on the homepage. Visits while you’re logged in to WordPress aren’t counted. Leave empty to turn tracking off. <b>Using Site Kit by Google?</b> Leave this empty: Site Kit adds Analytics and these homepage events still reach it.' ),
			),
		),
	);
}

/** All settings merged with defaults. */
function ed_settings() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$saved = get_option( 'ed_settings', array() );
	$out   = array();
	foreach ( ed_settings_schema() as $sec ) {
		foreach ( $sec['fields'] as $k => $f ) {
			$out[ $k ] = ( isset( $saved[ $k ] ) && '' !== $saved[ $k ] ) ? $saved[ $k ] : ( isset( $f['default'] ) ? $f['default'] : '' );
		}
	}
	// Optional link fields may be cleared on purpose.
	foreach ( array( 'emma_btn', 'emma_btn_url', 'faq_url', 'email_to', 'ga_id' ) as $k ) {
		if ( isset( $saved[ $k ] ) ) {
			$out[ $k ] = $saved[ $k ];
		}
	}
	return $cache = $out;
}

function ed_opt( $k ) {
	$s = ed_settings();
	return isset( $s[ $k ] ) ? $s[ $k ] : '';
}

add_action( 'admin_init', 'ed_register_settings' );
function ed_register_settings() {
	register_setting( 'ed_settings', 'ed_settings', array( 'sanitize_callback' => 'ed_sanitize_settings' ) );
}

function ed_sanitize_settings( $in ) {
	$in  = (array) $in;
	$old = get_option( 'ed_settings', array() );
	$out = is_array( $old ) ? $old : array();
	foreach ( ed_settings_schema() as $sec ) {
		foreach ( $sec['fields'] as $k => $f ) {
			if ( array_key_exists( $k, $in ) ) {
				$out[ $k ] = 'email' === ( isset( $f['type'] ) ? $f['type'] : '' ) ? sanitize_email( wp_unslash( $in[ $k ] ) ) : ed_sanitize_field( $f, $in[ $k ] );
			}
		}
	}
	return $out;
}

function ed_settings_page() {
	$schema = ed_settings_schema();
	$tab    = isset( $_GET['tab'] ) && isset( $schema[ sanitize_key( $_GET['tab'] ) ] ) ? sanitize_key( $_GET['tab'] ) : 'contact'; // phpcs:ignore WordPress.Security.NonceVerification
	$home   = ed_home_page_id();
	?>
	<div class="wrap ed-settings">
		<h1>Emma Divines · Site content</h1>
		<p class="ed-lead">Change any words or photos on the homepage here. Sessions, reviews and FAQs each have their own list in the <b>Emma Divines</b> menu.
		<?php if ( $home ) : ?>
			<a class="button" href="<?php echo esc_url( get_permalink( $home ) ); ?>" target="_blank" rel="noopener">View the homepage ↗</a>
		<?php endif; ?></p>
		<nav class="nav-tab-wrapper">
			<?php foreach ( $schema as $k => $sec ) : ?>
				<a class="nav-tab<?php echo $k === $tab ? ' nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=emma-divines&tab=' . $k ) ); ?>"><?php echo esc_html( $sec['title'] ); ?></a>
			<?php endforeach; ?>
		</nav>
		<form method="post" action="options.php">
			<?php settings_fields( 'ed_settings' ); ?>
			<table class="form-table" role="presentation">
				<?php ed_field_rows( $schema[ $tab ]['fields'], ed_settings(), 'ed_settings' ); ?>
			</table>
			<?php submit_button( 'Save changes' ); ?>
		</form>
	</div>
	<?php
}
