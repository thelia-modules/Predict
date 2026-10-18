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

namespace Predict\Tests\Export;

use PHPUnit\Framework\TestCase;
use Predict\Export\ExportEntry;
use Thelia\Model\Order;

/**
 * Class ExportEntryTest
 * @package Predict\Tests\Export
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class ExportEntryTest extends TestCase
{
    public function testGuarantyIsOffByDefault()
    {
        $order = new Order();

        $this->assertFalse((new ExportEntry($order))->isGuaranteed());
        $this->assertTrue((new ExportEntry($order, true))->isGuaranteed());
        $this->assertTrue((new ExportEntry($order, "1"))->isGuaranteed());
        $this->assertSame($order, (new ExportEntry($order))->getOrder());
    }
}
