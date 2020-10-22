<?php declare(strict_types=1);

namespace Wexo\HelloRetail\Export\Profiles;

/**
 * Interface ProfileExporterInterface
 * @package Wexo\HelloRetail\Export\Profiles
 */
interface ProfileExporterInterface
{
    public function generate(string $salesChannelId, array $feeds = []): array;
}
