<?php

namespace Kutny\NoBundleControllersBundle;

use Kutny\NoBundleControllersBundle\TemplateResolver\ResolvedTemplate;
use Symfony\Component\HttpKernel\KernelInterface;

class TemplateResolver
{
	private $kernel;

	public function __construct(KernelInterface $kernel)
	{
		$this->kernel = $kernel;
	}

	public function resolve($controllerClassName, $actionName)
	{
        $applyToNamespacesRegExp = $this->getApplyToNamespaceRegExp();

		if (preg_match($applyToNamespacesRegExp, $controllerClassName)) {
			if (!preg_match('~^[^\\\]+(?:\\\(.+))*\\\([^\\\]+)Controller$~', $controllerClassName, $controllerMatch)) {
				throw new \InvalidArgumentException(sprintf('The "%s" class does not look like a controller class (the class name must end with "Controller")', $controllerClassName));
			}
		}
		else {
			if (!preg_match('/Controller\\\(.+)Controller$/', $controllerClassName, $controllerMatch)) {
				throw new \InvalidArgumentException(sprintf('The "%s" class does not look like a controller class (it must be in a "Controller" sub-namespace and the class name must end with "Controller")', $controllerClassName));
			}
		}

		if (!preg_match('~^(.+)Action$~', $actionName, $actionMatch)) {
			throw new \InvalidArgumentException(sprintf('The "%s" method does not look like an action method (it does not end with Action)', $actionName));
		}

		$bundle = $this->getBundleForClass($controllerClassName, $applyToNamespacesRegExp);

		while ($bundleName = $bundle->getName()) {
			if (null === $parentBundleName = $bundle->getParent()) {
				$bundleName = $bundle->getName();

				break;
			}

			$bundles = $this->kernel->getBundle($parentBundleName, false);
			$bundle = array_pop($bundles);
		}

		return new ResolvedTemplate(substr($bundleName, 0, -6), $controllerMatch[1], $actionMatch[1]);
	}

	private function getBundleForClass($class, $applyToNamespacesRegExp)
	{
		$reflectionClass = new \ReflectionClass($class);
		$bundles = $this->kernel->getBundles();

        $mainBundleNamespace = $this->kernel->getContainer()->getParameter('kutny_no_bundle_controllers.main_bundle_namespace');

		if (preg_match($applyToNamespacesRegExp, $class)) {
			do {
				foreach ($bundles as $bundle) {
					if ($bundle->getNamespace() === $mainBundleNamespace) {
						return $bundle;
					}
				}
			} while ($reflectionClass);
		}

		do {
			$namespace = $reflectionClass->getNamespaceName();
			foreach ($bundles as $bundle) {
				if (0 === strpos($namespace, $bundle->getNamespace())) {
					return $bundle;
				}
			}
			$reflectionClass = $reflectionClass->getParentClass();
		} while ($reflectionClass);

		throw new \InvalidArgumentException(sprintf('The "%s" class does not belong to a registered bundle.', $class));
	}

    private function getApplyToNamespaceRegExp() {
        $namespaces = $this->kernel->getContainer()->getParameter('kutny_no_bundle_controllers.apply_to_namespaces');

        return '~^(' . implode('|', $namespaces) . ')~';
    }
}
