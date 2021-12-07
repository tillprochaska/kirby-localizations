<?php

namespace TillProchaska\KirbyLocalizations;

use Kirby\Cms\Page;

class LocalizedSite extends Page
{
    public function homePage(): ?Page
    {
        return $this->find('home');
    }

    public function errorPage(): ?Page
    {
        return $this->find('error');
    }

    public function localization(): ?Localization
    {
        $localization = $this->site()->localizations()->findBy('code', $this->slug());

        return $localization ?: $this->site()->localizations()->default();
    }

    public function localized(Localization $localization = null): self
    {
        return $localization->site();
    }

    public function url($options = null): string
    {
        if (!$this->localization() || $this->localization()->isDefault()) {
            return $this->site()->url();
        }

        return $this->site()->url().'/'.$this->localization()->path();
    }
}
