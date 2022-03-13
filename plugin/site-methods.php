<?php

use TillProchaska\KirbyLocalizations\Localization;
use TillProchaska\KirbyLocalizations\Localizations;

return [
    'localizations' => function () {
        return Localizations::fromSite($this);
    },

    'currentLocalization' => function () {
        return $this->localizations()->current() ?? $this->localizations()->default();
    },

    'localized' => function (?Localization $localization = null) {
        if (!$localization) {
            return $this->currentLocalization()->site();
        }

        return $localization->site();
    },
];
