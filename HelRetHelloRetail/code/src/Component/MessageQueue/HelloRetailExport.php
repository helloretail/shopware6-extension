<?php declare(strict_types=1);

namespace Wexo\HelloRetail\Component\MessageQueue;

use Wexo\HelloRetail\Export\ExportEntityInterface;

/**
 * Class HelloRetailExport
 * @package Wexo\HelloRetail\Component\MessageQueue
 */
class HelloRetailExport
{
    /**
     * @var ExportEntityInterface
     */
    protected $exportEntity;
    /**
     * @var string
     */
    protected $feed;

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
