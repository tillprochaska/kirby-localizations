<?php

namespace TillProchaska\KirbyLocalizations;

use Kirby\Cms\PageBlueprint;

class LocalizedPageBlueprint extends PageBlueprint
{
    protected function normalizeStatus($status): array
    {
        $normalized = parent::normalizeStatus($status);

        if (!$this->model->isHomePage()) {
            return $normalized;
        }

        if (empty($status) || !array_key_exists('draft', $status)) {
            return $normalized;
        }

        return [
            ...$normalized,
            'draft' => [
                'label' => $this->i18n('page.status.draft'),
                'text' => null,
            ],
        ];
    }
}
