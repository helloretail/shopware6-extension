<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service\Models\Requests;

use Helret\HelloRetail\Service\Models\PageParams;
use Helret\HelloRetail\Service\Models\PageProducts;

class PageRequest extends Request
{
    public PageParams $params;
    public PageProducts $products;
    public string $url;
    public bool $firstLoad;
    public string $format;
    public bool $layout;

    /**
     * @param PageParams $params
     * @param PageProducts $products
     * @param string $url
     * @param bool $firstLoad
     * @param string $format
     * @param bool $layout
     * @param string|null $websiteUuid
     * @param string|null $trackingUserId
     */
    public function __construct(
        PageParams $params,
        PageProducts $products,
        string $url,
        bool $firstLoad = true,
        string $format = "json",
        bool $layout = false,
        ?string $websiteUuid = null,
        ?string $trackingUserId = null
    ) {
        $this->params = $params;
        $this->products = $products;
        $this->url = $url;
        $this->firstLoad = $firstLoad;
        $this->format = $format;
        $this->layout = $layout;
        parent::__construct($websiteUuid, $trackingUserId);
    }

    /**
     * @return PageParams
     */
    public function getParams(): PageParams
    {
        return $this->params;
    }

    /**
     * @param PageParams $params
     */
    public function setParams(PageParams $params): void
    {
        $this->params = $params;
    }

    /**
     * @return PageProducts
     */
    public function getProducts(): PageProducts
    {
        return $this->products;
    }

    /**
     * @param PageProducts $products
     */
    public function setProducts(PageProducts $products): void
    {
        $this->products = $products;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return bool
     */
    public function isFirstLoad(): bool
    {
        return $this->firstLoad;
    }

    /**
     * @param bool $firstLoad
     */
    public function setFirstLoad(bool $firstLoad): void
    {
        $this->firstLoad = $firstLoad;
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
     * @return bool
     */
    public function isLayout(): bool
    {
        return $this->layout;
    }

    /**
     * @param bool $layout
     */
    public function setLayout(bool $layout): void
    {
        $this->layout = $layout;
    }
}