<?php

namespace Wexo\HelloRetail\Component\MessageQueue;

use Wexo\HelloRetail\Export\ExportEntityInterface;

class HelloRetailExport
{
    protected ExportEntityInterface $exportEntity;

    protected string $feed;

    public function __construct(ExportEntityInterface $exportEntity, string $feed)
    {
        $this->exportEntity = $exportEntity;
        $this->feed = $feed;
    }

    public function getExportEntity()
    {
        return $this->exportEntity;
    }

    public function getFeed()
    {
        return $this->feed;
    }
}
