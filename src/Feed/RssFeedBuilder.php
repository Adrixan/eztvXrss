<?php

declare(strict_types=1);

namespace EztvXrss\Feed;

use EztvXrss\Model\TorrentItem;
use XMLWriter;

final readonly class RssFeedBuilder
{
    public function __construct(
        private string $channelTitle = 'EZTV Show RSS Feed',
        private string $channelLink = 'https://code-alongsi.de/eztvxrss/',
        private string $channelDescription = 'Custom filtered EZTV show RSS feed',
        private int $ttl = 60,
        private string $language = 'en-us'
    ) {
    }

    /**
     * @param array<int, TorrentItem> $items
     * @return string Valid RSS 2.0 XML string
     */
    public function build(array $items): string
    {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->setIndent(true);
        $writer->setIndentString('  ');

        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('rss');
        $writer->writeAttribute('version', '2.0');
        $writer->writeAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');

        $writer->startElement('channel');
        $writer->writeElement('title', $this->channelTitle);
        $writer->writeElement('link', $this->channelLink);
        $writer->writeElement('description', $this->channelDescription);
        $writer->writeElement('language', $this->language);
        $writer->writeElement('lastBuildDate', gmdate(DATE_RFC2822));
        $writer->writeElement('generator', 'eztvXrss Feed Generator 1.0 (https://code-alongsi.de/eztvxrss)');
        $writer->writeElement('ttl', (string) $this->ttl);

        // Self link
        $writer->startElement('atom:link');
        $writer->writeAttribute('href', $this->channelLink);
        $writer->writeAttribute('rel', 'self');
        $writer->writeAttribute('type', 'application/rss+xml');
        $writer->endElement();

        foreach ($items as $item) {
            $writer->startElement('item');

            $writer->writeElement('title', $item->title);
            $writer->writeElement('link', $item->magnetUrl);

            // GUID
            $guid = $item->hash !== '' ? $item->hash : (string) $item->id;
            $writer->startElement('guid');
            $writer->writeAttribute('isPermaLink', 'false');
            $writer->text($guid);
            $writer->endElement();

            // Publication date from unix release time
            $writer->writeElement('pubDate', $item->formattedPubDate());

            // BitTorrent Enclosure
            $writer->startElement('enclosure');
            $writer->writeAttribute('url', $item->magnetUrl);
            $writer->writeAttribute('length', (string) $item->sizeBytes);
            $writer->writeAttribute('type', 'application/x-bittorrent');
            $writer->endElement();

            // Description summary
            $desc = sprintf(
                '<![CDATA[<p><strong>Release:</strong> %s</p><p><strong>Size:</strong> %s</p><p><strong>Seeds:</strong> %d | <strong>Peers:</strong> %d</p><p><strong>Hash:</strong> <code>%s</code></p><p><a href="%s">Magnet Link</a></p>]]>',
                htmlspecialchars($item->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                htmlspecialchars($item->formattedSize(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                $item->seeds,
                $item->peers,
                htmlspecialchars($item->hash, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                htmlspecialchars($item->magnetUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            );
            $writer->startElement('description');
            $writer->writeRaw($desc);
            $writer->endElement();

            $writer->endElement(); // item
        }

        $writer->endElement(); // channel
        $writer->endElement(); // rss
        $writer->endDocument();

        return $writer->outputMemory();
    }
}
