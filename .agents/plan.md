# Product Backlog & Plan: eztvXrss

## Product Backlog

### Epic 1: Core RSS Feed Generation Engine

#### US-01: EZTV API Client & Ingestion (Done)
- **Story**: As an RSS reader service, I want to fetch torrent listings for a given IMDb ID from the EZTV API (`https://eztvx.to/api/get-torrents`), so that current release data is available for processing.
- **Status**: Completed

#### US-02: Multi-Factor Torrent Filtering Engine (Done)
- **Story**: As a subscriber, I want to filter torrents by resolution, codec/encoding, and release attributes, so that my feed only contains media compatible with my playback devices.
- **Status**: Completed

#### US-03: Standards-Compliant RSS 2.0 Feed Builder (Done)
- **Story**: As an RSS reader (e.g., qBittorrent, Synology Download Station, Feedly), I want to consume an RSS 2.0 XML feed with magnet enclosures and accurate release dates, so that my client can automatically download releases.
- **Status**: Completed

#### US-08: Dynamic Show-Name Feed Title with Filter Tags & IMDb Lookup (In Progress)
- **Story**: As an RSS subscriber, I want the feed title and OPML entries to use the show's actual name (with resolution and format in square brackets), looking up the show name automatically if an IMDb ID is entered directly, so that feeds are clearly recognizable in my RSS reader.
- **Priority**: Must
- **Estimate**: 2 story points
- **Acceptance Criteria**:
  1. Given an IMDb ID (e.g. `12327578`) and filters (e.g. `1080p` and `x265`), the feed channel title is generated as `Star Trek: Strange New Worlds [1080p | HEVC x265]` (or corresponding filters).
  2. Given an IMDb ID with no filters, the feed channel title is `Star Trek: Strange New Worlds`.
  3. Given an IMDb ID entered directly, `ShowSearchClient::lookupByImdbId` queries TVMaze show lookup (`/lookup/shows?imdb=tt...`) to resolve the canonical show name.
  4. If TV lookup fails or times out, fall back to parsing the show title from the fetched torrent items, and if none exist, fall back to `IMDb tt<id>`.
  5. The web UI resolves the show name when an IMDb ID is entered and uses the show name in OPML export.
  6. Unit and integration tests verify show lookup, title formatting with square brackets, and fallbacks.

### Epic 2: Web Interface & User Experience

#### US-04: Web Frontend Feed Generator & Interactive Preview (Done)
- **Status**: Completed

#### US-06: OPML Export for Batch Subscriptions (Done)
- **Status**: Completed

#### US-07: Show Name Search & IMDb ID Auto-Resolver (Done)
- **Status**: Completed

### Epic 3: Deployment & Production Verification

#### US-05: Remote Deployment & Live Verification on code-alongsi.de (Done)
- **Status**: Completed
