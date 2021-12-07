<?php

namespace TillProchaska\KirbyLocalizations;

use Kirby\Cms\Site;
use Kirby\Toolkit\Collection;

class Localizations extends Collection
{
    public static function fromSite(Site $site): self
    {
        $pages = $site->children()->filterBy('intendedTemplate', 'localized-site');
        $localizations = $pages->map(fn ($page) => new Localization($page));
        $codes = $localizations->pluck('code');

        return new static(array_combine(keys: $codes, values: $localizations->values()));
    }

    public function default(): ?Localization
    {
        return $this->findBy('isDefault', true);
    }

    public function current(): ?Localization
    {
        return $this->findBy('isCurrent', true);
    }
}
