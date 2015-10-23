<?php

namespace Kutny\NoBundleControllersBundle;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\TemplateReference;

class TemplateGuesser
{
    private $templatesDir;
    private $templateResolver;
    private $sensioTemplateGuesser;

    public function __construct(
        $templatesDir,
        TemplateResolver $templateResolver,
        \Sensio\Bundle\FrameworkExtraBundle\Templating\TemplateGuesser $sensioTemplateGuesser
    ) {
        $this->templatesDir = $templatesDir;
        $this->templateResolver = $templateResolver;
        $this->sensioTemplateGuesser = $sensioTemplateGuesser;
    }

    public function guessTemplateName($controller, Request $request, $engine = 'twig')
    {
        $controllerClassName = get_class($controller[0]);

        if ($request->attributes->get('_template') instanceof Template) {
            $templatePath = $this->templateResolver->getTemplatePath($controllerClassName, $controller[1]);

            return new TemplateReference($this->templatesDir . '/' . $templatePath, $engine);
        }

        return $this->sensioTemplateGuesser->guessTemplateName($controller, $request, $engine);
    }

}
