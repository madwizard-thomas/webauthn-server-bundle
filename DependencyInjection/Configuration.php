<?php


namespace MadWizard\WebAuthnBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('madwizard_webauthn');

        // TODO stricter validation

        $rootNode
            ->children()
                ->arrayNode('relying_party')
                    ->isRequired()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('id')->end()
                        ->scalarNode('name')->isRequired()->end()
                        ->scalarNode('origin')->isRequired()->end()
                        ->scalarNode('icon')->end()
                    ->end()
                ->end() // relying party
                ->integerNode('challenge_length')->end()
                ->arrayNode('allowed_algorithms')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('credential_store')
                    ->isRequired()
                    ->children()
                        ->scalarNode('service_id')->isRequired()->end()
                    ->end()
                ->end() // credential store
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
