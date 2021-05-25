<?php declare(strict_types=1);
namespace Helret\HelloRetail\Subscriber;

use Helret\HelloRetail\HelretHelloRetail;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Core\System\SalesChannel\SalesChannelEvents;

class SaleschannelSubscriber implements EventSubscriberInterface
{

    private EntityRepository $salesChannelRepository;

    public function __construct(EntityRepository $salesChannelRepository)
    {
        $this->salesChannelRepository = $salesChannelRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
           SalesChannelEvents::SALES_CHANNEL_WRITTEN => "onRetailChannelWritten",
        ];
    }

    public function onRetailChannelWritten($event): void
    {

        $criteria = new Criteria([$event->getWriteResults()[0]->getPrimaryKey()]);
        $salesChannel = $this->salesChannelRepository->search($criteria, $event->getContext())->getEntities()->first();
        /* If not a hello retail channel, break! */
        if (HelretHelloRetail::SALES_CHANNEL_TYPE_HELLO_RETAIL != $salesChannel->getTypeId()) {
            return;
        }

        /* update payloads if is first time run */
        foreach ($event->getPayloads() as $payload) {
            /* If payloay for feeds set */
            if (isset($payload['configuration']) && isset($payload['configuration']['feeds'])) {
                /* save payload, as we are going to pass it through with a few edits */
                $updateStatement = $this->updateFeed($payload);
            }
            /* if changed */
            if (count($event->getPayloads()) > 0 && $event->getPayloads()[0] != $updateStatement) {
                $this->salesChannelRepository->update([$updateStatement], $event->getContext());
            }
        }
    }


    private function getFeedFile(array $feed): string
    {
        if ($feed['file'] == null && isset($feed['name'])) {
            if ($feed['name'] == 'product') {
                return HelretHelloRetail::PRODUCT_FEED;
            } elseif ($feed['name'] == 'order') {
                return HelretHelloRetail::ORDER_FEED;
            }
        }

        return "unknown.xml";
    }


    private function updateFeed(array $payload): array
    {
        foreach ($payload['configuration']['feeds'] as $feed_key => $feed) {
            if ($feed['file'] == null) {
                $payload['configuration']['feeds'][$feed_key] = $feed;
                $payload['configuration']['feeds'][$feed_key]['file'] = $this->getFeedFile($feed);
            }
        }
        return $payload;
    }
}
