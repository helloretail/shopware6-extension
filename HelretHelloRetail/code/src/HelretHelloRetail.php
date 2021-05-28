<?php declare(strict_types=1);

namespace Helret\HelloRetail;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class HelretHelloRetail
 * @package Helret\HelloRetail
 */
class HelretHelloRetail extends Plugin
{
    public const LOG_CHANNEL = 'hello-retail';
    public const EXPORT_ERROR = 'hello-retail.export.error';
    public const EXPORT_SUCCESS = 'hello-retail.export.success';
    public const SALES_CHANNEL_TYPE_HELLO_RETAIL = '44f7e183909376bb5824abf830f4b879';
    public const FILE_TYPE_INDICATOR_SEPARATOR = '_';
    public const CONFIG_PATH = 'HelretHelloRetail.config';
    public const STORAGE_PATH = 'hello-retail';
    public const ORDER_FEED = "orders.xml";
    public const PRODUCT_FEED = "products.xml";

    /* Settings config, used in task handler for mapping systemConfig, for the run interval. */
    public const CONFIG_FIELDS = [
        "order" => [
            ["amount" => "OrdersTimeAmount1", "format" => "OrdersTimeFormat1"],
            ["amount" => "OrdersTimeAmount2", "format" => "OrdersTimeFormat2"],
            ["amount" => "OrdersTimeAmount3", "format" => "OrdersTimeFormat3"]
        ],
        "product" => [
            ["amount" => "ProductTimeAmount1", "format" => "ProductTimeFormat1"],
            ["amount" => "ProductTimeAmount2", "format" => "ProductTimeFormat2"],
            ["amount" => "ProductTimeAmount3", "format" => "ProductTimeFormat3"]
        ]
    ];

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

    /**
     * @param UninstallContext $uninstallContext
     */
    public function uninstall(UninstallContext $uninstallContext): void
    {
        $context = $uninstallContext->getContext();

        if (!$uninstallContext->keepUserData()) {
            // Remove all feeds and the base folder
            $fileSystem = new Filesystem();
            $projectDir = $this->container->get('kernel')->getProjectDir();
            $dir = $projectDir
                . DIRECTORY_SEPARATOR
                . 'public'
                . DIRECTORY_SEPARATOR
                . self::STORAGE_PATH
                . DIRECTORY_SEPARATOR;
            $fileSystem->remove($dir);

            /** @var EntityRepositoryInterface $salesChannelRepository */
            $salesChannelRepository = $this->container->get('sales_channel.repository');

            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('typeId', self::SALES_CHANNEL_TYPE_HELLO_RETAIL));
            $ids = $salesChannelRepository->searchIds($criteria, $context);

            $deleteArray = array_map(function ($id) {
                return ['id' => $id];
            }, $ids->getIds());

            $salesChannelRepository->delete($deleteArray, $context);
        }
    }
}
