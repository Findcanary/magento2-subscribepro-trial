<?php
/**
 * Copyright © Find Canary, LLC. All rights reserved.
 */
declare(strict_types = 1);

namespace FindCanary\SubscribeProTrial\Plugin\Quote;

use FindCanary\SubscribeProTrial\Helper\QuoteItem as QuoteItemHelper;

class ItemPlugin extends \Swarming\SubscribePro\Plugin\Quote\Item
{
    /**
     * @param array $options1
     * @param array $options2
     * @return bool
     */
    protected function compareOptions($options1, $options2): bool
    {
        return $this->isAnyTrial($options1, $options2) ?: parent::compareOptions($options1, $options2);
    }

    /**
     * @param array $options1
     * @param array $options2
     * @return bool
     */
    private function isAnyTrial(array $options1, array $options2): bool
    {
        $isTrial1 = isset($options1['info_buyRequest'])
            ? $this->getParam($options1['info_buyRequest'], QuoteItemHelper::IS_TRIAL)
            : false;

        $isTrial2 = isset($options2['info_buyRequest'])
            ? $this->getParam($options2['info_buyRequest'], QuoteItemHelper::IS_TRIAL)
            : false;

        return $isTrial1 || $isTrial2;
    }
}
