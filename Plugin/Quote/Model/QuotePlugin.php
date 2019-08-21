<?php
/**
 * Copyright © Find Canary, LLC. All rights reserved.
 */
declare(strict_types = 1);

namespace FindCanary\SubscribeProTrial\Plugin\Quote\Model;

use Magento\Quote\Model\Quote;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Swarming\SubscribePro\Model\Quote\SubscriptionOption\OptionProcessor;
use Swarming\SubscribePro\Api\Data\SubscriptionOptionInterface;
use Swarming\SubscribePro\Api\Data\ProductInterface as PlatformProductInterface;

class QuotePlugin
{
    /**
     * @var \FindCanary\SubscribeProTrial\Service\TrialRegistry
     */
    private $trialRegistry;

    /**
     * @var \FindCanary\SubscribeProTrial\Helper\QuoteItem
     */
    private $quoteItemHelper;

    /**
     * @var \FindCanary\SubscribeProTrial\Model\Config\TrialProduct
     */
    private $trialProductConfig;

    /**
     * @param \FindCanary\SubscribeProTrial\Service\TrialRegistry $trialRegistry
     * @param \FindCanary\SubscribeProTrial\Helper\QuoteItem $quoteItemHelper
     * @param \FindCanary\SubscribeProTrial\Model\Config\TrialProduct $trialProductConfig
     */
    public function __construct(
        \FindCanary\SubscribeProTrial\Service\TrialRegistry $trialRegistry,
        \FindCanary\SubscribeProTrial\Helper\QuoteItem $quoteItemHelper,
        \FindCanary\SubscribeProTrial\Model\Config\TrialProduct $trialProductConfig
    ) {
        $this->trialRegistry = $trialRegistry;
        $this->quoteItemHelper = $quoteItemHelper;
        $this->trialProductConfig = $trialProductConfig;
    }

    /**
     * @param \Magento\Quote\Model\Quote $subject
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\DataObject|null $request
     * @return void
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeAddProduct(Quote $subject, Product $product, $request = null): void
    {
        $spOption = $request[OptionProcessor::KEY_SUBSCRIPTION_OPTION][SubscriptionOptionInterface::OPTION] ?? null;
        $isSpFulfilling = $request[OptionProcessor::KEY_SUBSCRIPTION_OPTION][SubscriptionOptionInterface::IS_FULFILLING]
            ?? false;

        $customerId = (int)$subject->getCustomerId();
        $maxPurchases = $this->trialProductConfig->getMaxPurchases((int)$subject->getStoreId());

        if (($spOption !== PlatformProductInterface::SO_SUBSCRIPTION && !$isSpFulfilling)
            || $customerId === 0
            || $maxPurchases === 0
        ) {
            return;
        }

        if ($this->trialRegistry->getPurchases($customerId, (int)$product->getId()) >= $maxPurchases) {
            throw new LocalizedException(__('Trial products may only be purchased once per customer.'));
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote $subject
     * @param \Magento\Quote\Model\Quote $quote
     * @return void
     */
    public function beforeMerge(Quote $subject, Quote $quote): void
    {
        $customerId = (int)$subject->getCustomerId();
        $maxPurchases = $this->trialProductConfig->getMaxPurchases((int)$subject->getStoreId());
        if ($customerId === 0 || $maxPurchases === 0) {
            return;
        }

        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            $productId = $quoteItem->getProduct() ? (int)$quoteItem->getProduct()->getId() : 0;
            if ($productId > 0
                && $this->quoteItemHelper->isSubscriptionEnabled($quoteItem)
                && $this->trialRegistry->getPurchases($customerId, $productId) >= $maxPurchases
            ) {
                $quote->removeItem($quoteItem->getItemId());
            }
        }
    }
}
