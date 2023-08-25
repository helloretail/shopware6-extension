<?php declare(strict_types=1);

namespace Helret\HelloRetail\Component\MessageQueue;

use Helret\HelloRetail\Export\ExportEntityInterface;

class HelloRetailExport
{
    protected ExportEntityInterface $exportEntity;
    protected string $feed;

    public function __construct(ExportEntityInterface $exportEntity, string $feed)
    {
        $this->exportEntity = $exportEntity;
        $this->feed = $feed;
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
