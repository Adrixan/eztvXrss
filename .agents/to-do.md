# Sprint Backlog & To-Do: eztvXrss

## Current Sprint: Sprint 3 — Fix RSS Item Links to EZTV & Clean Magnet URIs
- **Sprint Goal**: Point RSS item `<link>` and `<guid>` to canonical EZTV episode URLs, add `torrent:magnetURI`, and ensure magnet links are raw and fully functional.
- **Velocity Target**: 2 story points

## Sprint Stories

### [x] US-09: Fix RSS Item Links to EZTV Site & Clean Magnet URIs (2 pts)
- [x] Acceptance tests written (`tests/Unit/RssFeedBuilderTest.php`, `tests/Unit/TorrentItemTest.php`)
- [x] Implementation complete (all tests green)
- [x] Regression suite green (34 tests, 405 assertions)
- [x] Production deployment & live verification on code-alongsi.de
- [x] User review: ready for acceptance
