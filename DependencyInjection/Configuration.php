<?php


namespace MadWizard\WebAuthnBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    private const NAME = 'madwizard_webauthn';

    public function getConfigTreeBuilder()
    {
        $name = self::NAME;
        if (\method_exists(TreeBuilder::class, 'getRootNode')) {
            // Symfony 4.2+
            $treeBuilder = new TreeBuilder(self::NAME);
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // Other versions
            $treeBuilder = new TreeBuilder();
            $rootNode = $treeBuilder->root($name);
        }

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
