<?php
/**
 * Blog pages: document head, the site header and the mobile menu (same as the homepage; links lead back to it).
 *
 * @var string $ed_body_class Set by the template.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$s    = ed_settings();
$tel  = ed_tel( $s['phone'] );
$book = $s['book_url'];
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<?php wp_head(); ?>
</head>
<body <?php body_class( $ed_body_class ); ?>>
<?php wp_body_open(); ?>
<?php require ED_DIR . 'templates/symbols.svg.php'; ?>

<header class="ed-header" id="hdr">
  <div class="wrap">
    <a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="Emma Divines home"><img src="<?php echo esc_url( ed_logo() ); ?>" alt="Emma Divines"></a>
    <nav class="nav" aria-label="Main">
      <?php ed_nav_links( false, true ); ?>
      <?php ed_social_links( 'social social-mini', 'Emma on social media (header)' ); ?>
      <a class="btn btn-light" href="<?php echo esc_url( $book ); ?>" target="_blank" rel="noopener"><?php ed_e( 'hero_book' ); ?> <span class="arr">→</span></a>
    </nav>
    <div class="mobile-actions">
      <a class="btn btn-light" href="<?php echo esc_url( $book ); ?>" target="_blank" rel="noopener">Book <span class="arr">→</span></a>
      <button class="menu-btn" id="menu-open" aria-label="Open menu" aria-controls="mmenu" aria-expanded="false"><i></i><i></i><i></i></button>
    </div>
  </div>
</header>
<div class="mobile-menu" id="mmenu" hidden>
  <div class="top"><img src="<?php echo esc_url( ed_logo() ); ?>" alt="Emma Divines"><button class="close-btn" id="menu-close" aria-label="Close menu">✕</button></div>
  <nav aria-label="Mobile"><?php ed_nav_links( true, true ); ?></nav>
  <a class="btn btn-light" href="<?php echo esc_url( $book ); ?>" target="_blank" rel="noopener" style="align-self:flex-start"><?php ed_e( 'hero_book' ); ?> <span class="arr">→</span></a>
  <?php ed_social_links( 'social social-menu', 'Emma on social media (menu)' ); ?>
  <a class="menu-phone" href="<?php echo esc_attr( $tel ); ?>">Call or text <?php ed_e( 'phone' ); ?></a>
</div>
