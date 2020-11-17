<?php declare(strict_types=1);

namespace Wexo\HelloRetail;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;

/**
 * Class WexoHelloRetail
 * @package Wexo\HelloRetail
 */
class WexoHelloRetail extends Plugin
{
    public const LOG_CHANNEL = 'hello-retail';
    public const EXPORT_ERROR = 'hello-retail.export.error';
    public const EXPORT_SUCCESS = 'hello-retail.export.success';
    public const SALES_CHANNEL_TYPE_HELLO_RETAIL = '44f7e183909376bb5824abf830f4b879';
    public const FILE_TYPE_INDICATOR_SEPARATOR = '_';

    /**
     * @param DeactivateContext $context
     */
    public function deactivate(DeactivateContext $context): void
    {
        $defaultContext = Context::createDefaultContext();

        $salesChannelRepository = $this->container->get('sales_channel.repository');

        $criteria = new Criteria();
        $criteria
            ->addFilter(
                new EqualsFilter('typeId', self::SALES_CHANNEL_TYPE_HELLO_RETAIL),
                new EqualsFilter('active', true)
            );

        $result = $salesChannelRepository->searchIds($criteria, $defaultContext);

        $data = [];
        foreach ($result->getIds() as $salesChannelId) {
            $data[] = ['id' => $salesChannelId, 'active' => false];
        }

        if (\count($data) > 0) {
            $salesChannelRepository->update($data, $defaultContext);
        }
    }
}
