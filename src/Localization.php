<?php

namespace TillProchaska\KirbyLocalizations;

use Kirby\Cms\Page;

class Localization
{
    public function __construct(protected Page $site)
    {
    }

    public static function __callStatic(string $name, array $args): self
    {
        $code = strtolower($name);
        $localization = site()->localizations()->find($code);

        if (!$localization) {
            throw new \Exception("Language {$name} does not exist.");
        }

        return $localization;
    }

    public function is(self $other): bool
    {
        return $this->code() === $other->code();
    }

    public function isDefault(): bool
    {
        return $this->site()->default()->toBool();
    }

    public function isCurrent(): bool
    {
        return site()->page()->localization()->is($this);
    }

    public function code(): string
    {
        return $this->site()->uid();
    }

    public function formattedCode(): string
    {
        return strtoupper($this->code());
    }

    public function locale(): string
    {
        return $this->site()->locale()->value();
    }

    public function name(): string
    {
        return $this->site()->title()->or($this->code())->value();
    }

    public function path(): string
    {
        return $this->isDefault() ? '' : $this->code();
    }

    public function site(): Page
    {
        return $this->site;
    }

    public function homePage(): ?Page
    {
        return $this->site()->homePage();
    }

    public function errorPage(): Page
    {
        return $this->site()->errorPage();
    }

    public function url(): string
    {
        return $this->site()->url();
    }
}
