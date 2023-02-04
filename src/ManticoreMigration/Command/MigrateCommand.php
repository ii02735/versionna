<?php

namespace SiroDiaz\ManticoreMigration\Command;

use Exception;
use SiroDiaz\ManticoreMigration\Manticore\ManticoreConnection;
use SiroDiaz\ManticoreMigration\MigrationDirector;
use SiroDiaz\ManticoreMigration\Storage\DatabaseConfiguration;
use SiroDiaz\ManticoreMigration\Storage\DatabaseConnection;
use SiroDiaz\ManticoreMigration\Storage\MigrationTable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrateCommand extends AbstractCommand
{
    protected static $defaultName = 'manticore:migrations:migrate';

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this->setDescription('Syncronizes ManticoreSearch with the migration files')
            ->setHelp(sprintf(
                '%sRun pending Manticoresearch migrations%s',
                PHP_EOL,
                PHP_EOL
            ));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
		$io = new SymfonyStyle($input, $output);
        $dbConnection = new DatabaseConnection(
            DatabaseConfiguration::fromDsn(
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

		if ($migrationTable->exists() && !$director->hasPendingMigrations()) {
            $io->warning('No pending migrations');
            return Command::SUCCESS;
        }

        try {
            $director->migrate();
        } catch (Exception $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
