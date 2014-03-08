<?php

namespace Kutny\NoBundleControllersBundle\TemplateResolver;

class ResolvedTemplate
{
	private $bundleName;
	private $controllerName;
	private $actionName;

	public function __construct($bundleName, $controllerName, $actionName) {
		$this->bundleName = $bundleName;
		$this->controllerName = $controllerName;
		$this->actionName = $actionName;
	}

	public function getActionName() {
		return $this->actionName;
	}

	public function getBundleName() {
		return $this->bundleName;
	}

	public function getControllerName() {
		return $this->controllerName;
	}

	public function getTemplatePath($templateExtension) {
		return '@' . $this->bundleName . '/' . $this->getControllerNameForwardSlash() . '/' . $this->actionName . $templateExtension;
	}

	private function getControllerNameForwardSlash() {
		return str_replace('\\', '/', $this->controllerName);
	}

}
