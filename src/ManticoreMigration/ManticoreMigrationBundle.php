<?php

namespace SiroDiaz\ManticoreMigration;

use Exception;
use SiroDiaz\ManticoreMigration\DependencyInjection\ManticoreMigrationExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ManticoreMigrationBundle extends Bundle
{
	/**
	 * @throws Exception
	 */
	public function build(ContainerBuilder $container): void
	{
		parent::build($container);
		$extension = new ManticoreMigrationExtension();
		$extension->load([], $container);
	}
}
