<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service;

use Helret\HelloRetail\Core\Content\Feeds\ExportEntity;
use Helret\HelloRetail\HelretHelloRetail;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class ExportService
{
    /** @var ExportEntity[] */
    protected array $feeds;

    public function __construct(iterable $feeds)
    {
        $exportFeeds = [];
        foreach ($feeds as $key => $feed) {
            if ($feed instanceof ExportEntity) {
                $exportFeeds[$key] = $feed;
            }
        }

        $this->feeds = $exportFeeds;
    }

    public function getFeed(string $feed): ?ExportEntity
    {
        foreach ($this->feeds as $feedEntity) {
            if ($feedEntity->getFeed() === $feed) {
                return $feedEntity;
            }
        }

        return null;
    }

    public function getFeeds(): array
    {
        return $this->feeds;
    }

    public static function getSalesChannelCriteria(?array $salesChannelIds = null): Criteria
    {
        return (new Criteria($salesChannelIds ?: null))
            ->addFilter(new EqualsFilter('typeId', HelretHelloRetail::SALES_CHANNEL_TYPE_HELLO_RETAIL))
            ->addFilter(new EqualsFilter('active', true));
    }
}
