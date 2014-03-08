<?php

namespace Kutny\NoBundleControllersBundle;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Doctrine\Common\Util\ClassUtils;

class TemplateGuesser
{
	private $templateResolver;

	public function __construct(TemplateResolver $templateResolver) {
		$this->templateResolver = $templateResolver;
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

		$resolvedTemplate = $this->templateResolver->resolve($className, $controller[1]);

		return new TemplateReference(
			$resolvedTemplate->getBundleName() . 'Bundle',
			$resolvedTemplate->getControllerName(),
			$resolvedTemplate->getActionName(),
			$request->getRequestFormat(),
			$engine
		);
	}

}
