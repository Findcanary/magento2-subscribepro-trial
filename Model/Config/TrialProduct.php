<?php
/**
 * Copyright © Find Canary, LLC. All rights reserved.
 */
declare(strict_types = 1);

namespace FindCanary\SubscribeProTrial\Model\Config;

use Magento\Store\Model\ScopeInterface;

class TrialProduct
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param int|null $storeId
     * @return int
     */
    public function getMaxPurchases(int $storeId = null): int
    {
        return (int)$this->scopeConfig->getValue(
            'swarming_subscribepro/trial/max_purchases',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return string|null
     */
    public function getProductMessage(int $storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            'swarming_subscribepro/trial/product_message',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
