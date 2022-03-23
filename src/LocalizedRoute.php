<?php

namespace TillProchaska\KirbyLocalizations;

use Kirby\Http\Path;

class LocalizedRoute
{
    public function __construct(protected array $options)
    {
    }

    public function expand(Localizations $localizations): array
    {
        // Routes are sorted with the route for the default localization
        // coming last, as its the least specific route. Consider a route
        // that matches anything. This would yield expanded routes like this:
        // ['/(:any)', '/de/(:any)', '/fr/(:any)', ...].
        //
        // If the route for the default language came first, it would match
        // all requests, even something like `/de/hallo-welt`.

        return $localizations
            ->clone()
            ->sort(fn ($localization) => $localization->isDefault() ? 1 : -1)
            ->map(function ($localization) {
                return array_merge($this->options, [
                    'pattern' => $this->expandPattern($localization),
                    'action' => $this->expandAction($localization),
                ]);
            })
            ->values()
        ;
    }

    protected function expandPattern(Localization $localization): string
    {
        if ($pattern = $this->options['pattern']) {
            return (new Path($pattern))
                ->prepend($localization->path())
                ->toString()
            ;
        }

        if ('' === $localization->path()) {
            return '/';
        }

        return $localization->path();
    }

    protected function expandAction(Localization $localization): callable
    {
        $action = \Closure::fromCallable($this->options['action']);

        return function (...$args) use ($action, $localization) {
            if ($localization->locale()) {
                setlocale(LC_ALL, $localization->locale());

                // If multilang mode is inactive, Kirby sets the locale
                // everytime you call `resolve`. That’s why we also need
                // to set the locale configuration option (which Kirby
                // uses when it sets the locale).

                kirby()->extend([
                    'options' => [
                        'locale' => $localization->locale(),
                    ],
                ]);
            }

            return $action->call($this, $localization, ...$args);
        };
    }
}
