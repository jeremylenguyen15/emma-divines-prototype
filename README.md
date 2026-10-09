# Emma Divines — homepage prototype

A working prototype of the redesigned homepage for [emmadivines.com](https://emmadivines.com): psychic readings, mediumship, Reiki and classes by Emma Divines.

Emma chose the **Velvet Dusk** direction.

| File | What it is |
|---|---|
| `velvet-dusk.html` | The full homepage in the chosen direction (start here) |
| `index.html` | The earlier explorer comparing directions A · Midnight Aurora, B · Velvet Dusk, C · Moonlit Blush (`#a` / `#b` / `#c`) |
| `logo_white.png`, `logo_color.png` | Emma's logo, taken from the live site (white version generated for dark backgrounds) |

## Run it

No build step and no dependencies. Open `velvet-dusk.html` in a browser, or serve the folder:

```sh
python3 -m http.server 8000
# then open http://localhost:8000/velvet-dusk.html
```

## What's in it

- **Tarot-card offerings above the fold.** Cards are dealt in on load, tilt toward the cursor, and flip to show the description, price and a Book button (Calendly). On desktop they sit in a fanned spread; tabs with more than four sessions are dealt in sets of four. On phones they become a swipeable deck.
- **Draw a card.** Pick one of three card backs to get a suggested session.
- **Breathing dusk sky.** A small hand-written WebGL shader (0.35× resolution) slowly swells and fades the soft glows and drifts faint nebula clouds through the sky, all in the existing palette. It pauses when off-screen and falls back to a CSS gradient.
- **Parallax stars.** Three depth layers (far dust, mid and near sparkles) lag behind the page at different speeds as you scroll, with a slow, shallow twinkle. The sky glows and clouds drift with the scroll too.
- **Every homepage section from the wireframe:** how it works, meet Emma, reviews, FAQ, and a footer with a contact form.
- **Reduced motion:** cards fade in, sparkles stop twinkling, and the aurora stays still.

The whole page is about 14 KB gzipped, plus fonts (Philosopher and Inter from Google Fonts) and the logo. It uses no animation libraries.

## Brand

These come from Emma's brand guide:
- **Colours:** Deep Purple `#693F6A`, Mauve `#A8638C`, Warm Terracotta `#C08977`, Muted Purple `#936A94`, Soft Rose `#CA93B3`, Warm Blush `#DBB7AC`. The night, plum and cream tones are darker and lighter steps of that palette.
- **Type:** Philosopher for display, Inter for body.
- **Graphic elements:** the four-point sparkle and arrow CTAs.

## Still placeholder

- Emma's photo and bio
- Google reviews (the live site uses the Trustindex widget)
- Descriptions for the Mediumship, Reiki and Classes sessions, and the FAQ answers (marked "draft" on the page)
- Social links, and the contact form isn't connected to anything

Production target is WordPress with Elementor Pro. This prototype is a reference for look, motion and behaviour, not drop-in theme code.
