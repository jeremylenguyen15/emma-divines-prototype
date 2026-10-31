<?php
/**
 * An article in the new design. The post's own words, image, date and address are unchanged.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_obj = get_queried_object();
$c        = ed_post_card( $post_obj );
$s        = ed_settings();
$avatar   = ed_img_url( $s['hero_photo'], 'thumbnail' );
$related  = array_map( 'ed_post_card', ed_related_posts( $post_obj ) );
$url      = rawurlencode( $c['url'] );
$cover    = get_the_post_thumbnail_url( $post_obj, '1536x1536' ) ?: $c['img'];
$alt      = (string) get_post_meta( get_post_thumbnail_id( $post_obj ), '_wp_attachment_image_alt', true );

$ed_body_class = 'ed-blog ed-article';
require ED_DIR . 'templates/parts/top.php';
?>
<main>
<section class="art-hero">
  <div class="hero-bg" aria-hidden="true"><div class="stars" data-stars="40,22" data-top="1" data-span="62"></div></div>
  <div class="wrap ah-inner">
    <p class="crumbs"><a href="<?php echo esc_url( ed_blog_url() ); ?>">Blog</a><?php if ( $c['topic'] ) : ?><?php echo ed_sp(); // phpcs:ignore WordPress.Security.EscapeOutput ?><a href="<?php echo esc_url( ed_blog_url() . '#' . $c['slug'] ); ?>"><?php echo esc_html( $c['topic'] ); ?></a><?php endif; ?></p>
    <h1><?php echo esc_html( $c['title'] ); ?></h1>
    <div class="byline-row"><?php if ( $avatar ) : ?><img src="<?php echo esc_url( $avatar ); ?>" alt=""><?php endif; ?><span><b>By Emma</b></span><span class="dot-sep">·</span><span><?php echo esc_html( get_the_date( 'F j, Y', $post_obj ) ); ?></span><span class="dot-sep">·</span><span><?php echo esc_html( $c['mins'] ); ?> min read</span></div>
  </div>
</section>
<?php if ( $cover ) : ?>
<section class="art-cover"><div class="wrap"><div class="frame"><img src="<?php echo esc_url( $cover ); ?>" alt="<?php echo esc_attr( $alt ); ?>"></div></div></section>
<?php endif; ?>
<article class="wrap<?php echo $cover ? '' : ' no-cover'; ?>">
  <div class="art-body"><?php echo ed_post_body( $post_obj ); // phpcs:ignore WordPress.Security.EscapeOutput -- post content, filtered by WordPress ?></div>
  <div class="art-end">
    <aside class="art-cta"><div class="stars" data-stars="16,10" data-top="2" data-span="96" aria-hidden="true"></div>
      <p class="eyebrow"><svg><use href="#sp"/></svg>Ready when you are</p>
      <h2>Want a reading that’s about <em>you</em>?</h2>
      <p>Readings by phone, video or email. Or draw a card for a little guidance first.</p>
      <div class="cta-acts"><a class="btn btn-light" href="<?php echo esc_url( $s['book_url'] ); ?>" target="_blank" rel="noopener"><?php ed_e( 'hero_book' ); ?> <span class="arr">→</span></a><a class="link" href="<?php echo esc_url( ed_home_link( 'draw' ) ); ?>">Draw a card <span class="arr">→</span></a></div>
    </aside>
    <div class="author"><?php if ( $avatar ) : ?><img src="<?php echo esc_url( $avatar ); ?>" alt=""><?php endif; ?><div><h3>Written by Emma</h3><p>Emma is a psychic, medium and Reiki practitioner offering honest, compassionate readings by phone, video and email.</p><a class="link" href="<?php echo esc_url( ed_home_link( 'emma' ) ); ?>">More about Emma <span class="arr">→</span></a></div></div>
    <div class="art-foot"><a class="link" href="<?php echo esc_url( ed_blog_url() ); ?>">← All articles</a>
      <div class="share">Share
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_attr( $url ); ?>" target="_blank" rel="noopener" aria-label="Share on Facebook"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 8h3V4.5h-3c-2.2 0-3.8 1.7-3.8 3.9V11H8v3.5h2.2V20h3.6v-5.5H17l.5-3.5h-3.7V8.6c0-.4.2-.6.6-.6Z"/></svg></a>
        <a href="https://www.pinterest.com/pin/create/button/?url=<?php echo esc_attr( $url ); ?>&amp;media=<?php echo esc_attr( rawurlencode( (string) $cover ) ); ?>&amp;description=<?php echo esc_attr( rawurlencode( $c['title'] ) ); ?>" target="_blank" rel="noopener" aria-label="Save to Pinterest"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.5"/><path d="M11 9.5 9 18m2.6-9.1c2.4-.6 4.4.8 4 3-.3 1.9-2 3-3.6 2.4"/></svg></a>
        <button type="button" class="copy-link" data-url="<?php echo esc_attr( $c['url'] ); ?>" aria-label="Copy link"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1"/><path d="M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/></svg></button>
        <span class="copied" aria-live="polite"></span>
      </div>
    </div>
  </div>
</article>
<?php if ( $related ) : ?>
<section class="related"><div class="wrap">
  <div class="j-head"><div><p class="eyebrow"><svg><use href="#sp"/></svg>Keep reading</p><h2>You might also <em>like</em></h2></div><a class="link" href="<?php echo esc_url( ed_blog_url() ); ?>">All articles <span class="arr">→</span></a></div>
  <div class="post-grid"><?php foreach ( $related as $r ) { $r['ex'] = null; ed_print_post_card( $r ); } ?></div>
</div></section>
<?php endif; ?>
</main>
<?php
require ED_DIR . 'templates/parts/bottom.php';
