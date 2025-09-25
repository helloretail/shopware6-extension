<?php declare(strict_types=1);

namespace Helret\HelloRetail\Export;

class EntityType
{
    public const CUSTOMER = 'entity_type_customer';
    public const ORDER = 'entity_type_order';
    public const PRODUCT = 'entity_type_product';
    public const CATEGORY = 'entity_type_category';
    public static function getMatchingEntityType($entityType): ?string
    {
        return match (strtolower((string) $entityType)) {
            'customer' => self::CUSTOMER,
            'order' => self::ORDER,
            'product' => self::PRODUCT,
            'category' => self::CATEGORY,
            default => "entity__$entityType",
        };
    }
}
