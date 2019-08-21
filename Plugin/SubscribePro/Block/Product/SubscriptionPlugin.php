<?php
/**
 * Copyright © Find Canary, LLC. All rights reserved.
 */
declare(strict_types = 1);

namespace FindCanary\SubscribeProTrial\Plugin\SubscribePro\Block\Product;

use Magento\Framework\View\Element\AbstractBlock;

class SubscriptionPlugin
{
    /**
     * @var \FindCanary\SubscribeProTrial\Helper\UiTrialProduct
     */
    private $uiTrialProductHelper;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonSerializer;

    /**
     * @param \FindCanary\SubscribeProTrial\Helper\UiTrialProduct $uiTrialProductHelper
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
     */
    public function __construct(
        \FindCanary\SubscribeProTrial\Helper\UiTrialProduct $uiTrialProductHelper,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
    ) {
        $this->uiTrialProductHelper = $uiTrialProductHelper;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @param \Magento\Framework\View\Element\AbstractBlock $subject
     * @param string|null $result
     * @return string|null
     */
    public function afterGetJsLayout(AbstractBlock $subject, string $result = null): ?string
    {
        $jsLayout = $result ? $this->jsonSerializer->unserialize($result) : null;

        if (is_array($jsLayout) && !empty($jsLayout)) {
            $jsLayout = $this->uiTrialProductHelper->updateJsLayout(
                $jsLayout,
                'FindCanary_SubscribeProTrial/product/subscription'
            );
            $result = (string)$this->jsonSerializer->serialize($jsLayout);
        }

        return $result;
    }
}
