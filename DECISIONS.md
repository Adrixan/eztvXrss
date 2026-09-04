# Architecture & Design Decisions

## 2026-09-04 — Core Architecture & Runtime Framework
**Chosen:** Modular OOP PHP (native standard library) with PSR-4 autoloading and optional standalone zero-dependency autoloader fallback.
**Alternatives:** 
1. Micro-framework (Slim 4 / Lumen): Adds router and middleware overhead, requires URL rewriting configuration in remote Plesk subfolder (`httpdocs/eztvxrss`), and deploys bulky vendor directory to shared hosting.
2. Flat Procedural PHP scripts: Zero structure, hard to unit test with PHPUnit, violates SRP and DRY.
**Why:** Clean separation of concerns (`EztvApiClient`, `TorrentFilter`, `RssFeedBuilder`), 100% testable locally via PHPUnit with mocked JSON fixtures, light and fast execution on remote PHP 8.4/8.5 without needing complex rewrite rules or external framework runtime dependencies.
**Trade-offs:** We handcraft request routing (e.g. `feed.php` vs `index.php`), but since there are only two endpoints (the web generator UI and the RSS XML feed), a micro-framework is unnecessary overhead.
**Revisit if:** The app evolves into a multi-provider service requiring complex routing, auth, or persistent databases.

## 2026-09-04 — Feed Caching Strategy
**Chosen:** No caching (direct fetch on feed generation per user mandate: "do not implement caching").
**Alternatives:** Transient file cache or memory cache with TTL.
**Why:** User requirement explicitly states: "trigger reload whenever an rss reader access the feed on an hourly basis. do not implement caching." Feed responses include standard HTTP headers (`Cache-Control: no-cache, no-store, must-revalidate` and RSS `<ttl>60</ttl>`) guiding RSS readers to poll hourly while ensuring every fetch retrieves fresh data directly from EZTV API.
**Trade-offs:** Each RSS reader poll triggers outbound HTTP calls to EZTV API; mitigated by fast connection on remote server and efficient curl timeout handling.
**Revisit if:** EZTV API introduces strict rate limits or becomes unreachable under high client polling.

## 2026-09-04 — UI Theming & Accessibility
**Chosen:** Responsive semantic HTML5 + CSS with native dark mode support using CSS custom properties and `@media (prefers-color-scheme: dark)`.
**Alternatives:** Light-only, dark-only, or manual toggle button with localStorage.
**Why:** User chose system-preference detection. Zero client JavaScript needed for theming, optimal performance, WCAG 2.1 AA compliant contrast in both light and dark modes.
**Trade-offs:** User cannot override theme independently of OS theme without a manual toggle.
**Revisit if:** User requests a manual toggle button.

## 2026-09-04 — Feed Title Naming & IMDb Show Name Resolution
**Chosen:** Format channel titles as `<Show Name> [<Resolution> | <Format>]` (e.g. `Star Trek: Strange New Worlds [1080p | HEVC x265]`). If only resolution or only format is selected, include only that tag in square brackets. If neither is selected, omit the brackets. When an IMDb ID is entered directly, resolve the show title via TVMaze lookup (`/lookup/shows?imdb=tt...`), with fallback to parsing the show title from the release strings, and final fallback to `IMDb tt<id>`.
**Alternatives:**
1. Only parse show name from release title: May result in formatting quirks like dots (`Star.Trek.Strange.New.Worlds`).
2. Require user to enter show title manually: Worse user experience when entering an IMDb ID.
**Why:** TVMaze lookup gives the clean, canonical show name with punctuation (`Star Trek: Strange New Worlds`), while release string parsing guarantees a reliable offline/fallback title even if TVMaze is unreachable.
**Trade-offs:** Adds one lightweight HTTP call during feed generation if show title is not supplied via parameter.
**Revisit if:** Latency of show lookup impacts feed generation speed.

## 2026-09-04 — RSS Item Links & BitTorrent Magnet Syndication Format
**Chosen:** Item `<link>` and `<guid>` elements must point to the canonical EZTV episode webpage (`https://eztvx.to/ep/{id}/{slug}/`). BitTorrent magnet links are provided via standard `xmlns:torrent="http://xmlns.ezrss.it/0.1/"` `<torrent:magnetURI>`, RSS enclosure `<enclosure url="magnet:?..." length="..." type="application/x-bittorrent" />`, and raw unescaped `&` parameter delimiters inside `<description>` CDATA.
**Alternatives:** Setting item `<link>` to `magnet:?...`.
**Why:** In RSS specifications and readers, `<link>` represents the HTML web page of the entry. When set to a non-HTTP scheme like `magnet:`, browsers and readers misinterpret it as a relative URL and prepend the current website's base URL (e.g., `https://code-alongsi.de/eztvxrss/magnet:?...`), resulting in broken 404 links. Setting `<link>` to `https://eztvx.to/ep/...` ensures clicking the item opens its EZTV website page as requested, while `torrent:magnetURI` and `<enclosure>` provide dedicated, standard BitTorrent client ingestion.
**Trade-offs:** Users clicking the item title go to the web page instead of triggering their torrent client directly from the title, but this is the standard behavior in RSS and prevents URL corruption. Direct magnet links remain available in enclosures and descriptions.
**Revisit if:** BitTorrent client software changes its RSS enclosure parsing standard.

## 2026-09-04 — HTTPS Magnet Proxy Endpoint for Sanitizer-Equipped RSS Readers
**Chosen:** Provide an HTTPS redirect proxy endpoint (`https://code-alongsi.de/eztvxrss/magnet.php?url=...`) used by description HTML links (`<a href=".../magnet.php?url=...">Magnet Link</a>`), which issues an HTTP 302 redirect directly to the `magnet:?xt=...` URI and renders a responsive fallback page.
**Alternatives:** Using raw `href="magnet:?..."` in description HTML.
**Why:** Web-based feed aggregators (such as Nextcloud News, Feedly, FreshRSS) and security-focused readers sanitize HTML with Symfony HTML Sanitizer, HTMLPurifier, or DOMPurify. By default, these sanitizers strip any URI protocol that is not `http`, `https`, or `mailto`. When given a raw `magnet:` href, the sanitizer removes the `href` attribute completely, rendering the link as an empty `<a>Magnet Link</a>` or `<a href="">Magnet Link</a>`. Providing an HTTPS proxy link guarantees that the attribute is preserved across all sanitizers and readers, while seamlessly redirecting the user to their local BitTorrent client on click.
**Trade-offs:** Clicking the link routes through the server before launching the torrent client rather than launching client-side in supporting desktop readers, but it eliminates link stripping in all web readers.
**Revisit if:** Modern feed readers adopt `magnet:` into their default allowed protocol whitelists.

## 2026-09-04 — HTTP 200 Magnet Landing Page with Copy to Clipboard & Plaintext Feed URI
**Chosen:** Serve an HTTP 200 HTML page on `magnet.php` featuring a prominent "Copy Magnet URL to Clipboard" button and JS client auto-launch (rather than an HTTP 302 header), and include the full raw magnet URI in a `<code style="word-break: break-all; user-select: all;">` block inside `<description>` using standard single `htmlspecialchars()` escaping.
**Alternatives:** Relying on HTTP 302 Location redirect or raw meta refresh.
**Why:** When a browser receives an HTTP 302 with a `Location: magnet:...` or `<meta http-equiv="refresh">` pointing to an external protocol, and the user's system does NOT have a desktop torrent client registered, the browser aborts navigation without rendering the response body, leaving the user with a blank white screen. Serving HTTP 200 ensures the landing page and clipboard button are always visible, while client-side JavaScript attempts to trigger desktop clients without disrupting the UI. Adding the plaintext URI with single HTML entity escaping inside CDATA ensures RSS readers display and copy the exact raw magnet link with single `&` delimiters and zero entity distortion.
**Trade-offs:** Desktop client launch happens via client-side JavaScript instead of HTTP Location redirect, but works reliably across all client environments (desktop, web-only, headless, mobile).
**Revisit if:** Browser external protocol handling behavior changes in future specifications.

