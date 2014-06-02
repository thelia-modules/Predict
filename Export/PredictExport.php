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
        $response = new Response();



        return $response;
    }


    // FONCTION POUR LE FICHIER D'EXPORT BY Maitre eroudeix@openstudio.fr
    // extended by bperche@openstudio.fr
    public static function harmonise($value, $type, $len)
    {
        switch ($type) {
            case 'numeric':
                $value = (string) $value;
                if(mb_strlen($value, 'utf8') > $len);
                $value = substr($value, 0, $len);
                for ($i = mb_strlen($value, 'utf8'); $i < $len; $i++) {
                    $value = '0' . $value;
                }
                break;
            case 'alphanumeric':
                $value = (string) $value;
                if(mb_strlen($value, 'utf8') > $len);
                $value = substr($value, 0, $len);
                for ($i = mb_strlen($value, 'utf8'); $i < $len; $i++) {
                    $value .= ' ';
                }
                break;
            case 'float':
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