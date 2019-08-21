<?php
/**
 * Copyright © Find Canary, LLC. All rights reserved.
 */
declare(strict_types = 1);

namespace FindCanary\SubscribeProTrial\Plugin\Checkout\Block;

class CartPlugin
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
     * @param \Magento\Checkout\Block\Cart $subject
     * @return void
     */
    public function beforeToHtml(\Magento\Checkout\Block\Cart $subject): void
    {
        $quote = $subject->getQuote();

        $customerId = (int)$quote->getCustomerId();
        $maxPurchases = $this->trialProductConfig->getMaxPurchases((int)$quote->getStoreId());
        if ($customerId === 0 || $maxPurchases === 0) {
            return;
        }

        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            $productId = $quoteItem->getProduct() ? (int)$quoteItem->getProduct()->getId() : 0;
            if ($productId === 0 || !$this->quoteItemHelper->isSubscriptionEnabled($quoteItem)) {
                continue;
            }

            $purchases = $this->trialRegistry->getPurchases($customerId, $productId);
            if ($purchases >= $maxPurchases) {
                $quoteItem->addErrorInfo(
                    'subscribepro',
                    'trial',
                    __('The trial product was already purchased %1 time(s).', $purchases)
                );
            }
        }
    }
}
