<?php

use Kirby\Cms\Page;
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
});

it('stores origin page in a JSON file', function () {
    $path = kirby()->root('content').'/de/hallo-welt/_origin.json';
    (new OriginStore($this->dePage))->set($this->enPage);
    expect($path)->jsonFile()->toEqual(['en', 'hello-world']);
});

it('retrieves origin page from JSON file', function () {
    $store = new OriginStore($this->dePage);
    expect($store->get())->toBeNull();

    $store->set($this->enPage);
    expect($store->get())->toBePage('en/hello-world');
});

it('retrieves origin page for nested pages', function () {
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

    $store = (new OriginStore($deChild))->set($enChild);
    expect($store->get())->toBePage('en/hello-world/child');
});

it('checks if origin file exists', function () {
    $store = new OriginStore($this->dePage);
    $path = kirby()->root('content').'/de/hallo-welt/_origin.json';

    expect($store->exists())->toBeFalse();
    file_put_contents($path, '["en","hello-world"]');
    expect($store->exists())->toBeTrue();
});

it('deletes origin file', function () {
    $path = kirby()->root('content').'/de/hallo-welt/_origin.json';

    file_put_contents($path, '["en","hello-world"]');
    expect($path)->toBeFile();

    (new OriginStore($this->dePage))->delete();
    expect($path)->not()->toBeFile();
});
