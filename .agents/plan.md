# Product Backlog & Plan: eztvXrss

## Product Backlog

### Epic 1: Core RSS Feed Generation Engine

#### US-01: EZTV API Client & Ingestion (Done)
- **Status**: Completed

#### US-02: Multi-Factor Torrent Filtering Engine (Done)
- **Status**: Completed

#### US-03: Standards-Compliant RSS 2.0 Feed Builder (Done)
- **Status**: Completed

#### US-08: Dynamic Show-Name Feed Title with Filter Tags & IMDb Lookup (Done)
- **Status**: Completed

#### US-09: Fix RSS Item Links to EZTV Site & Clean Magnet URIs (Done)
- **Status**: Completed
- **Story**: As an RSS reader subscriber, I want clicking on an RSS item to navigate to the show's page on EZTV (instead of appending the magnet URI to the site URL), and I want valid unescaped magnet links in the feed enclosures, `torrent:magnetURI`, and description HTML, so that my BitTorrent client and browser open them cleanly.
- **Priority**: Must
- **Estimate**: 2 story points
- **Acceptance Criteria**:
  1. `<item><link>` points to `https://eztvx.to/ep/{id}/{slug}/` on the eztvx site.
  2. `<item><guid isPermaLink="true">` points to `https://eztvx.to/ep/{id}/{slug}/`.
  3. Root `<rss>` element declares `xmlns:torrent="http://xmlns.ezrss.it/0.1/"`.
  4. Each item includes `<torrent:magnetURI><![CDATA[...]]></torrent:magnetURI>` with clean raw magnet URI.
  5. Inside `<description>` CDATA, the magnet link href uses raw `&` parameter delimiters (not double-escaped `&amp;`), plus a direct link to the EZTV episode page.
  6. Unit and integration tests verify all link elements and magnet URI format.

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
