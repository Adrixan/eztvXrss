<?php

declare(strict_types=1);

namespace EztvXrss\Feed;

use XMLWriter;

final readonly class OpmlBuilder
{
    public function __construct(
        private string $title = 'EZTV RSS Subscriptions'
    ) {
    }

    /**
     * @param array<int, array{
     *     title: string,
     *     xmlUrl: string,
     *     htmlUrl?: string,
     *     description?: string
     * }> $feeds
     */
    public function build(array $feeds): string
    {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->setIndent(true);
        $writer->setIndentString('  ');

        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('opml');
        $writer->writeAttribute('version', '2.0');

        $writer->startElement('head');
        $writer->writeElement('title', $this->title);
        $writer->writeElement('dateCreated', gmdate(DATE_RFC2822));
        $writer->writeElement('docs', 'http://opml.org/spec2.opml');
        $writer->endElement(); // head

        $writer->startElement('body');

        foreach ($feeds as $feed) {
            $feedTitle = (string) ($feed['title'] ?? 'Show Feed');
            $xmlUrl = (string) ($feed['xmlUrl'] ?? '');
            $htmlUrl = (string) ($feed['htmlUrl'] ?? $xmlUrl);

            $writer->startElement('outline');
            $writer->writeAttribute('text', $feedTitle);
            $writer->writeAttribute('title', $feedTitle);
            $writer->writeAttribute('type', 'rss');
            $writer->writeAttribute('xmlUrl', $xmlUrl);
            $writer->writeAttribute('htmlUrl', $htmlUrl);
            if (!empty($feed['description'])) {
                $writer->writeAttribute('description', (string) $feed['description']);
            }
            $writer->endElement();
        }

        $writer->endElement(); // body
        $writer->endElement(); // opml
        $writer->endDocument();

        return $writer->outputMemory();
    }
}
