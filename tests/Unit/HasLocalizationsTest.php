<?php

use Kirby\Cms\Page;
use TillProchaska\KirbyLocalizations\Localization;
use TillProchaska\KirbyLocalizations\LocalizationsStore;
use TillProchaska\KirbyLocalizations\OriginStore;

beforeEach(function () {
    $this->enPage = Page::create([
        'parent' => page('en'),
        'draft' => false,
        'slug' => 'hello-world',
        'template' => 'test',
    ]);

    $this->dePage = Page::create([
        'parent' => page('de'),
        'draft' => false,
        'slug' => 'hallo-welt',
        'template' => 'test',
    ]);

    $this->frPage = Page::create([
        'parent' => page('fr'),
        'draft' => false,
        'slug' => 'bonjour',
        'template' => 'test',
    ]);

    (new OriginStore($this->dePage))->set($this->enPage);
    (new LocalizationsStore($this->enPage))->set($this->dePage);
});

it('has a localized site', function () {
    expect(page('de/hallo-welt')->localizedSite())->toBePage('de');
});

it('has a localization', function () {
    expect(page('de/hallo-welt')->localization())->toEqualObject(Localization::DE());
});

it('returns all localizations', function () {
    $codes = page('de/hallo-welt')->localizations()->pluck('code');
    expect($codes)->toContain('de', 'en');
    expect($codes)->not()->toContain('fr');
});

it('excludes draft localizations by default', function () {
    site()->page('de/hallo-welt')->changeStatus('draft');

    $codes = page('en/hello-world')->localizations()->pluck('code');
    expect($codes)->toContain('en');
    expect($codes)->not()->toContain('de');

    $codes = page('en/hello-world')->localizations(includeDrafts: true)->pluck('code');
    expect($codes)->toContain('en', 'de');
});

it('can be the origin of multiple localizations', function () {
    expect($this->enPage->isOrigin())->toBeTrue();
    expect($this->dePage->isOrigin())->toBeFalse();
    expect($this->frPage->isOrigin())->toBeFalse();
});

it('has an origin page', function () {
    expect($this->enPage->origin())->toBePage('en/hello-world');
    expect($this->dePage->origin())->toBePage('en/hello-world');
    expect($this->frPage->origin())->toBeNull();
});

it('returns localized pages', function () {
    // Origin page
    expect($this->enPage->localized(Localization::EN()))->toBePage('en/hello-world');
    expect($this->enPage->localized(Localization::DE()))->toBePage('de/hallo-welt');
    expect($this->enPage->localized(Localization::FR()))->toBeNull();

    // Non-origin page
    expect($this->dePage->localized(Localization::EN()))->toBePage('en/hello-world');
    expect($this->dePage->localized(Localization::DE()))->toBePage('de/hallo-welt');
    expect($this->dePage->localized(Localization::FR()))->toBeNull();
});

it('checks if a localization exists', function () {
    // Origin page
    expect($this->enPage->isLocalized(Localization::EN()))->toBeTrue();
    expect($this->enPage->isLocalized(Localization::DE()))->toBeTrue();
    expect($this->enPage->isLocalized(Localization::FR()))->toBeFalse();

    // Non-origin page
    expect($this->dePage->isLocalized(Localization::EN()))->toBeTrue();
    expect($this->dePage->isLocalized(Localization::DE()))->toBeTrue();
    expect($this->dePage->isLocalized(Localization::FR()))->toBeFalse();
});

it('has a url', function () {
    expect($this->enPage->url())->toEqual('https://example.org/hello-world');
    expect($this->dePage->url())->toEqual('https://example.org/de/hallo-welt');
});

it('returns url of localized page', function () {
    expect($this->enPage->url(Localization::EN()))->toEqual('https://example.org/hello-world');
    expect($this->enPage->url(Localization::DE()))->toEqual('https://example.org/de/hallo-welt');
});

it('raises exception if localization does not exist', function () {
    $this->enPage->url(Localization::FR());
})->throws(\Exception::class);

it('checks if it is home page of a localized site', function () {
    $home = Page::create([
        'parent' => page('en'),
        'draft' => false,
        'slug' => 'home',
        'template' => 'test',
    ]);

    expect($home->isHomePage())->toBeTrue();
});

it('checks if it is error page of a localized site', function () {
    $error = page('en/error');
    expect($error->isErrorPage())->toBeTrue();
});
