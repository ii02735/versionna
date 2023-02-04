<?php

namespace SiroDiaz\ManticoreMigration\DependencyInjection;

use Exception;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class ManticoreMigrationsExtension extends Extension implements PrependExtensionInterface
{

	/**
	 * @throws Exception
	 */
	public function load(array $configs, ContainerBuilder $container)
	{
		$loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__).'/Resources/config'));
		$loader->load('services.yaml');
		$configuration = new Configuration();

		$defaultConfig = [

				'migrations_path' => 'manticore_migrations',
				'migration_table' => 'manticore_migrations',
				'table_prefix' => '',
				'manticore_connection' => [
					'host' => '127.0.0.1',
					'http_port' => 9308,
				],
				'connections' => [
					'mysql_connection' => 'mysql://root:root@127.0.0.1:3306/database'
				],
				'connection' => 'mysql_connection'

		];
		$config = $this->processConfiguration($configuration, $configs);

		$config = array_merge($defaultConfig, $config);

		$container->setParameter('manticore_migrations.manticore_host', $config['manticore_connection']['host']);
		$container->setParameter('manticore_migrations.manticore_port', $config['manticore_connection']['http_port'] );
		$container->setParameter('manticore_migrations.migrations_path', $config['migrations_path'] );
		$container->setParameter('manticore_migrations.database_migration_table', $config['migration_table'] );
		$container->setParameter('manticore_migrations.database_table_prefix', $config['table_prefix'] );
		$container->setParameter('manticore_migrations.database_connections', $config['connections']);
		$container->setParameter('manticore_migrations.connection', $config['connection']);

	}

	public function prepend(ContainerBuilder $container)
	{
		// TODO: Implement prepend() method.
	}
}
