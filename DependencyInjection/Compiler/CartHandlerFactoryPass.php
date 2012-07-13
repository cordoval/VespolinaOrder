<?php
/**
 * (c) 2012 Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Vespolina\CartBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class CartHandlerFactoryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $factory = $container->getDefinition('vespolina.cart.pricing_provider');

        foreach ($container->findTaggedServiceIds('vespolina.cart_handler') as $id => $attr) {
            $factory->addMethodCall('addCartHandler', array(new Reference($id)));
        }

        //If a taxation manager exists, add it to the provider provider
        if (true === $container->hasDefinition('vespolina.taxation.taxation_manager')) {

            $taxationManagerDefinition = $container->getDefinition('vespolina.taxation.taxation_manager');
            $factory->addMethodCall('setTaxationManager', array($taxationManagerDefinition));
        }

    }
}
