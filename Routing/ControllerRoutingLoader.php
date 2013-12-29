<?php

namespace Kutny\NoBundleControllersBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class ControllerRoutingLoader extends Loader {

    private $controllerClasses = array();

    public function setControllerClasses(array $controllerClasses) {
        $this->controllerClasses = $controllerClasses;
    }

    public function load($resource, $type = null) {
        $collection = new RouteCollection();

        foreach ($this->controllerClasses as $serviceName => $controllerClass) {
            $importedRoutes = $this->import($controllerClass, 'annotation');

            foreach ($importedRoutes as $importedRoute) {
                $this->processRoute($importedRoute, $serviceName, $controllerClass);
            }

            $collection->addCollection($importedRoutes);
        }

        return $collection;
    }

    public function supports($resource, $type = null) {
        return 'kutny_no_bundle_controllers' === $type;
    }

    private function processRoute(Route $importedRoute, $serviceName, $controllerClass) {
        $controllerName = $importedRoute->getDefault('_controller');

        if ($this->containsClassNameNotServiceName($controllerName)) {
            // service has already been assigned to this controller -> skip
            return;
        }

        $controllerClassLength = strlen($controllerClass);
        $controllerClassFromName = substr($controllerName, 0, $controllerClassLength);

        if ($controllerClassFromName !== $controllerClass) {
            throw new \InvalidArgumentException('Something is wrong with controller class: ' . $controllerClass);
        }

        $importedRoute->setDefault(
            '_controller',
            $this->getServiceControllerName($serviceName, $controllerName, $controllerClassLength)
        );
    }

    private function containsClassNameNotServiceName($controllerName) {
        return strpos($controllerName, '::') === false;
    }

    private function getServiceControllerName($serviceName, $controllerName, $controllerClassLength) {
        return $serviceName . ':' . substr($controllerName, $controllerClassLength + 2);
    }

}
