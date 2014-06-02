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

namespace Predict\Constraints;
use Symfony\Component\Validator\Constraint;

/**
 * Class NewStatus
 * @package Predict\Constraints
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class NewStatus extends Constraint
{
    public $message = "The new status must be: nochange, processing or sent, but you tried \"{{value}}\"";
}
