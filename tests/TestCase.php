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
    public const CONTENT_DIR = __DIR__.'/support/kirby/content';

    protected function beforeKirbyInit(): void
    {
        // Create temporary storage directory
        if (Dir::exists(static::CONTENT_DIR)) {
            Dir::remove(static::CONTENT_DIR);
        }

        Dir::make(static::CONTENT_DIR);

        // Register plugin
        if (null === App::plugin('tillprochaska/localizations')) {
            App::plugin('tillprochaska/localizations', array_merge(
                require __DIR__.'/../plugin/extensions.php',
                [
                    'root' => __DIR__.'/..',
                    'pageModels' => [
                        'test' => TestPage::class,
                        'localized-site' => LocalizedSitePage::class,
                    ],
                ],
            ));
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
            $dir = static::CONTENT_DIR.'/'.$localization['code'];
            $site = $dir.'/localized-site.txt';
            $error = $dir.'/error/test.txt';

            Data::write($site, [
                'title' => $localization['name'],
                'locale' => $localization['locale'],
                'default' => $localization['default'] ?? null,
            ]);

            Data::write($error, [
                'title' => 'Error',
            ]);
        }
    }

    protected function kirbyProps(): array
    {
        return [
            'roots' => [
                'index' => __DIR__.'/support/kirby',
            ],
            'urls' => [
                'index' => 'https://example.org',
            ],
        ];
    }
}
