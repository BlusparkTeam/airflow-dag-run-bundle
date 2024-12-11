<?php

declare(strict_types=1);

namespace Bluspark\AirflowDagRunBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class BlusparkAirflowDagRunBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('airflow_host')
                    ->defaultValue('https://airflow.example.org')
                    ->info('Airflow API hostname (must include scheme "http" or "https")')
                    ->end()
                ->scalarNode('airflow_dag_ids')
                    ->info('Your Airflow dag IDs')
                    ->isRequired()
                    ->validate()
                        ->ifTrue(function($value) {
                            return preg_match('/^([0-9a-zA-Z\-\._]+|[0-9a-zA-Z\-\._]+\:[0-9a-zA-Z\-\._]+(,?[0-9a-zA-Z\-\._]+\:[0-9a-zA-Z\-\._]+)*)$/', $value);
                        })
                        ->thenInvalid("please use this format: dag-id or dagName:dag-id,anotherDagName:another-dag-id")
                    ->end()
                ->end()
                ->scalarNode('airflow_username')
                    ->defaultValue('username')
                    ->info('Your Airflow API username')
                    ->end()
                ->scalarNode('airflow_password')
                    ->defaultValue('!ChangeMe!')
                    ->info('Your Airflow API password')
                    ->end()
            ->end()
        ;
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.xml');

        $container->services()
            ->get('bluspark_airflow_dag_run.http_client')
            ->args([
                $config['airflow_dag_ids'],
                $config['airflow_host'],
                $config['airflow_username'],
                $config['airflow_password'],
            ])
        ;
    }
}
