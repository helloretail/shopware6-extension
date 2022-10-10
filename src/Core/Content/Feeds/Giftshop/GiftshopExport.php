<?php declare(strict_types=1);

namespace Helret\HelloRetail\Core\Content\Feeds\Giftshop;

use Helret\HelloRetail\Core\Content\Feeds\ExportEntity;
use Wexo\GiftShop\Core\Content\GiftList\GiftListDefinition;

/**
 * Class GiftshopExport
 * @package Helret\HelloRetail\Core\Content\Feeds\Product
 */
class GiftshopExport extends ExportEntity
{
    protected string $feed = GiftListDefinition::ENTITY_NAME;
    protected string $file = "giftshop.xml";

    public function getSnippetKey(): string
    {
        return "helret-hello-retail.comparison.feed.giftshop";
    }
}
