<?php

use Kirby\Cms\Page;

beforeEach(function () {
    $this->kirby()->impersonate('kirby');

    $this->draft = Page::create([
        'parent' => page('en'),
        'slug' => 'draft',
        'content' => [
            'title' => 'Hello World!',
        ],
    ]);

    $this->home = Page::create([
        'parent' => page('en'),
        'slug' => 'home',
        'template' => 'home',
        'content' => [
            'title' => 'Homepage',
        ],
    ]);

    $this->kirby()->impersonate(null);
});

it('previews drafts', function () {
    expect(site()->draft('en/draft'))->isDraft()->toBeTrue();
    expect($this->get('/draft'))->toHaveCode(404)->toSee('Error');
    expect($this->get($this->draft->previewUrl()))->toHaveCode(200)->toSee('Hello World!');
});

it('previews home page', function () {
    expect(site()->draft('en/home'))->isDraft()->toBeTrue();
    expect($this->get('/'))->toHaveCode(404)->toSee('Error');
    expect($this->get($this->home->previewUrl()))->toHaveCode(200)->toSee('Homepage');
});
