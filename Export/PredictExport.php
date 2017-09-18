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

use Predict\Predict;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Model\ConfigQuery;
use Thelia\Model\Country;
use Thelia\Model\CountryQuery;

/**
 * Class PredictExport
 * @package Predict\Export
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class PredictExport
{
    const CRLF = "\r\n";

    const NUMERIC = "numeric";
    const ALPHANUM = "alphanumeric";
    const FLOAT = "float";

    /** @var array */
    protected $exports=array();

    public static function create()
    {
        return new static();
    }

    public function addEntry(ExportEntry $export_entry)
    {
        if (!$export_entry->isValid()) {
            throw new \InvalidArgumentException("An export entry is not valid, the customer ".$export_entry->getCustomer()->getRef()." may have no cellphone");
        }

        $this->exports[] = $export_entry;
    }

    /**
     * @return string
     *                  Returns the generated file as a string
     */
    public function doExport()
    {
        /**
         * @var string $content
         * Contains the export content as a string
         */
        // export data
        $content            = ""                                                ;

        // stores information
        // Compute store's country
        $store_country_id   = ConfigQuery::read("store_country")                ;
        $store_country_o    = CountryQuery::create()->findPk($store_country_id) ;

        if ($store_country_o === null) {
            throw new \Exception("The country of the store doesn't exist");
        }

        $store_country = static::translateCountry($store_country_o);

        if ($store_country === null) {
            throw new \Exception("The store's country doesn't exist in Predict");
        }
        // ---

        $store_name                 = ConfigQuery::read("store_name")                   ;
        $store_email                = ConfigQuery::read("store_email")                  ;
        $store_zipcode              = ConfigQuery::read("store_zipcode")                ;
        $store_city                 = ConfigQuery::read("store_city")                   ;
        $store_address1             = ConfigQuery::read("store_address1")               ;
        $store_phone                = ConfigQuery::read("store_phone")                  ;
        $store_dpdcode              = ConfigQuery::read("dpd_account_code", false);
        $store_predict_option_raw   = Predict::getConfigValue("dpd_predict_option", false);
        $store_cellphone            = ConfigQuery::read("store_cellphone")              ;
        $store_predict_option       = !!$store_predict_option_raw ? "+" : ""            ;

        $return_type = Predict::getConfigValue(Predict::KEY_RETURN_TYPE, Predict::RETURN_NONE);

        if (!$store_dpdcode) {
            throw new \Exception("The DPD account code has not been set.");
        }

        /**
         * File Header
         */

        $content .= '$' . "VERSION=110" . self::CRLF; // N° 1 & 2 Header MANDATORY

        /**
         * Entries loop
         */

        /** @var ExportEntry $export_entry */
        foreach ($this->exports as $export_entry) {
            /**
             * Used objects
             */
            $order_address      = $export_entry->getDeliveryOrderAddress()                          ;
            $customer           = $export_entry->getCustomer()                                      ;
            $customer_name      = $customer->getLastname() . " " . $customer->getFirstname()        ;
            $delivery_country   = static::translateCountry($export_entry->getDeliveryOrderCountry());
            $order              = $export_entry->getOrder()                                         ;
            $price              = 0.0                                                               ;
            $price              = $order->getTotalAmount($price,false)                              ;

            // Compute order's weight
            $weight             = 0.0                                                               ;

            foreach ($order->getOrderProducts() as $p) {
                $weight += ((float) $p->getWeight())*(int) $p->getQuantity();
            }

            $weight         = floor($weight*100)                                        ;
            $guaranty_price = ($export_entry->isGuaranteed()) ? $price : 0              ;
            $date           = date("d/m/Y", $order->getUpdatedAt()->getTimestamp())     ;

            // ---
            // Compute cellphone
            $address        = $customer->getDefaultAddress();

            if ($address === null) {
                throw new \Exception("The address doesn't exist");
            }
            $cellphone = $address->getCellphone();

            if (empty($cellphone)) {
                throw new \Exception("The cellphone of the customer ".$customer->getId()." is empty");
            }




            $content .= self::harmonise($order->getRef(), self::ALPHANUM, 35);              // 1. Customer ref #1 = Order ref | MANDATORY
            $content .= self::harmonise("", self::ALPHANUM, 2);                             // 2. Filler
            $content .= self::harmonise($weight, self::NUMERIC, 8);                         // 3. Package weight
            $content .= self::harmonise("", self::ALPHANUM, 15);                            // 4. Filler
            $content .= self::harmonise($customer_name, self::ALPHANUM, 35);                // 5. Delivery name | MANDATORY
            $content .= self::harmonise($order_address->getAddress1(), self::ALPHANUM, 35); // 6. Delivery firstname
            $content .= self::harmonise($order_address->getAddress2(), self::ALPHANUM, 35); // 7. Delivery address 2
            $content .= self::harmonise($order_address->getAddress3(), self::ALPHANUM, 35); // 8. Delivery address 3
            $content .= self::harmonise("", self::ALPHANUM, 35);                            // 9. Delivery address 4 | SKIPPED
            $content .= self::harmonise("", self::ALPHANUM, 35);                            // 10. Delivery address 5 | SKIPPED
            $content .= self::harmonise($order_address->getZipcode(), self::ALPHANUM, 10);  // 11. Delivery zipcode | MANDATORY
            $content .= self::harmonise($order_address->getCity(), self::ALPHANUM, 35);     // 12. Delivery city | MANDATORY
            $content .= self::harmonise("", self::ALPHANUM, 10);                            // 13. Filler
            $content .= self::harmonise($order_address->getAddress1(), self::ALPHANUM, 35); // 14. Delivery street | MANDATORY
            $content .= self::harmonise("", self::ALPHANUM, 10);                            // 15. Filler
            $content .= self::harmonise($delivery_country, self::ALPHANUM, 3);              // 16. Delivery country code | MANDATORY
            $content .= self::harmonise($order_address->getPhone(), self::ALPHANUM, 20);    // 17. Delivery phone


            // Expeditor address

            $content .= self::harmonise("", self::ALPHANUM, 25);                            // 18. Filler
            $content .= self::harmonise($store_name, self::ALPHANUM, 35);                   // 19. Expeditor name
            $content .= self::harmonise($store_address1, self::ALPHANUM, 35);               // 20. Expeditor address
            $content .= self::harmonise("", self::ALPHANUM, 140);                           // 21-24. Filler
            $content .= self::harmonise($store_zipcode, self::ALPHANUM, 10);                // 25. Expeditor zipcode
            $content .= self::harmonise($store_city, self::ALPHANUM, 35);                   // 26. Expeditor city
            $content .= self::harmonise("", self::ALPHANUM, 10);                            // 27. Filler
            $content .= self::harmonise($store_address1, self::ALPHANUM, 35);               // 28. Expeditor street
            $content .= self::harmonise("", self::ALPHANUM, 10);                            // 29. Filler
            $content .= self::harmonise($store_country, self::ALPHANUM, 3);                 // 30. Expeditor country code
            $content .= self::harmonise($store_phone, self::ALPHANUM, 20);                  // 31. Expeditor phone
            $content .= self::harmonise("", self::ALPHANUM, 10);                            // 32. Filler
            $content .= self::harmonise("", self::ALPHANUM, 35);                            // 33. Order comment 1
            $content .= self::harmonise("", self::ALPHANUM, 35);                            // 34. Order comment 2
            $content .= self::harmonise("", self::ALPHANUM, 35);                            // 35. Order comment 3
            $content .= self::harmonise("", self::ALPHANUM, 35);                            // 36. Order comment 4
            $content .= self::harmonise($date, self::ALPHANUM, 10);                         // 37. Expedition date
            $content .= self::harmonise($store_dpdcode, self::NUMERIC, 8);                      // 38. Expeditor DPD code
            $content .= self::harmonise("", self::ALPHANUM, 35);                            // 39. Bar code
            $content .= self::harmonise($customer->getRef(), self::ALPHANUM, 35);           // 40. Customer ref #2
            $content .= self::harmonise("", self::ALPHANUM, 29);                            // 41. Filler
            $content .= self::harmonise($guaranty_price, self::FLOAT, 9);                   // 42. Insured value
            $content .= self::harmonise("", self::ALPHANUM, 8);                             // 43. Filler
            $content .= self::harmonise($customer->getId(), self::ALPHANUM, 35);            // 44. Customer ref #3
            $content .= self::harmonise("", self::ALPHANUM, 1);                             // 45. Filler
            $content .= self::harmonise("", self::ALPHANUM, 35);                            // 46. Consolidation number | SKIPPED
            $content .= self::harmonise("", self::ALPHANUM, 10);                            // 47. Filler
            $content .= self::harmonise($store_email, self::ALPHANUM, 80);                  // 48. Expeditor email
            $content .= self::harmonise($store_cellphone, self::ALPHANUM, 35);              // 49. Expeditor cellphone
            $content .= self::harmonise($customer->getEmail(), self::ALPHANUM, 80);         // 50. Customer email
            $content .= self::harmonise($cellphone, self::ALPHANUM, 35);                    // 51. Customer cellphone
            $content .= self::harmonise("", self::ALPHANUM, 96);                            // 52. Filler
            $content .= self::harmonise("", self::ALPHANUM, 8);                             // 53. DPD relay ID | SKIPPED
            $content .= self::harmonise("", self::ALPHANUM, 113);                           // 54. Filler
            $content .= self::harmonise("", self::ALPHANUM, 2);                             // 55. Consolidation type | SKIPPED
            $content .= self::harmonise("", self::ALPHANUM, 2);                             // 56. Consolidation attribute | SKIPPED
            $content .= self::harmonise("", self::ALPHANUM, 1);                             // 57. Filler
            $content .= self::harmonise($store_predict_option, self::NUMERIC, 1);           // 58. Predict
            $content .= self::harmonise($customer_name, self::ALPHANUM, 35);                // 59. Contact name
            $content .= self::harmonise("", self::ALPHANUM, 10);                            // 60. Digicode1 | SKIPPED
            $content .= self::harmonise("", self::ALPHANUM, 10);                            // 61. Digicode2 | SKIPPED
            $content .= self::harmonise("", self::ALPHANUM, 10);                            // 62. Intercom | SKIPPED


            // Return address

            if ($return_type != Predict::RETURN_NONE) {
                $content .= self::harmonise("", self::ALPHANUM, 200);                          // 63. Filler
                $content .= self::harmonise($return_type, self::NUMERIC, 1);                   // 64. Return type
                $content .= self::harmonise("", self::ALPHANUM, 15);                           // 65. Filler
                $content .= self::harmonise($store_name, self::ALPHANUM, 35);                  // 66. Return name
                $content .= self::harmonise($store_address1, self::ALPHANUM, 35);              // 67. Return address 1
                $content .= self::harmonise("", self::ALPHANUM, 35);                           // 68. Return address 2 | SKIPPED
                $content .= self::harmonise("", self::ALPHANUM, 35);                           // 69. Return address 3 | SKIPPED
                $content .= self::harmonise("", self::ALPHANUM, 35);                           // 70. Return address 4 | SKIPPED
                $content .= self::harmonise("", self::ALPHANUM, 35);                           // 71. Return address 5 | SKIPPED
                $content .= self::harmonise($store_zipcode, self::ALPHANUM, 10);               // 72. Return zipcode
                $content .= self::harmonise($store_city, self::ALPHANUM, 35);                  // 73. Return city
                $content .= self::harmonise("", self::ALPHANUM, 10);                           // 74. Filler
                $content .= self::harmonise($store_address1, self::ALPHANUM, 35);              // 75. Return street
                $content .= self::harmonise("", self::ALPHANUM, 10);                           // 76. Filler
                $content .= self::harmonise($store_country, self::ALPHANUM, 3);                // 77. Return country code
                $content .= self::harmonise($store_phone, self::ALPHANUM, 30);                 // 78. Return phone
                $content .= self::harmonise("", self::ALPHANUM, 18);                           // 79. CargoID | SKIPPED
                $content .= self::harmonise("", self::ALPHANUM, 35);                           // 80. Customer ref #4 | SKIPPED
            }

            $content .= self::CRLF;



        }

        return $content;
    }

    /**
     * @param  Country    $country
     * @return null|mixed
     */
    public static function translateCountry(Country $country)
    {
        $name = $country
            ->setLocale("en_US")
            ->getTitle();

        $name = str_replace(" ", "_", strtoupper($name));

        return defined($const="Predict\\Export\\CountryEnum::".$name) ? constant($const) : null;
    }

    // FONCTION POUR LE FICHIER D'EXPORT BY Maitre eroudeix@openstudio.fr
    // extended by bperche@openstudio.fr
    public static function harmonise($value, $type, $len)
    {
        switch ($type) {
            case self::NUMERIC:
                $value = (string)$value;
                if (mb_strlen($value, 'utf8') > $len) {
                    $value = substr($value, 0, $len);
                }
                for ($i = mb_strlen($value, 'utf8'); $i < $len; $i++) {
                    $value = '0' . $value;
                }
                break;
            case self::ALPHANUM:
                $value = (string)$value;
                if (mb_strlen($value, 'utf8') > $len) {
                    $value = substr($value, 0, $len);
                }
                for ($i = mb_strlen($value, 'utf8'); $i < $len; $i++) {
                    $value .= ' ';
                }
                break;
            case self::FLOAT:
                if (!preg_match("#\d{1,6}\.\d{1,}#", $value)) {
                    $value = str_repeat("0", $len - 3) . ".00";
                } else {
                    $value = explode(".", $value);
                    $int = self::harmonise($value[0], self::NUMERIC, $len - 3);
                    $dec = substr($value[1], 0, 2) . "." . substr($value[1], 2, strlen($value[1]));
                    $dec = (string)ceil(floatval($dec));
                    $dec = str_repeat("0", 2 - strlen($dec)) . $dec;
                    $value = $int . "." . $dec;
                }
                break;
        }

        return $value;
    }
}
