<?php

use TillProchaska\KirbyLocalizations\Localization;

it('has a localization', function () {
    expect(page('de')->localization())->toEqualObject(Localization::DE());
});

it('returns other localizations', function () {
    expect(page('de')->localized(Localization::EN()))->toBePage('en');
});

it('has a url', function () {
    expect(page('en')->url())->toEqual('https://example.org');
    expect(page('de')->url())->toEqual('https://example.org/de');
});
