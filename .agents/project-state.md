# Project State: eztvXrss

## User Profile
- **Level**: Senior (Full control, show all options with detailed trade-offs)
- **UI Preferences**: Dark mode support via system-preference detection (`prefers-color-scheme: dark`)

## Environment
- **Operating System**: Linux (Fedora / x86_64)
- **Local PHP**: PHP 8.5.9 CLI, Composer 2.x
- **Remote Host**: `code-alongsi.de` (SSH user `hosting149384`)
- **Remote PHP**: PHP 8.4.24 CLI + OPcache / ionCube, Apache / Nginx (Plesk)
- **Remote Target Path**: `httpdocs/eztvxrss`
- **Public URL**: `https://code-alongsi.de/eztvxrss/`
- **Live Feed A URL**: `https://code-alongsi.de/eztvxrss/feed.php?imdb=12327578&resolution=1080p&codec=x265`
- **Live Feed B URL**: `https://code-alongsi.de/eztvxrss/feed.php?imdb=12327578&resolution=720p&codec=x264`

## Demographics & Language
- **Primary Audience**: Users and RSS clients subscribing to TV show torrent releases
- **Language / Localization**: English (`en-US`), modular string catalog ready for i18n
- **Licensing**: MIT License

## Architecture & Tech Stack
- **Architecture**: Modular OOP PHP (native standard library) with PSR-4 autoloading and standalone fallback autoloader
  - `EztvApiClient`: Interfaces with `https://eztvx.to/api/get-torrents`, handles pagination and error responses
  - `ShowSearchClient`: Searches shows and looks up canonical titles by IMDb ID
  - `TorrentFilter`: Resolves resolution (2160p, 1080p, 720p, 480p), codecs (HEVC/x265, H.264/x264, XviD), source types, seasons/episodes
  - `FeedTitleFormatter`: Formats feed channel titles and OPML outlines as `<Show Name> [Resolution | Format]` with release title fallback
  - `RssFeedBuilder`: Generates RSS 2.0 XML with BitTorrent magnet enclosures, RFC 2822 dates from `date_released_unix`, TTL/Syndication hints
  - `OpmlBuilder`: Generates OPML 2.0 XML for one-click import into RSS readers
  - `WebFrontend`: Semantic HTML5 UI, responsive CSS with system dark mode, drop-downs for filters, instant feed URL generator, real-time IMDb show lookup & live preview table
- **Dependencies**: PHP 8.4+ standard library (`ext-curl`, `ext-json`, `ext-xmlwriter`, `ext-dom`), `phpunit/phpunit` (dev-only)
- **Caching**: Explicitly disabled per requirements ("do not implement caching", fresh API query on feed fetch)

## Decisions Log Reference
- See `DECISIONS.md` for complete trade-off analysis and rationale.

## Sprint History
- **Sprint 1 (2026-09-04)**:
  - **Goal**: Implement and verify all Must user stories (US-01 through US-07): API ingestion, filtering, RSS 2.0 generator, UI with search and OPML export, and remote deployment to code-alongsi.de.
  - **Delivered**: US-01, US-02, US-03, US-04, US-05, US-06, US-07 (19 story points).
  - **Velocity**: 19 story points.
  - **Test Suite**: 22 unit & integration tests, 378 assertions, 100% green.
  - **Deployment**: Live on `code-alongsi.de:httpdocs/eztvxrss`.
- **Sprint 2 (2026-09-04)**:
  - **Goal**: Show-Name Feed Title & Direct IMDb Lookup (US-08).
  - **Delivered**: US-08 (2 story points).
  - **Velocity**: 2 story points.
  - **Test Suite**: 31 unit & integration tests, 394 assertions, 100% green.
  - **Deployment**: Live on `code-alongsi.de:httpdocs/eztvxrss`. Tested and verified via curl and Puppeteer.
- **Sprint 3 (2026-09-04)**:
  - **Goal**: Fix RSS item links to point to canonical EZTV episode URLs and ensure magnet links are raw and unescaped (US-09).
  - **Delivered**: US-09 (2 story points).
  - **Velocity**: 2 story points.
  - **Test Suite**: 34 unit & integration tests, 405 assertions, 100% green.
  - **Deployment**: Live on `code-alongsi.de:httpdocs/eztvxrss`. Verified via curl and Puppeteer.

## Skipped Tests Log
- None.
