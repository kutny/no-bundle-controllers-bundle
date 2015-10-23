<?php

namespace Kutny\NoBundleControllersBundle;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class FindControllerServicesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $containerBuilder)
    {
        $this->processTaggedControllers($containerBuilder);
        $this->processTemplatesNamespace($containerBuilder);
    }

    private function processTaggedControllers(ContainerBuilder $containerBuilder)
    {
        $controllerClasses = [];

        $taggedControllerServices = $containerBuilder->findTaggedServiceIds('controller');

        foreach ($taggedControllerServices as $serviceName => $params) {
            $controllerClasses[$serviceName] = $containerBuilder->getDefinition($serviceName)->getClass();
        }

        $allServices = $containerBuilder->getServiceIds();

        foreach ($allServices as $serviceName) {
            if (substr($serviceName, -11) === '_controller') {
                $controllerClasses[$serviceName] = $containerBuilder->getDefinition($serviceName)->getClass();
            }
        }

        /** @var Definition $controllerRoutingLoaderDefinition */
        $controllerRoutingLoaderDefinition = $containerBuilder->getDefinition('kutny.no_bundle_controllers.controller_routing_loader');
        $controllerRoutingLoaderDefinition->addMethodCall('setControllerClasses', array($controllerClasses));
    }

    private function processTemplatesNamespace(ContainerBuilder $containerBuilder)
    {
        $templatesDir = $containerBuilder->getParameter('kutny_no_bundle_controllers.templates_dir');
        $templatesNamespaces = $containerBuilder->getParameter('kutny_no_bundle_controllers.templates_namespaces');

        if ($containerBuilder->hasDefinition('twig.loader.filesystem')) {
            $loader = $containerBuilder->getDefinition('twig.loader.filesystem');

            foreach ($templatesNamespaces as $templatesNamespace) {
                $loader->addMethodCall('addPath', [$templatesDir . '/' . $templatesNamespace, $templatesNamespace]);
            }
        }
    }

}
