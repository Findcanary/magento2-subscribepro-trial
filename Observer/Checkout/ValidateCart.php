<?php
/**
 * Copyright © Find Canary, LLC. All rights reserved.
 */
declare(strict_types = 1);

namespace FindCanary\SubscribeProTrial\Observer\Checkout;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;

class ValidateCart implements ObserverInterface
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
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    private $redirect;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    /**
     * @param \FindCanary\SubscribeProTrial\Service\TrialRegistry $trialRegistry
     * @param \FindCanary\SubscribeProTrial\Helper\QuoteItem $quoteItemHelper
     * @param \FindCanary\SubscribeProTrial\Model\Config\TrialProduct $trialProductConfig
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Checkout\Model\Cart $cart
     */
    public function __construct(
        \FindCanary\SubscribeProTrial\Service\TrialRegistry $trialRegistry,
        \FindCanary\SubscribeProTrial\Helper\QuoteItem $quoteItemHelper,
        \FindCanary\SubscribeProTrial\Model\Config\TrialProduct $trialProductConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->trialRegistry = $trialRegistry;
        $this->quoteItemHelper = $quoteItemHelper;
        $this->trialProductConfig = $trialProductConfig;
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
        $this->cart = $cart;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var \Magento\Checkout\Controller\Index\Index $controller */
        $controller = $observer->getData('controller_action');

        $quote = $this->cart->getQuote();

        $customerId = (int)$quote->getCustomerId();
        $maxPurchases = $this->trialProductConfig->getMaxPurchases((int)$quote->getStoreId());

        if ($customerId !== 0 && $maxPurchases > 0
            && $this->hasRestrictedTrialProduct($quote, $customerId, $maxPurchases)
        ) {
            $this->messageManager->addErrorMessage(__('Trial products may only be purchased once per customer.'));
            $this->redirect->redirect($controller->getResponse(), 'checkout/cart');
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param int $customerId
     * @param int $maxPurchases
     * @return bool
     */
    private function hasRestrictedTrialProduct(Quote $quote, int $customerId, int $maxPurchases): bool
    {
        $hasRestrictedTrialProduct = false;

        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            $productId = $quoteItem->getProduct() ? (int)$quoteItem->getProduct()->getId() : 0;
            if ($productId > 0
                && $this->quoteItemHelper->isSubscriptionEnabled($quoteItem)
                && $this->trialRegistry->getPurchases($customerId, $productId) >= $maxPurchases
            ) {
                $hasRestrictedTrialProduct = true;
                break;
            }
        }

        return $hasRestrictedTrialProduct;
    }
}
