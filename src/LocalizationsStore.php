<?php

namespace TillProchaska\KirbyLocalizations;

use Kirby\Cms\Page;
use Kirby\Filesystem\File;

class LocalizationsStore
{
    public function __construct(protected Page $page)
    {
    }

    public function exists(): bool
    {
        return $this->file()->exists();
    }

    public function delete(?Localization $localization = null): self
    {
        if (!$localization) {
            $this->file()->delete();

            return $this;
        }

        $localizations = $this->read();
        $code = $localization->code();

        if (isset($localizations[$code])) {
            unset($localizations[$code]);
        }

        if (count($localizations) <= 0) {
            $this->delete();

            return $this;
        }

        return $this->write($localizations);
    }

    public function set(Page $page): self
    {
        $localizations = array_merge($this->read(), [
            $page->localization()->code() => $page?->uid(),
        ]);

        $this->write($localizations);

        return $this;
    }

    public function get(Localization $localization): ?Page
    {
        $uid = $this->read()[$localization->code()] ?? null;

        if (!$uid) {
            return null;
        }

        $localizedParent = $this->page()->parent()->localized($localization);

        return $localizedParent->childrenAndDrafts()->find($uid);
    }

    protected function page(): Page
    {
        return $this->page;
    }

    protected function file(): File
    {
        return new File($this->page()->contentFileDirectory().'/_localizations.json');
    }

    protected function read(): array
    {
        if (!$this->file()->exists()) {
            return [];
        }

        return json_decode(
            json: $this->file()->read(),
            associative: true,
        );
    }

    protected function write(array $data): self
    {
        $this->file()->write(json_encode($data));

        return $this;
    }
}
