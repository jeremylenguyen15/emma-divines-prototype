<?php
/**
 * Emma Divines Blog: every published post, newest first. The newest is featured; topic chips filter the rest.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$posts = array_map( 'ed_post_card', ed_blog_posts() );
$first = $posts ? array_shift( $posts ) : null;
$all   = $first ? array_merge( array( $first ), $posts ) : array();

// Topic chips: only topics that have posts, most posts first
$topics = array();
foreach ( $all as $c ) {
	if ( $c['slug'] ) {
		$topics[ $c['slug'] ] = array( 'name' => $c['topic'], 'n' => ( isset( $topics[ $c['slug'] ] ) ? $topics[ $c['slug'] ]['n'] : 0 ) + 1 );
	}
}
uasort( $topics, function ( $a, $b ) { return $b['n'] - $a['n'] ?: strcmp( $a['name'], $b['name'] ); } );

$ed_body_class = 'ed-blog';
require ED_DIR . 'templates/parts/top.php';
?>
<main>
<section class="blog-hero">
  <div class="hero-bg" aria-hidden="true"><div class="stars" data-stars="40,22" data-top="1" data-span="70"></div></div>
  <div class="wrap bh-inner">
    <p class="eyebrow"><svg><use href="#sp"/></svg>Emma’s journal</p>
    <h1>Notes on spirit, <em>intuition</em> &amp; healing</h1>
    <p class="lede">Gentle guides to readings, mediumship and trusting what you feel, written by Emma.</p>
    <?php if ( count( $topics ) > 1 ) : ?>
    <div class="cat-chips" role="group" aria-label="Filter by topic">
      <button class="chip" aria-pressed="true" data-cat="">All<span class="ct"><?php echo count( $all ); ?></span></button>
      <?php foreach ( $topics as $slug => $t ) : ?><button class="chip" aria-pressed="false" data-cat="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $t['name'] ); ?><span class="ct"><?php echo (int) $t['n']; ?></span></button><?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php if ( $first ) : ?>
<section class="featured"><div class="wrap">
  <article class="feat-card" data-cat="<?php echo esc_attr( $first['slug'] ); ?>">
    <a class="feat-img" href="<?php echo esc_url( $first['url'] ); ?>" tabindex="-1" aria-hidden="true"><?php if ( $first['img'] ) : ?><img src="<?php echo esc_url( $first['img'] ); ?>" alt=""><?php endif; ?></a>
    <div class="feat-body">
      <div class="tagrow"><span class="ptag new">Newest</span><?php if ( $first['topic'] ) : ?><span class="ptag"><?php echo esc_html( $first['topic'] ); ?></span><?php endif; ?></div>
      <h2><a href="<?php echo esc_url( $first['url'] ); ?>"><?php echo esc_html( $first['title'] ); ?></a></h2>
      <p class="excerpt"><?php echo esc_html( $first['ex'] ); ?></p>
      <p class="meta"><?php echo esc_html( $first['date'] ); ?> <?php echo ed_sp(); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $first['mins'] ); ?> min read</p>
      <a class="btn btn-plum" href="<?php echo esc_url( $first['url'] ); ?>">Read the article <span class="arr">→</span></a>
    </div>
  </article>
</div></section>
<?php endif; ?>

<section class="journal"><div class="wrap">
  <div class="j-head"><div><p class="eyebrow"><svg><use href="#sp"/></svg>Keep reading</p><h2>More from the <em>journal</em></h2></div><p class="j-count"><?php echo count( $all ); ?> articles</p></div>
  <div class="post-grid">
    <?php foreach ( $posts as $c ) { ed_print_post_card( $c ); } ?>
    <?php require ED_DIR . 'templates/parts/book-card.php'; ?>
  </div>
  <p class="empty-note" hidden>No articles on this topic yet.</p>
</div></section>
</main>
<?php
require ED_DIR . 'templates/parts/bottom.php';
