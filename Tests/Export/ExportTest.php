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
use Predict\Export\CountryEnum;
use Predict\Export\ExportEntry;
use Predict\Export\PredictExport;
use Thelia\Model\CountryQuery;
use Thelia\Model\OrderAddressQuery;
use Thelia\Model\OrderQuery;

/**
 * Class ExportTest
 * @package Predict\Tests\Export
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class ExportTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Thelia\Model\Order */
    protected $order;

    /** @var  ExportEntry */
    protected $instance;

    /** @var  PredictExport */
    protected $export;

    protected function setUp()
    {
        $this->order = OrderQuery::create()
            ->addAscendingOrderByColumn('RAND()')
            ->findOne()
        ;

        $this->instance = new ExportEntry($this->order);
        $this->export = new PredictExport();
    }

    public function testTranslateCountry()
    {
        $france = CountryQuery::create()
            ->findOneByIsoalpha3("FRA");

        $unitedStates = CountryQuery::create()
            ->findOneByIsoalpha3("USA");


        $this->assertEquals(
            CountryEnum::FRANCE_METROPOLITAN,
            PredictExport::translateCountry($france)
        );

        $this->assertNull(
            PredictExport::translateCountry($unitedStates)
        );
    }


    public function testEntryValidity()
    {
        $delivery_address_id = $this->order->getDeliveryOrderAddressId();
        $delivery_address = OrderAddressQuery::create()
            ->findPk($delivery_address_id);

        $delivery_address->setCountryId(
            CountryQuery::create()
                ->findOneByIsoalpha3("FRA")
                ->getId()
        ); // France metropolitan

        $customer = $this->order->getCustomer();

        $customer->getDefaultAddress()->setCellphone("0600000000");
        $this->order->setCustomer($customer);

        /**
         * Valid cellphone and Country
         * => True
         */
        $this->assertTrue($this->instance->isValid());

        /**
         * empty cellphone
         * => False
         */
        $customer->getDefaultAddress()->setCellphone(null);
        $this->assertFalse($this->instance->isValid());

        /**
         * Invalid country
         * => False
         */
        $delivery_address->setCountryId(
            CountryQuery::create()
                ->findOneByIsoalpha3("USA")
                ->getId()
        );

        $customer->getDefaultAddress()->setCellphone("0600000000");

        $this->assertFalse($this->instance->isValid());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddInvalidEntry()
    {
        $delivery_address_id = $this->order->getDeliveryOrderAddressId();
        $delivery_address = OrderAddressQuery::create()
            ->findPk($delivery_address_id);

        $delivery_address->setCountryId(
            CountryQuery::create()
                ->findOneByIsoalpha3("USA")
                ->getId()
        ); // France metropolitaine


        $this->export->addEntry($this->instance);
    }

    public function testAddValidEntry()
    {
        $delivery_address_id = $this->order->getDeliveryOrderAddressId();
        $delivery_address = OrderAddressQuery::create()
            ->findPk($delivery_address_id);

        $delivery_address->setCountryId(
            CountryQuery::create()
                ->findOneByIsoalpha3("FRA")
                ->getId()
        ); // France metropolitan


        $this->export->addEntry($this->instance);
    }

    public function testHarmonise()
    {
        $export = &$this->export;

        $this->assertEquals(
            "abcdefgh",
            $this->export->harmonise("abcdefghi", $export::ALPHA_NUMERIC, 8)
        );

        $this->assertEquals(
            "abcd    ",
            $this->export->harmonise("abcd", $export::ALPHA_NUMERIC, 8)
        );

        $this->assertEquals(
            "00008",
            $this->export->harmonise(8, $export::NUMERIC, 5)
        );

        $this->assertEquals(
            "4",
            $this->export->harmonise(42, $export::NUMERIC, 1)
        );

        $this->assertEquals(
            "4.0",
            $this->export->harmonise(4, $export::FLOAT, 3)
        );

        $this->assertEquals(
            "43.26",
            $this->export->harmonise(43.256, $export::FLOAT, 5)
        );

        $this->assertEquals(
            "123.25",
            $this->export->harmonise(123.254, $export::FLOAT, 6)
        );
    }
}
 