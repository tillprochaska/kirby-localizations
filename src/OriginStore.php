<?php

namespace TillProchaska\KirbyLocalizations;

use Kirby\Cms\Page;
use Kirby\Filesystem\File;

class OriginStore
{
    public function __construct(protected Page $page)
    {
    }

    public function exists(): bool
    {
        return $this->file()->exists();
    }

    public function delete(): self
    {
        $this->file()->delete();

        return $this;
    }

    public function set(Page $page): self
    {
        $this->file()->write(json_encode([
            $page->localization()->code(),
            $page->uid(),
        ]));

        return $this;
    }

    public function get(): ?Page
    {
        if (!$this->file()->exists()) {
            return null;
        }

        [$code, $uid] = json_decode(
            json: $this->file()->read(),
            associative: true,
        );

        $originLocalization = $this->page()->site()->localizations()->findBy('code', $code);
        $originParent = $this->page()->parent()->localized($originLocalization);

        return $originParent->childrenAndDrafts()->find($uid);
    }

    protected function page(): Page
    {
        return $this->page;
    }

    protected function file(): File
    {
        return new File($this->page()->contentFileDirectory().'/_origin.json');
    }
}
