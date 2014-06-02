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
use Thelia\Model\Country;

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

    public function addExport(ExportEntry $export_entry)
    {
        if(!$export_entry->isValid()) {
            throw new \InvalidArgumentException("An export entry is not valid");
        }

        $this->exports[] = $export_entry;
    }

    /**
     * @return Response
     * Returns the generated file as a Thelia Response
     */
    public function doExport()
    {
        /**
         * @var string $content
         * Contains the export content as a string
         */
        $content = ""       ;
        $error   = false    ;

        /**
         * File Header
         */

        $content .= '$' . "VERSION=110" . self::CRLF;                   // N° 1 & 2 Header MANDATORY

        /**
         * Entries loop
         */

        /** @var ExportEntry $export_entry */
        foreach($this->exports as $export_entry) {

            /**
             * Delivery header
             */

            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35);  // N°1 Customer reference n°1 MANDATORY
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 2);   // N°2 Filler
            $content .= $this->harmonise(0, self::NUMERIC, 8);          // N°3 Weight in dag (Decagrams) || NUMERIC
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 15); // N°4 Filler

            /**
             * Delivery address
             */

            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°5 Delivery name MANDATORY
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°6 Delivery Address 1
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°7 Delivery Address 2
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°8 Delivery Address 3
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°9 Delivery Address 4
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°10 Delivery Address 5
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 10); // N°11 Delivery Zipcode MANDATORY
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°12 Delivery City MANDATORY
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 10); // N°13 Filler

            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°14 Delivery Street
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 10); // N°15 Filler

            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 3) ;  // N°16 Delivery Country code
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 20);  // N°17 Delivery phone
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 25); // N°18 Filler

            /**
             * Sender's address
             */

            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°19 Sender's name
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°20 Sender's address
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°21 Filler
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°22 Filler
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°23 Filler
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°24 Filler

            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 10); // N°25 Sender's Zipcode
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°26 Sender's City
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 10); // N°27 Filler

            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°28 Sender's Street
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 10); // N°29 Filler

            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 3) ; // N°30 Sender's Country code
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 20); // N°31 Sender's phone
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 10); // N°32 Filler

            /**
             * Sender and order extra information
             */

            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°33 Comment 1
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°34 Comment 2
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°35 Comment 3
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°36 Comment 4
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 10); // N°37 Expected sending date ( dd/mm/yyyy )
            $content .= $this->harmonise("", self::NUMERIC, 8)       ; // N°38 Exapaq account number
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°39 Barcode
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°40 Order reference
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 29); // N°41 Filler

            $content .= $this->harmonise("", self::FLOAT, 9)         ; // N°42 Price
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 8) ; // N°43 Filler

            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°44 Customer reference n°2
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 10); // N°45 Filler

            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°46 "Numéro de consolidation"
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 10); // N°47 Filler

            /**
             * Meta information and complementary delivery information
             */

            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 80); // N°48 Sender's email
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°49 Sender's cellphone
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 80); // N°50 Customer's email
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°51 Customer's cellphone
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 217); // N°52 Filler

            $content .= $this->harmonise("", self::NUMERIC, 2)       ; // N°53 "Consolidation / type"
            $content .= $this->harmonise("", self::NUMERIC, 2)       ; // N°54 "Consolidation / attribut"
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 1) ; // N°55 Filler
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 1) ; // N°56 Predict option, must be validated by Exapaq. put "+" to activate
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°57 Contact's name ???
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 10); // N°58 Digicode 1
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 10); // N°59 Digicode 2
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 10); // N°60 Intercom


            /**
             * End
             */

            $content .= self::CRLF; // N°61 EOL

        }

        if($error) {
            return Response::create($content, 500);
        }


        return Response::create(
            $content,
            200,
            array(
                'Content-Type' => 'application/csv-tab-delimited-table',
                'Content-disposition' => 'filename=export.dat',
            )
        );
    }

    /**
     * @param Country $country
     * @return null|mixed
     */
    public static function translateCountry(Country $country)
    {
        $name = $country
            ->setLocale("en_US")
            ->getTitle();

        $name = str_replace(" ", "_", strtoupper($name));

        if (!isset( CountryEnum::$name )) {
            return null;
        }

        return CountryEnum::$name;
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
                if (!preg_match("#\\d{1,6}(\\.\\d*)?#",$value)) {
                    $value=str_repeat("0",$len-3).".00";
                } else {
                    $value=explode(".",$value);
                    $int = self::harmonise($value[0],'numeric',$len-3);
                    $dec = substr($value[1],0,2).".".substr($value[1],2, strlen($value[1]));
                    $dec = (string) ceil(floatval($dec));
                    $dec = str_repeat("0", 2-strlen($dec)).$dec;
                    $value=$int.".".$dec;
                }
                break;
        }

        return $value;
    }
} 