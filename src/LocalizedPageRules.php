<?php

namespace TillProchaska\KirbyLocalizations;

use Kirby\Cms\Page;
use Kirby\Cms\PageRules;
use Kirby\Exception\PermissionException;

class LocalizedPageRules extends PageRules
{
    public static function changeStatusToDraft(Page $page)
    {
        if (!$page->permissions()->changeStatus()) {
            throw new PermissionException([
                'key' => 'page.changeStatus.permission',
                'data' => ['slug' => $page->slug()],
            ]);
        }

        if ($page->isErrorPage()) {
            throw new PermissionException([
                'key' => 'page.changeStatus.toDraft.invalid',
                'data' => ['slug' => $page->slug()],
            ]);
        }

        return true;
    }
}
