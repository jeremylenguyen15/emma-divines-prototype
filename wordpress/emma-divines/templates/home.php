<?php
/**
 * Emma Divines Home. Markup mirrors the approved prototype; words and photos come from the dashboard.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$s     = ed_settings();
$tel   = ed_tel( $s['phone'] );
$book  = $s['book_url'];
$photo = ed_img_url( $s['hero_photo'], 'medium' );
$emma  = ed_img_url( $s['emma_photo'], 'large' );
$life  = array_filter( array_map( 'absint', explode( ',', (string) $s['life_photos'] ) ) );
$faqs  = get_posts( array( 'post_type' => 'ed_faq', 'numberposts' => -1, 'orderby' => array( 'menu_order' => 'ASC', 'title' => 'ASC' ) ) );
$roles = array_filter( array_map( 'trim', explode( "\n", (string) $s['emma_roles'] ) ) );
$data  = ed_front_data();
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<?php wp_head(); ?>
</head>
<body <?php body_class( 'ed-home' ); ?>>
<?php wp_body_open(); ?>
<?php require ED_DIR . 'templates/symbols.svg.php'; ?>

<header class="ed-header" id="hdr">
  <div class="wrap">
    <a class="logo" href="#top" aria-label="Emma Divines home"><img src="<?php echo esc_url( ed_logo() ); ?>" alt="Emma Divines"></a>
    <nav class="nav" aria-label="Main">
      <?php ed_nav_links(); ?>
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
  <nav aria-label="Mobile"><?php ed_nav_links( true ); ?></nav>
  <a class="btn btn-light" href="<?php echo esc_url( $book ); ?>" target="_blank" rel="noopener" style="align-self:flex-start"><?php ed_e( 'hero_book' ); ?> <span class="arr">→</span></a>
  <?php ed_social_links( 'social social-menu', 'Emma on social media (menu)' ); ?>
  <a class="menu-phone" href="<?php echo esc_attr( $tel ); ?>">Call or text <?php ed_e( 'phone' ); ?></a>
</div>

<main>
<section class="hero" id="top">
  <div class="hero-bg" aria-hidden="true"><canvas id="aurora"></canvas><div class="stars" data-stars="46,24" data-top="1" data-span="40"></div><div class="shoots"></div></div>
  <div class="hero-inner wrap">
    <div class="copy">
      <?php ed_eyebrow( 'hero_eyebrow' ); ?>
      <h1><?php ed_headline( 'hero_title', 'hero_accent' ); ?></h1>
      <div class="byline"><?php if ( $photo ) : ?><img class="avatar" src="<?php echo esc_url( $photo ); ?>" alt="Emma Divines, smiling" width="140" height="140"><?php endif; ?>
        <p><?php ed_e( 'hero_intro' ); ?></p></div>
      <p class="vibe"><?php ed_e( 'hero_vibe' ); ?> <a href="<?php echo esc_attr( $tel ); ?>">Call or text <?php ed_e( 'phone' ); ?></a></p>
      <div class="trust"><a class="rev-link" href="#reviews"><svg class="sp" aria-hidden="true"><use href="#sp"/></svg>Read client reviews <span class="arr" aria-hidden="true">→</span></a><?php ed_social_links( 'social social-hero', 'Emma on social media' ); ?></div>
      <div class="ctas">
        <a class="btn btn-plum" href="<?php echo esc_url( $book ); ?>" target="_blank" rel="noopener"><?php ed_e( 'hero_book' ); ?> <span class="arr">→</span></a>
        <a class="btn btn-outline" href="#draw"><?php ed_e( 'hero_draw' ); ?> <svg class="sp"><use href="#sp"/></svg></a>
      </div>
    </div>
    <div class="offerings" id="offerings">
      <div class="cards" id="cards" aria-live="polite"></div>
      <div class="pager" id="pager" hidden><span class="dots" id="dots"></span><span id="pager-label"></span><button id="pager-btn"></button></div>
      <button class="deck-hint" id="deck-next">Swipe to deal the next card <span class="arr">→</span></button>
      <div class="tabs" role="tablist" aria-label="Session types" id="tabs"></div>
      <div class="after-cards">
        <button class="link js-all">See all sessions &amp; prices <span class="arr">→</span></button>
        <p class="policy"><?php ed_e( 'policy' ); ?></p>
      </div>
    </div>
  </div>
</section>

<section class="band how" id="how">
  <div class="wrap">
    <div class="sec-head" data-reveal><?php ed_eyebrow( 'how_eyebrow' ); ?><h2><?php ed_e( 'how_title' ); ?></h2></div>
    <ol class="steps">
      <?php foreach ( array( 1 => 'I', 2 => 'II', 3 => 'III' ) as $n => $roman ) : ?>
      <li class="step" data-reveal data-stagger><span class="n"><?php echo esc_html( $roman ); ?></span><h3><?php ed_e( "how_{$n}_title" ); ?></h3><p><?php ed_e( "how_{$n}_text" ); ?></p></li>
      <?php endforeach; ?>
    </ol>
    <p class="in-person-note" data-reveal><svg class="sp"><use href="#sp"/></svg> <?php ed_e( 'in_person' ); ?> <span class="call-line"><a href="<?php echo esc_attr( $tel ); ?>">Call <?php ed_e( 'phone' ); ?></a> to discuss.</span></p>
  </div>
</section>

<section class="band draw" id="draw">
  <div class="stars" data-stars="40,22" data-top="4" data-span="90" aria-hidden="true"></div><div class="shoots" aria-hidden="true"></div>
  <div class="wrap">
    <div class="sec-head" data-reveal><?php ed_eyebrow( 'draw_eyebrow' ); ?>
      <h2><?php ed_headline( 'draw_title', 'draw_accent' ); ?></h2>
      <p class="lede"><?php ed_e( 'draw_lede' ); ?></p></div>
    <div class="draw-stage" id="drawStage">
      <div class="dstep" id="step-focus">
        <h3 class="step-title">What’s on your heart today?</h3>
        <div class="focus-chips" role="group" aria-label="Choose a focus" id="focusChips"></div>
        <div class="deck-stage">
          <div class="deck" id="deck" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i><svg><use href="#sp"/></svg></i></div>
          <button class="shuffle-btn" id="shuffleBtn" aria-disabled="true"><svg class="sb-icon" viewBox="0 0 20 20" aria-hidden="true"><rect x="3" y="5" width="9" height="13" rx="2" transform="rotate(-14 7.5 11.5)"/><rect x="8" y="3" width="9" height="13" rx="2" transform="rotate(8 12.5 9.5)"/></svg><span class="sb-label">Choose a focus to shuffle</span><svg class="sp sb-sp" aria-hidden="true"><use href="#sp"/></svg></button>
        </div>
      </div>
      <div class="dstep" id="step-pick" hidden>
        <p class="eyebrow pick-for" id="pickFor"></p>
        <h3 class="step-title">Choose the card that calls to you</h3>
        <p class="step-sub">Take a breath and hold your question in mind.</p>
        <div class="fan" id="fan" aria-label="The 22 cards of the Major Arcana, face down"></div>
        <div class="pick-meta"><span>22 cards · the Major Arcana</span><button class="link light js-refocus">← Change focus</button></div>
      </div>
      <div class="dstep read-step" id="step-read" hidden></div>
    </div>
  </div>
</section>

<section class="band emma" id="emma">
  <div class="wrap emma-grid">
    <div class="arch" data-reveal="left"><?php if ( $emma ) : ?><img src="<?php echo esc_url( $emma ); ?>" alt="<?php echo esc_attr( get_post_meta( (int) $s['emma_photo'], '_wp_attachment_image_alt', true ) ?: 'Emma Divines' ); ?>" width="840" height="1040" loading="lazy"><?php endif; ?></div>
    <div class="emma-copy" data-reveal="right">
      <?php ed_eyebrow( 'emma_eyebrow' ); ?>
      <h2><?php ed_headline( 'emma_title', 'emma_accent' ); ?></h2>
      <?php ed_paragraphs( $s['emma_bio'] ); ?>
      <?php if ( $roles ) : ?>
      <ul class="roles" aria-label="What Emma offers"><?php foreach ( $roles as $r ) : ?><li><?php echo esc_html( $r ); ?></li><?php endforeach; ?></ul>
      <?php endif; ?>
      <?php if ( $s['emma_btn'] && $s['emma_btn_url'] ) : ?>
      <a class="btn btn-outline" href="<?php echo esc_url( $s['emma_btn_url'] ); ?>" style="align-self:flex-start"><?php ed_e( 'emma_btn' ); ?> <span class="arr">→</span></a>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php if ( $life ) : ?>
<section class="band life" id="life">
  <div class="wrap">
    <div class="life-top">
      <div class="life-head" data-reveal="left"><?php ed_eyebrow( 'life_eyebrow' ); ?>
        <h2><?php ed_headline( 'life_title', 'life_accent' ); ?></h2>
        <p class="lede"><?php ed_e( 'life_lede' ); ?></p></div>
      <div class="life-ctas" data-reveal="right">
        <a class="btn btn-plum js-hello" href="#contact">Say hello <span class="arr">→</span></a>
        <?php if ( $s['instagram'] ) : ?><a class="link" href="<?php echo esc_url( $s['instagram'] ); ?>" target="_blank" rel="noopener">Follow along on Instagram <span class="arr">→</span></a><?php endif; ?>
      </div>
    </div>
    <ul class="life-grid" aria-label="Photos from Emma’s life">
      <?php foreach ( $life as $aid ) : $src = ed_img_url( $aid, 'large' ); if ( ! $src ) { continue; } ?>
      <li data-reveal data-stagger><figure><img src="<?php echo esc_url( $src ); ?>" alt="<?php echo esc_attr( get_post_meta( $aid, '_wp_attachment_image_alt', true ) ); ?>" width="564" height="705" loading="lazy"><?php $cap = wp_get_attachment_caption( $aid ); if ( $cap ) : ?><figcaption><?php echo esc_html( $cap ); ?></figcaption><?php endif; ?></figure></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<?php if ( $data['reviews'] ) : ?>
<section class="band reviews" id="reviews">
  <div class="wrap">
    <div class="sec-head" data-reveal><?php ed_eyebrow( 'rev_eyebrow' ); ?><h2><?php ed_e( 'rev_title' ); ?></h2><span class="g-chip"><b>★★★★★</b> Reviews from Google</span></div>
    <div class="rev-wrap" data-reveal>
      <div class="rev-grid" id="revs" aria-label="Google reviews"></div>
      <div class="rev-controls"><button class="rev-btn" data-dir="-1" aria-label="Previous reviews">←</button><button class="rev-btn" data-dir="1" aria-label="Next reviews">→</button></div>
    </div>
    <div class="rev-cta" data-reveal><a class="btn btn-plum" href="<?php echo esc_url( $book ); ?>" target="_blank" rel="noopener"><?php ed_e( 'hero_book' ); ?> <span class="arr">→</span></a><?php if ( $s['rev_url'] ) : ?><a class="link" href="<?php echo esc_url( $s['rev_url'] ); ?>" target="_blank" rel="noopener">Read all reviews on Google <span class="arr">→</span></a><?php endif; ?></div>
  </div>
</section>
<?php endif; ?>

<?php if ( $faqs ) : ?>
<section class="band faq" id="faq">
  <div class="wrap faq-grid">
    <div class="faq-side" data-reveal="left"><?php ed_eyebrow( 'faq_eyebrow' ); ?><h2><?php ed_e( 'faq_title' ); ?></h2>
      <p class="lede"><?php ed_e( 'faq_lede' ); ?></p><?php if ( $s['faq_url'] ) : ?><a class="link" href="<?php echo esc_url( $s['faq_url'] ); ?>">All FAQs <span class="arr">→</span></a><?php endif; ?></div>
    <div>
      <?php foreach ( $faqs as $i => $q ) : ?>
      <details data-reveal data-stagger<?php echo 0 === $i ? ' open' : ''; ?>><summary><?php echo esc_html( get_the_title( $q ) ); ?><span class="pm" aria-hidden="true"></span></summary>
        <?php ed_paragraphs( get_post_meta( $q->ID, '_ed_answer', true ), '' ); ?></details>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
</main>

<footer class="footer">
  <div class="stars" data-stars="30,16" data-top="2" data-span="40" aria-hidden="true"></div>
  <div class="wrap foot-grid">
    <div data-reveal data-stagger>
      <h2><?php ed_e( 'foot_title' ); ?></h2>
      <form class="form" id="contact" novalidate>
        <input type="hidden" name="action" value="ed_contact">
        <label class="ed-hp" aria-hidden="true">Leave this empty<input name="website" tabindex="-1" autocomplete="off"></label>
        <label for="f-name">Name</label><input id="f-name" name="name" placeholder="Name" autocomplete="name">
        <label for="f-email">Email</label><input id="f-email" name="email" type="email" placeholder="Email" autocomplete="email" required>
        <label for="f-msg"><?php ed_e( 'foot_prompt' ); ?></label><textarea id="f-msg" name="msg" placeholder="<?php echo esc_attr( $s['foot_prompt'] ); ?>" required></textarea>
        <button class="btn btn-light" type="submit">Send <span class="arr">→</span></button>
        <p class="form-note" id="form-note" aria-live="polite"></p>
      </form>
    </div>
    <ul class="foot-links" data-reveal data-stagger aria-label="Site">
      <li><a href="<?php echo esc_url( $book ); ?>" target="_blank" rel="noopener">Book a session</a></li>
      <?php foreach ( array_keys( (array) $data['offers'] ) as $name ) : ?><li><a href="#top" data-tab="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $name ); ?></a></li><?php endforeach; ?>
      <?php if ( $data['eventsKey'] ) : ?><li><a href="#top" data-sheet="<?php echo esc_attr( $data['eventsKey'] ); ?>"><?php echo esc_html( $data['eventsKey'] ); ?></a></li><?php endif; ?>
      <li><a href="#emma">About</a></li><?php if ( ed_blog_url() ) : ?><li><a href="<?php echo esc_url( ed_blog_url() ); ?>">Blog</a></li><?php endif; ?><?php if ( $faqs ) : ?><li><a href="#faq">FAQs</a></li><?php endif; ?>
    </ul>
    <div class="foot-brand" data-reveal data-stagger><a class="foot-phone" href="<?php echo esc_attr( $tel ); ?>">Call or text <?php ed_e( 'phone' ); ?></a><img src="<?php echo esc_url( ed_logo() ); ?>" alt="Emma Divines">
      <?php ed_social_links( 'social', 'Emma on social media' ); ?></div>
  </div>
  <div class="wrap"><div class="copyright"><span>© <span id="yr"><?php echo esc_html( gmdate( 'Y' ) ); ?></span> Emma Divines</span><span class="credit">Created by <a href="https://jeremyux.com" target="_blank" rel="noopener">jeremyux.com</a></span></div></div>
</footer>

<dialog id="cat" aria-labelledby="cat-title"></dialog>

<dialog id="prices" aria-labelledby="prices-title">
  <div class="dlg-head"><h2 id="prices-title">All sessions &amp; prices</h2><button class="close-btn" id="dlg-close" aria-label="Close">✕</button></div>
  <div class="dlg-body" id="prices-body"></div>
</dialog>

<?php wp_footer(); ?>
</body>
</html>
