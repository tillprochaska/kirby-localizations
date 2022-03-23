<?php

use Kirby\Cms\Page;
use TillProchaska\KirbyLocalizations\Localization;
use TillProchaska\KirbyLocalizations\LocalizationsStore;
use TillProchaska\KirbyLocalizations\OriginStore;

beforeEach(function () {
    $this->kirby()->impersonate('kirby');

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

    $this->enChild = Page::create([
        'parent' => $this->enPage,
        'draft' => false,
        'slug' => 'child',
        'template' => 'test',
    ]);

    (new OriginStore($this->dePage))->set($this->enPage);
    (new LocalizationsStore($this->enPage))->set($this->dePage);
});

it('checks wether page can be localized', function () {
    expect($this->enPage)->isLocalizable(Localization::FR())->toBeTrue();
    expect($this->enChild)->isLocalizable(Localization::FR())->toBeFalse();
});

it('links existing page to origin page', function () {
    expect($this->enPage)->localizations()->pluck('code')->toContain('en', 'de');
    expect($this->frPage)->localizations()->pluck('code')->toContain('fr');
    $this->enPage->linkLocalizedPage($this->frPage);
    expect($this->frPage)->origin()->toBePage('en/hello-world');
    expect($this->frPage)->localizations()->pluck('code')->toContain('en', 'de', 'fr');
    expect($this->enPage)->localizations()->pluck('code')->toContain('en', 'de', 'fr');
});

it('links existing page to localized page', function () {
    expect($this->dePage)->localizations()->pluck('code')->toContain('en', 'de');
    expect($this->frPage)->localizations()->pluck('code')->toContain('fr');
    $this->dePage->linkLocalizedPage($this->frPage);
    expect($this->frPage)->origin()->toBePage('en/hello-world');
    expect($this->frPage)->localizations()->pluck('code')->toContain('en', 'de', 'fr');
    expect($this->dePage)->localizations()->pluck('code')->toContain('en', 'de', 'fr');
});

it('creates new localized page', function () {
    $this->enPage->localize(Localization::FR());

    $codes = $this->enPage->localizations(includeDrafts: true)->pluck('code');
    expect($codes)->toContain('de', 'en', 'fr');
    expect($this->enPage->localized(Localization::FR()))->toBeDraft('fr/hello-world');
    expect($this->enPage->localized(Localization::FR())->isOrigin())->toBeFalse();
});

it('sets origin for new localized page', function () {
    $localizedPage = $this->enPage->localize(Localization::FR());
    expect($localizedPage->origin())->toBePage($this->enPage);
});

it('does not copy child pages when creating a new localized page', function () {
    $localizedPage = $this->enPage->localize(Localization::FR());
    expect($localizedPage->children()->count())->toEqual(0);

    // Sanity check
    expect($this->enPage->children()->count())->toEqual(1);
});

it('creates new localized page if page is not the origin', function () {
    $this->dePage->localize(Localization::FR());

    $codes = $this->enPage->localizations(includeDrafts: true)->pluck('code');
    expect($codes)->toContain('de', 'en', 'fr');
    expect($this->enPage->localized(Localization::FR()))->toBeDraft('fr/hello-world');
});

it('raises exception if localized page already exists', function () {
    $this->enPage->localize(Localization::FR());

    // Creating another localization fails
    $this->enPage->localize(Localization::FR());
})->throws(\Exception::class);

it('creates new localize page for nested page', function () {
    $this->enChild->localize(Localization::DE());
    $codes = $this->enChild->localizations(includeDrafts: true)->pluck('code');
    expect($codes)->toContain('de', 'en');
});

it('raises exception if parent page is not localized', function () {
    $this->enChild->localize(Localization::FR());
})->throws(\Exception::class);

it('updates localizaitons after changing the slug', function () {
    $this->dePage->changeSlug('new-slug');
    expect($this->enPage->localized(Localization::DE()))->toBePage('de/new-slug');
});

it('updates origins of localized pages and drafts after changing the slug', function () {
    $frPage = $this->enPage->localize(Localization::FR());
    expect($frPage->origin())->toBePage('en/hello-world');

    $this->enPage->changeSlug('new-slug');
    expect($this->dePage->origin())->toBePage('en/new-slug');
    expect($frPage->origin())->toBePage('en/new-slug');
});

it('ignores slug change if slug has not changed', function () {
    $newPage = $this->enPage->changeSlug('hello-world');
    expect($this->dePage->origin())->toBePage($newPage);
});

it('ignores slug change if page has no other localizations', function () {
    $newPage = $this->frPage->changeSlug('new-slug');
    expect($newPage->localizations()->count())->toEqual(1);
});

it('updates localizations after deleting a localized page', function () {
    expect($this->enPage->localizations()->count())->toEqual(2);
    $this->dePage->delete();
    expect($this->enPage->localizations()->count())->toEqual(1);
});

it('ignores deletion if the page is the only localization', function () {
    expect($this->frPage)->toBePage('fr/bonjour');
    $this->frPage->delete();
    expect(page('fr/bonjour'))->toBeNull();
});

it('uses an existing localized page as new origin if deleted page is the origin', function () {
    // Create another localized page so that two remain
    // after deleting the origin
    $frPage = $this->enPage->localize(Localization::FR());

    expect($this->enPage->isOrigin())->toBeTrue();

    // Force-delete is required as the page has subpages
    $this->enPage->delete(force: true);

    expect($this->dePage->isOrigin())->toBeTrue();
    expect($frPage->origin())->toBe($this->dePage);
});
