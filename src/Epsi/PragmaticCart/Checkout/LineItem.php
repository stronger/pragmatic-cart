<?php

namespace Epsi\PragmaticCart\Checkout;

use \Epsi\PragmaticCart\Store\Product;

/**
 * Line item in cart
 *
 * Holds reference to purchased product along with its quantity.
 * Can provide a quote based on product, quantity and available promotions.
 *
 * @author Michał Rudnicki <michal@epsi.pl>
 */
final class LineItem implements Quote {

    /**
     * Product
     * @var \Epsi\PragmaticCart\Store\Product
     */
    private $product;

    /**
     * Quantity of product in cart
     * @var int
     */
    private $quantity;

    /**
     * Calculated discount amount
     * @var int
     */
    private $discount = 0;

    /**
     * List of available promotions
     * @var \Epsi\PragmaticCart\Promo\Promo[]
     */
    private $availablePromos = [];

    /**
     * List of applicable promotions
     * @var \Epsi\PragmaticCart\Promo\Promo[]
     */
    private $applicablePromos = [];

    /**
     * Flag if discount calculation already performed
     * @var int
     */
    private $calculated = false;

    /**
     * Constructor
     *
     * @param \Epsi\PragmaticCart\Store\Product $product
     * @param int $quantity of product
     * @param \Epsi\PragmaticCart\Promo\Promo[] $promos available
     */
    public function __construct(Product $product, $quantity, array $promos) {
        $this->product = $product;
        $this->quantity = $quantity;
        $this->availablePromos = $promos;
    }

    public function getProduct() {
        return $this->product;
    }

    public function getQuantity() {
        return $this->quantity;
    }

    public function modifyQuantityBy($quantity) {
        if ($this->quantity + $quantity > 0) {
            $this->quantity += $quantity;
        } else {
            $this->quantity = 0;
        }
        $this->calculated = false;
        return $this;
    }

    public function getAmount() {
        return $this->product->getPrice() * $this->quantity;
    }

    public function getDiscount() {
        $this->calculated or $this->calculate();
        return $this->discount;
    }

    public function getTotal() {
        return $this->getAmount() - $this->getDiscount();
    }

    public function getAvailablePromos() {
        return $this->availablePromos;
    }

    public function getApplicablePromos() {
        $this->calculated or $this->calculate();
        return $this->applicablePromos;
    }

    protected function calculate() {
        $this->calculated = true;
        $this->applicablePromos = [];
        $this->discount = 0;

        // apply promos to calculate discount
        foreach ($this->availablePromos as $promo) {
            $discount = $promo->getLineItemDiscount($this);
            if ($discount > 0) {
                $this->applicablePromos[] = $promo;
                $this->discount += $discount;
            }
        }

        // cap discount to amount level
        $amount = $this->getAmount();
        if ($this->discount > $amount) {
            $this->discount = $amount;
        }
    }

}