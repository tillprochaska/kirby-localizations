<?php

use Kirby\Cms\Page;
use Kirby\Cms\User;
use Kirby\Exception\PermissionException;

beforeEach(function () {
    $this->kirby()->impersonate('kirby');

    User::create([
        'email' => 'admin@example.org',
        'password' => 'test1234',
        'role' => 'admin',
    ]);

    Page::create([
        'parent' => page('en'),
        'slug' => 'home',
        'template' => 'home',
        'draft' => false,
    ]);

    $this->kirby()->impersonate(null);
});

it('can convert home page to draft', function () {
    $home = page('en/home');
    expect($home)->isDraft()->toBeFalse();
    expect($home)->isHomePage()->toBeTrue();

    $this->kirby()->impersonate('admin@example.org');
    $home->changeStatus('draft');

    $home = page('en')->purge()->findPageOrDraft('home');
    expect($home)->isDraft()->toBeTrue();
});

it('cannot change status of error pages', function () {
    $this->kirby()->impersonate('admin@example.org');
    page('en/error')->changeStatus('listed');
})->throws(PermissionException::class);

it('fails to change home page status if not authenticated', function () {
    page('en/home')->changeStatus('draft');
})->throws(PermissionException::class);
