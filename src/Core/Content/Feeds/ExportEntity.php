<?php declare(strict_types=1);

namespace Helret\HelloRetail\Core\Content\Feeds;

use ReflectionClass;

abstract class ExportEntity
{
    public array $associations = [];
    protected string $feed;
    protected string $file;
    private ?string $baseDir = null;

    final protected function getBaseDir(): string
    {
        return ($this->baseDir ?: ($this->baseDir = dirname((new ReflectionClass($this))->getFileName())));
    }

    final public function getFeed(): string
    {
        return $this->feed;
    }

    // This will allow "sales_channel.{feed}.repository
    public function getEntity(): string
    {
        return $this->feed;
    }

    final public function getFile(): string
    {
        return $this->file;
    }

    final public function getHeaderTemplate(): string
    {
        return @file_get_contents("{$this->getBaseDir()}/template/header.xml.twig");
    }

    final public function getBodyTemplate(): string
    {
        return @file_get_contents("{$this->getBaseDir()}/template/body.xml.twig");
    }

    final public function getFooterTemplate(): string
    {
        return @file_get_contents("{$this->getBaseDir()}/template/footer.xml.twig");
    }

    abstract public function getSnippetKey(): string;
}
