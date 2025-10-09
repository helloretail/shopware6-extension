<?php declare(strict_types=1);

namespace Helret\HelloRetail\Component\MessageQueue;

use Helret\HelloRetail\Export\ExportEntityInterface;

class HelloRetailExport
{
    public function __construct(protected ExportEntityInterface $exportEntity, protected string $feed)
    {
    }

    public function getExportEntity(): ExportEntityInterface
    {
        return $this->exportEntity;
    }

    public function getFeed(): string
    {
        return $this->feed;
    }
}
