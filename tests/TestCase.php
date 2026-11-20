<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Review tables live on their own connection and migration path (see config/database.php),
     * which RefreshDatabase does not know about.
     *
     * @return array<class-string, class-string>
     */
    protected function setUpTraits()
    {
        $uses = parent::setUpTraits();

        if (isset($uses[RefreshDatabase::class])) {
            $this->artisan('migrate', ['--database' => 'reviews', '--path' => 'database/migrations/reviews']);
        }

        return $uses;
    }
}
