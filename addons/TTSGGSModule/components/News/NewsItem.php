<?php

namespace ModulesGarden\TTSGGSModule\Components\News;

class NewsItem
{
    protected string $text;
    protected string $link;
    protected string $date;

    public function __construct(string $text, string $link, string $date)
    {
        $this->text = $text;
        $this->link = $link;
        $this->date = $date;
    }

    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'link' => $this->link,
            'date' => $this->date
        ];
    }
}
