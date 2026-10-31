"""Builds the prototype's blog pages from index.html (CSS, header, footer) and the plugin's blog.css.

    python3 tools/build-blog.py

Writes blog.html and one article-<name>.html per post. Post text is a snapshot in tools/blog-posts.json
(refresh it from https://emmadivines.com/wp-json/wp/v2/posts). Re-run after changing index.html or blog.css.
"""
import json, pathlib, re

ROOT = pathlib.Path(__file__).resolve().parent.parent
src = (ROOT / 'index.html').read_text()
blog_css = (ROOT / 'wordpress/emma-divines/assets/blog.css').read_text()
snap = {p['slug']: p for p in json.loads((ROOT / 'tools/blog-posts.json').read_text())}

def between(a, b, s=src, inclusive=True):
    i = s.index(a); j = s.index(b, i) + (len(b) if inclusive else 0)
    return s[i:j]

head = src[:src.index('</style>') + len('</style>')]
defs = between('<svg width="0" height="0"', '</defs></svg>')
header = between('<header class="site-header"', '</header>') + '\n' + src[src.index('<div class="mobile-menu"'):src.index('\n\n<main>')]
footer = between('<footer class="footer">', '</footer>')

# Header + footer on blog pages: Blog is the current page, homepage links go back home
header = header.replace('aria-current="page"', '')
header = re.sub(r'<a href="blog\.html"[^>]*>Blog</a>', '<a href="blog.html" aria-current="page">Blog</a>', header)
footer = re.sub(r'<li><a href="blog\.html"[^>]*>Blog</a></li>', '<li><a href="blog.html" aria-current="page">Blog</a></li>', footer)
header, footer = [re.sub(r'href="#(top|emma|faq|draw)"', r'href="index.html#\1"', x) for x in (header, footer)]
header = header.replace('<a class="logo" href="index.html#top"', '<a class="logo" href="index.html"')
assert 'aria-current="page">Blog' in header and 'aria-current="page">Blog' in footer, 'add Blog to index.html nav/footer first'

POSTS = [  # newest first; text from the live site, excerpts lightly tidied
    dict(slug='how-to-choose-your-first-tarot-deck-a-beginners-guide', name='tarot-deck', cat='Tarot', date='Apr 15, 2025', long='April 15, 2025',
         title='How to Choose Your First Tarot Deck: A Beginner’s Guide', h1='How to Choose Your First Tarot Deck: A <em>Beginner’s</em> Guide',
         ex='Choosing your first tarot deck is an exciting and personal experience. Here’s how to find one that speaks to you, whether you’re drawn to tarot for self-reflection or spiritual growth.'),
    dict(slug='online-psychic-readings', name='online-reading', cat='Psychic Readings', date='Apr 9, 2025', long='April 9, 2025',
         title='Online Psychic Reading: How It Works', h1='Online Psychic Reading: <em>How It Works</em>',
         ex='More people than ever are seeking guidance, clarity and reassurance. Here’s what happens in a reading by phone, video or email, and how to get the most from one.'),
    dict(slug='spirit_world_signs', name='spirit-signs', cat='Mediumship', date='Apr 9, 2025', long='April 9, 2025',
         title='How to Recognize Signs from the Spirit World in Your Daily Life', h1='How to Recognize Signs from the <em>Spirit World</em> in Your Daily Life',
         ex='In our busy lives, it’s easy to overlook the subtle ways the spirit world reaches out: unusual sensations, dreams, synchronicities.'),
    dict(slug='psychic-children', name='psychic-children', cat='Intuitive Development', date='Apr 9, 2025', long='April 9, 2025',
         title='Psychic Abilities in Children: Fostering Intuition from a Young Age', h1='Psychic Abilities in Children: Fostering <em>Intuition</em> from a Young Age',
         ex='Children are naturally intuitive. How to recognise the signs, and gently support a sensitive child as they grow.'),
    dict(slug='your-guide-to-finding-the-right-psychic-medium-or-healer-tips-from-psychic-healer-emma-divines-copy', name='right-psychic', cat='Psychic Readings', date='Mar 23, 2024', long='March 23, 2024',
         title='Your Guide to Finding the ‘Right’ Psychic, Medium or Healer', h1='Your Guide to Finding the ‘Right’ <em>Psychic, Medium or Healer</em>',
         ex='If you’re seeking clarity, guidance or healing, finding the right person matters. A few tips on how to search for the right match.'),
    dict(slug='hello-world-copy-copy', name='mediumship', cat='Mediumship', date='Mar 10, 2024', long='March 10, 2024',
         title='Exploring Mediumship: How Connecting with Spirit Can Provide Comfort', h1='Exploring Mediumship: How Connecting with Spirit Can Provide <em>Comfort</em>',
         ex='One of my favourite things about mediumship is how it can bring healing along a grief journey, and the comfort of knowing our loved ones are near.'),
]
slugify = lambda t: re.sub(r'[^a-z0-9]+', '-', t.lower()).strip('-')
for p in POSTS:
    p['mins'] = max(1, -(-snap[p['slug']]['words'] // 200))
    p['href'] = f'article-{p["name"]}.html'
    p['catslug'] = slugify(p['cat'])

SP = '<svg class="sp" aria-hidden="true"><use href="#sp"/></svg>'
BOOK = 'https://calendly.com/emmadivinesbook'
EXTRA_CSS = '<style>\n' + blog_css.replace('.ed-header .nav a[aria-current=page]', '.site-header .nav a[aria-current=page]') + '</style>'

def page(title, main):
    h = re.sub(r'<title>.*?</title>', f'<title>{title}</title>', head)
    h = re.sub(r'<meta property="og:url" content="[^"]*">', '', h)
    return '\n'.join([h, EXTRA_CSS, '</head>', '<body>', defs, header, main, footer,
                      '<script>' + BLOG_JS + '</script>', '</body>', '</html>'])

BLOG_JS = (ROOT / 'wordpress/emma-divines/assets/blog.js').read_text().replace(
    "const ED=window.ED||{};", "const ED=Object.assign({book:'" + BOOK + "',thanks:'Thank you! For the quickest reply, call or text Emma at (916) 248-6213.',view:location.pathname.includes('article')?'post':'blog'},window.ED||{});")
# The prototype has no server: show the thank-you note instead of posting the form
BLOG_JS = BLOG_JS.replace("try{const r=await fetch(ED.ajax,{method:'POST',body:new FormData(f)});const d=await r.json();",
                          "try{const d={success:true};")

def card(p, ex=True):
    return f'''<article class="post" data-cat="{p['catslug']}"><a href="{p['href']}">
  <div class="ph"><img src="images/blog/{p['name']}.jpg" alt="" loading="lazy"></div>
  <span class="ptag">{p['cat']}</span><h3>{p['title']}</h3>
  {f'<p class="excerpt">{p["ex"]}</p>' if ex else ''}
  <p class="meta">{p['date']} {SP} {p['mins']} min read</p></a></article>'''

BOOK_CARD = f'''<aside class="post-cta"><div class="stars" data-stars="14,10" data-top="2" data-span="96" aria-hidden="true"></div>
  <p class="eyebrow"><svg><use href="#sp"/></svg>Ready when you are</p>
  <h3>Want guidance that’s about <em>you</em>?</h3>
  <p>Readings by phone, video or email. Or draw a card for a little guidance first.</p>
  <div class="cta-acts"><a class="btn btn-light" href="{BOOK}" target="_blank" rel="noopener">Book a Reading <span class="arr">→</span></a><a class="link" href="index.html#draw">Draw a card <span class="arr">→</span></a></div>
</aside>'''

# ---------- Article list ----------
topics = {}
for p in POSTS:
    topics.setdefault(p['catslug'], [p['cat'], 0])[1] += 1
chips = f'<button class="chip" aria-pressed="true" data-cat="">All<span class="ct">{len(POSTS)}</span></button>' + ''.join(
    f'<button class="chip" aria-pressed="false" data-cat="{k}">{v[0]}<span class="ct">{v[1]}</span></button>'
    for k, v in sorted(topics.items(), key=lambda kv: (-kv[1][1], kv[1][0])))
f = POSTS[0]
blog_main = f'''<main>
<section class="blog-hero">
  <div class="hero-bg" aria-hidden="true"><div class="stars" data-stars="40,22" data-top="1" data-span="70"></div></div>
  <div class="wrap bh-inner">
    <p class="eyebrow"><svg><use href="#sp"/></svg>Emma’s journal</p>
    <h1>Notes on spirit, <em>intuition</em> &amp; healing</h1>
    <p class="lede">Gentle guides to readings, mediumship and trusting what you feel, written by Emma.</p>
    <div class="cat-chips" role="group" aria-label="Filter by topic">{chips}</div>
  </div>
</section>
<section class="featured"><div class="wrap">
  <article class="feat-card" data-cat="{f['catslug']}">
    <a class="feat-img" href="{f['href']}" tabindex="-1" aria-hidden="true"><img src="images/blog/{f['name']}.jpg" alt=""></a>
    <div class="feat-body">
      <div class="tagrow"><span class="ptag new">Newest</span><span class="ptag">{f['cat']}</span></div>
      <h2><a href="{f['href']}">{f['title']}</a></h2>
      <p class="excerpt">{f['ex']}</p>
      <p class="meta">{f['date']} {SP} {f['mins']} min read</p>
      <a class="btn btn-plum" href="{f['href']}">Read the article <span class="arr">→</span></a>
    </div>
  </article>
</div></section>
<section class="journal"><div class="wrap">
  <div class="j-head"><div><p class="eyebrow"><svg><use href="#sp"/></svg>Keep reading</p><h2>More from the <em>journal</em></h2></div><p class="j-count">{len(POSTS)} articles</p></div>
  <div class="post-grid">{''.join(card(p) for p in POSTS[1:])}{BOOK_CARD}</div>
  <p class="empty-note" hidden>No articles on this topic yet.</p>
</div></section>
</main>'''
(ROOT / 'blog.html').write_text(page('Blog · Emma Divines', blog_main))

# ---------- Articles ----------
ICON = {
    'fb': '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 8h3V4.5h-3c-2.2 0-3.8 1.7-3.8 3.9V11H8v3.5h2.2V20h3.6v-5.5H17l.5-3.5h-3.7V8.6c0-.4.2-.6.6-.6Z"/></svg>',
    'pin': '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.5"/><path d="M11 9.5 9 18m2.6-9.1c2.4-.6 4.4.8 4 3-.3 1.9-2 3-3.6 2.4"/></svg>',
    'link': '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1"/><path d="M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/></svg>',
}
for p in POSTS:
    body = snap[p['slug']]['content'].replace('<h1>', '<h2>').replace('</h1>', '</h2>')
    body = body.replace('<img ', '<img loading="lazy" ')
    same = [q for q in POSTS if q is not p and q['cat'] == p['cat']]
    related = (same + [q for q in POSTS if q is not p and q not in same])[:3]
    url = snap[p['slug']]['link']
    main = f'''<main>
<section class="art-hero">
  <div class="hero-bg" aria-hidden="true"><div class="stars" data-stars="40,22" data-top="1" data-span="62"></div></div>
  <div class="wrap ah-inner">
    <p class="crumbs"><a href="blog.html">Blog</a>{SP}<a href="blog.html#{p['catslug']}">{p['cat']}</a></p>
    <h1>{p['h1']}</h1>
    <div class="byline-row"><img src="images/emma-avatar.jpg" alt=""><span><b>By Emma</b></span><span class="dot-sep">·</span><span>{p['long']}</span><span class="dot-sep">·</span><span>{p['mins']} min read</span></div>
  </div>
</section>
<section class="art-cover"><div class="wrap"><div class="frame"><img src="images/blog/{p['name']}.jpg" alt=""></div></div></section>
<article class="wrap"><div class="art-body">
{body}
</div>
<div class="art-end">
  <aside class="art-cta"><div class="stars" data-stars="16,10" data-top="2" data-span="96" aria-hidden="true"></div>
    <p class="eyebrow"><svg><use href="#sp"/></svg>Ready when you are</p>
    <h2>Want a reading that’s about <em>you</em>?</h2>
    <p>Readings by phone, video or email. Or draw a card for a little guidance first.</p>
    <div class="cta-acts"><a class="btn btn-light" href="{BOOK}" target="_blank" rel="noopener">Book a Reading <span class="arr">→</span></a><a class="link" href="index.html#draw">Draw a card <span class="arr">→</span></a></div>
  </aside>
  <div class="author"><img src="images/emma-avatar.jpg" alt=""><div><h3>Written by Emma</h3><p>Emma is a psychic, medium and Reiki practitioner offering honest, compassionate readings by phone, video and email.</p><a class="link" href="index.html#emma">More about Emma <span class="arr">→</span></a></div></div>
  <div class="art-foot"><a class="link" href="blog.html">← All articles</a><div class="share">Share
    <a href="https://www.facebook.com/sharer/sharer.php?u={url}" target="_blank" rel="noopener" aria-label="Share on Facebook">{ICON['fb']}</a>
    <a href="https://www.pinterest.com/pin/create/button/?url={url}" target="_blank" rel="noopener" aria-label="Save to Pinterest">{ICON['pin']}</a>
    <button type="button" class="copy-link" data-url="{url}" aria-label="Copy link">{ICON['link']}</button><span class="copied" aria-live="polite"></span></div></div>
</div></article>
<section class="related"><div class="wrap">
  <div class="j-head"><div><p class="eyebrow"><svg><use href="#sp"/></svg>Keep reading</p><h2>You might also <em>like</em></h2></div><a class="link" href="blog.html">All articles <span class="arr">→</span></a></div>
  <div class="post-grid">{''.join(card(q, ex=False) for q in related)}</div>
</div></section>
</main>'''
    html = page(f'{p["title"]} · Emma Divines', main).replace('<body>', '<body class="ed-article">', 1)
    (ROOT / p['href']).write_text(html)
print('built blog.html +', len(POSTS), 'articles')
