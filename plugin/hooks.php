<?php

use TillProchaska\KirbyLocalizations\Localization;
use TillProchaska\KirbyLocalizations\LocalizedRoute;

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

        $errorRoute = new LocalizedRoute([
            'pattern' => '(:all)',
            'method' => 'ALL',
            'action' => function (Localization $localization) {
                return site()->visit($localization->errorPage());
            },
        ]);

        kirby()->extend([
            'routes' => [
                ...$homeRoute->expand($localizations),
                ...$defaultRoute->expand($localizations),
                ...$errorRoute->expand($localizations),
            ],
        ]);
    },
];
