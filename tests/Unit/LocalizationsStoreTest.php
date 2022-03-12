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
});

it('stores multiple localizations in JSON file', function () {
    $store = new LocalizationsStore($this->enPage);
    $path = kirby()->root('content').'/en/hello-world/_localizations.json';

    $store->set($this->dePage);
    expect($path)->jsonFile()->toEqual(['de' => 'hallo-welt']);

    $store->set($this->frPage);
    expect($path)->jsonFile()->toEqual(['de' => 'hallo-welt', 'fr' => 'bonjour']);
});

it('overwrites existing localizations', function () {
    $store = new LocalizationsStore($this->enPage);
    $path = kirby()->root('content').'/en/hello-world/_localizations.json';

    $store->set($this->dePage);
    expect($path)->jsonFile()->toEqual(['de' => 'hallo-welt']);

    $newLocalization = Page::create([
        'parent' => page('de'),
        'draft' => false,
        'slug' => 'hallo-welt-neu',
        'template' => 'test',
    ]);

    $store->set($newLocalization);
    expect($path)->jsonFile()->toEqual(['de' => 'hallo-welt-neu']);
});

it('retrieves localized pages from JSON file', function () {
    $store = new LocalizationsStore($this->enPage);
    $path = kirby()->root('content').'/en/hello-world/_localizations.json';

    file_put_contents($path, json_encode(['de' => 'hallo-welt']));
    expect($store->get(Localization::DE()))->toBePage('de/hallo-welt');
});

it('retrieves localized pages for nested page structures', function () {
    $enChild = Page::create([
        'parent' => $this->enPage,
        'draft' => false,
        'slug' => 'child',
        'template' => 'test',
    ]);

    $deChild = Page::create([
        'parent' => $this->dePage,
        'draft' => false,
        'slug' => 'kind',
        'template' => 'test',
    ]);

    (new OriginStore($this->dePage))->set($this->enPage);
    (new LocalizationsStore($this->enPage))->set($this->dePage);
    (new OriginStore($deChild))->set($enChild);

    $store = (new LocalizationsStore($enChild))->set($deChild);
    expect($store->get(Localization::DE()))->toBePage('de/hallo-welt/kind');
});

it('deletes a single localization', function () {
    $store = new LocalizationsStore($this->enPage);
    $path = kirby()->root('content').'/en/hello-world/_localizations.json';

    $store->set($this->dePage)->set($this->frPage);
    expect($path)->jsonFile()->toEqual(['de' => 'hallo-welt', 'fr' => 'bonjour']);

    $store->delete(Localization::DE());

    expect($path)->jsonFile()->toEqual(['fr' => 'bonjour']);
});

it('deletes all localizations', function () {
    $store = new LocalizationsStore($this->enPage);
    $path = kirby()->root('content').'/en/hello-world/_localizations.json';

    $store->set($this->dePage)->set($this->frPage);
    expect($path)->jsonFile()->toEqual(['de' => 'hallo-welt', 'fr' => 'bonjour']);

    $store->delete();
    expect($path)->not()->toBeFile();
});

it('deletes JSON file if there are no localizations left', function () {
    $store = new LocalizationsStore($this->enPage);
    $path = kirby()->root('content').'/en/hello-world/_localizations.json';

    $store->set($this->dePage);
    expect($path)->toBeFile();

    $store->delete(Localization::DE());
    expect($path)->not()->toBeFile();
});

it('checks if JSON file exists', function () {
    $store = new LocalizationsStore($this->enPage);
    expect($store->exists())->toBeFalse();

    $store->set($this->dePage);
    expect($store->exists())->toBeTrue();
});
