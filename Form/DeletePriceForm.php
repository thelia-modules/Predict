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

/**
 * Class DeletePriceForm
 * @package Predict\Form
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class DeletePriceForm extends AbstractPriceForm
{
    /**
     * @return string the name of you form. This name must be unique
     */
    public function getName()
    {
        return "delete_price_slice_form";
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
