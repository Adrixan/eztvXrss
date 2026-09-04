<?php

declare(strict_types=1);

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'code-alongsi.de';
$baseDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/eztvxrss'), '/\\');
$baseUrl = $scheme . '://' . $host . $baseDir;
$feedEndpoint = $baseUrl . '/feed.php';

$initialImdb = htmlspecialchars((string) ($_GET['imdb'] ?? $_GET['imdb_id'] ?? ''), ENT_QUOTES, 'UTF-8');
$initialResolution = htmlspecialchars((string) ($_GET['resolution'] ?? 'any'), ENT_QUOTES, 'UTF-8');
$initialCodec = htmlspecialchars((string) ($_GET['codec'] ?? 'any'), ENT_QUOTES, 'UTF-8');
$initialSource = htmlspecialchars((string) ($_GET['source'] ?? 'any'), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EZTV Show RSS Feed Generator</title>
  <meta name="description" content="Generate custom filtered RSS feeds for TV shows from EZTV by IMDb ID with resolution and codec filters.">
  <link rel="stylesheet" href="css/app.css">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%230284c7'><path d='M4 4.5A2.5 2.5 0 0 1 6.5 2H18a2.5 2.5 0 0 1 2.5 2.5v14.25a.75.75 0 0 1-1.28.53L16 16H6.5A2.5 2.5 0 0 1 4 13.5V4.5z'/></svg>">
</head>
<body>
  <div class="container">
    <header>
      <h1 class="header-title">
        <svg aria-hidden="true" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary-color);">
          <path d="M4 11a9 9 0 0 1 9 9"></path>
          <path d="M4 4a16 16 0 0 1 16 16"></path>
          <circle cx="5" cy="19" r="1.5"></circle>
        </svg>
        eztvXrss Feed Generator
      </h1>
      <p class="header-subtitle">
        Create clean, custom-filtered RSS 2.0 feeds directly from the EZTV API by IMDb ID. Instant updates, zero caching.
      </p>
    </header>

    <main>
      <!-- Section: Show Search -->
      <section class="card" aria-labelledby="search-heading">
        <h2 id="search-heading" class="card-title">
          <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          Search TV Show (Optional)
        </h2>
        <div class="form-grid" style="grid-template-columns: 1fr auto; align-items: end;">
          <div class="form-group">
            <label for="show-search-input">Show Title</label>
            <input type="text" id="show-search-input" placeholder="e.g. Strange New Worlds, Fallout, The Bear" autocomplete="off">
          </div>
          <button type="button" id="btn-search-show" class="btn btn-secondary">
            Search
          </button>
        </div>
        <div id="search-results-container" class="search-results" aria-live="polite"></div>
      </section>

      <!-- Section: Feed Configuration Form -->
      <section class="card" aria-labelledby="config-heading">
        <h2 id="config-heading" class="card-title">
          <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
          </svg>
          Configure Feed &amp; Filters
        </h2>

        <form id="feed-generator-form" onsubmit="return false;">
          <div class="form-group" style="margin-bottom: 1rem;">
            <label for="imdb-input">
              IMDb ID <span style="color: var(--primary-color);">*</span>
            </label>
            <input 
              type="text" 
              id="imdb-input" 
              name="imdb" 
              value="<?= $initialImdb ?>" 
              placeholder="e.g. 12327578 or tt12327578" 
              required
              pattern="^(?:tt)?\d+$"
              title="Digits or tt followed by digits"
            >

            <!-- Presets -->
            <div class="presets-container" aria-label="Quick Presets">
              <span class="presets-label">Presets:</span>
              <button type="button" class="btn btn-secondary btn-preset" id="preset-1080p-hevc">
                1080p + HEVC x265
              </button>
              <button type="button" class="btn btn-secondary btn-preset" id="preset-720p-x264">
                720p + x264
              </button>
            </div>
            <div id="imdb-lookup-status" style="margin-top: 0.5rem; min-height: 24px;" aria-live="polite"></div>
          </div>

          <div class="form-grid">
            <!-- Resolution Drop-Down -->
            <div class="form-group">
              <label for="resolution-select">Resolution</label>
              <select id="resolution-select" name="resolution">
                <option value="any" <?= $initialResolution === 'any' ? 'selected' : '' ?>>Any Resolution</option>
                <option value="1080p" <?= $initialResolution === '1080p' ? 'selected' : '' ?>>1080p (Full HD)</option>
                <option value="720p" <?= $initialResolution === '720p' ? 'selected' : '' ?>>720p (HD)</option>
                <option value="2160p" <?= $initialResolution === '2160p' ? 'selected' : '' ?>>2160p (4K UHD)</option>
                <option value="480p" <?= $initialResolution === '480p' ? 'selected' : '' ?>>480p (SD)</option>
              </select>
            </div>

            <!-- Codec / Encoding Drop-Down -->
            <div class="form-group">
              <label for="codec-select">Encoding / Codec</label>
              <select id="codec-select" name="codec">
                <option value="any" <?= $initialCodec === 'any' ? 'selected' : '' ?>>Any Codec</option>
                <option value="x265" <?= $initialCodec === 'x265' ? 'selected' : '' ?>>HEVC / x265</option>
                <option value="x264" <?= $initialCodec === 'x264' ? 'selected' : '' ?>>H.264 / x264</option>
                <option value="xvid" <?= $initialCodec === 'xvid' ? 'selected' : '' ?>>XviD</option>
              </select>
            </div>

            <!-- Source Quality Drop-Down -->
            <div class="form-group">
              <label for="source-select">Source Quality</label>
              <select id="source-select" name="source">
                <option value="any" <?= $initialSource === 'any' ? 'selected' : '' ?>>Any Source</option>
                <option value="WEB-DL" <?= $initialSource === 'WEB-DL' ? 'selected' : '' ?>>WEB-DL</option>
                <option value="WEBRip" <?= $initialSource === 'WEBRip' ? 'selected' : '' ?>>WEBRip</option>
                <option value="HDTV" <?= $initialSource === 'HDTV' ? 'selected' : '' ?>>HDTV</option>
                <option value="BluRay" <?= $initialSource === 'BluRay' ? 'selected' : '' ?>>BluRay</option>
              </select>
            </div>

            <!-- Min Seeds -->
            <div class="form-group">
              <label for="min-seeds-input">Min Seeders</label>
              <input type="number" id="min-seeds-input" name="min_seeds" min="0" placeholder="0" value="0">
            </div>

            <!-- Season (Optional) -->
            <div class="form-group">
              <label for="season-input">Season (Optional)</label>
              <input type="number" id="season-input" name="season" min="1" placeholder="All seasons">
            </div>

            <!-- Episode (Optional) -->
            <div class="form-group">
              <label for="episode-input">Episode (Optional)</label>
              <input type="number" id="episode-input" name="episode" min="1" placeholder="All episodes">
            </div>
          </div>
        </form>
      </section>

      <!-- Section: Generated Feed URL & Actions -->
      <section class="card" aria-labelledby="url-heading">
        <h2 id="url-heading" class="card-title">
          <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
          </svg>
          Generated RSS 2.0 Feed URL
        </h2>
        <div id="feed-name-display" style="font-weight: 600; margin-bottom: 0.6rem; font-size: 1.05rem;">
          Feed Name: <span id="feed-name-text" style="color: var(--primary-color);">Enter an IMDb ID</span>
        </div>
        <div id="feed-url-display" class="feed-url-box" tabindex="0" role="textbox" aria-label="Generated RSS Feed URL">
          Enter an IMDb ID above to generate the feed URL.
        </div>

        <div class="actions-bar">
          <button type="button" id="btn-copy-url" class="btn btn-primary">
            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
              <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
            </svg>
            Copy Feed URL
          </button>

          <a id="btn-open-feed" href="#" target="_blank" rel="noopener noreferrer" class="btn btn-secondary">
            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
              <polyline points="15 3 21 3 21 9"></polyline>
              <line x1="10" y1="14" x2="21" y2="3"></line>
            </svg>
            Open Feed in Browser
          </a>

          <button type="button" id="btn-preview-feed" class="btn btn-secondary">
            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
              <circle cx="12" cy="12" r="3"></circle>
            </svg>
            Preview Releases
          </button>

          <a id="btn-export-opml" href="#" class="btn btn-secondary" download="eztv-feed.opml">
            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
              <polyline points="7 10 12 15 17 10"></polyline>
              <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            Export OPML
          </a>
        </div>
      </section>

      <!-- Section: Live Preview Results -->
      <section class="card" id="preview-section" aria-labelledby="preview-heading" style="display: none;">
        <h2 id="preview-heading" class="card-title">
          <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="8" y1="6" x2="21" y2="6"></line>
            <line x1="8" y1="12" x2="21" y2="12"></line>
            <line x1="8" y1="18" x2="21" y2="18"></line>
            <line x1="3" y1="6" x2="3.01" y2="6"></line>
            <line x1="3" y1="12" x2="3.01" y2="12"></line>
            <line x1="3" y1="18" x2="3.01" y2="18"></line>
          </svg>
          Releases Preview (<span id="preview-matched-count">0</span> matches)
        </h2>
        <div id="preview-status" aria-live="polite" style="margin-bottom: 0.5rem; color: var(--text-muted);"></div>
        <div class="table-responsive">
          <table>
            <thead>
              <tr>
                <th scope="col">Release Title</th>
                <th scope="col">Size</th>
                <th scope="col">Seeds / Peers</th>
                <th scope="col">Release Date (UTC)</th>
                <th scope="col">Action</th>
              </tr>
            </thead>
            <tbody id="preview-table-body">
            </tbody>
          </table>
        </div>
      </section>
    </main>

    <footer style="margin-top: 3rem; text-align: center; color: var(--text-muted); font-size: 0.85rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
      <p>eztvXrss Feed Generator · Data sourced directly from EZTV API (<code>https://eztvx.to/api/</code>)</p>
      <p style="margin-top: 0.25rem;">Live unbuffered reload · RFC 2822 dates · Hourly reader refresh · WCAG 2.1 AA Compliant</p>
    </footer>
  </div>

  <div id="toast" class="toast" role="status" aria-live="polite"></div>

  <script>
    (function () {
      const feedBase = <?= json_encode($feedEndpoint) ?>;
      const opmlBase = <?= json_encode($baseUrl . '/opml.php') ?>;
      const apiBase = <?= json_encode($baseUrl . '/api.php') ?>;

      const imdbInput = document.getElementById('imdb-input');
      const resolutionSelect = document.getElementById('resolution-select');
      const codecSelect = document.getElementById('codec-select');
      const sourceSelect = document.getElementById('source-select');
      const minSeedsInput = document.getElementById('min-seeds-input');
      const seasonInput = document.getElementById('season-input');
      const episodeInput = document.getElementById('episode-input');

      const imdbLookupStatus = document.getElementById('imdb-lookup-status');
      const feedNameText = document.getElementById('feed-name-text');
      const feedUrlDisplay = document.getElementById('feed-url-display');
      const btnCopyUrl = document.getElementById('btn-copy-url');
      const btnOpenFeed = document.getElementById('btn-open-feed');
      const btnPreviewFeed = document.getElementById('btn-preview-feed');
      const btnExportOpml = document.getElementById('btn-export-opml');
      const previewSection = document.getElementById('preview-section');
      const previewTableBody = document.getElementById('preview-table-body');
      const previewMatchedCount = document.getElementById('preview-matched-count');
      const previewStatus = document.getElementById('preview-status');
      const toast = document.getElementById('toast');

      const showSearchInput = document.getElementById('show-search-input');
      const btnSearchShow = document.getElementById('btn-search-show');
      const searchResultsContainer = document.getElementById('search-results-container');

      let currentShowName = '';
      let lastLookedUpImdb = '';
      let lookupTimeout = null;

      function showToast(message) {
        toast.textContent = message;
        toast.style.display = 'block';
        setTimeout(() => {
          toast.style.display = 'none';
        }, 3000);
      }

      function cleanImdb(val) {
        val = (val || '').trim();
        const m = val.match(/^(?:tt)?(\d+)$/i);
        return m ? m[1] : '';
      }

      function formatFeedName(showName) {
        if (!showName) return '';
        const tags = [];
        if (resolutionSelect.value !== 'any') tags.push(resolutionSelect.value);
        if (codecSelect.value !== 'any') {
          if (codecSelect.value === 'x265') tags.push('HEVC x265');
          else if (codecSelect.value === 'x264') tags.push('x264');
          else tags.push(codecSelect.value);
        }
        if (sourceSelect.value !== 'any') tags.push(sourceSelect.value);
        if (seasonInput.value && parseInt(seasonInput.value, 10) > 0) {
          tags.push('S' + String(seasonInput.value).padStart(2, '0'));
        }
        if (episodeInput.value && parseInt(episodeInput.value, 10) > 0) {
          tags.push('E' + String(episodeInput.value).padStart(2, '0'));
        }
        return tags.length > 0 ? `${showName} [${tags.join(' | ')}]` : showName;
      }

      function generateUrl() {
        const rawImdb = imdbInput.value;
        const imdb = cleanImdb(rawImdb);

        if (!imdb) {
          feedNameText.textContent = 'Enter an IMDb ID';
          feedUrlDisplay.textContent = 'Enter an IMDb ID above to generate the feed URL.';
          btnOpenFeed.removeAttribute('href');
          btnExportOpml.removeAttribute('href');
          return '';
        }

        const params = new URLSearchParams();
        params.set('imdb', imdb);

        if (currentShowName) {
          feedNameText.textContent = formatFeedName(currentShowName);
          params.set('title', currentShowName);
        } else {
          feedNameText.textContent = formatFeedName('Show') + ' (resolving name...)';
        }

        if (resolutionSelect.value !== 'any') params.set('resolution', resolutionSelect.value);
        if (codecSelect.value !== 'any') params.set('codec', codecSelect.value);
        if (sourceSelect.value !== 'any') params.set('source', sourceSelect.value);

        const minSeeds = parseInt(minSeedsInput.value, 10);
        if (minSeeds > 0) params.set('min_seeds', minSeeds);

        const season = parseInt(seasonInput.value, 10);
        if (season > 0) params.set('season', season);

        const episode = parseInt(episodeInput.value, 10);
        if (episode > 0) params.set('episode', episode);

        const finalUrl = feedBase + '?' + params.toString();
        feedUrlDisplay.textContent = finalUrl;
        btnOpenFeed.href = finalUrl;
        btnExportOpml.href = opmlBase + '?' + params.toString();

        return finalUrl;
      }

      function triggerLookup() {
        const rawImdb = imdbInput.value;
        const imdb = cleanImdb(rawImdb);
        if (!imdb) {
          currentShowName = '';
          lastLookedUpImdb = '';
          imdbLookupStatus.innerHTML = '';
          generateUrl();
          return;
        }

        if (imdb === lastLookedUpImdb && currentShowName) {
          return;
        }

        imdbLookupStatus.innerHTML = '<span style="color: var(--text-muted); font-size: 0.85rem;">Looking up show name...</span>';

        fetch(apiBase + '?action=lookup&imdb=' + encodeURIComponent(imdb))
          .then(res => res.json())
          .then(data => {
            if (data.name) {
              currentShowName = data.name;
              lastLookedUpImdb = imdb;
              imdbLookupStatus.innerHTML = '<span class="badge badge-success">✓ ' + data.name + '</span>';
            } else {
              currentShowName = '';
              lastLookedUpImdb = imdb;
              imdbLookupStatus.innerHTML = '<span style="color: var(--text-muted); font-size: 0.85rem;">Show name will be parsed from release title</span>';
            }
            generateUrl();
          })
          .catch(() => {
            imdbLookupStatus.innerHTML = '';
            generateUrl();
          });
      }

      // Presets
      document.getElementById('preset-1080p-hevc').addEventListener('click', () => {
        resolutionSelect.value = '1080p';
        codecSelect.value = 'x265';
        generateUrl();
        if (cleanImdb(imdbInput.value)) loadPreview();
      });

      document.getElementById('preset-720p-x264').addEventListener('click', () => {
        resolutionSelect.value = '720p';
        codecSelect.value = 'x264';
        generateUrl();
        if (cleanImdb(imdbInput.value)) loadPreview();
      });

      // Inputs change listener
      [resolutionSelect, codecSelect, sourceSelect, minSeedsInput, seasonInput, episodeInput].forEach(el => {
        el.addEventListener('input', generateUrl);
        el.addEventListener('change', generateUrl);
      });

      imdbInput.addEventListener('input', () => {
        generateUrl();
        clearTimeout(lookupTimeout);
        lookupTimeout = setTimeout(triggerLookup, 500);
      });

      imdbInput.addEventListener('blur', triggerLookup);

      // Copy to Clipboard
      btnCopyUrl.addEventListener('click', () => {
        const url = generateUrl();
        if (!url) {
          showToast('Please provide a valid IMDb ID first.');
          return;
        }
        navigator.clipboard.writeText(url).then(() => {
          showToast('Feed URL copied to clipboard!');
        }).catch(() => {
          showToast('Failed to copy. Please select the URL manually.');
        });
      });

      // Show Search via TVMaze
      function runSearch() {
        const query = showSearchInput.value.trim();
        if (!query) return;

        searchResultsContainer.innerHTML = '<p style="color: var(--text-muted); font-size: 0.9rem;">Searching shows...</p>';

        fetch(apiBase + '?action=search&q=' + encodeURIComponent(query))
          .then(res => res.json())
          .then(data => {
            searchResultsContainer.innerHTML = '';
            if (!data.results || data.results.length === 0) {
              searchResultsContainer.innerHTML = '<p style="color: var(--text-muted); font-size: 0.9rem;">No shows found with IMDb IDs.</p>';
              return;
            }

            data.results.forEach(show => {
              const card = document.createElement('div');
              card.className = 'search-card';
              card.tabIndex = 0;
              card.setAttribute('role', 'button');
              card.setAttribute('aria-label', `Select ${show.name}`);

              const imgHtml = show.image 
                ? `<img src="${encodeURI(show.image)}" alt="${show.name}" loading="lazy">` 
                : `<div style="width: 50px; height: 70px; background: var(--border-color); border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 10px;">No Img</div>`;

              card.innerHTML = `
                ${imgHtml}
                <div class="search-card-info">
                  <h4>${show.name} ${show.year ? '(' + show.year + ')' : ''}</h4>
                  <p><span class="badge badge-success">IMDb: ${show.imdb_code}</span></p>
                  <p style="margin-top: 4px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">${show.summary || ''}</p>
                </div>
              `;

              const selectShow = () => {
                imdbInput.value = show.imdb_id;
                currentShowName = show.name;
                lastLookedUpImdb = show.imdb_id;
                imdbLookupStatus.innerHTML = '<span class="badge badge-success">✓ ' + show.name + '</span>';
                generateUrl();
                loadPreview();
                showToast(`Selected "${show.name}" (IMDb: ${show.imdb_id})`);
                card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
              };

              card.addEventListener('click', selectShow);
              card.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                  e.preventDefault();
                  selectShow();
                }
              });

              searchResultsContainer.appendChild(card);
            });
          })
          .catch(err => {
            searchResultsContainer.innerHTML = '<p style="color: #dc2626;">Search failed. Please try again.</p>';
          });
      }

      btnSearchShow.addEventListener('click', runSearch);
      showSearchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
          runSearch();
        }
      });

      // Live Preview Table
      function loadPreview() {
        const rawImdb = imdbInput.value;
        const imdb = cleanImdb(rawImdb);
        if (!imdb) {
          showToast('Please enter an IMDb ID to preview.');
          return;
        }

        previewSection.style.display = 'block';
        previewStatus.textContent = 'Fetching fresh torrents from EZTV API...';
        previewTableBody.innerHTML = '';

        const params = new URLSearchParams();
        params.set('action', 'preview');
        params.set('imdb', imdb);
        if (resolutionSelect.value !== 'any') params.set('resolution', resolutionSelect.value);
        if (codecSelect.value !== 'any') params.set('codec', codecSelect.value);
        if (sourceSelect.value !== 'any') params.set('source', sourceSelect.value);

        const minSeeds = parseInt(minSeedsInput.value, 10);
        if (minSeeds > 0) params.set('min_seeds', minSeeds);

        const season = parseInt(seasonInput.value, 10);
        if (season > 0) params.set('season', season);

        const episode = parseInt(episodeInput.value, 10);
        if (episode > 0) params.set('episode', episode);

        fetch(apiBase + '?' + params.toString())
          .then(res => res.json())
          .then(data => {
            if (data.error) {
              previewStatus.textContent = 'Error: ' + data.error;
              return;
            }

            previewMatchedCount.textContent = data.total_matched;
            previewStatus.textContent = `Found ${data.total_matched} matching torrents (from ${data.total_fetched} total fetched). Showing top ${data.items.length}:`;

            if (data.items.length === 0) {
              previewTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; color: var(--text-muted);">No torrents matched the selected filters.</td></tr>';
              return;
            }

            data.items.forEach(item => {
              const tr = document.createElement('tr');
              tr.innerHTML = `
                <td style="word-break: break-word; font-weight: 500;">
                  <a href="${item.eztv_url || '#'}" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: underline;" title="View episode on EZTV">
                    ${item.title}
                  </a>
                </td>
                <td style="white-space: nowrap;"><span class="badge">${item.size}</span></td>
                <td style="white-space: nowrap;">
                  <span style="color: var(--success-color); font-weight: 600;">▲ ${item.seeds}</span> / 
                  <span style="color: var(--text-muted);">▼ ${item.peers}</span>
                </td>
                <td style="white-space: nowrap; font-size: 0.85rem; color: var(--text-muted);">${item.released}</td>
                <td style="white-space: nowrap;">
                  <a href="${item.magnet}" class="btn btn-secondary btn-preset" title="Open Magnet Link" style="margin-right: 4px;">
                    Magnet
                  </a>
                  ${item.eztv_url ? `<a href="${item.eztv_url}" target="_blank" rel="noopener noreferrer" class="btn btn-secondary btn-preset" title="View on EZTV">EZTV ↗</a>` : ''}
                </td>
              `;
              previewTableBody.appendChild(tr);
            });
          })
          .catch(err => {
            previewStatus.textContent = 'Failed to load preview: ' + err.message;
          });
      }

      btnPreviewFeed.addEventListener('click', loadPreview);

      // Initialize on load
      generateUrl();
    })();
  </script>
</body>
</html>
