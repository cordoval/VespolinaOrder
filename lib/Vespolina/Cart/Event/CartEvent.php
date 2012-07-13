<?php
/**
 * (c) 2012 Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Cart\Event;

use Symfony\Component\HttpKernel\Event\KernelEvent;
use Vespolina\Entity\OrderInterface;

class CartEvent extends KernelEvent
{
    /**
     * @var \Vespolina\Entity\OrderInterface $cart
     */
    protected $cart;

    public function __construct(OrderInterface $cart)
    {
        $this->cart = $cart;
    }

    public function getCart()
    {
        return $this->cart;
    }
}