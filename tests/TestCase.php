<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $testEnvironment = [
            'APP_ENV' => 'testing',
            'APP_CONFIG_CACHE' => sys_get_temp_dir().DIRECTORY_SEPARATOR.'bookstats-test-config-'.getmypid().'.php',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => ':memory:',
            'DB_URL' => '',
        ];

        foreach ($testEnvironment as $key => $value) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        $app = parent::createApplication();

        if ($app['config']->get('database.default') !== 'sqlite'
            || $app['config']->get('database.connections.sqlite.database') !== ':memory:') {
            throw new \RuntimeException('Tests must use the isolated in-memory SQLite database.');
        }

        return $app;
    }
}
