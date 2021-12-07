<?php

namespace TillProchaska\KirbyLocalizations;

use Kirby\Cms\Page;

trait HasLocalizations
{
    use HasLocalizationActions;

    public function localizedSite(): ?LocalizedSite
    {
        return $this->parents()
            ->filter(fn ($parent) => is_a($parent, LocalizedSite::class))
            ->first()
        ;
    }

    public function localization(): ?Localization
    {
        return $this->localization = $this->localizedSite()?->localization();
    }

    public function isOrigin(): bool
    {
        return !$this->originStore()->exists() && $this->localizationsStore()->exists();
    }

    public function origin(): ?Page
    {
        if ($this->isOrigin()) {
            return $this;
        }

        return $this->originStore()->get();
    }

    public function isLocalized(Localization $localization, bool $includeDrafts = false): bool
    {
        $localized = $this->localized($localization);

        if (!$localized) {
            return false;
        }

        if ($localized->isDraft() && !$includeDrafts) {
            return false;
        }

        return true;
    }

    public function localized(Localization $localization): ?Page
    {
        if ($this->localization()->is($localization)) {
            return $this;
        }

        if (!$this->isOrigin() && $this->origin()) {
            return $this->origin()->localized($localization);
        }

        return $this->localizationsStore()->get($localization);
    }

    public function localizations(bool $includeDrafts = false): Localizations
    {
        return $this->localizations = site()
            ->localizations()
            ->filter(fn ($localization) => $this->isLocalized($localization, $includeDrafts))
        ;
    }

    public function url($localization = null): string
    {
        if ($localization && !$this->isLocalized($localization)) {
            throw new \Exception('Can’t return localized url as the page is not localized.');
        }

        if ($localization) {
            return $this->localized($localization)?->url();
        }

        if ($this->isHomePage()) {
            return $this->localizedSite()->url();
        }

        return $this->parent()->url().'/'.$this->slug();
    }

    public function isHomePage(): bool
    {
        $code = $this->localization()->code();

        return "{$code}/home" === $this->id();
    }

    public function isErrorPage(): bool
    {
        $code = $this->localization()->code();

        return "{$code}/error" === $this->id();
    }

    protected function localizationsStore(): LocalizationsStore
    {
        return new LocalizationsStore($this);
    }

    protected function originStore(): OriginStore
    {
        return new OriginStore($this);
    }
}