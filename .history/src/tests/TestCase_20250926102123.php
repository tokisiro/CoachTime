<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use PHPUnit\Framework\Assert as PHPUnit;
use Symfony\Component\DomCrawler\Crawler;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function assertElementDoesNotExist(string $selector, string $html): void
    {
        $crawler = new Crawler($html);
        $nodes = $crawler->filter($selector);
        PHPUnit::assertCount(0, $nodes, "Failed asserting that element '{$selector}' does not exist.");
    }


    protected function assertElementExists(string $selector, string $html): void
    {
        $crawler = new Crawler($html);
        $nodes = $crawler->filter($selector);
        PHPUnit::assertGreaterThan(0, $nodes->count(), "Failed asserting that element '{$selector}' exists.");
    }
}
