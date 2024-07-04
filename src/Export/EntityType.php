<?php declare(strict_types=1);

namespace Helret\HelloRetail\Export;

/**
 * Class EntityType
 * @package Helret\HelloRetail\Export
 */
class EntityType
{
    public const CUSTOMER = 'entity_type_customer';
    public const ORDER = 'entity_type_order';
    public const PRODUCT = 'entity_type_product';
    public const CATEGORY = 'entity_type_category';

    /**
     * @param $entityType
     * @return string|null
     */
    public static function getMatchingEntityType($entityType): ?string
    {
        switch (strtolower($entityType)) {
            case 'customer':
                return self::CUSTOMER;
            case 'order':
                return self::ORDER;
            case 'product':
                return self::PRODUCT;
            case 'category':
                return self::CATEGORY;
            default:
                return "entity__$entityType";
        }
    }
}
