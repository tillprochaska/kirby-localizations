<?php

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Toolkit\Collection;
use TillProchaska\KirbyLocalizations\Localization;
use TillProchaska\KirbyLocalizations\LocalizedRoute;
use TillProchaska\KirbyLocalizations\LocalizedSite;

return [
    'system.loadPlugins:after' => function () {
        $localizations = site()->localizations();

        $customRoutes = (new Collection(kirby()->option('tillprochaska.localizations.routes', [])))
            ->map(function (array $route) use ($localizations) {
                $localizedRoute = new LocalizedRoute($route);

                return $localizedRoute->expand($localizations);
            })
            ->values()
        ;

        $customRoutes = array_merge(...$customRoutes);

        $homeRoute = new LocalizedRoute([
            'pattern' => '',
            'method' => 'ALL',
            'action' => function (Localization $localization) {
                $homePage = $localization->homePage() ?? $localization->site()->draft('home');
                $token = kirby()->request()->query()->get('token');

                if (!$homePage) {
                    return $this->next();
                }

                if ($homePage->isDraft() && !$homePage->isVerified($token)) {
                    return site()->visit($localization->errorPage());
                }

                return site()->visit($homePage);
            },
        ]);

        $defaultRoute = new LocalizedRoute([
            'pattern' => '(:all)',
            'method' => 'ALL',
            'action' => function (Localization $localization, string $path) {
                $page = kirby()->resolve($localization->code().'/'.$path);

                if (!$page || !is_a($page, Page::class)) {
                    return $this->next();
                }

                return site()->visit($page);
            },
        ]);

        $fileRoute = new LocalizedRoute([
            'pattern' => '(:all)',
            'method' => 'ALL',
            'action' => function (Localization $localization, string $path) {
                $file = kirby()->resolve($path);

                if (!$file || !is_a($file, File::class)) {
                    return $this->next();
                }

                return $file;
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
                ...$customRoutes,
                ...$homeRoute->expand($localizations),
                ...$defaultRoute->expand($localizations),
                ...$fileRoute->expand($localizations),
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
