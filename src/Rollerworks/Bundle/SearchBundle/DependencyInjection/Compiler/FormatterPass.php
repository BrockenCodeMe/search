<?php

/*
 * This file is part of the RollerworksSearchBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\SearchBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to register tagged services for a chain-formatter.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class FormatterPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('rollerworks_search.chain_formatter')) {
            return;
        }

        $formatters = array();

        foreach ($container->findTaggedServiceIds('rollerworks_search.formatter') as $serviceId => $tag) {
            $priority = isset($tag[0]['priority']) ? $tag[0]['priority'] : 0;

            $formatters[$priority][] = new Reference($serviceId);
        }

        // sort by priority and flatten
        if (count($formatters)) {
            krsort($formatters);
            $formatters = call_user_func_array('array_merge', $formatters);
        }

        $definition = $container->getDefinition('rollerworks_search.chain_formatter');

        foreach ($formatters as $service) {
            $definition->addMethodCall('addFormatter', array($service));
        }
    }
}