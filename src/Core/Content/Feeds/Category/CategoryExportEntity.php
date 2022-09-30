<?php declare(strict_types=1);

namespace Helret\HelloRetail\Core\Content\Feeds\Category;

use Helret\HelloRetail\Core\Content\Feeds\ExportEntity;
use Shopware\Core\Content\Category\CategoryDefinition;

class CategoryExportEntity extends ExportEntity
{
    protected string $feed = CategoryDefinition::ENTITY_NAME;

    public function getSnippetKey(): string
    {
        return "helret-hello-retail.comparison.feed.category";
    }
}
