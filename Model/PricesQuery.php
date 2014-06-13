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

namespace Predict\Model;
use Thelia\Core\Translation\Translator;
use Thelia\Module\Exception\DeliveryException;
use Predict\Predict;
use Thelia\Model\ConfigQuery;

/**
 * Class PricesQuery
 * @package Predict\Model
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class PricesQuery 
{

    public static $prices = null;

    public static function getPath()
    {
        return sprintf('%s/../%s', __DIR__, Predict::JSON_PRICE_RESOURCE);
    }

    public static function getPrices()
    {
        if (null === static::$prices) {
            static::$prices = json_decode(file_get_contents(static::getPath()), true);
        }

        return static::$prices;
    }

    /**
     * @param $areaId
     * @param $weight
     *
     * @return mixed
     * @throws DeliveryException
     */
    public static function getPostageAmount($areaId, $weight)
    {
        $freeshipping = @(bool)ConfigQuery::read("predict_freeshipping");
        $postage = 0;
        if (!$freeshipping) {
            $prices = static::getPrices();

            /* check if Predict delivers the asked area */
            if (!isset($prices[$areaId]) || !isset($prices[$areaId]["slices"])) {
                throw new DeliveryException("Predict delivery unavailable for the chosen delivery country");
            }

            $areaPrices = $prices[$areaId]["slices"];
            ksort($areaPrices);

            /* check this weight is not too much */
            end($areaPrices);
            $maxWeight = key($areaPrices);
            if ($weight > $maxWeight) {
                throw new DeliveryException(sprintf("Predict delivery unavailable for this cart weight (%s kg)", $weight));
            }

            $postage = current($areaPrices);

            while (prev($areaPrices)) {
                if ($weight > key($areaPrices)) {
                    break;
                }

                $postage = current($areaPrices);
            }
        }

        return $postage;
    }

    public static function sliceExists($area_id, $weight)
    {
        if(static::$prices === null) {
            static::getPrices();
        }

        $area_id = (string)$area_id;
        $weight = (string)$weight;

        return array_key_exists($weight,static::$prices[$area_id]["slices"]);
    }

    /**
     * @param false|double $postage set false to remove the value, a double to set a value
     * @param string $area_id
     * @param string $weight
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public static function setPostageAmount($postage, $area_id, $weight)
    {
        if(static::$prices === null) {
            static::getPrices();
        }

        $area_id = (string)$area_id;
        $weight = (string)$weight;

        if(false === $postage && isset(static::$prices[$area_id]["slices"][$weight])) {
            unset(static::$prices[$area_id]["slices"][$weight]);
        } else if(false !== $price = @(double)$postage) {
            static::$prices[$area_id]["slices"][$weight] = $price;
        } else {
            throw new \InvalidArgumentException(
                Translator::getInstance()->trans("\$postage argument in PricesQuery::setPostageAmout must be numeric")
            );
        }



        if(!is_writable(static::getPath())) {
            throw new \Exception(
                Translator::getInstance()->trans("The file prices.json is not writable, please change the rights on this file.")
            );
        }

        ksort(static::$prices[$area_id]["slices"]);
        file_put_contents(static::getPath(), json_encode(static::$prices));
    }
}