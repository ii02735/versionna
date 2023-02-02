<?php

namespace SiroDiaz\ManticoreMigration\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

	public function getConfigTreeBuilder(): TreeBuilder
	{
		/**
		 * manticore_migrations:
		 *
		 *    migrations_path:
		 *
		 *    manticore_server:
		 *       host:
		 *       port:
		 *
		 *    database_server:
		 *       dsn:
		 *       driver:
		 *       host:
		 *       port:
		 *       table_prefix:
		 *
		 */
		$treeBuilder = new TreeBuilder('manticore_migrations');

		$treeBuilder->getRootNode()
			->children()
			->scalarNode('migrations_path')->end()
			->arrayNode('manticore_server')
			->children()
			->scalarNode('host')->end()
			->scalarNode('port')->end()
			->end()
			->end()
			->arrayNode('database_server')
			->children()
			->scalarNode('type')->end()
			->scalarNode('host')->end()
			->integerNode('port')->end()
			->integerNode('table_prefix')
			->end()
		;

		return $treeBuilder;
	}
}
