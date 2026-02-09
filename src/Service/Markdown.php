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

        $text = str_replace("\n", "\n\n", $text);
        $text = str_replace("\n\n\n", "\n\n", $text);

        return $this->parsedown->text($text);
    }
}
