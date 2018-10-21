<?php


namespace MadWizard\WebAuthnBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class WebAuthnExtension extends Extension
{
    public function getAlias()
    {
        return 'madwizard_webauthn';
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);


        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );


        $loader->load('services.xml');

        $container->getDefinition('madwizard_webauthn.server')->setArgument(
            '$credentialStore',
            new Reference($config['credential_store']['service_id'])
        );
        $container->getDefinition('madwizard_webauthn.config_factory')->replaceArgument(0, $config);
    }
}
