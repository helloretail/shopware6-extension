<?php declare(strict_types=1);

namespace Helret\HelloRetail\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

/**
 * Class HelloRetailTask
 * @package Helret\HelloRetail\ScheduledTask
 */
class HelloRetailTask extends ScheduledTask
{
    /**
     * @return string
     */
    public static function getTaskName(): string
    {
        return 'hello-retail.update-feeds';
    }

    /**
     * @return int the default interval this task should run in seconds
     */
    public static function getDefaultInterval(): int
    {
        return 86400;
    }
}
