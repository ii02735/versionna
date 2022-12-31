<?php

namespace Sharpen\Versionna\Command;

use Exception;
use Sharpen\Versionna\Manticore\ManticoreConnection;
use Sharpen\Versionna\MigrationDirector;
use Sharpen\Versionna\Storage\DatabaseConfiguration;
use Sharpen\Versionna\Storage\DatabaseConnection;
use Sharpen\Versionna\Storage\MigrationTable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FreshCommand extends AbstractCommand
{
    protected static $defaultName = 'fresh';

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Drop all tables and run again all migrations');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $commandExitCode = parent::execute($input, $output);

        if ($commandExitCode !== Command::SUCCESS) {
            return $commandExitCode;
        }

        $dbConnection = new DatabaseConnection(
            DatabaseConfiguration::fromArray(
                $this->configuration['connections'][$this->connection]
            )
        );

        $manticoreConnection = new ManticoreConnection(
            $this->configuration['manticore_connection']['host'],
            $this->configuration['manticore_connection']['port'],
        );

        $migrationTable = new MigrationTable(
            $dbConnection,
            $this->configuration['table_prefix'],
            $this->configuration['migration_table'],
        );

        $director = new MigrationDirector();

        $director
            ->dbConnection($dbConnection)
            ->manticoreConnection($manticoreConnection)
            ->migrationsPath($this->configuration['migrations_path'])
            ->migrationTable($migrationTable);

        if (!$migrationTable->exists()) {
            $output->writeln('<info>Migration table doesn\'t exist</info>');
        }

        try {
            $director->fresh();
        } catch (Exception $exception) {
            $output->writeln($exception->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
