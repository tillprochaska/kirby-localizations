<?php

namespace TillProchaska\KirbyLocalizations\Tests;

use Kirby\Cms\Page;
use PHPUnit\Framework\Assert;

uses(TestCase::class)->in(__DIR__);

expect()->extend('toEqualObject', function (object $other) {
    Assert::assertObjectEquals(expected: $other, actual: $this->value, method: 'is');

    return $this;
});

expect()->extend('toBePage', function (string|Page $otherPage) {
    if (is_string($otherPage)) {
        $otherPage = page($otherPage);
    }

    $pageId = $this->value->id();
    $otherPageId = $otherPage->id();

    // `$page->is($other)` does not declare a type for `$other`,
    // so we cannot use PHPUnit’s `assertObjectEquals` assertion.
    Assert::assertTrue(
        condition: $this->value->is($otherPage),
        message: "Expected `{$pageId}` to equal page `{$otherPageId}`.",
    );

    return $this;
});

expect()->extend('toBeDraft', function (string $otherPageId) {
    $pageId = $this->value->id();

    // `$page->is($other)` does not declare a type for `$other`,
    // so we cannot use PHPUnit’s `assertObjectEquals` assertion.
    Assert::assertTrue(
        condition: $this->value->is(site()->draft($otherPageId)),
        message: "Expected `{$pageId}` to equal draft `{$otherPageId}`.",
    );

    return $this;
});

expect()->extend('jsonFile', function () {
    return $this->and(file_get_contents($this->value))->json();
});

expect()->extend('toBeFile', function () {
    return $this->and(file_exists($this->value))->toBeTrue();
});
