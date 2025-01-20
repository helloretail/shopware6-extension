<?php

declare(strict_types=1);

namespace Helret\HelloRetail\Service;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Core\Framework\Log\Package;

class HelloRetailConfigService
{

    public function __construct(
        protected readonly SystemConfigService $systemConfigService,
    ) {}

    /**
     * @return bool
     */
    public function hrSearchEnabled(string|null $id = null): bool
    {
        return $this->systemConfigService->getBool('HelretHelloRetail.config.searchEnabled', $id)
            && $this->systemConfigService->getBool('HelretHelloRetail.config.searchConfigKey', $id);
    }

    /**
     * @param string|null $id
     * @return string
     */
    public function getSearchConfigKey(string|null $id = null): string
    {
        return $this->systemConfigService->getString('HelretHelloRetail.config.searchConfigKey', $id);
    }

    /**
     * @param string|null $id
     * @return bool
     */
    public function getEnabledCategory(string|null $id = null): bool
    {
        return $this->systemConfigService->getBool('HelretHelloRetail.config.categorySearchEnabled', $id);
    }

    /**
     * @param string|null $id
     * @return int
     */
    public function getProductCount(string|null $id = null): int
    {
        return $this->systemConfigService->getInt('HelretHelloRetail.config.productRequestCount', $id);
    }

    /**
     * @param string|null $id
     * @return int
     */
    public function getCategoryCount(string|null $id = null): int
    {
        return $this->systemConfigService->getInt('HelretHelloRetail.config.categoryRequestCount', $id);
    }
}
