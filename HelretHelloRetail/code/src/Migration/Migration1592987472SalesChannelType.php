<?php declare(strict_types=1);

namespace Helret\HelloRetail\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;
use Helret\HelloRetail\HelretHelloRetail;

/**
 * Class Migration1592987472SalesChannelType
 * @package Helret\HelloRetail\Migration
 */
class Migration1592987472SalesChannelType extends MigrationStep
{
    /**
     * @return int
     */
    public function getCreationTimestamp(): int
    {
        return 1592987472;
    }

    /**
     * @param Connection $connection
     * @throws \Doctrine\DBAL\DBALException
     */
    public function update(Connection $connection): void
    {
        $helloRetail = Uuid::fromHexToBytes(HelretHelloRetail::SALES_CHANNEL_TYPE_HELLO_RETAIL);

        $languageDefault = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);
        $languageDE = $this->getLanguageIdByLocale($connection, 'de-DE');

        $idExists = $connection->fetchColumn(
            'SELECT `id` FROM `sales_channel_type` WHERE `id` = :id',
            [
                'id' => $helloRetail,
            ]
        );

        if (!empty($idExists)) {
            return;
        }

        $connection->insert(
            'sales_channel_type',
            [
                'id' => $helloRetail,
                'icon_name' => 'brand-hello-retail',
                'created_at' => date(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]
        );
        $connection->insert(
            'sales_channel_type_translation',
            [
                'sales_channel_type_id' => $helloRetail,
                'language_id' => $languageDefault,
                'name' => 'Hello Retail',
                'manufacturer' => 'Hello Retail ApS',
                'description' => 'Hello Retail Integration Sales Channel Type',
                'created_at' => date(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]
        );
        $connection->insert(
            'sales_channel_type_translation',
            [
                'sales_channel_type_id' => $helloRetail,
                'language_id' => $languageDE,
                'name' => 'Hello Retail',
                'manufacturer' => 'Hello Retail ApS',
                'description' => 'Hello Retail für die Einzelhandelsintegration',
                'created_at' => date(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]
        );
    }

    /**
     * @param Connection $connection
     */
    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }

    /**
     * @param Connection $connection
     * @param string $locale
     * @return string
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getLanguageIdByLocale(Connection $connection, string $locale): string
    {
        $sql = <<<SQL
SELECT `language`.`id`
FROM `language`
INNER JOIN `locale` ON `locale`.`id` = `language`.`locale_id`
WHERE `locale`.`code` = :code
SQL;

        $languageId = $connection->executeQuery($sql, ['code' => $locale])->fetchColumn();

        if ($languageId === false) {
            throw new \RuntimeException(sprintf('Language for locale "%s" not found.', $locale));
        }

        return (string) $languageId;
    }
}
