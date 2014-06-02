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

namespace Predict\Export;
use Thelia\Model\CountryQuery;
use Thelia\Model\Customer;
use Thelia\Model\Order;
use Thelia\Model\OrderAddress;
use Thelia\Model\OrderAddressQuery;

/**
 * Class ExportEntry
 * @package Predict\Export
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class ExportEntry 
{
    /** @var \Thelia\Model\Order $order */
    protected $order;

    function __construct(Order $order) {
        $this->order = $order;
    }

    /**
     * @return \Thelia\Model\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->order->getCustomer();
    }

    /**
     * @return array|mixed|OrderAddress
     * @throws \UnexpectedValueException
     */
    public function getDeliveryOrderAddress()
    {
        $delivery_order_address_id = $this->order->getDeliveryOrderAddressId();
        $delivery_order_address = OrderAddressQuery::create()->findPk($delivery_order_address_id);

        if ($delivery_order_address === null) {
            throw new \UnexpectedValueException("The delivery address doesn't exist");
        }

        return $delivery_order_address;
    }

    /**
     * @return array|mixed|\Thelia\Model\Country
     * @throws \UnexpectedValueException
     */
    public function getDeliveryOrderCountry() {
        $country_id = $this->getDeliveryOrderAddress()->getCountryId();
        $country = CountryQuery::create()->findPK($country_id);

        if($country === null) {
            throw new \UnexpectedValueException("The country doesn't exist");
        }

        return $country;
    }

    /**
     * @return boolean
     * Check if the export entry is valid
     */
    public function isValid()
    {
        /**
         * Get country
         */

        $country = $this->getDeliveryOrderCountry();

        /**
         * Do the checks
         */

        $checks = $this->order->getCustomer()->getDefaultAddress()->getCellphone() === null;
        $checks &= PredictExport::translateCountry($country) === null;

        return $checks;
    }

} 