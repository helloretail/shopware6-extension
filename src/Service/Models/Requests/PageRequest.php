<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service\Models\Requests;

use Helret\HelloRetail\Service\Models\PageParams;
use Helret\HelloRetail\Service\Models\PageProducts;

class PageRequest extends Request
{
    public string $layout;
    public bool $firstLoad;
    public PageParams $params;
    public PageProducts $products;

    /**
     * @param string $layout
     * @param bool $firstLoad
     * @param PageParams $params
     * @param PageProducts $products
     * @param null $websiteUuid
     * @param null $trackingUserId
     */
    public function __construct(
        string $layout,
        bool $firstLoad,
        PageParams $params,
        PageProducts $products,
        $websiteUuid = null,
        $trackingUserId = null
    ) {
        $this->layout = $layout;
        $this->firstLoad = $firstLoad;
        $this->params = $params;
        $this->products = $products;

        parent::__construct($websiteUuid, $trackingUserId);
    }


}