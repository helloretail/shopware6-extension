<?php declare(strict_types=1);

namespace Helret\HelloRetail\Subscriber;

use Helret\HelloRetail\Event\HelretBeforeCartLoadEvent;
use Helret\HelloRetail\HelretHelloRetail;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Storefront\Checkout\Cart\SalesChannel\StorefrontCartFacade;
use Shopware\Storefront\Page\GenericPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Core\System\SalesChannel\SalesChannelEvents;

/**
 * Class SalesChannelSubscriber
 * @package Helret\HelloRetail\Subscriber
 */
class SalesChannelSubscriber implements EventSubscriberInterface
{
    protected EntityRepositoryInterface $salesChannelRepository;
    protected StorefrontCartFacade $cartService;
    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EntityRepositoryInterface $salesChannelRepository,
        StorefrontCartFacade $cartService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->salesChannelRepository = $salesChannelRepository;
        $this->cartService = $cartService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SalesChannelEvents::SALES_CHANNEL_WRITTEN => "onRetailChannelWritten",

            GenericPageLoadedEvent::class => 'pageLoadedEvent',
        ];
    }

    public function pageLoadedEvent(GenericPageLoadedEvent $event): void
    {
        /** @var HelretBeforeCartLoadEvent $beforeLoad */
        $beforeLoad = $this->eventDispatcher->dispatch(new HelretBeforeCartLoadEvent([
            'frontend.checkout', // Checkout pages has cart loaded.
        ], $event->getSalesChannelContext()));

        if ($beforeLoad->shouldSkipCartLoad() ||
            !($route = $event->getRequest()->attributes->get('_route'))
        ) {
            return;
        }

        foreach ($beforeLoad->getIgnored() as $name) {
            if (strpos($route, $name) === 0) {
                return;
            }
        }

        $event->getPage()->addExtension(
            'helretCart',
            $this->cartService->get(
                $event->getSalesChannelContext()->getToken(),
                $event->getSalesChannelContext()
            )
        );
    }


    /**
     * @deprecated Will be removed in Shopware:v6.5
     */
    public function onRetailChannelWritten(EntityWrittenEvent $event): void
    {
        /* try catch in case writeResults are empty */
        try {
            $criteria = new Criteria([$event->getWriteResults()[0]->getPrimaryKey()]);
            $salesChannel = $this
                ->salesChannelRepository
                ->search($criteria, $event->getContext())
                ->getEntities()
                ->first();
        } catch (\Exception $e) {
            $salesChannel = null;
        }

        /* If not a hello retail channel, break! */
        if ($salesChannel == null || HelretHelloRetail::SALES_CHANNEL_TYPE_HELLO_RETAIL != $salesChannel->getTypeId()) {
            return;
        }

        /* update payloads if is first time run */
        foreach ($event->getPayloads() as $payload) {
            $updateStatement = [];
            /* If payload for feeds set */
            if (isset($payload['configuration']) && isset($payload['configuration']['feeds'])) {
                /* save payload, as we are going to pass it through with a few edits */
                $updateStatement = $this->updateFeed($payload);
            }
            /* if changed */
            if (count($event->getPayloads()) > 0
                && !empty($updateStatement)
                && $event->getPayloads()[0] != $updateStatement) {
                $this->salesChannelRepository->update([$updateStatement], $event->getContext());
            }
        }
    }

    /**
     * @param array $feed
     * @return string
     */
    private function getFeedFile(array $feed): string
    {
        if ($feed['file'] == null && isset($feed['name'])) {
            if ($feed['name'] == 'product') {
                return HelretHelloRetail::PRODUCT_FEED;
            } elseif ($feed['name'] == 'order') {
                return HelretHelloRetail::ORDER_FEED;
            } elseif ($feed['name'] == 'category') {
                return HelretHelloRetail::CATEGORY_FEED;
            }
        }

        return "unknown.xml";
    }

    /**
     * @param array $payload
     * @return array
     */
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
