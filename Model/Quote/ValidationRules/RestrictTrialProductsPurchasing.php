<?php
/**
 * Copyright © Find Canary, LLC. All rights reserved.
 */
declare(strict_types = 1);

namespace FindCanary\SubscribeProTrial\Model\Quote\ValidationRules;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ValidationRules\QuoteValidationRuleInterface;

class RestrictTrialProductsPurchasing implements QuoteValidationRuleInterface
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
     * @var \Magento\Framework\Validation\ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @param \FindCanary\SubscribeProTrial\Service\TrialRegistry $trialRegistry
     * @param \FindCanary\SubscribeProTrial\Helper\QuoteItem $quoteItemHelper
     * @param \FindCanary\SubscribeProTrial\Model\Config\TrialProduct $trialProductConfig
     * @param \Magento\Framework\Validation\ValidationResultFactory $validationResultFactory
     */
    public function __construct(
        \FindCanary\SubscribeProTrial\Service\TrialRegistry $trialRegistry,
        \FindCanary\SubscribeProTrial\Helper\QuoteItem $quoteItemHelper,
        \FindCanary\SubscribeProTrial\Model\Config\TrialProduct $trialProductConfig,
        \Magento\Framework\Validation\ValidationResultFactory $validationResultFactory
    ) {
        $this->trialRegistry = $trialRegistry;
        $this->quoteItemHelper = $quoteItemHelper;
        $this->trialProductConfig = $trialProductConfig;
        $this->validationResultFactory = $validationResultFactory;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return array
     */
    public function validate(Quote $quote): array
    {
        $validationErrors = [];

        $customerId = (int)$quote->getCustomerId();
        $maxPurchases = $this->trialProductConfig->getMaxPurchases((int)$quote->getStoreId());

        if ($customerId !== 0 && $maxPurchases > 0) {
            $validationErrors = $this->validateQuoteItems($quote->getAllVisibleItems(), $customerId, $maxPurchases);
        }

        return [$this->validationResultFactory->create(['errors' => $validationErrors])];
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item[] $quoteItems
     * @param int $customerId
     * @param int $maxPurchases
     * @return array
     */
    private function validateQuoteItems(array $quoteItems, int $customerId, int $maxPurchases): array
    {
        $validationErrors = [];

        foreach ($quoteItems as $quoteItem) {
            $product = $quoteItem->getProduct();
            $productId = $product ? (int)$product->getId() : 0;
            if ($productId > 0
                && $this->quoteItemHelper->isSubscriptionEnabled($quoteItem)
                && $this->trialRegistry->getPurchases($customerId, $productId) >= $maxPurchases
            ) {
                $validationErrors[] = __('"%1" trial product may only be purchased once. Please remove it from the shopping cart.', $product->getName());
            }
        }

        return $validationErrors;
    }
}
