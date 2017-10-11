<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2017 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace SpomkyLabs\LexikJoseBundle\DependencyInjection;

use Jose\Bundle\JoseFramework\Helper\ConfigurationHelper;
use SpomkyLabs\LexikJoseBundle\Encoder\LexikJoseEncoder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class SpomkyLabsLexikJoseExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'lexik_jose';
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('lexik_jose_bridge.encoder.key_index', $config['key_index']);
        $container->setParameter('lexik_jose_bridge.encoder.signature_algorithm', $config['signature_algorithm']);
        $container->setParameter('lexik_jose_bridge.encoder.issuer', $config['server_name']);
        $container->setParameter('lexik_jose_bridge.encoder.ttl', $config['ttl']);

        $container->setParameter('lexik_jose_bridge.encoder.encryption.enabled', $config['encryption']['enabled']);
        if (true === $config['encryption']['enabled']) {
            $container->setParameter('lexik_jose_bridge.encoder.encryption.key_index', $config['key_index']);
            $container->setParameter('lexik_jose_bridge.encoder.encryption.key_encryption_algorithm', $config['encryption']['key_encryption_algorithm']);
            $container->setParameter('lexik_jose_bridge.encoder.encryption.content_encryption_algorithm', $config['encryption']['content_encryption_algorithm']);
        }

        $this->loadServices($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    public function loadServices(ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $isDebug = $container->getParameter('kernel.debug');
        $bridgeConfig = current($container->getExtensionConfig($this->getAlias()));

        ConfigurationHelper::addJWSBuilder($container, $this->getAlias(), [$bridgeConfig['signature_algorithm']], $isDebug);
        ConfigurationHelper::addJWSLoader($container, $this->getAlias(), [$bridgeConfig['signature_algorithm']], [], ['jws_compact'], false);
        ConfigurationHelper::addClaimChecker($container, $this->getAlias(), ['exp', 'iat', 'nbf', 'lexik_jose_audience', 'lexik_jose_issuer'], false);
        ConfigurationHelper::addKeyset($container, 'lexik_jose_bridge.signature', 'jwkset', ['value' => $bridgeConfig['key_set'], 'is_public' => $isDebug]);

        if (true === $bridgeConfig['encryption']['enabled']) {
            ConfigurationHelper::addJWEBuilder($container, $this->getAlias(), [$bridgeConfig['encryption']['key_encryption_algorithm']], [$bridgeConfig['encryption']['content_encryption_algorithm']], ['DEF'], $isDebug);
            ConfigurationHelper::addJWELoader($container, $this->getAlias(), [$bridgeConfig['encryption']['key_encryption_algorithm']], [$bridgeConfig['encryption']['content_encryption_algorithm']], ['DEF'], ['exp', 'iat', 'nbf', 'lexik_jose_audience', 'lexik_jose_issuer'], ['jwe_compact'], false);
            ConfigurationHelper::addKeyset($container, 'lexik_jose_bridge.encryption', 'jwkset', ['value' => $bridgeConfig['encryption']['key_set'], 'is_public' => $isDebug]);
        }

        $lexikConfig = ['encoder' => [
            'service' => LexikJoseEncoder::class
        ]];
        $container->prependExtensionConfig('lexik_jwt_authentication', $lexikConfig);
    }
}
