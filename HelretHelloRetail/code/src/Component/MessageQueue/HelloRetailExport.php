<?php declare(strict_types=1);

namespace Helret\HelloRetail\Component\MessageQueue;

use Helret\HelloRetail\Export\ExportEntityInterface;

/**
 * Class HelloRetailExport
 * @package Helret\HelloRetail\Component\MessageQueue
 */
class HelloRetailExport
{
    protected ExportEntityInterface $exportEntity;
    protected string $feed;

    /**
     * HelloRetailExport constructor.
     * @param ExportEntityInterface $exportEntity
     * @param string $feed
     */
    public function __construct(ExportEntityInterface $exportEntity, string $feed)
    {
        $this->exportEntity = $exportEntity;
        $this->feed = $feed;
    }

    /**
     * @return ExportEntityInterface
     */
    public function getExportEntity(): ExportEntityInterface
    {
        return $this->exportEntity;
    }

    /**
     * @return string
     */
    public function getFeed(): string
    {
        return $this->feed;
    }
}
