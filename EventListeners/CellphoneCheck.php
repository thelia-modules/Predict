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
use Thelia\Action\BaseAction;
use Thelia\Core\Event\Address\AddressCreateOrUpdateEvent;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\Translation\Translator;
use Thelia\Form\Exception\FormValidationException;

/**
 * Class CellphoneCheck
 * @package Predict\EventListeners
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class CellphoneCheck extends BaseAction implements EventSubscriberInterface
{
    /** @var  Request */
    protected $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param  OrderEvent                                     $event
     * @throws \Thelia\Form\Exception\FormValidationException
     */
    public function cellphoneCheck(OrderEvent $event)
    {
        if (Predict::getModuleId() === $event->getDeliveryModule()) {
            $cellphone = $this->request->request->get("predict_cellphone");
            $cellphone = str_replace(array(' ', '.', '-', ',', ';', '/', '\\', '(', ')'),'', $cellphone);

            $partial_number = "";
            if (empty($cellphone) || !preg_match('#^[0|\+33][6-7]([0-9]{8})$#', $cellphone, $partial_number)) {
                throw new FormValidationException(
                    Translator::getInstance()->trans(
                        "You must give a cellphone number in order to use Predict services",
                        [], Predict::MESSAGE_DOMAIN
                    )
                );
            }

            $cellphone = str_replace("+33","0",$cellphone);

            $banned_cellphones = array(
                '00000000','11111111','22222222','33333333',
                '44444444','55555555','66666666','77777777',
                '88888888','99999999','12345678','23456789',
                '98765432'
            );

            if (in_array($partial_number[1], $banned_cellphones)) {
                throw new FormValidationException(
                    Translator::getInstance()->trans(
                        "This phone number is not valid",
                        [], Predict::MESSAGE_DOMAIN
                    )
                );
            }
            /** @var \Thelia\Model\Customer $customer */
            $customer =$this->getRequest()->getSession()
                ->getCustomerUser();

            $address = $customer->getDefaultAddress();
            $addressEvent = new AddressCreateOrUpdateEvent(
                $address->getLabel(),
                $address->getTitleId(),
                $address->getFirstname(),
                $address->getLastname(),
                $address->getAddress1(),
                $address->getAddress2(),
                $address->getAddress3(),
                $address->getZipcode(),
                $address->getCity(),
                $address->getCountryId(),
                $cellphone,
                $address->getPhone(),
                $address->getCompany()
            );

            $addressEvent->setAddress($address);

            $dispatcher = $event->getDispatcher();

            $dispatcher->dispatch(TheliaEvents::ADDRESS_UPDATE, $addressEvent);
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
