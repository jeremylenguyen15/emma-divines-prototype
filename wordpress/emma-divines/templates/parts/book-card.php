<?php
/** The booking card that closes the blog grid. */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<aside class="post-cta"><div class="stars" data-stars="14,10" data-top="2" data-span="96" aria-hidden="true"></div>
  <p class="eyebrow"><svg><use href="#sp"/></svg>Ready when you are</p>
  <h3>Want guidance that’s about <em>you</em>?</h3>
  <p>Readings by phone, video or email. Or draw a card for a little guidance first.</p>
  <div class="cta-acts"><a class="btn btn-light" href="<?php echo esc_url( ed_settings()['book_url'] ); ?>" target="_blank" rel="noopener"><?php ed_e( 'hero_book' ); ?> <span class="arr">→</span></a><a class="link" href="<?php echo esc_url( ed_home_link( 'draw' ) ); ?>">Draw a card <span class="arr">→</span></a></div>
</aside>
