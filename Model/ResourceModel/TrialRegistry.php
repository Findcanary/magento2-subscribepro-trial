<?php
/**
 * Copyright © Find Canary, LLC. All rights reserved.
 */
declare(strict_types = 1);

namespace FindCanary\SubscribeProTrial\Model\ResourceModel;

class TrialRegistry extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const TABLE_NAME = 'findcanary_subscribepro_trial_registry';
    const ENTITY_ID = 'entity_id';
    const CUSTOMER_ID = 'customer_id';
    const PRODUCT_ID = 'product_id';
    const PURCHASES = 'purchases';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, self::ENTITY_ID);
    }

    /**
     * @param int $customerId
     * @param int $productId
     * @return int
     */
    public function getPurchases(int $customerId, int $productId): int
    {
        $dbConnection = $this->getConnection();

        $select = $dbConnection->select();
        $select->from(self::TABLE_NAME, [self::PURCHASES]);
        $select->where(self::CUSTOMER_ID . ' =?', $customerId);
        $select->where(self::PRODUCT_ID . ' =?', $productId);

        return (int)$dbConnection->fetchOne($select);
    }

    /**
     * @param int $customerId
     * @param int $productId
     * @return void
     */
    public function add(int $customerId, int $productId): void
    {
        $data = [
            self::CUSTOMER_ID => $customerId,
            self::PRODUCT_ID => $productId,
            self::PURCHASES => '1'
        ];
        $onDuplicate = [
            self::PURCHASES => new \Zend_Db_Expr('purchases+1')
        ];
        $this->getConnection()->insertOnDuplicate(self::TABLE_NAME, $data, $onDuplicate);
    }
}
