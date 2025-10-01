<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use PHPUnit\Framework\Assert as PHPUnit;
use Symfony\Component\DomCrawler\Crawler;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}
