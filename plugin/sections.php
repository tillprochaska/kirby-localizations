<?php

return [
    'localizations' => [
        'computed' => [
            'link' => function () {
                return $this->model()->panel()->path();
            },

            'add' => function () {
                $maxCount = $this->model()->site()->localizations()->count();
                $currentCount = $this->model()->localizations(includeDrafts: true)->count();

                return $currentCount < $maxCount;
            },

            'localizations' => function () {
                return $this->model()
                    ->localizations(includeDrafts: true)
                    ->map(function ($localization) {
                        $localized = $this->model()->localized($localization);

                        return [
                            'code' => $localization->formattedCode(),
                            'name' => $localization->name(),
                            'status' => $localized->status(),
                            'link' => $localized->panel()->path(),
                            'isOrigin' => $localized->isOrigin(),
                            'isCurrent' => $localized->is($this->model()),
                        ];
                    })
                    ->values()
                ;
            },
        ],
    ],
];
