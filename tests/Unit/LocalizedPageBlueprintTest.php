<?php

use Kirby\Cms\Page;
use TillProchaska\KirbyLocalizations\LocalizedPageBlueprint;

beforeEach(function () {
    $this->kirby()->impersonate('kirby');

    $this->home = Page::create([
        'parent' => page('en'),
        'slug' => 'home',
        'template' => 'home',
    ]);
});

it('does not remove draft status', function () {
    $blueprint = new LocalizedPageBlueprint([
        'model' => $this->home,
        'status' => [
            'draft' => [],
            'listed' => [],
        ],
    ]);

    expect($blueprint)->status()->toHaveKeys(['listed', 'draft']);
});

it('does not fail if status is not set in blueprint', function () {
    $blueprint = new LocalizedPageBlueprint([
        'model' => $this->home,
    ]);

    expect($blueprint)->status()->toHaveKeys(['listed']);
});
