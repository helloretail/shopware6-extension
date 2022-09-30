<?php declare(strict_types=1);

namespace Helret\HelloRetail\Core\Content\Feeds\Product;

use Helret\HelloRetail\Core\Content\Feeds\ExportEntity;
use Shopware\Core\Content\Product\ProductDefinition;

class ProductExportEntity extends ExportEntity
{
    protected string $feed = ProductDefinition::ENTITY_NAME;

    public function getSnippetKey(): string
    {
        return "helret-hello-retail.comparison.feed.product";
    }
}
