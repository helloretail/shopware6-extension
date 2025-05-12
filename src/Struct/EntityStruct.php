<?php declare(strict_types=1);

namespace Helret\HelloRetail\Struct;

use Shopware\Core\Framework\Struct\ArrayStruct;

class EntityStruct extends ArrayStruct
{
    public function __construct(array $data = [], $apiAlias = 'hello-retail-struct')
    {
        if (isset($data['extraData']) && is_array($data['extraData'])) {
            $data['extraData'] = new self($data['extraData'], $apiAlias);
        }

        parent::__construct($data, $apiAlias);
    }

    public function getId(): string
    {
        return $this->getExtraData()?->get('id');
    }

    public function getTrackingCode(): ?string
    {
        return $this->get('trackingCode');
    }

    public function getExtraData(): ?self
    {
        return $this->get('extraData');
    }
}
