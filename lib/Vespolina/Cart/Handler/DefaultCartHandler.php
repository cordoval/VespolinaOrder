<?php
/**
 * (c) 2012 Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Cart\Handler;

use Vespolina\Cart\Handler\AbstractCartHandler;
use Vespolina\Entity\Order\ItemInterface;
use Vespolina\Entity\Order\CartInterface;
use Vespolina\Entity\Order\OrderInterface;

/**
 * DefaultHandler for the cart
 */
class DefaultCartHandler extends  AbstractCartHandler
{
    protected $fulfillmentPricingEnabled;
    protected $taxPricingEnabled;

    public function __construct()
    {
        $this->fulfillmentPricingEnabled = true;
        $this->taxPricingEnabled = true;
    }

    public function determineCartItemPrices(ItemInterface $cartItem, $pricingContext)
    {
        $pricing = $cartItem->getProduct()->getPricing();
        $pricingSet = $cartItem->getPricingSet();
        $unitNett = $pricing['unitPriceTotal'];
        $upChargeNett = 0;

        //Add additional upcharges for a chosen product option
        $upChargeNett = $this->determineCartItemUpCharge($cartItem, $pricingContext);

        //Calculate fulfillment costs (eg. shipping, packaging cost)
        if ($this->fulfillmentPricingEnabled) {

            //$this->determineCartItemFulfillmentPrices($cartItem, $pricingContext);
        }

        $totalNett = ( $cartItem->getQuantity() * $unitNett ) + $upChargeNett;
        $pricingSet->set('upchargeNett', $upChargeNett);
        $pricingSet->set('totalNett', $totalNett);

        //Determine item level taxes
        $taxationEnabled = $cartItem->getCart()->getAttribute('taxation_enabled');

        if ($taxationEnabled) {

            $this->determineCartItemTaxes(
                    $cartItem,
                    array('totalNett' => $totalNett),
                    $pricingSet,
                    $pricingContext);
        }

        $pricingSet->set('totalGross', $pricingContext['totalNett'] + $pricingContext['totalTax']);
        // set the total price in the cart item
        $cartItem->setTotalPrice($totalNett);   //Todo: remove
    }

    public function getTypes()
    {
        return 'default';
    }

    protected function determineCartItemUpCharge(ItemInterface $cartItem, $pricingContext)
    {
        $upCharge = 0;

        foreach($cartItem->getOptions() as $type => $value) {

            if ($productOption = $cartItem->getProduct()->getOptionSet(array($type => $value))) {
                $upCharge += $productOption->getUpcharge();
            }
        }

        return $upCharge;
    }

    protected function determineCartItemTaxes(ItemInterface $cartItem, array $pricesToBeTaxed, $cartItemPricingSet, $pricingContext)
    {

        $rate = 0;
        $taxes = array();
        $totalTax = 0;

        //We currently assume that all cart items use the default tax zone and associated tax rate
        $taxZone = $pricingContext->get('taxZone');

        if (null != $taxZone) {
            $rate = $taxZone->getDefaultRate();
        }

        //Each price which should be taxed is aggregated into one value, for instance shipment tax + sales tax
        //This is especially true for flat rate taxes, but insufficient for mixed tax rates
        foreach($pricesToBeTaxed as $name => $value) {

            $taxValue = $rate * $value / 100;
            $totalTax += $taxValue;
        }
        $cartItemPricingSet->set('totalTax', $totalTax);
  }

    protected function determineCartFulfillmentPrices(CartInterface $cart, $pricingContext)
    {
        //Additional fulfillment to be applied not related to cart item taxes
        // eg. fixed fulfillment fee
    }

    protected function sumItemPrices(ItemInterface $cartItem, $pricingContext)
    {
        return null;
        //$pricingContext['total'] += $cartItem->getPrice('total');
    }
}
