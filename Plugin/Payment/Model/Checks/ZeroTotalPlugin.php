<?php
/**
 * Copyright © Find Canary, LLC. All rights reserved.
 */
declare(strict_types = 1);

namespace FindCanary\SubscribeProTrial\Plugin\Payment\Model\Checks;

use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;
use Magento\Payment\Model\Checks\ZeroTotal;
use Swarming\SubscribePro\Gateway\Config\ConfigProvider as SubscribeProGatewayConfigProvider;

class ZeroTotalPlugin
{
    /**
     * @var \Swarming\SubscribePro\Helper\Quote
     */
    private $quoteHelper;

    /**
     * @param \Swarming\SubscribePro\Helper\Quote $quoteHelper
     */
    public function __construct(
        \Swarming\SubscribePro\Helper\Quote $quoteHelper
    ) {
        $this->quoteHelper = $quoteHelper;
    }

    /**
     * @param \Magento\Payment\Model\Checks\ZeroTotal $subject
     * @param bool $result
     * @param \Magento\Payment\Model\MethodInterface $paymentMethod
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool
     */
    public function afterIsApplicable(
        ZeroTotal $subject,
        bool $result,
        MethodInterface $paymentMethod,
        Quote $quote
    ): bool {
        return $result ?: $this->isSubscribeProPayment($paymentMethod) && $this->quoteHelper->hasSubscription($quote);
    }

    /**
     * @param \Magento\Payment\Model\MethodInterface $paymentMethod
     * @return bool
     */
    private function isSubscribeProPayment(MethodInterface $paymentMethod): bool
    {
        return in_array(
            $paymentMethod->getCode(),
            [SubscribeProGatewayConfigProvider::CODE, SubscribeProGatewayConfigProvider::VAULT_CODE],
            true
        );
    }
}
