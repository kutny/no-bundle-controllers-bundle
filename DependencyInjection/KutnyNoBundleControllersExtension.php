<?php

namespace Kutny\NoBundleControllersBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class KutnyNoBundleControllersExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $templatesDir = $config['templates_dir'];
        if (null === $templatesDir) {
            $templatesDir = realpath($container->getParameter('kernel.root_dir') . '/../src');
        }

        $container->setParameter('kutny_no_bundle_controllers.templates_dir', $templatesDir);
        $container->setParameter('kutny_no_bundle_controllers.templates_namespaces', $config['templates_namespaces']);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
    }
}
