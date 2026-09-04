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

#### US-10: Plaintext Magnet URI in Feed Descriptions & Clipboard Landing Page (In Progress)
- **Status**: In Progress
- **Story**: As a user without a desktop BitTorrent client, I want the magnet link displayed as plain text in the feed item description and a landing page with a "Copy Magnet URL to Clipboard" button, so that I never get a blank screen and can easily copy and paste the magnet URI into web-based torrent clients.
- **Priority**: Must
- **Estimate**: 2 story points
- **Acceptance Criteria**:
  1. Feed `<description>` includes the full magnet URI in `<p><strong>Magnet URI:</strong><br/><code style="word-break: break-all; user-select: all;">...</code></p>`.
  2. The magnet URI in HTML description is correctly escaped with `htmlspecialchars()` so rendered text and clipboard copy produces exact raw URI without `&amp;` or entity corruption.
  3. `magnet.php` serves an HTTP 200 landing page (not HTTP 302) with a prominent "Copy Magnet URL to Clipboard" button.
  4. `magnet.php` attempts direct client launch via JavaScript on load while keeping the page and button fully functional for systems without desktop clients.
  5. Security: Strict regex validation on magnet URI and secure HTTP headers.


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
