<?php

declare(strict_types=1);

namespace Avanzu\AdminThemeBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AvanzuAdminThemeExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $baseConfiguration = new Configuration();

        // Load the configuration from files
        try {
            $config = $this->processConfiguration($baseConfiguration, $configs);
        } catch (InvalidConfigurationException $e) {
            // Fallback: ignore invalid config from the container user config file and abort use configuration in load
            echo 'AvanzuAdminBundle: invalid config (load): ' . $e->getMessage() . PHP_EOL
                . '    The config options for the bundle AvanzuAdminBundle were skipped' . PHP_EOL;
            $config = [];
        }

        // Use the config only if it is fully validated from the processed configuration
        if (!empty($config)) {
            // Set the parameters from config files
            $container->setParameter('avanzu_admin_theme.bower_bin', (string)($config['bower_bin'] ?? ''));
            $container->setParameter('avanzu_admin_theme.use_twig', (bool)($config['use_twig'] ?? false));
            $container->setParameter('avanzu_admin_theme.options', (array)($config['options'] ?? []));
        }

        // Load the services (with parameters loaded), since twig require theme_manager service
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * Allow an extension to prepend the extension configurations.
     *
     * @see https://symfony.com/doc/current/bundles/prepend_extension.html
     */
    public function prepend(ContainerBuilder $container): void
    {
        $baseConfiguration = new Configuration();

        // Load the configuration from files

        // The configuration of AvanzuAdminThemeExtension
        $configs = $container->getExtensionConfig($this->getAlias());

        try {
            // use the Configuration class to generate a config array with the config extension
            $config = $this->processConfiguration($baseConfiguration, $configs);
        } catch (InvalidConfigurationException $e) {
            // Fallback: ignore invalid config from the container user config file and abort use configuration in prepend
            echo 'AvanzuAdminBundle: invalid config (prepend): ' . $e->getMessage() . PHP_EOL
                . '    The config options for the bundle AvanzuAdminBundle were skipped' . PHP_EOL;
            $config = [];
        }

        // Create the parameter for the service (dependency with avanzu_admin_theme.extension.class) even if empty config
        $container->setParameter(
            'avanzu_admin_theme.options',
            (array)($config['options'] ?? [])
        );

        // Use the config only if it is fully validated from the processed configuration
        if (!empty($config)) {
            // Set the parameters from config files
            $container->setParameter(
                'avanzu_admin_theme.bower_bin',
                (string)($config['bower_bin'] ?? '')
            );
            $container->setParameter(
                'avanzu_admin_theme.use_twig',
                (bool)($config['use_twig'] ?? false)
            );

            /**
             * Get all the bundles
             *
             * @var array $bundles
             */
            $bundles = $container->getParameter('kernel.bundles');

            // Inject in twig global config the theme_manager service
            if ($config['use_twig'] && isset($bundles['TwigBundle'])) {
                $container->prependExtensionConfig(
                    'twig',
                    [
                        'form_theme' => [
                            '@AvanzuAdminTheme/layout/form-theme.html.twig',
                        ],
                        'globals'    => [
                            'admin_theme' => '@avanzu_admin_theme.theme_manager',
                        ],
                    ]
                );
            }

            if ($config['use_assetic'] && isset($bundles['AsseticBundle'])) {
                $assets = include __DIR__ . '/../Resources/config/assets.php';

                $container->prependExtensionConfig(
                    'assetic',
                    [
                        'assets'  => $assets,
                        'bundles' => [
                            'AvanzuAdminThemeBundle',
                        ],
                    ]
                );
            }
        }
    }
}
