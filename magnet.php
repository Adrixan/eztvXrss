<?php

declare(strict_types=1);

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: no-referrer');

$rawMagnet = (string) ($_GET['url'] ?? $_GET['magnet'] ?? '');

// If not provided directly, try hash and title
if ($rawMagnet === '' && isset($_GET['hash']) && preg_match('/^[a-fA-F0-9]{32,40}$/', (string) $_GET['hash'])) {
    $title = (string) ($_GET['title'] ?? 'Torrent');
    $rawMagnet = sprintf(
        'magnet:?xt=urn:btih:%s&dn=%s',
        $_GET['hash'],
        rawurlencode($title)
    );
}

// Strictly validate that the target is a magnet URI with a BitTorrent info hash
// This prevents open redirect attacks to external web pages or javascript: payloads
if (!preg_match('/^magnet:\?xt=urn:btih:[a-zA-Z0-9]{32,40}/i', $rawMagnet)) {
    http_response_code(400);
    header('Content-Type: text/html; charset=utf-8');
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invalid Magnet Link — eztvXrss</title>
</head>
<body style="font-family: sans-serif; padding: 2rem; max-width: 600px; margin: 0 auto; line-height: 1.6;">
    <h2>Invalid Magnet URI</h2>
    <p>The supplied magnet link parameter is invalid or missing a valid BitTorrent info hash.</p>
    <p><a href="./">Return to eztvXrss Generator</a></p>
</body>
</html>
    <?php
    exit;
}

// Optional 302 redirect if explicitly requested via query parameter
if (isset($_GET['redirect']) && $_GET['redirect'] === '1') {
    header('Location: ' . $rawMagnet, true, 302);
    exit;
}

// Serve HTTP 200 HTML page so browsers without desktop torrent clients are never shown a blank page
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Magnet Link — eztvXrss</title>
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: #1e293b;
            --border-color: #334155;
            --text-color: #f8fafc;
            --text-muted: #94a3b8;
            --primary: #0284c7;
            --primary-hover: #0369a1;
            --success: #16a34a;
            --success-hover: #15803d;
            --secondary-bg: #334155;
            --secondary-hover: #475569;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg-color);
            color: var(--text-color);
            padding: 2rem 1rem;
            margin: 0;
            line-height: 1.6;
        }
        .container {
            max-width: 680px;
            margin: 0 auto;
        }
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4);
        }
        h1 {
            font-size: 1.5rem;
            margin-top: 0;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        p {
            margin: 0.75rem 0;
            color: #cbd5e1;
        }
        .btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin: 1.5rem 0;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.15s ease, transform 0.05s ease;
        }
        .btn:active {
            transform: translateY(1px);
        }
        .btn-copy {
            background: var(--primary);
            color: #ffffff;
        }
        .btn-copy:hover {
            background: var(--primary-hover);
        }
        .btn-copy.copied {
            background: var(--success);
        }
        .btn-secondary {
            background: var(--secondary-bg);
            color: #ffffff;
        }
        .btn-secondary:hover {
            background: var(--secondary-hover);
        }
        label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-top: 1.5rem;
            margin-bottom: 0.4rem;
        }
        textarea {
            width: 100%;
            box-sizing: border-box;
            background: #090d16;
            color: #38bdf8;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0.85rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.85rem;
            line-height: 1.5;
            word-break: break-all;
            resize: vertical;
        }
        textarea:focus {
            outline: 2px solid var(--primary);
        }
        .nav-link {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.9rem;
        }
        .nav-link:hover {
            text-decoration: underline;
        }
        .toast-msg {
            display: none;
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: #4ade80;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary);">
                    <path d="M4 11a9 9 0 0 1 9 9"></path>
                    <path d="M4 4a16 16 0 0 1 16 16"></path>
                    <circle cx="5" cy="19" r="1.5"></circle>
                </svg>
                Magnet Link
            </h1>

            <p>Attempting to launch your desktop BitTorrent client directly. If you do not have a desktop client installed, click the button below to copy the magnet URL to your clipboard for pasting into your torrent application or web interface.</p>

            <div class="btn-group">
                <button type="button" id="copy-btn" class="btn btn-copy" onclick="copyMagnet()">
                    📋 Copy Magnet URL to Clipboard
                </button>
                <a href="<?= htmlspecialchars($rawMagnet, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="btn btn-secondary">
                    Open Magnet in Client
                </a>
            </div>

            <div id="toast-msg" class="toast-msg" aria-live="polite">✓ Copied to clipboard!</div>

            <label for="magnet-textarea">Manual Copy (Raw Magnet URI):</label>
            <textarea id="magnet-textarea" rows="4" readonly onclick="this.select()" aria-label="Raw Magnet Link"><?= htmlspecialchars($rawMagnet, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>

            <p style="margin-top: 1.5rem; font-size: 0.85rem; color: var(--text-muted);">
                <a href="./" class="nav-link">← Back to eztvXrss Generator</a>
            </p>
        </div>
    </div>

    <script>
        const magnetUrl = <?= json_encode($rawMagnet) ?>;

        function copyMagnet() {
            const btn = document.getElementById('copy-btn');
            const toast = document.getElementById('toast-msg');
            const textarea = document.getElementById('magnet-textarea');
            const originalHtml = btn.innerHTML;

            function onCopied() {
                btn.innerHTML = '✓ Copied to Clipboard!';
                btn.classList.add('copied');
                toast.style.display = 'block';
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.classList.remove('copied');
                    toast.style.display = 'none';
                }, 2500);
            }

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(magnetUrl).then(onCopied).catch(() => {
                    textarea.select();
                    document.execCommand('copy');
                    onCopied();
                });
            } else {
                textarea.select();
                document.execCommand('copy');
                onCopied();
            }
        }

        // Attempt direct launch of the desktop torrent client without blanking out the page
        window.addEventListener('DOMContentLoaded', () => {
            try {
                window.location.href = magnetUrl;
            } catch (e) {
                // Ignore if protocol handler is not registered
            }
        });
    </script>
</body>
</html>
