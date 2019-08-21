<?php
/**
 * Copyright © Find Canary, LLC. All rights reserved.
 */
declare(strict_types = 1);

namespace FindCanary\SubscribeProTrial\Helper;

use Swarming\SubscribePro\Api\Data\ProductInterface;

class UiTrialProduct
{
    /**
     * @var \FindCanary\SubscribeProTrial\Model\Config\TrialProduct
     */
    private $trialProductConfig;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceFormatter;

    /**
     * @param \FindCanary\SubscribeProTrial\Model\Config\TrialProduct $trialProductConfig
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceFormatter
     */
    public function __construct(
        \FindCanary\SubscribeProTrial\Model\Config\TrialProduct $trialProductConfig,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceFormatter
    ) {
        $this->trialProductConfig = $trialProductConfig;
        $this->priceFormatter = $priceFormatter;
    }

    /**
     * @param array $jsLayout
     * @param string $template
     * @return array
     */
    public function updateJsLayout(array $jsLayout, string $template): array
    {
        if (!empty($jsLayout['components']) && is_array($jsLayout['components'])) {
            $jsLayout['components'] = $this->processUiComponents($jsLayout['components'], $template);
        }
        return $jsLayout;
    }

    /**
     * @param array $uiComponents
     * @param string $template
     * @return array
     */
    private function processUiComponents(array $uiComponents, string $template): array
    {
        foreach ($uiComponents as &$uiComponent) {
            $isTrialProduct = $uiComponent['config']['product']['is_trial_product'] ?? false;
            if ($isTrialProduct === true) {
                $uiComponent = $this->updateTrialProductData($uiComponent, $template);
            }
        }
        return $uiComponents;
    }

    /**
     * @param array $uiComponent
     * @param string $template
     * @return array
     */
    private function updateTrialProductData(array $uiComponent, string $template): array
    {
        $config = &$uiComponent['config'];
        $config['template'] = $template;

        $platformProductData = &$uiComponent['config']['product'];
        $platformProductData['subscription_option_mode'] = 'subscription_only';
        $platformProductData['trial_product_message'] = $this->getTrialProductMessage($platformProductData);

        return $uiComponent;
    }

    /**
     * @param array $platformProductData
     * @return string
     */
    private function getTrialProductMessage(array $platformProductData): string
    {
        $productMessage = $this->trialProductConfig->getProductMessage();
        $replace = [
            '%trial_price' => $this->getFormattedTrialPrice($platformProductData),
            '%trial_interval' => $platformProductData[ProductInterface::TRIAL_INTERVAL],
        ];
        return str_replace(array_keys($replace), array_values($replace), $productMessage);
    }

    /**
     * @param array $platformProductData
     * @return string
     */
    private function getFormattedTrialPrice(array $platformProductData): string
    {
        return (string)$this->priceFormatter->format((float)$platformProductData[ProductInterface::TRIAL_PRICE], false);
    }
}
