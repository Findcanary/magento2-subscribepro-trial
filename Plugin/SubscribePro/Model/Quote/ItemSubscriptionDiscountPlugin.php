<?php
/**
 * Copyright © Find Canary, LLC. All rights reserved.
 */
declare(strict_types = 1);

namespace FindCanary\SubscribeProTrial\Plugin\SubscribePro\Model\Quote;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem as QuoteItem;
use Swarming\SubscribePro\Model\Quote\ItemSubscriptionDiscount;

class ItemSubscriptionDiscountPlugin
{
    /**
     * @var \Swarming\SubscribePro\Platform\Manager\Product
     */
    private $platformProductManager;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @param \Swarming\SubscribePro\Platform\Manager\Product $platformProductManager
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Swarming\SubscribePro\Platform\Manager\Product $platformProductManager,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        $this->platformProductManager = $platformProductManager;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param \Swarming\SubscribePro\Model\Quote\ItemSubscriptionDiscount $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param $itemBasePrice
     * @param callable $rollbackCallback
     * @return void
     */
    public function aroundProcessSubscriptionDiscount(
        ItemSubscriptionDiscount $subject,
        \Closure $proceed,
        QuoteItem $item,
        $itemBasePrice,
        callable $rollbackCallback
    ): void {
        $storeId = (int)$item->getQuote()->getStoreId();
        $platformProduct = $this->getPlatformProduct($item);

        if ($platformProduct->getIsTrialProduct()) {
            $rollbackCallback($item);
            $this->setSubscriptionDiscount($item, $platformProduct, (float)$itemBasePrice, $storeId);
            $this->addDiscountDescription($item);
            return;
        }
        $proceed($item, $itemBasePrice, $rollbackCallback);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return \Swarming\SubscribePro\Api\Data\ProductInterface
     */
    private function getPlatformProduct(QuoteItem $item): \Swarming\SubscribePro\Api\Data\ProductInterface
    {
        $sku = $item->getProduct()->getData(ProductInterface::SKU);
        return $this->platformProductManager->getProduct($sku, $item->getQuote()->getStore()->getWebsiteId());
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param \Swarming\SubscribePro\Api\Data\ProductInterface $platformProduct
     * @param float $itemBasePrice
     * @param int $storeId
     * @return void
     */
    private function setSubscriptionDiscount(QuoteItem $item, $platformProduct, $itemBasePrice, $storeId): void
    {
        $baseSubscriptionDiscount = $itemBasePrice - $platformProduct->getTrialPrice();
        $baseSubscriptionDiscount = max($baseSubscriptionDiscount, 0);
        $subscriptionDiscount = $this->priceCurrency->convertAndRound($baseSubscriptionDiscount, $storeId);

        $item->setDiscountAmount($subscriptionDiscount);
        $item->setBaseDiscountAmount($baseSubscriptionDiscount);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return void
     */
    protected function addDiscountDescription(QuoteItem $item): void
    {
        $discountDescriptions = $item->getAddress()->getDiscountDescriptionArray();
        $discountDescriptions['trial'] = __('Trial Product');
        $item->getAddress()->setDiscountDescriptionArray($discountDescriptions);
    }
}
