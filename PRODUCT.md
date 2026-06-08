# Product

## Register

brand

## Users

Indonesian musicians and bands who want to find each other. A guitarist hunting for a band to join; a band that lost its drummer two weeks before a gig; a session player looking for paid recording work. They are scene people, comfortable with WhatsApp, allergic to corporate software. They browse on phones, often late at night, between rehearsals.

## Product Purpose

BenKonek is a matchmaking board for the Indonesian music scene. Bands post what they need ("Cari Gitaris untuk Rekaman EP"), musicians list what they play, and the two sides connect directly over WhatsApp. No in-app chat, no algorithm, no gatekeeping. The product succeeds when a real band fills a real slot and makes music.

## Brand Personality

Three words: analog, scene-built, direct. The voice is a flyer stapled to a rehearsal-studio corkboard, not a SaaS dashboard. Warm and lived-in, not slick. It should feel made by people who actually play, printed cheap and proud. Confidence comes from specificity (instrument, city, genre), never from buzzwords.

## Anti-references

- The amber-gold-on-near-black "music app" template the previous version shipped (gradient hero, three centered stat numbers, identical icon cards, `border-left` accent cards). That is the exact AI-slop default this redesign exists to kill.
- Spotify/streaming-service minimalism: smooth dark gradients, perfect circular avatars, frictionless glass.
- Generic Bootstrap dashboard look: pill badges, soft shadows, rounded everything.

## Design Principles

1. **Print, not pixels.** Every surface is a piece of physical print: newsprint stock, ink registration, halftone, tape, rubber stamps. Texture and imperfection are the brand, not noise to sand off.
2. **The classified ad is the unit.** A gig vacancy is a small-ad in a music paper, not a SaaS card. Treat listings like catalog entries with index numbers, ruled dividers, set-in-lead typography.
3. **Loud where it counts, quiet everywhere else.** One or two spot inks (rust, teal) carry the energy against ink-on-paper. Color is earned, never sprayed.
4. **Specific beats slick.** Lead with the instrument, the city, the genre, the date. Concrete scene language over marketing adjectives.
5. **Direct connection.** The whole flow points at one verb: get the two people talking (WhatsApp). Never bury the contact action.

## Accessibility & Inclusion

Target WCAG 2.1 AA. Body ink on newsprint must clear 4.5:1 (warm near-black on warm off-white clears it comfortably). Spot inks used for text only at large/bold sizes. All decorative texture, halftone, tape, and stamps are `aria-hidden` / pure CSS, never load-bearing. Full `prefers-reduced-motion` fallbacks. Indonesian-language UI throughout.
