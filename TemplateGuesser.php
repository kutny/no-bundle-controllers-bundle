<?php

namespace Kutny\NoBundleControllersBundle;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Doctrine\Common\Util\ClassUtils;

class TemplateGuesser
{
	private $kernel;

	public function __construct(KernelInterface $kernel)
	{
		$this->kernel = $kernel;
	}

	/**
	 * Guesses and returns the template name to render based on the controller
	 * and action names.
	 *
	 * @param  array                     $controller An array storing the controller object and action method
	 * @param  Request                   $request    A Request instance
	 * @param  string                    $engine
	 * @return TemplateReference         template reference
	 * @throws \InvalidArgumentException
	 */
	public function guessTemplateName($controller, Request $request, $engine = 'twig')
	{
		$className = class_exists('Doctrine\Common\Util\ClassUtils') ? ClassUtils::getClass($controller[0]) : get_class($controller[0]);

        $applyToNamespacesRegExp = $this->getApplyToNamespaceRegExp();

		if (preg_match($applyToNamespacesRegExp, $className)) {
			if (!preg_match('~^[^\\\]+(?:\\\(.+))*\\\([^\\\]+)Controller$~', $className, $matchController)) {
				throw new \InvalidArgumentException(sprintf('The "%s" class does not look like a controller class (the class name must end with "Controller")', get_class($controller[0])));
			}
		}
		else {
			if (!preg_match('/Controller\\\(.+)Controller$/', $className, $matchController)) {
				throw new \InvalidArgumentException(sprintf('The "%s" class does not look like a controller class (it must be in a "Controller" sub-namespace and the class name must end with "Controller")', get_class($controller[0])));
			}
		}

		if (!preg_match('~^(.+)Action$~', $controller[1], $matchAction)) {
			throw new \InvalidArgumentException(sprintf('The "%s" method does not look like an action method (it does not end with Action)', $controller[1]));
		}

		$bundle = $this->getBundleForClass($className, $applyToNamespacesRegExp);

		while ($bundleName = $bundle->getName()) {
			if (null === $parentBundleName = $bundle->getParent()) {
				$bundleName = $bundle->getName();

				break;
			}

			$bundles = $this->kernel->getBundle($parentBundleName, false);
			$bundle = array_pop($bundles);
		}

		return new TemplateReference($bundleName, $matchController[1], $matchAction[1], $request->getRequestFormat(), $engine);
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
