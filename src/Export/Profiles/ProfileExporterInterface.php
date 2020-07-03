<?php

namespace Wexo\HelloRetail\Export\Profiles;

use Shopware\Core\System\SalesChannel\SalesChannelEntity;

/**
 * Interface ProfileExporterInterface
 * @package Wexo\HelloRetail\Export\Profiles
 */
interface ProfileExporterInterface
{
    public function generate(string $salesChannelId, array $feeds = []): array;
}
