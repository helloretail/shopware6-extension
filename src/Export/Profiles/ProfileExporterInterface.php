<?php declare(strict_types=1);

namespace Helret\HelloRetail\Export\Profiles;

use Shopware\Core\Framework\Routing\Exception\SalesChannelNotFoundException;

interface ProfileExporterInterface
{
    /**
     * @throws SalesChannelNotFoundException
     */
    public function generate(string $salesChannelId, array $feeds = [], bool $now = false): array;
}
