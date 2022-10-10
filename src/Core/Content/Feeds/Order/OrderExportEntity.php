<?php declare(strict_types=1);

namespace Helret\HelloRetail\Core\Content\Feeds\Order;

use Helret\HelloRetail\Core\Content\Feeds\ExportEntity;
use Shopware\Core\Checkout\Order\OrderDefinition;

class OrderExportEntity extends ExportEntity
{
    protected string $feed = OrderDefinition::ENTITY_NAME;
    protected string $file = "orders.xml";
    public array $associations = [
        'lineItems.product',
        'transactions',
        'transactions.stateMachineState',
        'transactions.paymentMethod',
        'deliveries',
        'deliveries.shippingMethod'
    ];

    public function getSnippetKey(): string
    {
        return "helret-hello-retail.comparison.feed.order";
    }
}
