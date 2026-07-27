<?php

declare(strict_types=1);

/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*************************************************************************************/

namespace Predict\EventListeners;

use Predict\Predict;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Api\Bridge\Propel\Event\DeliveryModuleOptionEvent;
use Thelia\Api\Resource\DeliveryModuleOption;
use Thelia\Core\Event\TheliaEvents;

/**
 * Exposes Predict as a delivery option in the Thelia 3 checkout (native API Platform bridge).
 *
 * Ported from the Thelia 2 / OpenApi implementation: the option now uses
 * Thelia\Api\Resource\DeliveryModuleOption and listens on the native
 * TheliaEvents::MODULE_DELIVERY_GET_OPTIONS event. Its code ("Predict") is what the
 * Flexy checkout template matches to display the cellphone field.
 */
class APIListener implements EventSubscriberInterface
{
    public function __construct(
        protected ContainerInterface $container,
        protected RequestStack $requestStack,
    ) {
    }

    public function getDeliveryModuleOptions(DeliveryModuleOptionEvent $deliveryModuleOptionEvent): void
    {
        if ($deliveryModuleOptionEvent->getModule()->getId() !== Predict::getModuleId()) {
            return;
        }

        $isValid = true;
        $orderPostage = null;

        $locale = $this->requestStack->getCurrentRequest()?->getSession()->getLang()->getLocale();

        $module = new Predict();
        $module->setContainer($this->container);

        $country = $deliveryModuleOptionEvent->getCountry();
        $cart = $deliveryModuleOptionEvent->getCart();

        if (null === $country || null === $cart) {
            $isValid = false;
        } else {
            try {
                $orderPostage = $module->getOrderPostage(
                    $country,
                    $cart->getWeight(),
                    $locale,
                    $cart->getTaxedAmount($country)
                );
            } catch (\Exception) {
                $isValid = false;
            }
        }

        $title = $deliveryModuleOptionEvent->getModule()->setLocale($locale)->getTitle();

        $deliveryModuleOption = new DeliveryModuleOption();
        $deliveryModuleOption
            ->setCode(Predict::getModuleCode())
            ->setValid($isValid)
            ->setTitle($title)
            ->setImage('')
            ->setMinimumDeliveryDate('')
            ->setMaximumDeliveryDate('')
            ->setPostage($orderPostage ? $orderPostage->getAmount() : 0)
            ->setPostageTax($orderPostage ? $orderPostage->getAmountTax() : 0)
            ->setPostageUntaxed($orderPostage ? $orderPostage->getAmount() - $orderPostage->getAmountTax() : 0);

        $deliveryModuleOptionEvent->appendDeliveryModuleOptions($deliveryModuleOption);
    }

    public static function getSubscribedEvents(): array
    {
        $listenedEvents = [];

        if (class_exists(DeliveryModuleOptionEvent::class)) {
            $listenedEvents[TheliaEvents::MODULE_DELIVERY_GET_OPTIONS] = ['getDeliveryModuleOptions', 129];
        }

        return $listenedEvents;
    }
}
