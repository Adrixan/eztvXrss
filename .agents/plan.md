# Product Backlog & Plan: eztvXrss

## Product Backlog

### Epic 1: Core RSS Feed Generation Engine

#### US-01: EZTV API Client & Ingestion
- **Story**: As an RSS reader service, I want to fetch torrent listings for a given IMDb ID from the EZTV API (`https://eztvx.to/api/get-torrents`), so that current release data is available for processing.
- **Priority**: Must
- **Estimate**: 3 story points
- **Acceptance Criteria**:
  1. Given a numeric IMDb ID (e.g. `12327578`) or prefixed format (`tt12327578`), the client queries `https://eztvx.to/api/get-torrents?limit=100&imdb_id=12327578`.
  2. Given multiple pages of results for a show, client handles pagination up to the total torrent count or configurable page cap.
  3. Given an upstream API timeout or failure, safe error handling returns a clean error message without fatal crashes.
  4. *(Mandatory Security)* IMDb ID input is strictly validated (digits or `tt\d+` only); invalid inputs are rejected before sending requests.
  5. *(Mandatory Security)* Upstream responses are validated for valid JSON; no unhandled exceptions leak server paths.

#### US-02: Multi-Factor Torrent Filtering Engine
- **Story**: As a subscriber, I want to filter torrents by resolution, codec/encoding, and release attributes, so that my feed only contains media compatible with my playback devices.
- **Priority**: Must
- **Estimate**: 3 story points
- **Acceptance Criteria**:
  1. Given a resolution filter (`1080p`, `720p`, `2160p`, `480p`, or `any`), only torrents matching the specified resolution pattern in title/filename are retained.
  2. Given an encoding filter (`HEVC x265`, `H264 / x264`, `XviD`, or `any`), only torrents matching the codec pattern (`x265|hevc` or `x264|h264|h\.264|h 264` or `xvid`) are retained.
  3. Given IMDb `12327578` ("Star Trek Strange New Worlds"), filter with `1080p` and `HEVC x265` returns only 1080p HEVC releases.
  4. Given IMDb `12327578` ("Star Trek Strange New Worlds"), filter with `720p` and `x264` returns only 720p x264 releases.
  5. Support additional explored filters: source quality (`WEB-DL`, `WEBRip`, `HDTV`, `BluRay`), minimum seeders threshold, and season/episode matching.
  6. *(Mandatory Security)* Filter inputs are validated against an allowlist of valid options or sanitized regex to prevent ReDoS.

#### US-03: Standards-Compliant RSS 2.0 Feed Builder
- **Story**: As an RSS reader (e.g., qBittorrent, Synology Download Station, Feedly), I want to consume an RSS 2.0 XML feed with magnet enclosures and accurate release dates, so that my client can automatically download releases.
- **Priority**: Must
- **Estimate**: 3 story points
- **Acceptance Criteria**:
  1. Feed generates valid RSS 2.0 XML with `Content-Type: application/rss+xml; charset=utf-8`.
  2. Each item contains `<title>`, `<link>` (magnet URI), `<guid isPermaLink="false">` (torrent hash or ID), `<pubDate>` (RFC 2822 formatted date from `date_released_unix`).
  3. Each item includes `<enclosure url="..." length="..." type="application/x-bittorrent" />` containing the magnet or torrent URL and file size.
  4. Channel contains `<ttl>60</ttl>` and HTTP response headers include `Cache-Control: no-cache, no-store, must-revalidate` to prevent stale caches and trigger reload on reader access.
  5. No local caching is performed per specification ("do not implement caching").
  6. *(Mandatory Security)* All XML entities are escaped (`htmlspecialchars` with `ENT_XML1 | ENT_QUOTES`) to prevent XML injection and XSS.

### Epic 2: Web Interface & User Experience

#### US-04: Web Frontend Feed Generator & Interactive Preview
- **Story**: As a user, I want a web interface with convenient drop-downs and instant feed preview, so that I can easily configure and generate custom RSS feed URLs for any show.
- **Priority**: Must
- **Estimate**: 3 story points
- **Acceptance Criteria**:
  1. Accessible HTML5 form featuring IMDb ID input with one-click quick test button for "Star Trek: Strange New Worlds (12327578)".
  2. Convenient drop-downs for Resolution (`Any`, `1080p`, `720p`, `2160p / 4K`, `480p`) and Encoding (`Any`, `HEVC / x265`, `H.264 / x264`, `XviD`), plus Source Quality (`Any`, `WEB-DL`, `WEBRip`, `HDTV`, `BluRay`).
  3. Instant feed URL generator display with one-click "Copy URL" button and "Open Feed" link.
  4. Live preview table showing matching torrents, sizes, seeders, and publication timestamps.
  5. Dark mode support automatically applied based on system preference (`prefers-color-scheme: dark`).
  6. *(Mandatory Accessibility)* WCAG 2.1 AA compliant: Semantic landmarks, proper `<label for="...">` associations, visible focus indicators, touch targets >= 44px, and contrast ratio >= 4.5:1.
  7. *(Mandatory Security)* Strict output encoding for all rendered data preventing XSS.

#### US-06: OPML Export for Batch Subscriptions
- **Story**: As a power user, I want to export my configured feeds as an OPML file, so that I can import multiple show feeds into feed readers in one step.
- **Priority**: Must
- **Estimate**: 2 story points
- **Acceptance Criteria**:
  1. Endpoint and UI action to generate valid OPML 2.0 XML with feeds list.
  2. Includes show titles, feed URLs, and RSS type attributes.
  3. Content-Type set to `application/xml` or `text/x-opml` with download disposition.
  4. *(Mandatory Security)* OPML entity escaping for XML safety.

#### US-07: Show Name Search & IMDb ID Auto-Resolver
- **Story**: As a user, I want to search for shows by title in the web frontend, so that I don't have to look up the IMDb ID manually.
- **Priority**: Must
- **Estimate**: 3 story points
- **Acceptance Criteria**:
  1. Search input for show titles (e.g., "Strange New Worlds", "Breaking Bad", "The Bear").
  2. Query TVMaze open public TV API endpoint to find matching shows with IMDb IDs.
  3. Clicking or selecting a search result automatically populates the IMDb ID input with the show's IMDb ID.
  4. *(Mandatory Security)* Search queries sanitized against injection; upstream responses validated before parsing.

### Epic 3: Deployment & Production Verification

#### US-05: Remote Deployment & Live Verification on code-alongsi.de
- **Story**: As an operator, I want the RSS generator deployed to `code-alongsi.de/eztvxrss` via SSH, so that feeds can be subscribed to over the public internet.
- **Priority**: Must
- **Estimate**: 2 story points
- **Acceptance Criteria**:
  1. Project files deployed via SSH to `hosting149384@code-alongsi.de:httpdocs/eztvxrss`.
  2. Web interface is accessible at `https://code-alongsi.de/eztvxrss/`.
  3. Live verification of Feed A: `https://code-alongsi.de/eztvxrss/feed.php?imdb=12327578&resolution=1080p&codec=x265` returns valid RSS with 1080p HEVC releases.
  4. Live verification of Feed B: `https://code-alongsi.de/eztvxrss/feed.php?imdb=12327578&resolution=720p&codec=x264` returns valid RSS with 720p x264 releases.
  5. Live verification of Search and OPML endpoints on production.
  6. Verification that each feed request executes fresh live queries without caching, respecting hourly reader poll cadence.
