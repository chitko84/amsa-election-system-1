# Redesign Notes

## Theme
- Primary deep maroon: `#64101C`
- Rich burgundy/red: `#7A1725` / `#941F2E`
- Deep background: `#29060C`
- Gold: `#D3A445`
- Bright gold: `#E2B85C`
- Soft gold: `#F0D79A`
- Cream: `#F7F0E3`
- Warm paper: `#FFFDF8`

## Navigation change
The desktop fixed side navigation from the newest prototype is no longer permanently visible. Member and admin pages now use the same hamburger menu pattern at every breakpoint. This leaves more room for dashboards, tables, candidate cards and election results.

## Design principles
- Gold is used as an accent rather than a full-page fill.
- Dark maroon/red is used for authority and official election identity.
- Main content remains warm white/cream for readability.
- Cards use light shadows, restrained gradients and small gold accents.
- Micro-interactions are intentionally subtle.
- Mobile layouts are intentionally reflowed instead of simply scaled down.

## Navigation interaction
On desktop/laptop devices with a mouse or trackpad, hovering over the hamburger button automatically opens the maroon/gold navigation drawer. The drawer stays open while the pointer remains over the trigger or drawer and closes smoothly after a short delay when the pointer leaves. Click remains available as a fallback. On touch devices, tap/click remains the primary interaction.

## Projector Reveal Direction

The projector experience is intentionally more theatrical than the admin/member UI while staying inside the AMSA maroon-red-gold identity. It uses a dark cinema-stage treatment, gold light beams, controlled particles, curtain transitions, suspense timing, and a final winner spotlight. The presentation remains readable on a projector and does not depend on sound. Motion automatically reduces when the viewer has `prefers-reduced-motion` enabled.

## Projector Reveal Layout Refinement
The final winner reveal now uses independent responsive layout zones for the winner identity and official result summary. Candidate names are width-constrained and balanced, the result card has its own reserved column, and larger projector widths use a wider 1440px presentation grid. This prevents winner-name text from crossing beneath the results card while preserving the same cinematic maroon-and-gold visual direction.

### Presenter-controlled result sequence
The projector reveal now includes a candidate roll before every result. Each candidate receives an individual cinematic intro. After the final candidate, the screen enters a non-expiring presenter hold with all nominees visible. The actual result remains hidden until the administrator explicitly presses **Reveal Official Result**. This gives the emcee unlimited speaking time and prevents an accidental timed reveal.

## Admin / User folder organization

The HTML pages are now separated by responsibility without changing the visual design or application behavior:

- `user/` contains authentication and member-facing pages.
- `admin/` contains all administration and presenter/projector pages.
- Shared styling, scripts, logos, and candidate assets remain in the root `css/`, `js/`, and `assets/` folders to avoid duplication.
