<?php

namespace AmzsCMS\BlockBundle\DependencyInjection;


use AmzsCMS\BlockBundle\Constant\BlockRoute;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AmzsBlockExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yaml');

        $container->setParameter('amz.user_bundle.default_password', $config['default_password']);
    }
    public function prepend(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('twig', [
            'globals' => [
                'amzs_admin_block_index' => BlockRoute::ROUTE_INDEX,
                'amzs_admin_block_data' => BlockRoute::ROUTE_DATA,
                'amzs_admin_block_add' => BlockRoute::ROUTE_ADD,
                'amzs_admin_block_edit' => BlockRoute::ROUTE_EDIT,
                'amzs_admin_block_delete' => BlockRoute::ROUTE_DELETE,

            ],
        ]);
    }
}