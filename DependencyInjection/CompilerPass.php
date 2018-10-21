<?php


namespace MadWizard\WebAuthnBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->getDefinition('session')
            ->addMethodCall('registerBag', [new Reference('madwizard_webauthn.context_session_bag')]);
    }
}
