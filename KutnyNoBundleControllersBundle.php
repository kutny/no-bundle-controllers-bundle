<?php

namespace Kutny\NoBundleControllersBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class KutnyNoBundleControllersBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(
            new FindControllerServicesCompilerPass()
        );
    }

}
