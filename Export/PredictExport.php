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
use Thelia\Model\Address;
use Thelia\Model\Customer;

/**
 * Class PredictExport
 * @package Predict\Export
 * @author Benjamin Perche <bperche9@gmail.com>
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
        $content =  "";

        /**
         * File Header
         */

        $content .= '$' . "VERSION=110" . self::CRLF;       // N° 1 & 2 Header

        /**
         * Entries loop
         */

        /** @var ExportEntry $export_entry */
        foreach($this->exports as $export_entry) {

            /**
             * Delivery header
             */

            // N°1 Customer reference n1
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 2); // N°2 Filler
            // N°3 Weight in dag (Decagrams) || NUMERIC


            /**
             * Delivery address
             */

            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 15); // N°4 Filler

            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°5 Delivery name
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°6 Address 1
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°7 Address 2
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°8 Address 3
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°9 Address 4
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°10 Address 5
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 10); // N°11 Zipcode
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°12 City
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 10); // N°13 Filler

            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 35); // N°14 Street
            $content .= $this->harmonise("", self::ALPHA_NUMERIC, 10); // N°15 Filler






            /**
             * Sender's address
             */
        }

        return Response::create($content);
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