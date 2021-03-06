<?php

namespace TillProchaska\KirbyLocalizations\Tests;

use Kirby\Cms\App;
use Kirby\Data\Data;
use Kirby\Filesystem\Dir;
use TillProchaska\KirbyTestUtils\TestCase as BaseTestCase;

/**
 * @internal
 * @coversNothing
 */
class TestCase extends BaseTestCase
{
    public const STORAGE_DIR = __DIR__.'/support/kirby/storage';

    protected function beforeKirbyInit(): void
    {
        // Create temporary storage directory
        if (Dir::exists(static::STORAGE_DIR)) {
            Dir::remove(static::STORAGE_DIR);
        }

        Dir::make(static::STORAGE_DIR);

        // Register plugin
        if (null === App::plugin('tillprochaska/localizations-tests')) {
            App::plugin('tillprochaska/localizations-tests', [
                'pageModels' => [
                    'test' => TestPage::class,
                    'error' => TestPage::class,
                    'home' => TestPage::class,
                    'localized-site' => LocalizedSitePage::class,
                ],
            ]);
        }

        // Create localized sites
        $localizations = [
            [
                'code' => 'en',
                'default' => true,
                'locale' => 'en_US',
                'name' => 'English',
            ],
            [
                'code' => 'de',
                'locale' => 'de_DE',
                'name' => 'Deutsch',
            ],
            [
                'code' => 'fr',
                'locale' => 'fr_FR',
                'name' => 'Français',
            ],
        ];

        foreach ($localizations as $localization) {
            // Create localized site directories manually (i.e. without
            // using Kirby’s API), because we want them to exist before
            // Kirby is initialized and plugin hooks run.
            $dir = static::STORAGE_DIR.'/content/'.$localization['code'];
            $site = $dir.'/localized-site.txt';
            $error = $dir.'/error/test.txt';

            Data::write($site, [
                'title' => $localization['name'],
                'locale' => $localization['locale'],
                'default' => $localization['default'] ?? null,
            ]);

            $errorTitle = [
                'en' => 'Error',
                'de' => 'Fehler',
                'fr' => 'Erreur',
            ];

            Data::write($error, [
                'title' => $errorTitle[$localization['code']],
            ]);
        }
    }

    protected function kirbyProps(): array
    {
        return [
            'roots' => [
                'index' => __DIR__.'/support/kirby',
                'site' => __DIR__.'/support/kirby/site',
                'content' => static::STORAGE_DIR.'/content',
                'accounts' => static::STORAGE_DIR.'/accounts',
            ],
            'urls' => [
                'index' => 'https://example.org',
            ],
        ];
    }
}
