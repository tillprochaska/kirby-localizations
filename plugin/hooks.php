<?php

use Kirby\Cms\Page;
use Kirby\Toolkit\Collection;
use TillProchaska\KirbyLocalizations\Localization;
use TillProchaska\KirbyLocalizations\LocalizedRoute;
use TillProchaska\KirbyLocalizations\LocalizedSite;

return [
    'system.loadPlugins:after' => function () {
        $localizations = site()->localizations();

        $homeRoute = new LocalizedRoute([
            'pattern' => '',
            'method' => 'ALL',
            'action' => function (Localization $localization) {
                return site()->visit($localization->homePage() ?: $localization->errorPage());
            },
        ]);

        $defaultRoute = new LocalizedRoute([
            'pattern' => '(:all)',
            'method' => 'ALL',
            'action' => function (Localization $localization, string $path) {
                $page = kirby()->resolve($localization->code().'/'.$path);

                return site()->visit($page ?: $localization->errorPage());
            },
        ]);

        $customRoutes = (new Collection(kirby()->option('tillprochaska.localizations.routes', [])))
            ->map(function (array $route) use ($localizations) {
                $localizedRoute = new LocalizedRoute($route);

                return $localizedRoute->expand($localizations);
            })
            ->values()
        ;

        $customRoutes = array_merge(...$customRoutes);

        $errorRoute = new LocalizedRoute([
            'pattern' => '(:all)',
            'method' => 'ALL',
            'action' => function (Localization $localization) {
                return site()->visit($localization->errorPage());
            },
        ]);

        kirby()->extend([
            'routes' => [
                ...$customRoutes,
                ...$homeRoute->expand($localizations),
                ...$defaultRoute->expand($localizations),
                ...$errorRoute->expand($localizations),
            ],
        ]);
    },

    'page.create:after' => function (Page $page) {
        if (!in_array(LocalizedSite::class, class_uses($page))) {
            return;
        }

        kirby()->impersonate('kirby');

        Page::create([
            'parent' => $page,
            'draft' => false,
            'slug' => 'error',
            'template' => 'error',
            'content' => [
                'title' => 'Error',
            ],
        ]);
    },
];
