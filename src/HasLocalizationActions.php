<?php

namespace TillProchaska\KirbyLocalizations;

use Kirby\Cms\Page;

trait HasLocalizationActions
{
    public function isLocalizable(Localization $localization): bool
    {
        return null !== $this->parent()->localized($localization);
    }

    public function linkLocalizedPage(Page $localizedPage): Page
    {
        if ($localizedPage->isOrigin()) {
            throw new \Exception('Can’t link this page, as it already has other localizations.');
        }

        $localization = $localizedPage->localization();

        if (!$this->isLocalizable($localization)) {
            throw new \Exception('Can’t localize this page, because the parent page is not localized.');
        }

        if ($this->localized($localizedPage->localization())) {
            throw new \Exception('Can’t localize this page: A localization already exists.');
        }

        if (!$this->parent()->localized($localization)->is($localizedPage->parent())) {
            throw new \Exception('Can’t localize this page: Localizations must have the same parent page.');
        }

        if (!$this->isOrigin() && $this->origin()) {
            $this->origin()->linkLocalizedPage($localizedPage);

            return $this;
        }

        $origin = $this->origin() ?? $this;

        $origin->localizationsStore()->set($localizedPage);
        $localizedPage->originStore()->set($origin);

        return $this;
    }

    public function localize(Localization $localization): Page
    {
        if (!$this->isLocalizable($localization)) {
            throw new \Exception('Can’t localize this page, because the parent page is not localized.');
        }

        if ($this->localized($localization)) {
            throw new \Exception('Can’t localize this page: A localization already exists.');
        }

        if (!$this->isOrigin() && $this->origin()) {
            $this->origin()->localize($localization);

            return $this;
        }

        $localizedPage = $this->copy([
            'slug' => $this->slug(),
            'parent' => $this->parent()->localized($localization),
            'isDraft' => true, // Always create new localizations as drafts
            'children' => false, // Don't copy children
            'files' => true, // Copy all children to new localization
        ]);

        // Kirby copies the complete page directory, including the
        // localizations file, so we need to delete that from the copy.
        if ($localizedPage->localizationsStore()->exists()) {
            $localizedPage->localizationsStore()->delete();
        }

        $this->linkLocalizedPage($localizedPage);

        return $localizedPage;
    }

    public function changeSlug(string $slug, string $languageCode = null): Page
    {
        $localizedPages = $this->localizations(includeDrafts: true)
            ->map(fn ($localization) => $this->localized($localization))
            ->filter(fn ($page) => !$page->is($this))
        ;

        $newPage = parent::changeSlug($slug, $languageCode);

        if ($this->slug() === $newPage->slug()) {
            return $newPage;
        }

        if ($localizedPages->count() <= 0) {
            return $newPage;
        }

        if ($newPage->isOrigin()) {
            foreach ($localizedPages as $page) {
                $page->originStore()->set($newPage);
            }

            return $newPage;
        }

        if ($origin = $newPage->origin()) {
            $origin->localizationsStore()->set($newPage);
        }

        return $newPage;
    }

    public function delete(bool $force = false): bool
    {
        $origin = $this->origin();
        $isOrigin = $this->isOrigin();
        $localizations = $this->localizations(includeDrafts: true);
        $localization = $this->localization();
        $localizedPages = $localizations
            ->map(fn ($localization) => $this->localized($localization))
            ->filter(fn ($page) => !$page->is($origin) && !$page->is($this))
        ;

        if ($isOrigin) {
            $newOrigin = $localizedPages->first();
        }

        parent::delete($force);

        if (!$isOrigin && $origin) {
            $origin->localizationsStore()->delete($localization);

            return true;
        }

        if ($localizedPages->count() <= 0) {
            return true;
        }

        $newOrigin->originStore()->delete();

        foreach ($localizedPages as $localizedPage) {
            if ($localizedPage->is($newOrigin)) {
                continue;
            }

            $newOrigin->localizationsStore()->set($localizedPage);
            $localizedPage->originStore()->set($newOrigin);
        }

        return true;
    }
}
