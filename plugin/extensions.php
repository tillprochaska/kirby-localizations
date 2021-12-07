<?php

use TillProchaska\KirbyLocalizations\LocalizedSite;

return [
    'siteMethods' => require __DIR__.'/site-methods.php',
    'hooks' => require __DIR__.'/hooks.php',
    'areas' => require __DIR__.'/areas.php',
    'sections' => require __DIR__.'/sections.php',
    'pageModels' => [
        'localized-site' => LocalizedSite::class,
    ],
];
