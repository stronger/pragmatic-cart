<?php

namespace Epsi\PragmaticCart\Promo;

use \Epsi\PragmaticCart\Checkout\LineItem;
use \Epsi\PragmaticCart\Store\Product;

/**
 * Promo to apply special price if quantity over threshold
 *
 * @author Michał Rudnicki <michal@epsi.pl>
 */
final class BulkDiscount extends Promo {

    /**
     * Target product the promo applies to
     * @var \Epsi\PragmaticCart\Store\Product
     */
    private $target;

    /**
     * Flag to apply promo only if the only promo on line item
     */
    private $exclusive;

    /**
     * Constructor
     *
     * @param string $description to be shown on receipt
     * @param \Epsi\PragmaticCart\Store\Product $target product the promo applies to
     * @param boolean $exclusive flag to make the promo only available as the only one on a product
     */
    public function __construct($description, Product $target, $exclusive) {
        parent::__construct($description);
        $this->target = $target;
        $this->exclusive = $exclusive;
    }

    /**
     * Return promo description
     *
     * @return string
     */
    public function getDescription() {
        return "{$this->description} ({$this->target->getUnitsInBulk()} for {$this->target->getPriceInBulk()})";
    }

    /**
     * Return discount amount for given line item
     *
     * Will only apply special price for multitudes of threshold quantity.
     * For the remainder regular price will be charged.
     *
     * @param \Epsi\PragmaticCart\Checkout\LineItem $item
     * @return int
     */
    public function getLineItemDiscount(LineItem $item) {
        // target product must match subject
        $subject = $item->getProduct();
        if ($this->target != $subject) {
            return 0;
        }

        // if exclusive no other promo allowed on subject
        if ($this->exclusive and count($item->getApplicablePromos()) > 0) {
            return 0;
        }
        
        // quantity over threshold
        $quantity = $item->getQuantity();
        $threshold = $subject->getUnitsInBulk();
        if ($quantity < $threshold) {
            return 0;
        }

        // calculate discount amount
        $quantityInPromo = floor($quantity / $threshold) * $threshold;
        $priceInBulkPerUnit = $subject->getPriceInBulk() / $threshold;
        return floor($quantityInPromo * ($subject->getPrice() * $threshold - $subject->getPriceInBulk()) / $threshold);
    }

}