<?php

use TillProchaska\KirbyLocalizations\Localization;

it('expands and registers custom routes', function () {
    $this->withOption('tillprochaska.localizations.routes', [
        [
            'pattern' => '/custom-route',
            'action' => function (Localization $localization) {
                return $localization->code();
            },
        ],
    ]);

    $en = $this->get('/custom-route');
    $de = $this->get('/de/custom-route');

    expect($en)->code()->toEqual(200)->body()->toEqual('en');
    expect($de)->code()->toEqual(200)->body()->toEqual('de');
});
