<?php

namespace DirectokiBundle\DependencyInjection;


use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class DirectokiExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {

        $processedConfig = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter( 'directoki.read_only', $processedConfig[ 'read_only' ] );
    }

}
