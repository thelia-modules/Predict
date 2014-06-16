<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace Predict\EventListeners;
use Predict\Predict;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Translation\Translator;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Model\AddressQuery;

/**
 * Class CellphoneCheck
 * @package Predict\EventListeners
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class CellphoneCheck implements EventSubscriberInterface
{
    /**
     * @param  OrderEvent                                     $event
     * @throws \Thelia\Form\Exception\FormValidationException
     */
    public function cellphoneCheck(OrderEvent $event)
    {
        if (Predict::getModuleId() === $event->getDeliveryModule()) {
            $address_id = $event->getDeliveryAddress();

            $address = AddressQuery::create()
                ->findPk($address_id);

            if ($address === null) {
                throw new  FormValidationException(
                    Translator::getInstance()->trans("The address is not valid")
                );
            }

            $default_address = $address->getCustomer()->getDefaultAddress();

            $cellphone = $default_address->getCellphone();

            if (empty($cellphone)) {
                throw new FormValidationException(
                    Translator::getInstance()->trans(
                        "You must define the cellphone field in your default address in order to use Predict",
                        [], Predict::MESSAGE_DOMAIN
                    )
                );
            }
        }
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            TheliaEvents::ORDER_SET_DELIVERY_ADDRESS => array("cellphoneCheck", 128),
        );
    }

}
