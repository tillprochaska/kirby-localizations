<?php

use Kirby\Cms\Page;
use TillProchaska\KirbyLocalizations\Tests\LocalizedSitePage;

it('creates an error page when a new localized site is created', function () {
    $this->kirby()->impersonate('kirby');

    $es = Page::create([
        'draft' => false,
        'slug' => 'es',
        'template' => 'localized-site',
        'content' => [
            'title' => 'Spanish',
            'locale' => 'es_ES',
        ],
    ]);

    expect($es)->toBeInstanceOf(LocalizedSitePage::class);
    expect($es)->find('error')->toBeInstanceOf(Page::class);
    expect($es)->find('error')->isErrorPage()->toBeTrue();
    expect($es)->find('error')->intendedTemplate()->name()->toEqual('error');

    // Sanity check
    $otherPage = Page::create([
        'parent' => $es,
        'slug' => 'some-other-page',
        'template' => 'default',
        'content' => [
            'title' => 'Some other page',
        ],
    ]);

    expect($otherPage)->find('error')->toBeNull();
});
