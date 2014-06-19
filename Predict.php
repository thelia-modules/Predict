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

namespace Predict;

use Predict\Model\PricesQuery;
use Propel\Runtime\Connection\ConnectionInterface;
use Thelia\Install\Database;
use Thelia\Model\Country;
use Thelia\Model\ModuleImageQuery;
use Thelia\Model\ModuleQuery;
use Thelia\Module\AbstractDeliveryModule;
use Thelia\Module\Exception\DeliveryException;

/**
 * Class Predict
 * @package Predict
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class Predict extends AbstractDeliveryModule
{

    const MESSAGE_DOMAIN = 'predict';

    const JSON_PRICE_RESOURCE = "/Config/prices.json";

    /**
     * This method is called by the Delivery loop, to check if the current module has to be displayed to the customer.
     * Override it to implements your delivery rules/
     *
     * If you return true, the delivery method will de displayed to the customer
     * If you return false, the delivery method will not be displayed
     *
     * @param Country $country the country to deliver to.
     *
     * @return boolean
     */
    public function isValidDelivery(Country $country)
    {
        $cartWeight = $this->getRequest()->getSession()->getCart()->getWeight();

        $areaId = $country->getAreaId();

        $prices = PricesQuery::getPrices();

        /* check if Predict delivers the asked area */
        if (isset($prices[$areaId]) && isset($prices[$areaId]["slices"])) {

            $areaPrices = $prices[$areaId]["slices"];
            ksort($areaPrices);

            /* check this weight is not too much */
            end($areaPrices);

            $maxWeight = key($areaPrices);
            if ($cartWeight <= $maxWeight) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * calculate and return delivery price
     *
     * @param  Country           $country
     * @return mixed
     * @throws DeliveryException
     */
    public function getPostage(Country $country)
    {
        $cartWeight = $this->getRequest()->getSession()->getCart()->getWeight();

        $postage = PricesQuery::getPostageAmount(
            $country->getAreaId(),
            $cartWeight
        );

        return $postage;
    }

    public function getCode()
    {
        return 'Predict';
    }

    public function postActivation(ConnectionInterface $con = null)
    {
        $database = new Database($con);

        $database->insertSql(null, [__DIR__ . '/Config/insert.sql']);

        /* insert the images from image folder if first module activation */
        $module = $this->getModuleModel();
        if(ModuleImageQuery::create()->filterByModule($module)->count() == 0) {
            $this->deployImageFolder($module, sprintf('%s/media', __DIR__), $con);
        }

        /* set module title */
        $this->setTitle(
            $module,
            array(
                "en_US" => "Predict by Exapaq",
                "fr_FR" => "Predict par Exapaq",
            )
        );
    }

    public static function getModuleId()
    {
        return ModuleQuery::create()->findOneByCode("Predict")->getId();
    }
}
