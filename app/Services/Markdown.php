<?php

namespace App\Services;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use League\CommonMark\CommonMarkConverter;

class Markdown
{
    public function render(?string $markdown): HtmlString
    {
        return new HtmlString(
            (new CommonMarkConverter([
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]))->convert($markdown ?? '')->getContent()
        );
    }

    /**
     * @return array{html: HtmlString, headings: array<int, array{level: int, text: string, id: string}>}
     */
    public function renderWithTableOfContents(?string $markdown): array
    {
        $headings = [];
        $usedIds = [];
        $html = (string) $this->render($markdown);

        $html = preg_replace_callback(
            '/<h([2-3])>(.*?)<\/h\1>/s',
            function (array $matches) use (&$headings, &$usedIds): string {
                $text = trim(strip_tags(html_entity_decode($matches[2], ENT_QUOTES | ENT_HTML5)));
                $baseId = Str::slug($text) ?: 'section';
                $id = $baseId;
                $suffix = 2;

                while (isset($usedIds[$id])) {
                    $id = $baseId.'-'.$suffix++;
                }

                $usedIds[$id] = true;
                $headings[] = [
                    'level' => (int) $matches[1],
                    'text' => $text,
                    'id' => $id,
                ];

                return sprintf('<h%s id="%s">%s</h%s>', $matches[1], e($id), $matches[2], $matches[1]);
            },
            $html
        );

        return [
            'html' => new HtmlString($html ?? ''),
            'headings' => $headings,
        ];
    }
}
