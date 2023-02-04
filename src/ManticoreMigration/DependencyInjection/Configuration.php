<?php

namespace SiroDiaz\ManticoreMigration\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class has the purpose to parse a provided user configuration
 * (from a yaml file for example)
 */
class Configuration implements ConfigurationInterface
{

	public function getConfigTreeBuilder(): TreeBuilder
	{
		/**
		 * The desired structure of the file is the following
		 * (from the developer's project : ./config/packages/manticore_migrations.yaml)
		 *
		 * manticore_migrations:
		 *
		 *    migrations_path:
		 *
		 *    manticore_server:
		 *       host:
		 *       port:
		 *
		 *    connections:
		 *       connection1: connection1_dsn
		 *       connection2: connection2_dsn
		 *       .etc
		 *
		 *    connection: "chosen connection"
		 *
		 */
		$treeBuilder = new TreeBuilder('manticore_migrations');

		$treeBuilder->getRootNode()
			->children()
				->scalarNode('migrations_path')->end()
				->scalarNode('table_prefix')->end()
				->arrayNode('manticore_connection')
					->children()
					->scalarNode('host')->end()
					->scalarNode('http_port')->end()
					->end()
				->end()
				->variableNode('connections')
			    ->end()
				->scalarNode('connection')->end()
			->end()
		;

		return $treeBuilder;
	}
}
