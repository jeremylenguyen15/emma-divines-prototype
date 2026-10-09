# Emma Divines — homepage prototype

A working prototype of the redesigned homepage for [emmadivines.com](https://emmadivines.com): psychic readings, mediumship, Reiki and classes by Emma Divines.

Emma chose the **Velvet Dusk** direction.

| File | What it is |
|---|---|
| `index.html` | The full homepage in the chosen direction (start here) |
| `explore.html` | The earlier explorer comparing directions A · Midnight Aurora, B · Velvet Dusk, C · Moonlit Blush (`#a` / `#b` / `#c`) |
| `logo_white.png`, `logo_color.png` | Emma's logo, taken from the live site (white version generated for dark backgrounds). The pages embed copies so they never break. |

## Run it

No build step and no dependencies. Open `index.html` in a browser, or serve the folder:

```sh
python3 -m http.server 8000
# then open http://localhost:8000
```

## What's in it

- **Tarot-card offerings above the fold.** Cards are dealt in on load, tilt toward the cursor, and flip to show the description, price and a Book button (Calendly). On desktop they sit in a fanned spread; tabs with more than four sessions are dealt in sets of four. On phones they become a swipeable deck.
- **Card art.** Each session card shows one of Emma's six "clair" illustrations (clairvoyance, clairsentience, clairaudience, clairalience, clairgustance, claircognizance) in an arched window, matched to the session in `ART_FOR`. The images are cropped from a single sheet she sent, so ask for the individual full-size originals before launch.
- **Draw a card.** When the section scrolls into view, three cards fall from the sky flipping side over side (purple back, cream moon face), then land in a spread with a sparkle burst. Pick one to get a suggested session; reshuffling drops them again.
- **Breathing dusk sky.** A small hand-written WebGL shader (0.35× resolution) slowly swells and fades the soft glows and drifts faint nebula clouds through the sky, all in the existing palette. It pauses when off-screen and falls back to a CSS gradient.
- **Shooting stars.** Every 1–3.5 seconds a soft streak crosses the hero or Draw a card sky, only while that section is on screen.
- **Scroll reveals.** Headings, steps, cards, the photo, reviews, FAQs and footer columns fade and rise in as you reach them, with a slight stagger.
- **Parallax stars.** Three depth layers (far dust, mid and near sparkles) lag behind the page at different speeds as you scroll, with a slow, shallow twinkle. The sky glows and clouds drift with the scroll too.
- **Every homepage section from the wireframe:** how it works, meet Emma, reviews, FAQ, and a footer with a contact form.
- **Category sheets.** Readings / Mediumship / Reiki / Classes in the header, mobile menu and footer open a sheet for that category: a photo of Emma doing that work (placeholder slots for now), a short intro, every session with price, description and Book. On phones it's a bottom sheet. Open one directly with `?sheet=Reiki`.
- **Real Google reviews.** Seven reviews from Emma's Google profile (taken from the Trustindex widget on her current site), shown as first name + initial in a swipeable row with Read more. On WordPress, keep the Trustindex plugin so new reviews appear automatically, and style it to match.
- **Life gallery ("A little more of me").** Four everyday-life photos from @emmadivines right after Meet Emma, with a Say hello button that jumps to the contact form.
- **Reduced motion:** cards fade in, sparkles stop twinkling, and the aurora stays still.

The whole page is about 14 KB gzipped, plus fonts (Philosopher and Inter from Google Fonts) and the logo. It uses no animation libraries.

## Brand

These come from Emma's brand guide:
- **Colours:** Deep Purple `#693F6A`, Mauve `#A8638C`, Warm Terracotta `#C08977`, Muted Purple `#936A94`, Soft Rose `#CA93B3`, Warm Blush `#DBB7AC`. The night, plum and cream tones are darker and lighter steps of that palette.
- **Type:** Philosopher for display, Inter for body.
- **Graphic elements:** the four-point sparkle and arrow CTAs.

## Still placeholder

- Emma's bio (the hero headshot, Meet Emma photo and gallery photos are from her Instagram, at screen resolution; swap in originals for launch)
- Descriptions for the Mediumship, Reiki and Classes sessions, and the FAQ answers (marked "draft" on the page)
- The contact form isn't connected to anything

Production target is WordPress with Elementor Pro. This prototype is a reference for look, motion and behaviour, not drop-in theme code.
