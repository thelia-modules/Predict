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
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Thelia\Core\Translation\Translator;

/**
 * Class EditPriceForm
 * @package Predict\Form
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class EditPriceForm extends DeletePriceForm
{
    protected function buildForm()
    {
        parent::buildForm();

        $this->formBuilder
            ->add("price", "number", array(
                "label" => Translator::getInstance()->trans("Price (€)"),
                "label_attr" => ["for"=>"create_price_slice_form_price"],
                "constraints" => [
                    new GreaterThanOrEqual(["value"=>0]),
                ],
            ));
    }


    public function getName() {
        return "edit_price_form";
    }
} 