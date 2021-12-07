<?php

use Kirby\Cms\Find;
use Kirby\Panel\Panel;

return [
    'localizations' => [
        'dialogs' => [
            'create' => [
                'pattern' => '/pages/(:any)/localizations/create',

                'load' => function (string $id) {
                    $page = Find::page($id);

                    if (!$page) {
                        throw new \Exception('Could not find page.');
                    }

                    $options = site()->localizations()
                        ->filter(function ($localization) use ($page) {
                            return !$page->isLocalized($localization, includeDrafts: true);
                        })
                        ->map(function ($localization) {
                            $name = $localization->name();
                            $code = strtoupper($localization->code());

                            return [
                                'value' => $localization->code(),
                                'text' => "{$code}: {$name}",
                            ];
                        })
                    ;

                    return [
                        'component' => 'k-form-dialog',
                        'props' => [
                            'submitButton' => 'Create',
                            'fields' => [
                                'localization' => [
                                    'type' => 'select',
                                    'label' => 'Create a new localization',
                                    'required' => true,
                                    'autofocus' => true,
                                    'options' => $options->values(),
                                ],
                            ],
                            'value' => [
                                'localization' => $options->first()['value'],
                            ],
                        ],
                    ];
                },

                'submit' => function (string $id) {
                    $page = Find::page($id);

                    if (!$page) {
                        throw new \Exception('Could not find page.');
                    }

                    $code = get('localization');
                    $localization = site()->localizations()->findBy('code', $code);

                    if (!$localization) {
                        throw new \Exception('Invalid localization');
                    }

                    $localizedPage = $page->localize($localization);

                    return Panel::go($localizedPage->panel()->path());
                },
            ],
        ],
    ],
];
