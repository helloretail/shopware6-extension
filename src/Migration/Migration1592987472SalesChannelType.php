<?php declare(strict_types=1);

namespace Helret\HelloRetail\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;
use Helret\HelloRetail\HelretHelloRetail;

class Migration1592987472SalesChannelType extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1_592_987_472;
    }

    /**
     * @throws Exception
     */
    public function update(Connection $connection): void
    {
        $helloRetail = Uuid::fromHexToBytes(HelretHelloRetail::SALES_CHANNEL_TYPE_HELLO_RETAIL);

        $idExists = $connection->fetchOne(
            'SELECT `id` FROM `sales_channel_type` WHERE `id` = :id',
            [
                'id' => $helloRetail,
            ]
        );

        // Delete previous translations
        $connection->delete(
            'sales_channel_type_translation',
            [
                'sales_channel_type_id' => $helloRetail
            ]
        );

        if (empty($idExists)) {
            $connection->insert(
                'sales_channel_type',
                [
                    'id' => $helloRetail,
                    'icon_name' => 'brand-hello-retail',
                    'created_at' => date(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );
        }
        $connection->insert(
            'sales_channel_type_translation',
            [
                'sales_channel_type_id' => $helloRetail,
                'language_id' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM),
                'name' => 'Hello Retail',
                'manufacturer' => 'Hello Retail ApS',
                'description' => 'Hello Retail Integration Sales Channel Type',
                'created_at' => date(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]
        );
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }

    /**
     * @throws Exception
     */
    private function getLanguageIdByLocale(Connection $connection, string $locale): string
    {
        $sql = <<<SQL
SELECT `language`.`id`
FROM `language`
INNER JOIN `locale` ON `locale`.`id` = `language`.`locale_id`
WHERE `locale`.`code` = :code
SQL;

        $languageId = $connection->executeQuery($sql, ['code' => $locale])->fetchOne();

        if ($languageId === false) {
            throw new \RuntimeException(sprintf('Language for locale "%s" not found.', $locale));
        }

        return (string) $languageId;
    }
}
