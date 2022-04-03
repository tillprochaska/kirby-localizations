<?php

use Kirby\Cms\Page;
use Kirby\Filesystem\File;
use TillProchaska\KirbyLocalizations\Localization;

beforeEach(function () {
    $this->withOption('tillprochaska.localizations.routes', [
        [
            'pattern' => '/custom-route',
            'action' => function (Localization $localization) {
                return $localization->code();
            },
        ],
    ]);

    $this->kirby()->impersonate('kirby');

    $this->en = Page::create([
        'parent' => page('en'),
        'draft' => false,
        'slug' => 'hello-world',
        'template' => 'test',
        'content' => [
            'title' => 'Hello World!',
        ],
    ]);

    $this->de = Page::create([
        'parent' => page('de'),
        'draft' => false,
        'slug' => 'hallo-welt',
        'template' => 'test',
        'content' => [
            'title' => 'Hallo Welt!',
        ],
    ]);
});

it('routes homepages', function () {
    Page::create([
        'parent' => page('en'),
        'draft' => false,
        'slug' => 'home',
        'template' => 'home',
        'content' => [
            'title' => 'Homepage',
        ],
    ]);

    Page::create([
        'parent' => page('de'),
        'draft' => false,
        'slug' => 'home',
        'template' => 'home',
        'content' => [
            'title' => 'Startseite',
        ],
    ]);

    expect($this->get('/'))->toHaveCode(200)->toSee('Homepage');
    expect($this->get('/de'))->toHaveCode(200)->toSee('Startseite');

    // Not every localization needs a homepage
    expect($this->get('/fr'))->toHaveCode(404);
});

it('routes localized pages', function () {
    expect($this->get('/hello-world'))->toHaveCode(200)->toSee('Hello World!');
    expect($this->get('/de/hallo-welt'))->toHaveCode(200)->toSee('Hallo Welt!');
});

it('routes files', function () {
    $svg = function ($text): string {
        return "<svg version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\"><text>{$text}</text></svg>";
    };

    (new File("{$this->en->contentFileDirectory()}/en.svg"))->write($svg('English'));
    (new File("{$this->de->contentFileDirectory()}/de.svg"))->write($svg('Deutsch'));

    $enSvgUrl = $this->en->file('en.svg')->url();
    $deSvgUrl = $this->de->file('de.svg')->url();

    expect($this->get('/en/hello-world/en.svg'))->toHaveCode(307)->toHaveHeader('Location', $enSvgUrl);
    expect($this->get('/de/hallo-welt/de.svg'))->toHaveCode(307)->toHaveHeader('Location', $deSvgUrl);
});

it('expands and registers custom routes', function () {
    expect($this->get('/custom-route'))->toHaveCode(200)->toSee('en');
    expect($this->get('/de/custom-route'))->toHaveCode(200)->toSee('de');
});

it('returns localized error page', function () {
    expect(page('en/does-not-exist'))->toBeNull();
    expect(page('de/does-not-exist'))->toBeNull();

    expect($this->get('/does-not-exist'))->toHaveCode(404)->toSee('Error');
    expect($this->get('/de/does-not-exist'))->toHaveCode(404)->toSee('Fehler');
});
