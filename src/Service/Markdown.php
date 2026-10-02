<?php

namespace App\Service;

class Markdown
{
    private $parsedown;

    public function __construct()
    {
        $this->parsedown = new \Parsedown();
    }

    public function toHtml(?string $text): ?string
    {
        if(!$text) return '';

        //Костыль лучше не использовать
        /*
        $text = str_replace("\n", "\n\n", $text);
        $text = str_replace("|\n", "|", $text);
        $text = str_replace("\n\n\n", "\n\n", $text);
        */

        return $this->parsedown->text($text);
    }

    public function addImageCaptions(string $html): string
    {
        return preg_replace_callback(
            '/<p>\s*(<img\b[^>]*?\/>)\s*<\/p>/i',
            function (array $matches) {
                $img = $matches[1];
                if (!preg_match('/\balt\s*=\s*"([^"]*)"/', $img, $altMatch)) {
                    return $matches[0];
                }

                $caption = '';
                $alt = trim(html_entity_decode($altMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                if ($alt !== '') {
                    $caption = '<figcaption class="book-caption">'
                        . htmlspecialchars($alt, ENT_QUOTES | ENT_HTML5, 'UTF-8')
                        . '</figcaption>';
                }

                return '<figure class="book-figure">' . $img . $caption . '</figure>';
            },
            $html
        );
    }
}
