<?php
/**
 * The blog in the new design: the "Emma Divines Blog" page template (the article list) and, while a page
 * uses it, the article page for every post. Switching the Blog page back to its old template turns both off.
 * Posts keep their own addresses, so links from Google keep working.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The page that uses the blog template, or 0. Only a published page switches the posts over for everyone;
 * a private one lets Emma (or Jeremy) preview it first.
 */
function ed_blog_page_id( $published_only = true ) {
	static $ids = array();
	$key = $published_only ? 'pub' : 'any';
	if ( isset( $ids[ $key ] ) ) {
		return $ids[ $key ];
	}
	$pages = get_posts(
		array(
			'post_type'   => 'page',
			'post_status' => $published_only ? 'publish' : array( 'publish', 'private' ),
			'meta_key'    => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'  => ED_BLOG_TEMPLATE, // phpcs:ignore WordPress.DB.SlowDBQuery
			'numberposts' => 1,
			'fields'      => 'ids',
		)
	);
	return $ids[ $key ] = $pages ? (int) $pages[0] : 0;
}

/** Editors can see posts in the new design before it's switched on, by adding ?ed_preview to a post's address. */
function ed_blog_previewing() {
	return isset( $_GET['ed_preview'] ) && current_user_can( 'edit_posts' ); // phpcs:ignore WordPress.Security.NonceVerification
}

function ed_blog_url() {
	$id = ed_blog_page_id() ?: ( ed_blog_previewing() || ed_view_is_blog_page() ? ed_blog_page_id( false ) : 0 );
	return $id ? (string) get_permalink( $id ) : '';
}

function ed_view_is_blog_page() {
	return is_singular( 'page' ) && ED_BLOG_TEMPLATE === get_page_template_slug( get_queried_object_id() );
}

/** Which of our layouts this request uses: 'home', 'blog', 'post' or ''. */
function ed_view() {
	if ( ed_is_home_template() ) {
		return 'home';
	}
	if ( ed_view_is_blog_page() ) {
		return 'blog';
	}
	if ( is_singular( 'post' ) && ( ed_blog_page_id() || ed_blog_previewing() ) && ! isset( $_GET['elementor-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return 'post';
	}
	return '';
}

function ed_is_ours() {
	return '' !== ed_view();
}

/** The topic shown on a post: its first category other than "Uncategorized" (WordPress builds the address from the first one, so it never changes). */
function ed_post_topic( $post ) {
	$default = (int) get_option( 'default_category' );
	foreach ( (array) get_the_category( $post->ID ) as $t ) {
		if ( (int) $t->term_id !== $default ) {
			return $t;
		}
	}
	return null;
}

function ed_reading_mins( $post ) {
	$words = str_word_count( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ) );
	return max( 1, (int) ceil( $words / 200 ) );
}

/** Excerpt for cards: the post's own excerpt if Emma wrote one, else the opening words (minus a leading "Introduction"). */
function ed_post_excerpt( $post, $words = 34 ) {
	$text = has_excerpt( $post ) ? $post->post_excerpt : wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
	$text = preg_replace( '/^\s*introduction\b[:.\s]*/i', '', html_entity_decode( $text, ENT_QUOTES ) );
	return wp_trim_words( $text, $words, '…' );
}

function ed_post_card( $post ) {
	$topic = ed_post_topic( $post );
	return array(
		'id'    => $post->ID,
		'title' => html_entity_decode( get_the_title( $post ), ENT_QUOTES ),
		'url'   => (string) get_permalink( $post ),
		'img'   => (string) get_the_post_thumbnail_url( $post, 'large' ),
		'topic' => $topic ? html_entity_decode( $topic->name, ENT_QUOTES ) : '',
		'slug'  => $topic ? $topic->slug : '',
		'date'  => get_the_date( 'M j, Y', $post ),
		'mins'  => ed_reading_mins( $post ),
		'ex'    => ed_post_excerpt( $post ),
	);
}

function ed_blog_posts( $args = array() ) {
	return get_posts(
		array_merge(
			array(
				'post_type'   => 'post',
				'post_status' => 'publish',
				'numberposts' => 60,
				'orderby'     => 'date',
				'order'       => 'DESC',
			),
			$args
		)
	);
}

/** Up to $n other posts: same topic first, then the newest. */
function ed_related_posts( $post, $n = 3 ) {
	$out   = array();
	$topic = ed_post_topic( $post );
	if ( $topic ) {
		$out = ed_blog_posts( array( 'numberposts' => $n, 'cat' => $topic->term_id, 'post__not_in' => array( $post->ID ) ) );
	}
	if ( count( $out ) < $n ) {
		$skip = array_merge( array( $post->ID ), wp_list_pluck( $out, 'ID' ) );
		$out  = array_merge( $out, ed_blog_posts( array( 'numberposts' => $n - count( $out ), 'post__not_in' => $skip ) ) );
	}
	return $out;
}

/** The post body, with any H1 inside it shown as a section heading (the page already has the title as its H1). */
function ed_post_body( $post ) {
	$html = apply_filters( 'the_content', $post->post_content ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	// SiteGround's lazy-load swaps each image for a blank placeholder and relies on its own script, which these pages
	// don't load: put the real image back (the browser's loading="lazy" does the same job)
	$html = preg_replace( '/<noscript>\s*<img[^>]*>\s*<\/noscript>/i', '', $html );
	$html = preg_replace( '/\ssrc="data:image\/[^"]*"(.*?)\sdata-src="([^"]+)"/i', ' src="$2"$1 loading="lazy"', $html );
	$html = preg_replace( '/\sdata-srcset="/i', ' srcset="', $html );
	$html = ed_flatten_columns( $html );
	return preg_replace( array( '/<h1(\s[^>]*)?>/i', '/<\/h1>/i' ), array( '<h2$1>', '</h2>' ), $html );
}

/**
 * Articles read as one column: WordPress "Columns" blocks are unwrapped. A column holding only a picture
 * becomes a picture floated beside the text that was next to it, so the words wrap round it instead of
 * running on in a narrow strip after the picture ends.
 */
function ed_flatten_columns( $html ) {
	if ( false === stripos( $html, 'wp-block-columns' ) || ! class_exists( 'DOMDocument' ) ) {
		return $html;
	}
	$doc  = new DOMDocument();
	$prev = libxml_use_internal_errors( true );
	$ok   = $doc->loadHTML( '<?xml encoding="utf-8"?><div id="ed-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();
	libxml_use_internal_errors( $prev );
	if ( ! $ok ) {
		return $html;
	}
	$xp   = new DOMXPath( $doc );
	$has  = function ( $class ) {
		return "contains(concat(' ', normalize-space(@class), ' '), ' $class ')";
	};
	$side = 0;
	foreach ( iterator_to_array( $xp->query( '//div[' . $has( 'wp-block-columns' ) . ']' ) ) as $group ) {
		$cols = array();
		foreach ( iterator_to_array( $group->childNodes ) as $c ) {
			if ( XML_ELEMENT_NODE === $c->nodeType ) {
				$cols[] = $c;
			}
		}
		$pictures = array();
		$texts    = array();
		foreach ( $cols as $c ) {
			$kids = array();
			foreach ( $c->childNodes as $k ) {
				if ( XML_ELEMENT_NODE === $k->nodeType ) {
					$kids[] = $k;
				}
			}
			$is_picture = 1 === count( $kids ) && ( 'figure' === $kids[0]->nodeName || 'img' === $kids[0]->nodeName ) && '' === trim( $c->textContent );
			if ( $is_picture ) {
				$pictures[] = $kids[0];
			} else {
				$texts[] = $c;
			}
		}
		$parent = $group->parentNode;
		// Pictures first, floated (alternating sides when a post has several), then the text columns' contents
		foreach ( $pictures as $pic ) {
			$pic->setAttribute( 'class', trim( $pic->getAttribute( 'class' ) . ' ed-float ' . ( $side++ % 2 ? 'ed-float-left' : 'ed-float-right' ) ) );
			$parent->insertBefore( $pic, $group );
		}
		foreach ( $texts as $c ) {
			while ( $c->firstChild ) {
				$parent->insertBefore( $c->firstChild, $group );
			}
		}
		$parent->removeChild( $group );
	}
	$out = '';
	foreach ( $doc->getElementById( 'ed-root' )->childNodes as $n ) {
		$out .= $doc->saveHTML( $n );
	}
	return $out;
}

/** Address of a homepage section from another page, e.g. /#readings (the homepage opens it). */
function ed_home_link( $hash ) {
	return home_url( '/' ) . ( '' !== $hash ? '#' . $hash : '' );
}

function ed_sp() {
	return '<svg class="sp" aria-hidden="true"><use href="#sp"/></svg>';
}

function ed_print_post_card( $c, $lazy = true ) {
	?>
<article class="post" data-cat="<?php echo esc_attr( $c['slug'] ); ?>"><a href="<?php echo esc_url( $c['url'] ); ?>">
  <div class="ph"><?php if ( $c['img'] ) : ?><img src="<?php echo esc_url( $c['img'] ); ?>" alt=""<?php echo $lazy ? ' loading="lazy"' : ''; ?>><?php endif; ?></div>
  <?php if ( $c['topic'] ) : ?><span class="ptag"><?php echo esc_html( $c['topic'] ); ?></span><?php endif; ?>
  <h3><?php echo esc_html( $c['title'] ); ?></h3>
  <?php if ( null !== $c['ex'] ) : ?><p class="excerpt"><?php echo esc_html( $c['ex'] ); ?></p><?php endif; ?>
  <p class="meta"><?php echo esc_html( $c['date'] ); ?> <?php echo ed_sp(); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $c['mins'] ); ?> min read</p></a></article>
	<?php
}
