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

    public static function getPrices()
    {
        if (null === self::$prices) {
            self::$prices = json_decode(file_get_contents(sprintf('%s%s', __DIR__, Predict::JSON_PRICE_RESOURCE)), true);
        }

        return self::$prices;
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
            $prices = self::getPrices();

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
}