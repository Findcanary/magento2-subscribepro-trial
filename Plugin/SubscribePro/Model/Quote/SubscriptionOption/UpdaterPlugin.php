<?php
/**
 * Copyright © Find Canary, LLC. All rights reserved.
 */
declare(strict_types = 1);

namespace FindCanary\SubscribeProTrial\Plugin\SubscribePro\Model\Quote\SubscriptionOption;

class UpdaterPlugin
{
    /**
     * @var \FindCanary\SubscribeProTrial\Helper\QuoteItem
     */
    private $quoteItemHelper;

    /**
     * @param \FindCanary\SubscribeProTrial\Helper\QuoteItem $quoteItemHelper
     */
    public function __construct(
        \FindCanary\SubscribeProTrial\Helper\QuoteItem $quoteItemHelper
    ) {
        $this->quoteItemHelper = $quoteItemHelper;
    }

    /**
     * @param \Swarming\SubscribePro\Model\Quote\SubscriptionOption\Updater $subject
     * @param array $result
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param \Swarming\SubscribePro\Api\Data\ProductInterface $platformProduct
     * @param string $subscriptionOption
     * @param string $subscriptionInterval
     * @return array
     */
    public function afterUpdate(
        \Swarming\SubscribePro\Model\Quote\SubscriptionOption\Updater $subject,
        $result,
        $quoteItem,
        $platformProduct,
        $subscriptionOption,
        $subscriptionInterval
    ) {
        $isTrial = (bool)$platformProduct->getIsTrialProduct();
        $this->quoteItemHelper->setIsTrial($quoteItem, $isTrial);

        if ($isTrial) {
            $trialInterval = $platformProduct->getTrialInterval() ?: null;
            $this->quoteItemHelper->setTrialInterval($quoteItem, $trialInterval);
        }

        return $result;
    }
}
