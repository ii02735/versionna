<?php

namespace SiroDiaz\ManticoreMigration\Command;

use SiroDiaz\ManticoreMigration\MigrationCreator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeMigrationCommand extends AbstractCommand
{
    protected static $defaultName = 'manticore:migrations:make';

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this->setDescription('Generate a new migration file')
            ->setHelp(sprintf(
                '%sGenerate a new migration file%s',
                PHP_EOL,
                PHP_EOL
            ));

        $this->addOption('description', null, InputOption::VALUE_OPTIONAL, 'The migration description or use case');
        $this->addArgument('name', InputArgument::REQUIRED, 'The migration name in snake_case_style');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
		$io = new SymfonyStyle($input,$output);

		if (is_null($this->configuration['migrations_path']))
		{
			$io->error('The migrations_path parameter must be filled');
			return Command::FAILURE;
		}

        $creator = new MigrationCreator(
            $this->configuration['migrations_path'],
            $input->getArgument('name'),
            $input->getOption('description') ?? '',
        );

        $creator->create();

        $output->writeln('<info>Migration created successfully</info>');

        return Command::SUCCESS;
    }
}
