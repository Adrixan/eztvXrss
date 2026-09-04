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
