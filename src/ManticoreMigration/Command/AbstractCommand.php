<?php

namespace SiroDiaz\ManticoreMigration\Command;

use Symfony\Component\Console\Command\Command;

class AbstractCommand extends Command
{
    protected static $defaultName = 'manticore';

    protected array $configuration;

    protected string $connection;

	public function __construct(array $configuration, string $name = null)
	{
		parent::__construct($name);
		$this->configuration = $configuration;
		$this->connection = $this->configuration['connection'];
	}
}
