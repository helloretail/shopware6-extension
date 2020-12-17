<?php declare(strict_types=1);

namespace Helret\HelloRetail\Export\Profiles;

/**
 * Interface ProfileExporterInterface
 * @package Helret\HelloRetail\Export\Profiles
 */
interface ProfileExporterInterface
{
    /**
     * @param string $salesChannelId
     * @param array $feeds
     * @return array
     */
    public function generate(string $salesChannelId, array $feeds = []): array;
}
