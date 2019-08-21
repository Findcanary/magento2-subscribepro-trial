<?php
/**
 * Copyright © Find Canary, LLC. All rights reserved.
 */
declare(strict_types = 1);

namespace FindCanary\SubscribeProTrial\Service;

class TrialRegistry
{
    /**
     * @var \FindCanary\SubscribeProTrial\Model\ResourceModel\TrialRegistry
     */
    private $trialRegistryResource;

    /**
     * @param \FindCanary\SubscribeProTrial\Model\ResourceModel\TrialRegistry $trialRegistryResource
     */
    public function __construct(
        \FindCanary\SubscribeProTrial\Model\ResourceModel\TrialRegistry $trialRegistryResource
    ) {
        $this->trialRegistryResource = $trialRegistryResource;
    }

    /**
     * @param int $customerId
     * @param int $productId
     * @return void
     */
    public function add(int $customerId, int $productId): void
    {
        $this->trialRegistryResource->add($customerId, $productId);
    }

    /**
     * @param int $customerId
     * @param int $productId
     * @return int
     */
    public function getPurchases(int $customerId, int $productId): int
    {
        return $this->trialRegistryResource->getPurchases($customerId, $productId);
    }
}
