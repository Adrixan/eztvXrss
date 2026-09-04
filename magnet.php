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

// Issue 302 redirect directly to the magnet URI
header('Location: ' . $rawMagnet, true, 302);
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Launching Magnet Link — eztvXrss</title>
    <meta http-equiv="refresh" content="0; url=<?= htmlspecialchars($rawMagnet, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
    <script>
        window.location.href = <?= json_encode($rawMagnet) ?>;
    </script>
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: #1e293b;
            --border-color: #334155;
            --text-color: #f8fafc;
            --text-muted: #94a3b8;
            --primary: #3b82f6;
            --primary-hover: #2563eb;
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
            max-width: 640px;
            margin: 0 auto;
        }
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
        }
        h1 {
            font-size: 1.4rem;
            margin-top: 0;
            color: var(--text-color);
        }
        .btn {
            display: inline-block;
            background: var(--primary);
            color: #fff;
            text-decoration: none;
            padding: 0.75rem 1.25rem;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 1rem;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background: var(--primary-hover);
        }
        textarea {
            width: 100%;
            box-sizing: border-box;
            background: var(--bg-color);
            color: #38bdf8;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 0.75rem;
            font-family: monospace;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            word-break: break-all;
            resize: vertical;
        }
        a.nav-link {
            color: var(--primary);
            text-decoration: none;
        }
        a.nav-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>Opening BitTorrent Client...</h1>
            <p>Your browser was redirected to the magnet URI. If your torrent client did not open automatically, click the button below:</p>
            <p>
                <a href="<?= htmlspecialchars($rawMagnet, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="btn">Open Magnet in Client</a>
            </p>

            <h2 style="font-size: 1rem; margin-top: 1.5rem;">Raw Magnet URI:</h2>
            <textarea rows="4" readonly onclick="this.select()"><?= htmlspecialchars($rawMagnet, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>

            <p style="margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-muted);">
                <a href="./" class="nav-link">← Back to eztvXrss Generator</a>
            </p>
        </div>
    </div>
</body>
</html>
