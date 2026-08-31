<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Disable Vite manifest resolution in tests — assets are not built in CI/test env.
        $this->withoutVite();
    }
}
