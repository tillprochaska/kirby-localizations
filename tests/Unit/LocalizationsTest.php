<?php

use TillProchaska\KirbyLocalizations\Localization;
use TillProchaska\KirbyLocalizations\Localizations;

it('can be created from site a site object', function () {
    $localizations = Localizations::fromSite(site());
    $codes = $localizations->map(fn ($loc) => $loc->code())->values();

    expect($localizations->count())->toEqual(3);
    expect($codes)->toContain('de', 'en', 'fr');
});

it('has a default localization', function () {
    $default = Localizations::fromSite(site())->default();
    expect($default)->toEqualObject(Localization::EN());
});

it('has a current localization', function () {
    site()->visit(page('de'));
    $current = Localizations::fromSite(site())->current();
    expect($current)->toEqualObject(Localization::DE());
});

it('finds localizaiton by code', function () {
    $localizations = Localizations::fromSite(site());
    expect($localizations->find('fr'))->toEqualObject(Localization::FR());
});
