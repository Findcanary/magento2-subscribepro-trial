<?php
/**
 * Copyright © Find Canary, LLC. All rights reserved.
 */
declare(strict_types = 1);

namespace FindCanary\SubscribeProTrial\Observer\SubscribePro\Subscription;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CreateAfter implements ObserverInterface
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
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \FindCanary\SubscribeProTrial\Service\TrialRegistry $trialRegistry
     * @param \FindCanary\SubscribeProTrial\Helper\QuoteItem $quoteItemHelper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \FindCanary\SubscribeProTrial\Service\TrialRegistry $trialRegistry,
        \FindCanary\SubscribeProTrial\Helper\QuoteItem $quoteItemHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->trialRegistry = $trialRegistry;
        $this->quoteItemHelper = $quoteItemHelper;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $quoteItem = $observer->getData('quote_item');
        if (!$quoteItem instanceof \Magento\Quote\Model\Quote\Item || !$this->quoteItemHelper->isTrial($quoteItem)) {
            return;
        }

        $customerId = (int)$quoteItem->getQuote()->getCustomerId();
        $productId = (int)$quoteItem->getProduct()->getId();

        try {
            $this->trialRegistry->add($customerId, $productId);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
