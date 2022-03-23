<?php

use Kirby\Toolkit\A;
use TillProchaska\KirbyLocalizations\Localization;
use TillProchaska\KirbyLocalizations\LocalizedRoute;

beforeEach(function () {
    $this->actionCallCount = 0;
    $this->actionArgs = null;
    $self = $this;

    $this->action = function (...$args) use ($self) {
        ++$self->actionCallCount;
        $self->actionArgs = $args;

        return 'Hello World!';
    };

    $this->routes = (new LocalizedRoute([
        'pattern' => 'test/(:any)',
        'action' => $this->action,
    ]))->expand(site()->localizations());

    $this->localization = Localization::DE();
    $this->localizedAction = $this->routes[0]['action'];
});

it('returns an expanded route for each localization', function () {
    expect(site()->localizations())->toHaveCount(3);
    expect($this->routes)->toHaveCount(3);
});

it('prepends localization path to pattern', function () {
    $patterns = A::pluck($this->routes, 'pattern');
    expect($patterns)->toContain('de/test/(:any)', 'fr/test/(:any)', '/test/(:any)');
});

it('does not add unnecessary slash after localization path', function () {
    $localizations = site()->localizations();

    $withSlash = new LocalizedRoute(['pattern' => '/test', 'action' => fn () => 0]);
    $withoutSlash = new LocalizedRoute(['pattern' => 'test', 'action' => fn () => 0]);

    $withSlash = A::pluck($withSlash->expand($localizations), 'pattern');
    $withoutSlash = A::pluck($withoutSlash->expand($localizations), 'pattern');

    expect($withSlash)->toEqual(['de/test', 'fr/test', '/test']);
    expect($withoutSlash)->toEqual(['de/test', 'fr/test', '/test']);
});

it('returns localization root paths if pattern is empty', function () {
    $routes = (new LocalizedRoute([
        'pattern' => '',
        'action' => function () {
        },
    ]))->expand(site()->localizations());

    $patterns = A::pluck($routes, 'pattern');
    expect($patterns)->toContain('de', 'fr', '/');
});

it('passes matched localization and route parameters to original action', function () {
    ($this->localizedAction)('slug');

    expect($this->actionCallCount)->toEqual(1);
    expect($this->actionArgs)->toEqual([$this->localization, 'slug']);
});

it('returns return value of original action', function () {
    expect(($this->localizedAction)('slug'))->toEqual('Hello World!');
});

it('sets locale', function () {
    ($this->localizedAction)('slug');
    expect(kirby()->option('locale'))->toEqual('de_DE');
    expect(setlocale(LC_ALL, 0))->toEqual('de_DE');
});

it('sorts expanded routes with least specific routes coming last', function () {
    $routes = (new LocalizedRoute([
        'pattern' => '',
        'action' => function () {
        },
    ]))->expand(site()->localizations());

    $patterns = A::pluck($routes, 'pattern');
    expect($patterns)->toEqual(['de', 'fr', '/']);
});

it('returns an array with numeric keys', function () {
    expect($this->routes)->toHaveKeys([0, 1, 2]);
    expect($this->routes)->not()->toHaveKeys(['de', 'en', 'fr']);
});
