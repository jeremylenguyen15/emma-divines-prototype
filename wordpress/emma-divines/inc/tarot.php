<?php
/**
 * Emma Divines → Tarot cards: the 22 Major Arcana used by "Draw a card".
 * The original wording ships in assets/arcana.js; only the changes Emma makes are stored (option ed_arcana),
 * so an emptied field goes back to the original.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** The original 22 cards, read from assets/arcana.js. */
function ed_arcana_defaults() {
	static $cards = null;
	if ( null === $cards ) {
		$js    = (string) file_get_contents( ED_DIR . 'assets/arcana.js' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$json  = trim( preg_replace( array( '/^\s*window\.ED_ARC\s*=\s*/', '/;\s*$/' ), '', $js ) );
		$cards = json_decode( $json, true );
		$cards = is_array( $cards ) ? $cards : array();
	}
	return $cards;
}

function ed_arcana_fields() {
	$sessions = array( '' => '(Keep the original suggestion)' );
	foreach ( get_posts( array( 'post_type' => 'ed_session', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ) ) as $p ) {
		$t              = html_entity_decode( get_the_title( $p ), ENT_QUOTES );
		$sessions[ $t ] = $t;
	}
	return array(
		'img'     => array( 'label' => 'Picture', 'type' => 'image', 'help' => 'Leave empty to keep the original art.' ),
		'name'    => array( 'label' => 'Card name' ),
		'kw'      => array( 'label' => 'Keywords', 'help' => 'Shown under the name, e.g. “New beginnings · Trust · Leap”.' ),
		'guide'   => array( 'label' => 'Reading: Just guide me', 'type' => 'textarea', 'rows' => 2 ),
		'love'    => array( 'label' => 'Reading: Love & relationships', 'type' => 'textarea', 'rows' => 2 ),
		'career'  => array( 'label' => 'Reading: Career & purpose', 'type' => 'textarea', 'rows' => 2 ),
		'healing' => array( 'label' => 'Reading: Healing & self', 'type' => 'textarea', 'rows' => 2 ),
		'loved'   => array( 'label' => 'Reading: A loved one who’s passed', 'type' => 'textarea', 'rows' => 2 ),
		'sess'    => array( 'label' => 'Suggested session', 'type' => 'select', 'options' => $sessions, 'help' => 'Offered after a “Just guide me” draw. The other topics suggest their own session.' ),
	);
}

/** Emma's changes only: index => field => value. */
function ed_arcana_edits() {
	$edits = get_option( 'ed_arcana', array() );
	return is_array( $edits ) ? $edits : array();
}

add_action( 'admin_init', 'ed_arcana_register' );
function ed_arcana_register() {
	register_setting( 'ed_arcana', 'ed_arcana', array( 'type' => 'array', 'sanitize_callback' => 'ed_arcana_sanitize', 'default' => array() ) );
}

function ed_arcana_sanitize( $input ) {
	$defaults = ed_arcana_defaults();
	$fields   = ed_arcana_fields();
	$out      = array();
	foreach ( (array) $input as $i => $card ) {
		$i = absint( $i );
		if ( ! isset( $defaults[ $i ] ) || ! is_array( $card ) ) {
			continue;
		}
		foreach ( $fields as $k => $f ) {
			if ( ! isset( $card[ $k ] ) ) {
				continue;
			}
			// Plain text only: these are written straight into the card on the page
			$v = 'image' === ( isset( $f['type'] ) ? $f['type'] : '' ) ? (string) absint( $card[ $k ] ) : sanitize_textarea_field( wp_unslash( $card[ $k ] ) );
			$v = trim( $v );
			$orig = isset( $defaults[ $i ][ $k ] ) ? (string) $defaults[ $i ][ $k ] : '';
			if ( '' !== $v && '0' !== $v && $v !== $orig ) {
				$out[ $i ][ $k ] = $v;
			}
		}
	}
	return $out;
}

add_action( 'admin_menu', 'ed_arcana_menu', 21 );
function ed_arcana_menu() {
	add_submenu_page( 'emma-divines', 'Tarot cards', 'Tarot cards', 'manage_options', 'emma-divines-tarot', 'ed_arcana_page' );
}

function ed_arcana_page() {
	$defaults = ed_arcana_defaults();
	$edits    = ed_arcana_edits();
	$fields   = ed_arcana_fields();
	$home     = ed_home_page_id();
	?>
	<div class="wrap ed-settings ed-tarot">
		<h1>Emma Divines · Tarot cards</h1>
		<p class="ed-lead">The 22 cards in <b>Draw a card</b>. Click a card to change its name, keywords, readings or picture. Empty a field to go back to the original wording.
		<?php if ( $home ) : ?>
			<a class="button" href="<?php echo esc_url( get_permalink( $home ) . '#draw' ); ?>" target="_blank" rel="noopener">Try Draw a card ↗</a>
		<?php endif; ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'ed_arcana' ); ?>
			<?php foreach ( $defaults as $i => $card ) : ?>
				<?php
				$values = array_merge( $card, isset( $edits[ $i ] ) ? $edits[ $i ] : array() );
				$thumb  = ! empty( $values['img'] ) ? wp_get_attachment_image_url( (int) $values['img'], 'thumbnail' ) : '';
				$thumb  = $thumb ? $thumb : ED_URL . 'assets/images/arcana/' . str_pad( (string) $i, 2, '0', STR_PAD_LEFT ) . '.jpg';
				?>
				<details class="ed-card">
					<summary>
						<img src="<?php echo esc_url( $thumb ); ?>" alt="">
						<span class="ed-card-num"><?php echo esc_html( $card['num'] ); ?></span>
						<b><?php echo esc_html( $values['name'] ); ?></b>
						<?php if ( ! empty( $edits[ $i ] ) ) : ?><span class="ed-card-edited">Edited</span><?php endif; ?>
					</summary>
					<table class="form-table" role="presentation">
						<?php ed_field_rows( $fields, $values, 'ed_arcana[' . $i . ']' ); ?>
					</table>
				</details>
			<?php endforeach; ?>
			<?php submit_button( 'Save changes' ); ?>
		</form>
	</div>
	<?php
}

/** Changes sent to the page, applied over the original deck by emma.js. Escaped because the cards are built as HTML. */
function ed_arcana_front_edits() {
	$out = array();
	foreach ( ed_arcana_edits() as $i => $card ) {
		foreach ( $card as $k => $v ) {
			if ( 'img' === $k ) {
				$url = wp_get_attachment_image_url( (int) $v, 'large' );
				if ( $url ) {
					$out[ $i ]['img'] = esc_url( $url );
				}
			} else {
				$out[ $i ][ $k ] = 'sess' === $k ? $v : esc_html( $v );
			}
		}
	}
	return (object) $out;
}
