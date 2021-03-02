<?php

use Robo\Tasks;

class RoboFile extends Tasks
{
    const ENVS = ['test', 'dev', 'prod'];
    const SRC_DIR = __DIR__ . '/src';
    const CONFIG_DIR = __DIR__ . '/config';
    const CACHE_DIR = __DIR__ . '/var/cache';
    const TESTS_DIR = __DIR__ . '/tests';
    const TEMPLATES_DIR = __DIR__ . '/templates';
    const CODECEPTION_SUITES = ['api', 'unit', 'acceptance'];
    const DEPLOYMENT_TAR = 'deployment.tar.gz';
    const DEPLOYMENT_FOLDER = 'deployment';

    public function install()
    {
        $this->stopOnFail(true);
        $this->createConfigs();
        $this->build();
        $this->dbCreateProd();
        $this->dbCreateTest();
        $this->dbMigrateProd();
        $this->dbMigrateTest();
        $this->test();
    }

    public function update()
    {
        $this->stopOnFail(true);
        $this->build();
        $this->clearCache();
        $this->dbMigrateProd();
        $this->dbMigrateTest();
        $this->build();
        $this->test();
    }

    public function deployFinalize($phpBin = null)
    {
        $this->stopOnFail(true);
        $this->clearCache('prod', $phpBin);
        $this->dbMigrateProd($phpBin);
        $this->clearCache('prod', $phpBin);
    }

    public function test()
    {
        $this->stopOnFail(true);
        $this->lintPhp();
        $this->lintYaml();
        $this->lintTwig();
        $this->phpcs();
        $this->taskExec('vendor/bin/codecept build')->run();
        // Temporary disabled
        // $this->phpStan();
        $this->codecept();
    }

    public function codecept($suite = null)
    {
        $suites = $suite ? [$suite] : self::CODECEPTION_SUITES;
        foreach ($suites as $suite) {
            $task = $this->taskCodecept('vendor/bin/codecept');
            $task = $task->suite($suite);
            $task->run();
        }
    }

    private function lintYaml()
    {
        $this
            ->taskExec('bin/console lint:yaml')
            ->args(self::CONFIG_DIR)
            ->run();
    }

    private function lintTwig()
    {
        $this
            ->taskExec('bin/console lint:twig')
            ->args(self::TEMPLATES_DIR)
            ->run();
    }

    public function lintPhp()
    {
        $this
            ->taskExec(vsprintf('find %s -name "*.php" -print0 | xargs -0 -n1 -P8 php -l', [
                implode(' ', [
                    self::SRC_DIR,
                    self::TESTS_DIR,
                ]),
            ]))
            ->run();
    }

    public function phpcs()
    {
        $this
            ->taskExec('vendor/bin/phpcs')
            ->args('--standard=.php_cs_ruleset.xml')
            ->args('--encoding=utf-8')
            ->args(sprintf('--ignore=%s/**/_bootstrap.php', self::TESTS_DIR))
            ->args(sprintf('--ignore=%s/_support/*Tester.php', self::TESTS_DIR))
            ->args(self::SRC_DIR)
            ->args(self::TESTS_DIR)
            ->run();

        $this
            ->taskExec('vendor/bin/php-cs-fixer fix')
            ->args('--dry-run')
            ->args('--diff')
            ->args('--config', '.php_cs')
            ->run();
    }

    public function phpcsFix()
    {
        $this
            ->taskExec('vendor/bin/php-cs-fixer fix')
            ->args('--diff')
            ->args('--config', '.php_cs')
            ->run();
    }

    public function phpStan()
    {
        $this
            ->taskExec('vendor/bin/phpstan')
            ->args('analyze')
            ->args(self::SRC_DIR)
            ->args('-c', 'phpstan.neon')
            ->args('--level', 7)
            ->run();
    }

    private function build()
    {
        $this->taskComposerInstall()
            ->optimizeAutoloader()
            ->run();
        $this->clearCache();
        $this->taskExec('bin/console assets:install')->run();
    }

    private function createConfigs()
    {
        $file = '/.env';
        if (!realpath($file) && realpath($file . '.dist')) {
            copy($file . '.dist', $file);
        }
    }

    private function dbCreateProd($phpBin = null) {
        $this
            ->taskExec(($phpBin !== null ? $phpBin . ' ' : '') . 'bin/console doctrine:database:create')
            ->args('--env=prod')
            ->run();
    }

    private function dbCreateTest($phpBin = null) {
        $this
            ->taskExec(($phpBin !== null ? $phpBin . ' ' : '') . 'bin/console doctrine:database:create')
            ->args('--env=test')
            ->run();
    }

    private function dbMigrateProd($phpBin = null)
    {
        $this
            ->taskExec(($phpBin !== null ? $phpBin . ' ' : '') . 'bin/console doctrine:migrations:migrate')
            ->args('--no-interaction')
            ->args('--env=prod')
            ->run();
    }

    private function dbMigrateTest($phpBin = null)
    {
        $this
            ->taskExec(($phpBin !== null ? $phpBin . ' ' : '') .'bin/console doctrine:migrations:migrate')
            ->args('--no-interaction')
            ->args('--env=test')
            ->run();
    }

    private function clearCache($env = null, $phpBin = null)
    {
        $warmUp = function ($env, $phpBin) {
            $this
                ->taskExec(($phpBin !== null ? $phpBin . ' ' : '') . 'bin/console cache:clear')
                ->args(sprintf('--env=%s', $env))
                ->run();
        };

        $this->stopOnFail(true);
        $this->taskExec('rm -rf var/cache/*')->run();

        if ($env) {
            $warmUp($env, $phpBin);
            return;
        }

        foreach (self::ENVS as $env) {
            $warmUp($env, $phpBin);
        }
    }
}
