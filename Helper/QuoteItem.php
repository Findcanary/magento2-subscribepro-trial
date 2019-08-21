<?php
/**
 * Copyright © Find Canary, LLC. All rights reserved.
 */
declare(strict_types = 1);

namespace FindCanary\SubscribeProTrial\Helper;

class QuoteItem
{
    const IS_TRIAL = 'is_trial';
    const TRIAL_INTERVAL = 'trial_interval';

    /**
     * @var \Swarming\SubscribePro\Helper\QuoteItem
     */
    private $quoteItemHelper;

    /**
     * @param \Swarming\SubscribePro\Helper\QuoteItem $quoteItemHelper
     */
    public function __construct(
        \Swarming\SubscribePro\Helper\QuoteItem $quoteItemHelper
    ) {
        $this->quoteItemHelper = $quoteItemHelper;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item  $quoteItem
     * @return bool
     */
    public function isSubscriptionEnabled(\Magento\Quote\Model\Quote\Item $quoteItem): bool
    {
        return $this->quoteItemHelper->isSubscriptionEnabled($quoteItem);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param bool $isTrial
     * @return void
     */
    public function setIsTrial(\Magento\Quote\Model\Quote\Item $quoteItem, bool $isTrial): void
    {
        $this->quoteItemHelper->setSubscriptionParam($quoteItem, self::IS_TRIAL, $isTrial);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @return bool
     */
    public function isTrial(\Magento\Quote\Model\Quote\Item $quoteItem): bool
    {
        $subscriptionParams = $this->quoteItemHelper->getSubscriptionParams($quoteItem);
        return (bool)$subscriptionParams[self::IS_TRIAL] ?: false;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param string|null $intervalTitle
     * @return void
     */
    public function setTrialInterval(\Magento\Quote\Model\Quote\Item $quoteItem, string $intervalTitle = null): void
    {
        $this->quoteItemHelper->setSubscriptionParam($quoteItem, self::TRIAL_INTERVAL, $intervalTitle);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @return string|null
     */
    public function getTrialInterval(\Magento\Quote\Model\Quote\Item $quoteItem): ?string
    {
        $subscriptionParams = $this->quoteItemHelper->getSubscriptionParams($quoteItem);
        return $subscriptionParams[self::TRIAL_INTERVAL] ?: null;
    }
}
