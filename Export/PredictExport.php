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
use Thelia\Core\HttpFoundation\Response;
use Thelia\Model\AddressQuery;
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
    const ALPHA_NUMERIC = "alphanumeric";
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
            throw new \InvalidArgumentException("An export entry is not valid");
        }

        $this->exports[] = $export_entry;
    }

    /**
     * @return Response
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
        $store_exapaq_account       = ConfigQuery::read("store_exapaq_account")         ;
        $store_predict_option_raw   = ConfigQuery::read("store_predict_option")         ;
        $store_cellphone            = ConfigQuery::read("store_cellphone")              ;
        $store_predict_option       = !!$store_predict_option_raw ? "+" : ""            ;


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
            $address        = AddressQuery::create()->findPk($order_address->getId())   ;

            if ($address === null) {
                throw new \Exception("The address doesn't exist");
            }
            $cellphone = $address->getCellphone();

            if (empty($cellphone)) {
                throw new \Exception("The cellphone of the customer ".$customer->getId()." is empty");
            }

            // ---
            /**
             * Delivery header
             */

            $content .= $this->harmonise($customer->getRef(), self::ALPHA_NUMERIC, 35)          ; // N°1 Customer reference n°1 MANDATORY
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 2)                            ; // N°2 Filler
            $content .= $this->harmonise($weight, self::NUMERIC, 8)                             ; // N°3 Weight in dag (Decagrams) || NUMERIC
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 15)                           ; // N°4 Filler

            /**
             * Delivery address
             */

            $content .= $this->harmonise($customer_name , self::ALPHA_NUMERIC, 35)              ; // N°5 Delivery name MANDATORY
            $content .= $this->harmonise($order_address->getAddress1(), self::ALPHA_NUMERIC, 35); // N°6 Delivery Address 1
            $content .= $this->harmonise($order_address->getAddress2(), self::ALPHA_NUMERIC, 35); // N°7 Delivery Address 2
            $content .= $this->harmonise($order_address->getAddress3(), self::ALPHA_NUMERIC, 35); // N°8 Delivery Address 3
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35)                           ; // N°9 Delivery Address 4
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35)                           ; // N°10 Delivery Address 5
            $content .= $this->harmonise($order_address->getZipcode(), self::ALPHA_NUMERIC, 10) ; // N°11 Delivery Zipcode MANDATORY
            $content .= $this->harmonise($order_address->getCity(), self::ALPHA_NUMERIC, 35)    ; // N°12 Delivery City MANDATORY
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 10)                           ; // N°13 Filler

            $content .= $this->harmonise($order_address->getAddress1(), self::ALPHA_NUMERIC, 35); // N°14 Delivery Street
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 10)                           ; // N°15 Filler

            $content .= $this->harmonise($delivery_country, self::ALPHA_NUMERIC, 3)             ; // N°16 Delivery Country code
            $content .= $this->harmonise($order_address->getPhone(), self::ALPHA_NUMERIC, 20)   ; // N°17 Delivery phone
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 25)                           ; // N°18 Filler

            /**
             * Sender's address
             */

            $content .= $this->harmonise($store_name, self::ALPHA_NUMERIC, 35)                  ; // N°19 Sender's name
            $content .= $this->harmonise($store_address1, self::ALPHA_NUMERIC, 35)              ; // N°20 Sender's address
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35)                           ; // N°21 Filler
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35)                           ; // N°22 Filler
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35)                           ; // N°23 Filler
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35)                           ; // N°24 Filler

            $content .= $this->harmonise($store_zipcode, self::ALPHA_NUMERIC, 10)               ; // N°25 Sender's Zipcode
            $content .= $this->harmonise($store_city, self::ALPHA_NUMERIC, 35)                  ; // N°26 Sender's City
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 10)                           ; // N°27 Filler

            $content .= $this->harmonise($store_address1, self::ALPHA_NUMERIC, 35)              ; // N°28 Sender's Street
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 10)                           ; // N°29 Filler

            $content .= $this->harmonise($store_country, self::ALPHA_NUMERIC, 3)                ; // N°30 Sender's Country code
            $content .= $this->harmonise($store_phone, self::ALPHA_NUMERIC, 20)                 ; // N°31 Sender's phone
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 10)                           ; // N°32 Filler

            /**
             * Sender and order extra information
             */

            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35)                           ; // N°33 Comment 1
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35)                           ; // N°34 Comment 2
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35)                           ; // N°35 Comment 3
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35)                           ; // N°36 Comment 4
            $content .= $this->harmonise($date, self::ALPHA_NUMERIC, 10)                        ; // N°37 Expected sending date ( dd/mm/yyyy )
            $content .= $this->harmonise($store_exapaq_account, self::NUMERIC, 8)               ; // N°38 Exapaq account number
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35)                           ; // N°39 Barcode
            $content .= $this->harmonise($order->getRef(), self::ALPHA_NUMERIC, 35)             ; // N°40 Order reference
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 29)                           ; // N°41 Filler

            $content .= $this->harmonise($guaranty_price, self::FLOAT, 9)                       ; // N°42 Price
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 8)                            ; // N°43 Filler

            $content .= $this->harmonise($customer->getId(), self::ALPHA_NUMERIC, 35)           ; // N°44 Customer reference n°2
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 10)                           ; // N°45 Filler

            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35)                           ; // N°46 "Numéro de consolidation"
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 10)                           ; // N°47 Filler

            /**
             * Meta information and complementary delivery information
             */

            $content .= $this->harmonise($store_email, self::ALPHA_NUMERIC, 80)                 ; // N°48 Sender's email
            $content .= $this->harmonise($store_cellphone, self::ALPHA_NUMERIC, 35)             ; // N°49 Sender's cellphone
            $content .= $this->harmonise($customer->getEmail(), self::ALPHA_NUMERIC, 80)        ; // N°50 Customer's email
            $content .= $this->harmonise($cellphone, self::ALPHA_NUMERIC, 35)                   ; // N°51 Customer's cellphone
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 217)                          ; // N°52 Filler

            $content .= $this->harmonise("", self::NUMERIC, 2)                                  ; // N°53 "Consolidation / type"
            $content .= $this->harmonise("", self::NUMERIC, 2)                                  ; // N°54 "Consolidation / attribut"
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 1)                            ; // N°55 Filler

            $content .= $this->harmonise($store_predict_option, self::ALPHA_NUMERIC, 1)         ; // N°56 Predict option, must be validated by Exapaq. put "+" to activate
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35)                           ; // N°57 Contact's name (???)
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 10)                           ; // N°58 Digicode 1 | not handled in Thelia
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 10)                           ; // N°59 Digicode 2 | not handled in Thelia
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 10)                           ; // N°60 Intercom

            /**
             * End
             */

            $content .= self::CRLF                                                              ; // N°61 EOL

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

        $reflect_country = new \ReflectionClass("Predict\\Export\\CountryEnum");

        if (!$reflect_country->hasConstant($name)) {
            return null;
        }

        return $reflect_country->getConstant($name);
    }

    // FONCTION POUR LE FICHIER D'EXPORT BY Maitre eroudeix@openstudio.fr
    // extended by bperche@openstudio.fr
    public static function harmonise($value, $type, $len)
    {
        switch ($type) {
            case self::NUMERIC:
                $value = (string) $value;
                if(mb_strlen($value, 'utf8') > $len);
                $value = substr($value, 0, $len);
                for ($i = mb_strlen($value, 'utf8'); $i < $len; $i++) {
                    $value = '0' . $value;
                }
                break;
            case self::ALPHA_NUMERIC:
                $value = (string) $value;
                if(mb_strlen($value, 'utf8') > $len);
                $value = substr($value, 0, $len);
                for ($i = mb_strlen($value, 'utf8'); $i < $len; $i++) {
                    $value .= ' ';
                }
                break;
            case self::FLOAT:
                $data = @(float) $value;
                if($data === false) {
                    throw new \Exception("Can't cast \"".$value."\" as a float");
                }
                $data = sprintf("%.2f", $data);
                if(strlen($data) > 9) {
                    throw new \Exception("You can't guaranty a package of ".$data."€ with Predict.");
                }
                while(strlen($data) < 9) $data = "0".$data;
                $value=$data;
                break;

        }

        return $value;
    }
}
