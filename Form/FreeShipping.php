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

namespace Predict\Form;

use Predict\Predict;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Thelia\Model\ConfigQuery;

/**
 * Class FreeShipping
 * @package Predict\Form
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class FreeShipping extends BaseForm
{
    /**
     *
     * in this function you add all the fields you need for your Form.
     * Form this you have to call add method on $this->formBuilder attribute :
     *
     * $this->formBuilder->add("name", "text")
     *   ->add("email", "email", array(
     *           "attr" => array(
     *               "class" => "field"
     *           ),
     *           "label" => "email",
     *           "constraints" => array(
     *               new \Symfony\Component\Validator\Constraints\NotBlank()
     *           )
     *       )
     *   )
     *   ->add('age', 'integer');
     *
     * @return null
     */
    protected function buildForm()
    {
        $freeshipping = @(bool) ConfigQuery::read("predict_freeshipping");
        $freeshipping_amount = !empty(ConfigQuery::read("predict_freeshipping_amount")) ? ConfigQuery::read("predict_freeshipping_amount") : null;
        $this->formBuilder
            ->add("freeshipping", "checkbox", array(
                'data'=>$freeshipping,
                'label'=>Translator::getInstance()->trans("Activate free shipping: ", [], Predict::MESSAGE_DOMAIN)
            ))
            ->add("freeshipping_amount", "number", array(
                'data'=>$freeshipping_amount,
                'label'=>Translator::getInstance()->trans("Free shipping from (€) - only if free shipping is enabled", [], Predict::MESSAGE_DOMAIN),
                'required'=>false
            ))
        ;
    }

    /**
     * @return string the name of you form. This name must be unique
     */
    public function getName()
    {
        return "Predictfreeshipping";
    }

}
