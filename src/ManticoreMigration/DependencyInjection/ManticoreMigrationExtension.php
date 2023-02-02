<?php

namespace SiroDiaz\ManticoreMigration\DependencyInjection;

use Exception;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class ManticoreMigrationExtension extends Extension implements PrependExtensionInterface
{

	/**
	 * @throws Exception
	 */
	public function load(array $configs, ContainerBuilder $container)
	{
		$loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__).'/Resources/config'));
		$loader->load('services.yaml');
		$configuration = new Configuration();

		$config = $this->processConfiguration($configuration, $configs);

		$container->setParameter('manticore_migrations.manticore_path', $config['manticore_migrations']['migrations_path']);
		$container->setParameter('manticore_migrations.manticore_host', $config['manticore_migrations']['manticore_instance']['host']);
		$container->setParameter('manticore_migrations.manticore_port', $config['manticore_migrations']['manticore_instance']['port']);
		$container->setParameter('manticore_migrations.migrations_path', $config['manticore_migrations']['migrations_path']);
		$container->setParameter('manticore_migrations.database_driver', $config['manticore_migrations']['database_server']['driver']);
		$container->setParameter('manticore_migrations.database_host', $config['manticore_migrations']['database_server']['host']);
		$container->setParameter('manticore_migrations.database_port', $config['manticore_migrations']['database_server']['port']);
		$container->setParameter('manticore_migrations.database_name', $config['manticore_migrations']['database_server']['name']);
		$container->setParameter('manticore_migrations.database_user', $config['manticore_migrations']['database_server']['user']);
		$container->setParameter('manticore_migrations.database_password', $config['manticore_migrations']['database_server']['password']);
		$container->setParameter('manticore_migrations.database_table_prefix', $config['manticore_migrations']['database_server']['table_prefix']);

		if (key_exists('dsn', $config['manticore_migrations']['database_server']))
		{
			$container->setParameter('manticore_migrations.database_dsn', $config['manticore_migrations']['database_server']['dsn']);
		}




	}

	public function prepend(ContainerBuilder $container)
	{
		// TODO: Implement prepend() method.
	}
}
