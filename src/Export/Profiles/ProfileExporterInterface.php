<?php declare(strict_types=1);

namespace Helret\HelloRetail\Export\Profiles;

use Shopware\Core\Framework\Routing\Exception\SalesChannelNotFoundException;

/**
 * Interface ProfileExporterInterface
 * @package Helret\HelloRetail\Export\Profiles
 */
interface ProfileExporterInterface
{
    /**
     * @param string $salesChannelId
     * @param array $feeds
     * @param bool $now
     * @return array
     * @throws SalesChannelNotFoundException
     */
    public function generate(string $salesChannelId, array $feeds = [], bool $now = false): array;
}
