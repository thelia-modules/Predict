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
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\Template\TemplateDefinition;

/**
 * Class DeliveryModuleList
 * @package Predict\Controller
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class DeliveryModuleList extends BaseFrontController
{
    public function replace()
    {
        $country = $this->getRequest()->get(
            'country_id',
            $this->container->get('thelia.taxEngine')->getDeliveryCountry()->getId()
        );

        $this->checkXmlHttpRequest();

        /** @var \Thelia\Model\Customer $customer */
        $customer = $this->getSession()->getCustomerUser();

        $args = array(
            'country' => $country,
            "predict_id" => Predict::getModuleId(),
            "customer_cellphone" => $customer->getDefaultAddress()->getCellphone(),
        );



        return $this->render('ajax/order-delivery-module-list', $args);
    }


    /**
     * @return \Thelia\Core\Template\ParserInterface instance parser
     */
    protected function getParser($template = null)
    {
        $parser = $this->container->get("thelia.parser");

        // Define the template that should be used
        $parser->setTemplateDefinition(
            new TemplateDefinition(
                'predict_module',
                TemplateDefinition::FRONT_OFFICE
            )
        );

        return $parser;
    }
} 