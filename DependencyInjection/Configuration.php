<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2019 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace SpomkyLabs\LexikJoseBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('lexik_jose');
        $rootNode = $this->getRootNode($treeBuilder, 'lexik_jose');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('server_name')
                    ->info('The name of the server. The recommended value is the server URL. This value will be used to check the issuer of the token.')
                    ->isRequired()
                ->end()
                ->scalarNode('audience')
                    ->info('The audience of the token. If not set `server_name` will be used.')
                ->end()
                ->integerNode('ttl')
                    ->info('The lifetime of a token (in second). For security reasons, a value below 1 hour (3600 sec) is recommended.')
                    ->min(0)
                    ->defaultValue(1800)
                ->end()
                ->scalarNode('key_set')
                    ->info('Private/Shared keys used by this server to validate signed tokens. Must be a JWKSet object.')
                    ->isRequired()
                ->end()
                ->scalarNode('key_index')
                    ->info('Index of the key in the key set used to sign the tokens. Could be an integer or the key ID.')
                    ->isRequired()
                ->end()
                ->scalarNode('signature_algorithm')
                    ->info('Signature algorithm used to sign the tokens.')
                    ->isRequired()
                ->end()
                ->arrayNode('claim_checked')
                    ->info('List of aliases to claim checkers.')
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                    ->treatNullLike([])
                    ->treatFalseLike([])
                ->end()
                ->arrayNode('mandatory_claims')
                    ->info('List of claims that must be present.')
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                    ->defaultValue([])
                    ->treatNullLike([])
                    ->treatFalseLike([])
                ->end()
            ->end();

        $this->addEncryptionSection($rootNode);

        return $treeBuilder;
    }

    /**
     * @return void
     */
    private function addEncryptionSection(NodeDefinition $node)
    {
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('encryption')
                    ->addDefaultsIfNotSet()
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('key_set')
                            ->info('Private/ Shared keys used by this server to decrypt the tokens. Must be a JWKSet object.')
                            ->isRequired()
                        ->end()
                        ->scalarNode('key_index')
                            ->isRequired()
                            ->info('Index of the key in the key set used to encrypt the tokens. Could be an integer or the key ID.')
                        ->end()
                        ->scalarNode('key_encryption_algorithm')
                            ->isRequired()
                            ->info('Key encryption algorithm used to encrypt the tokens.')
                        ->end()
                        ->scalarNode('content_encryption_algorithm')
                            ->info('Content encryption algorithm used to encrypt the tokens.')
                            ->isRequired()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function getRootNode(TreeBuilder $treeBuilder, string $name): NodeDefinition
    {
        if (!\method_exists($treeBuilder, 'getRootNode')) {
            return $treeBuilder->root($name);
        }

        return $treeBuilder->getRootNode();
    }
}
