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

use Predict\Model\PredictFreeshipping;
use Symfony\Component\HttpFoundation\JsonResponse;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\HttpFoundation\Response;

/**
 * Class FreeShipping
 * @package Predict\Controller
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class FreeShipping extends BaseAdminController {
    public function set() {
        $form = new \Predict\Form\FreeShipping($this->getRequest());
        $response=null;

        try {
            $vform = $this->validateForm($form);
            $data = $vform->get('freeshipping')->getData();

            $save = new PredictFreeshipping();
            $save->setActive(!empty($data))->save();
            $response = Response::create('');
        } catch (\Exception $e) {
            $response = JsonResponse::create(array("error"=>$e->getMessage()), 500);
        }
        return $response;
    }
}