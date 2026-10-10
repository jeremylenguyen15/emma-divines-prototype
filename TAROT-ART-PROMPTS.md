# Tarot card art prompts: one image per offering

Use these in ChatGPT or Weavy to create a matching set of tarot illustrations of Emma, in the style of her "clair" cards. Each image fits the arched window on the session cards.

## References (attach to every generation)
1. **Face:** `images/emma-avatar.jpg` (her headshot). Keeps her likeness.
2. **Style:** the six-card "clair" sheet Emma sent. Keeps her art style.

In Weavy, use the headshot as the character/face reference and the clair sheet as the style reference (strength about 0.6–0.7). Keep the same model, seed and settings for every card.

## Category colors (Emma's brand palette)

| Category | Lead color | How it should feel |
|---|---|---|
| Psychic Readings | Deep Purple #693F6A | violet night sky, indigo, starlight |
| Mediumship | Muted Purple #936A94 | lavender, moonlit silver-blue, misty |
| Reiki | Soft Rose #CA93B3 | rose-pink healing light, peonies, glow |
| Classes & mentorship | Warm Terracotta #C08977 | amber, candlelight, golden hour |
| Events | Mauve #A8638C | berry-magenta dusk, firelight, celebration |

Every card also uses Warm Blush #DBB7AC and soft gold for highlights, skin glow and filigree, so the whole set feels related.

## Master prompt
Swap in `[PALETTE]` and `[SCENE]` for each card.

> A romantic, painterly tarot illustration in the exact style of the attached tarot cards: luminous digital painting, soft glowing light, gold filigree accents, drifting sparkles and stardust, flowers and celestial details.
>
> Color palette: [PALETTE], with warm blush (#DBB7AC) and soft gold highlights.
>
> The subject is the woman in the attached photo. Keep her likeness exactly: her face shape, eyes, nose, smile and warm complexion. She has long, softly waved brown hair with loose strands, rosy cheeks, mauve-rose lips, small gold hoop earrings and a gold spiral pendant necklace, and wears a flowing sheer chiffon gown with delicate gold embroidery in the palette colors. Her expression is serene and gentle.
>
> [SCENE]
>
> Composition: vertical 4:5 portrait. She is centered, shown from mid-chest up, with her head in the upper-middle of the frame. Leave clear headroom above her head for a small glowing symbol, as in the reference cards. Keep her face and key details within the middle 80% of the width, because the image will be cropped into an arch with a rounded top. Background fills the frame edge to edge.
>
> No border, no frame, no card outline, no text, no letters, no numerals, no captions, no logos. Just the illustration.

**Negative prompt (Weavy):** `text, letters, numbers, caption, border, frame, card outline, watermark, logo, extra fingers, distorted hands, different face, cartoon, anime, 3D render, harsh shadows, dark gloomy mood`

## The 17 cards

### Psychic Readings
`[PALETTE]` = **deep purple (#693F6A), indigo and violet night sky**

| File name | `[SCENE]` |
|---|---|
| `classic-reading` | She holds a fan of glowing tarot cards close to her heart, gazing softly upward. A starry violet night sky and a golden crescent moon glow above her head. |
| `deep-dive` | Eyes closed in deep focus, with a slow spiral of stars and indigo nebula swirling around her and a radiant eye of light above her head. |
| `quickie-reading` | She lifts a single tarot card that glows with soft light, a small burst of sparkles rising from it, against a violet sky with the first stars appearing. |
| `email-reading` | She holds a glowing letter sealed with a gold star, ribbons of light and tiny stars drifting from it into the violet night. |

### Mediumship
`[PALETTE]` = **muted purple (#936A94), lavender and moonlit silver-blue**

| File name | `[SCENE]` |
|---|---|
| `evidential-mediumship` | Eyes closed, one hand near her ear as if listening, with soft translucent wisps of light and white doves rising around her. A silver crescent moon above her head. |

### Reiki
`[PALETTE]` = **soft rose (#CA93B3), rose-pink healing light and peonies**

| File name | `[SCENE]` |
|---|---|
| `reiki-tune-up` | Her hands hover gently in front of her, soft rose-gold light flowing between them, with pink peonies around her. |
| `reiki-full-session` | Both hands rest over her heart, radiating warm rose-gold light that spreads outward in soft rays. A gentle halo of light above her head. |
| `reiki-healing-package` | Three glowing rose-quartz crystals float in an arc around her, linked by threads of light, with three small moons above her head. |

### Classes & mentorship
`[PALETTE]` = **warm terracotta (#C08977), amber candlelight and golden hour**

| File name | `[SCENE]` |
|---|---|
| `free-student-consult` | She offers an open palm holding a small glowing star, with a warm, welcoming smile, in golden-hour light. |
| `intuitive-tarot-workshop` | She lays tarot cards across a velvet table lit by candles, eyes on the cards, sparkles rising from them. |
| `mentorship-session` | She holds up a glowing lantern that lights a winding path of stars behind her. |
| `tarot-boot-camp` | Four moon phases arc above her head as she holds an open book of tarot cards glowing with amber light. |
| `reiki-i` | A single glowing golden spiral symbol hovers above her open hands, with warm amber light. |
| `reiki-ii` | Two glowing spiral symbols orbit her as she sends light outward through her palms. |
| `reiki-i-and-ii` | She stands in a full circle of golden light with glowing spiral symbols, radiant and serene, petals and stars swirling around her. |

### Events
`[PALETTE]` = **mauve (#A8638C), berry-magenta dusk and firelight**

| File name | `[SCENE]` |
|---|---|
| `artistry-of-mediumship-tour` | She stands on a softly lit stage in a warm spotlight, one hand raised as she delivers a message, with gentle orbs of light floating above an audience glow. |
| `private-sacred-ceremony` | She sits in a circle of candles beneath a full moon, a singing bowl glowing in her hands, with soft silhouettes of friends around the circle and rose petals in the air. |

## Delivery
- Generate at **1024×1280 (4:5)** or larger.
- Name each file as listed above (e.g. `classic-reading.png`) and drop them in `images/tarot/`.
- Get Emma's sign-off on each final image, since they show her likeness.
