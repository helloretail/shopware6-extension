<?php declare(strict_types=1);

namespace Helret\HelloRetail\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class HelloRetailTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'hello-retail.update-feeds';
    }

    public static function getDefaultInterval(): int
    {
        return 60;
    }
}
