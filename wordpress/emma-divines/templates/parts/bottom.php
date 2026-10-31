<?php
/** Blog pages: the site footer (same as the homepage; links lead back to it). */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$s    = ed_settings();
$tel  = ed_tel( $s['phone'] );
$data = ed_front_data();
?>
<footer class="footer">
  <div class="stars" data-stars="30,16" data-top="2" data-span="40" aria-hidden="true"></div>
  <div class="wrap foot-grid">
    <div>
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
    <ul class="foot-links" aria-label="Site">
      <li><a href="<?php echo esc_url( $s['book_url'] ); ?>" target="_blank" rel="noopener">Book a session</a></li>
      <?php foreach ( array_keys( (array) $data['offers'] ) as $name ) : ?><li><a href="<?php echo esc_url( ed_home_link( sanitize_title( $name ) ) ); ?>"><?php echo esc_html( $name ); ?></a></li><?php endforeach; ?>
      <?php if ( $data['eventsKey'] ) : ?><li><a href="<?php echo esc_url( ed_home_link( sanitize_title( $data['eventsKey'] ) ) ); ?>"><?php echo esc_html( $data['eventsKey'] ); ?></a></li><?php endif; ?>
      <li><a href="<?php echo esc_url( ed_home_link( 'emma' ) ); ?>">About</a></li>
      <li><a href="<?php echo esc_url( ed_blog_url() ); ?>" aria-current="page">Blog</a></li>
      <li><a href="<?php echo esc_url( ed_home_link( 'faq' ) ); ?>">FAQs</a></li>
    </ul>
    <div class="foot-brand"><a class="foot-phone" href="<?php echo esc_attr( $tel ); ?>">Call or text <?php ed_e( 'phone' ); ?></a><img src="<?php echo esc_url( ed_logo() ); ?>" alt="Emma Divines">
      <?php ed_social_links( 'social', 'Emma on social media' ); ?></div>
  </div>
  <div class="wrap"><div class="copyright"><span>© <?php echo esc_html( gmdate( 'Y' ) ); ?> Emma Divines</span><span class="credit">Created by <a href="https://jeremyux.com" target="_blank" rel="noopener">jeremyux.com</a></span></div></div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
