<?php

namespace Kutny\NoBundleControllersBundle;

class TemplateResolver
{
    public function getTemplatePath($controllerClassName, $actionName)
    {
        return $this->getTemplateDirectory($controllerClassName) . '/' . $this->getTemplateFileName($actionName);
    }

    private function getTemplateDirectory($controllerClassName)
    {
        if (!preg_match('~^(.+)\\\[^\\\]+Controller$~', $controllerClassName, $controllerMatch)) {
            throw new \InvalidArgumentException(sprintf('The "%s" class does not look like a controller class (the class name must end with "Controller")', $controllerClassName));
        }

        return $this->convertBackslashToSlash($controllerMatch[1]);
    }

    private function getTemplateFileName($actionName)
    {
        if (!preg_match('~^(.+)Action$~', $actionName, $actionMatch)) {
            throw new \InvalidArgumentException(sprintf('The "%s" method does not look like an action method (it does not end with Action)', $actionName));
        }

        return $actionMatch[1] . '.html.twig';
    }

    private function convertBackslashToSlash($controllerName)
    {
        return str_replace('\\', '/', $controllerName);
    }

}
