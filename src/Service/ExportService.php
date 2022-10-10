<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service;

use Helret\HelloRetail\Core\Content\Feeds\ExportEntity;

/**
 * Class ExportService
 * @package Helret\HelloRetail\Service
 */
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

    /**
     * @return ExportEntity[]
     */
    public function getFeeds(): array
    {
        return $this->feeds;
    }
}
