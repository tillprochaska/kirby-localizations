<?php

use TillProchaska\KirbyLocalizations\Localization;

it('has static accessors for localizations', function () {
    $en = new Localization(page('en'));
    $de = new Localization(page('de'));
    $fr = new Localization(page('fr'));

    expect(Localization::EN())->toEqualObject($en);
    expect(Localization::DE())->toEqualObject($de);
    expect(Localization::FR())->toEqualObject($fr);
});

it('throws exception when calling accessor for non-existent localization', function () {
    Localization::BR();
})->throws(\Exception::class);

it('equals other localization with same code', function () {
    expect(Localization::EN()->is(Localization::EN()))->toBeTrue();
    expect(Localization::EN()->is(Localization::DE()))->toBeFalse();
});

it('can be the default localization', function () {
    expect(Localization::DE()->isDefault())->toBeFalse();
    expect(Localization::EN()->isDefault())->toBeTrue();
});

it('is current if it’s the current page’s localization', function () {
    site()->visit(page('en'));
    expect(Localization::DE()->isCurrent())->toBeFalse();

    site()->visit(page('de'));
    expect(Localization::DE()->isCurrent())->toBeTrue();
});

it('has a code that’s based on the page slug', function () {
    expect(Localization::EN()->code())->toEqual('en');
});

it('has a formatted code', function () {
    expect(Localization::EN()->formattedCode())->toEqual('EN');
});

it('returns a URL path based on the page slug', function () {
    expect(Localization::DE()->path())->toEqual('de');
});

it('returns the root path if it’s the default localization', function () {
    expect(Localization::EN()->path())->toEqual('');
});

it('has a locale', function () {
    expect(Localization::EN()->locale())->toEqual('en_US');
});

it('has a url', function () {
    expect(Localization::DE()->url())->toEqual('https://example.org/de');
});

it('returns the root url if it’s the default localization', function () {
    expect(Localization::EN()->url())->toEqual('https://example.org');
});

it('has a name', function () {
    expect(Localization::DE()->name())->toEqual('Deutsch');
});
