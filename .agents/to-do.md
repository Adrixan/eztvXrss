# Sprint Backlog & To-Do: eztvXrss

## Current Sprint: Sprint 1 — Full Feed Generator, Search, OPML & Production Deployment
- **Sprint Goal**: Implement and verify all Must user stories (US-01 through US-07): API ingestion, filtering, RSS 2.0 generator, UI with search and OPML export, and remote deployment to code-alongsi.de.
- **Velocity Target**: 19 story points
- **Status**: Completed (19/19 story points delivered)

## Sprint Stories

### [x] US-01: EZTV API Client & Ingestion (3 pts)
- [x] Acceptance tests written (`tests/Unit/EztvApiClientTest.php`)
- [x] Implementation complete (all tests green)
- [x] Regression suite green
- [x] User review: approved (batch mode pre-approved)

### [x] US-02: Multi-Factor Torrent Filtering Engine (3 pts)
- [x] Acceptance tests written (`tests/Unit/TorrentFilterTest.php`)
- [x] Implementation complete (all tests green)
- [x] Regression suite green
- [x] User review: approved (batch mode pre-approved)

### [x] US-03: Standards-Compliant RSS 2.0 Feed Builder (3 pts)
- [x] Acceptance tests written (`tests/Unit/RssFeedBuilderTest.php`)
- [x] Implementation complete (all tests green)
- [x] Regression suite green
- [x] User review: approved (batch mode pre-approved)

### [x] US-04: Web Frontend Feed Generator & Interactive Preview (3 pts)
- [x] Acceptance tests written (`tests/Unit/EndpointIntegrationTest.php`, Puppeteer verification)
- [x] Implementation complete (all tests green)
- [x] Regression suite green
- [x] User review: approved (batch mode pre-approved)

### [x] US-07: Show Name Search & IMDb ID Auto-Resolver (3 pts)
- [x] Acceptance tests written (`tests/Unit/ShowSearchClientTest.php`)
- [x] Implementation complete (all tests green)
- [x] Regression suite green
- [x] User review: approved (batch mode pre-approved)

### [x] US-06: OPML Export for Batch Subscriptions (2 pts)
- [x] Acceptance tests written (`tests/Unit/OpmlBuilderTest.php`)
- [x] Implementation complete (all tests green)
- [x] Regression suite green
- [x] User review: approved (batch mode pre-approved)

### [x] US-05: Remote Deployment & Live Verification on code-alongsi.de (2 pts)
- [x] Deployed via SSH tar pipeline to `hosting149384@code-alongsi.de:httpdocs/eztvxrss`
- [x] Live web frontend verified at `https://code-alongsi.de/eztvxrss/`
- [x] Live Feed A (1080p + HEVC x265) verified for IMDb 12327578
- [x] Live Feed B (720p + x264) verified for IMDb 12327578
- [x] Live unbuffered headers (`Cache-Control: no-cache, no-store, must-revalidate`, `Expires: 0`, `<ttl>60</ttl>`) verified
- [x] User review: approved (batch mode pre-approved)
