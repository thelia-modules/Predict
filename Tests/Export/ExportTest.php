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
use Thelia\Model\Country;
use Thelia\Model\CountryQuery;
use Thelia\Model\OrderAddressQuery;
use Thelia\Test\IntegrationTestCase;

/**
 * Class ExportTest
 * @package Predict\Tests\Export
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class ExportTest extends IntegrationTestCase
{
    /** @var  \Thelia\Model\Order */
    protected $order;

    /** @var  ExportEntry */
    protected $instance;

    /** @var  PredictExport */
    protected $export;

    protected function setUp(): void
    {
        parent::setUp();

        $fixtures = $this->createFixtureFactory();

        $this->order = $fixtures->order();
        $this->makeDefaultAddressWithCellphone("0600000000");
        $this->setDeliveryCountry($this->getCountry("FRA", "France Metropolitan"));

        $this->instance = new ExportEntry($this->order);
        $this->export = new PredictExport();
    }

    private function getCountry(string $isoalpha3, string $englishTitle): Country
    {
        $country = CountryQuery::create()->findOneByIsoalpha3($isoalpha3);

        if (null === $country) {
            $country = $this->createFixtureFactory()->country([
                'isocode' => 'FRA' === $isoalpha3 ? '250' : '840',
                'isoalpha2' => 'FRA' === $isoalpha3 ? 'FR' : 'US',
                'isoalpha3' => $isoalpha3,
            ]);
            $country->setLocale("en_US")->setTitle($englishTitle)->save();
        }

        return $country;
    }

    private function setDeliveryCountry(Country $country): void
    {
        $deliveryAddress = OrderAddressQuery::create()
            ->findPk($this->order->getDeliveryOrderAddressId());

        $deliveryAddress->setCountryId($country->getId())->save();
    }

    private function makeDefaultAddressWithCellphone(?string $cellphone): void
    {
        $address = $this->order->getCustomer()->getDefaultAddress();

        if (null === $address) {
            $address = $this->createFixtureFactory()->address($this->order->getCustomer());
            $address->setIsDefault(1);
        }

        $address->setCellphone($cellphone)->save();
    }

    public function testTranslateCountry()
    {
        $france = $this->getCountry("FRA", "France Metropolitan");
        $unitedStates = $this->getCountry("USA", "United States");

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
        /**
         * Valid cellphone and Country
         * => True
         */
        $this->assertTrue($this->instance->isValid());

        /**
         * empty cellphone
         * => False
         */
        $this->makeDefaultAddressWithCellphone(null);
        $this->assertFalse($this->instance->isValid());

        /**
         * Invalid country
         * => False
         */
        $this->makeDefaultAddressWithCellphone("0600000000");
        $this->setDeliveryCountry($this->getCountry("USA", "United States"));

        $this->assertFalse($this->instance->isValid());
    }

    public function testAddInvalidEntry()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->setDeliveryCountry($this->getCountry("USA", "United States"));

        $this->export->addEntry($this->instance);
    }

    public function testAddValidEntry()
    {
        $this->expectNotToPerformAssertions();

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
            "000004.00",
            $this->export->harmonise(4, $export::FLOAT, 3)
        );

        $this->assertEquals(
            "000043.26",
            $this->export->harmonise(43.256, $export::FLOAT, 5)
        );

        $this->assertEquals(
            "000123.25",
            $this->export->harmonise(123.254, $export::FLOAT, 6)
        );
    }
}
