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
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Thelia\Core\Translation\Translator;

/**
 * Class EditPriceForm
 * @package Predict\Form
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class EditPriceForm extends AbstractPriceForm
{

    protected function buildForm()
    {
        parent::buildForm();

        $this->formBuilder
            ->add("price", NumberType::class, array(
                "label" => Translator::getInstance()->trans("Price (€)"),
                "label_attr" => ["for"=>$this->getName()."_price"],
                "constraints" => [
                    new GreaterThanOrEqual(["value"=>0]),
                ],
            ))
        ;
    }

    public static function getName()
    {
        return "edit_price_slice_form";
    }

    /**
     * @return bool
     *
     * return true if the weight has to exist or false if not
     */
    protected function getWeightCheck()
    {
        return true;
    }

}
