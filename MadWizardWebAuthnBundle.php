<?php


namespace MadWizard\WebAuthnBundle;

use MadWizard\WebAuthnBundle\DependencyInjection\CompilerPass;
use MadWizard\WebAuthnBundle\DependencyInjection\WebAuthnExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MadWizardWebAuthnBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new CompilerPass());
    }

    public function getContainerExtension()
    {
        return new WebAuthnExtension();
    }
}
