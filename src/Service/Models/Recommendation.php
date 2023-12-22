<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service\Models;

class Recommendation
{
    public string $key;
    public string $format;
    public array $fields = [];
    public RecommendationContext|null $context;

    /**
     * @param string $key
     * @param array|string $fields
     * @param RecommendationContext|null $context
     * @param string $format
     */
    public function __construct(
        string $key,
        array|string $fields,
        RecommendationContext|null $context = null,
        string $format = "json"
    ) {
        $this->key = $key;
        $this->format = $format;
        $this->fields = is_array($fields) ? $fields : [$fields];
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @param string $format
     */
    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    /**
     * @return RecommendationContext
     */
    public function getContext(): RecommendationContext
    {
        return $this->context;
    }

    /**
     * @param RecommendationContext $context
     */
    public function setContext(RecommendationContext $context): void
    {
        $this->context = $context;
    }
}