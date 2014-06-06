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

namespace Predict\Controller;
use Predict\Predict;
use Thelia\Model\AreaQuery;
use Thelia\Controller\Admin\BaseAdminController;

/**
 * Class EditPrices
 * @package Predict\Controller
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class EditPrices extends BaseAdminController
{

    public function edit_prices()
    {
        // Get data & treat
        $post = $this->getRequest();
        $operation = $post->get('operation');
        $area = $post->get('area');
        $weight = $post->get('weight');
        $price = $post->get('price');
        if( preg_match("#^add|delete$#", $operation) &&
            preg_match("#^\d+$#", $area) &&
            preg_match("#^\d+\.?\d*$#", $weight)
          ) {
            // check if area exists in db
            $exists = AreaQuery::create()
                ->findPK($area);
            if ($exists !== null) {
                $json_path= __DIR__."/../".Predict::JSON_PRICE_RESOURCE;

                if (is_readable($json_path)) {
                    $json_data = json_decode(file_get_contents($json_path),true);
                } elseif(!file_exists($json_path)) {
                    $json_data = array();
                } else {
                    throw new \Exception("Can't read Predict".Predict::JSON_PRICE_RESOURCE.". Please change the rights on the file.");
                }
                if((float) $weight > 0 && $operation == "add"
                  && preg_match("#\d+\.?\d*#", $price)) {
                    $json_data[$area]['slices'][$weight] = round((float)$price, 2);
                } elseif ($operation == "delete") {
                    if(isset($json_data[$area]['slices'][$weight]))
                        unset($json_data[$area]['slices'][$weight]);
                } else {
                    throw new \Exception("Weight must be superior to 0");
                }
                ksort($json_data[$area]['slices']);
                if ((file_exists($json_path) ?is_writable($json_path):is_writable(__DIR__."/../"))) {
                    $file = fopen($json_path, 'w');
                    fwrite($file, json_encode($json_data));;
                    fclose($file);
                } else {
                    throw new \Exception("Can't write Predict".Predict::JSON_PRICE_RESOURCE.". Please change the rights on the file.");
                }
            } else {
                throw new \Exception("Area not found");
            }
          } else {
            throw new \ErrorException("Arguments are missing or invalid");
          }

        $this->redirectToRoute(
            "admin.module.configure",
            [],
            array (
                'module_code'=>"Predict"                                                        ,
                '_controller' => 'Thelia\\Controller\\Admin\\ModuleController::configureAction' ,
                'tab' => 'prices'                                                                        ,
            )
        );
    }
}
