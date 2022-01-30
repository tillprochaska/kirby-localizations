<?php

namespace TillProchaska\KirbyLocalizations\Tests;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * @internal
 * @coversNothing
 */
class TestCase extends BaseTestCase
{
    public const KIRBY_DIR = __DIR__.'/support/kirby';

    protected App $kirby;

    protected function setUp(): void
    {
        if (Dir::exists(static::KIRBY_DIR)) {
            Dir::remove(static::KIRBY_DIR);
        }

        Dir::make(static::KIRBY_DIR);

        $this->kirby = new App([
            'roots' => [
                'index' => __DIR__.'/support/kirby',
            ],
            'urls' => [
                'index' => 'https://example.org',
            ],
        ]);

        $this->loadPlugin();
        $this->setUpTestPageModels();
        $this->setUpLocalizedSites();
    }

    protected function loadPlugin(): void
    {
        $this->kirby->extend(require __DIR__.'/../plugin/extensions.php');
    }

    protected function setUpTestPageModels(): void
    {
        $this->kirby->extend([
            'pageModels' => [
                'test' => TestPage::class,
                'localized-site' => LocalizedSitePage::class,
            ],
        ]);
    }

    protected function setUpLocalizedSites(): void
    {
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

        $this->kirby->impersonate('kirby');

        foreach ($localizations as $localization) {
            $site = Page::create([
                'parent' => null,
                'slug' => $localization['code'],
                'template' => 'localized-site',
                'draft' => false,
                'content' => [
                    'title' => $localization['name'],
                    'locale' => $localization['locale'],
                    'default' => $localization['default'] ?? null,
                ],
            ]);

            Page::create([
                'parent' => $site,
                'slug' => 'error',
                'draft' => false,
                'template' => 'test',
                'content' => [
                    'title' => 'Error',
                ],
            ]);
        }
    }
}
