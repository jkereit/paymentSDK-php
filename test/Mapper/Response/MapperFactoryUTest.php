<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Mapper\Response;

use PHPUnit_Framework_TestCase;
use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Constant\PayloadFields;
use Wirecard\PaymentSdk\Entity\Payload\IdealPayloadData;
use Wirecard\PaymentSdk\Entity\Payload\NvpPayloadData;
use Wirecard\PaymentSdk\Entity\Payload\PayloadDataInterface;
use Wirecard\PaymentSdk\Entity\Payload\PayPalPayloadData;
use Wirecard\PaymentSdk\Entity\Payload\RatepayPayloadData;
use Wirecard\PaymentSdk\Entity\Payload\SyncPayloadData;
use Wirecard\PaymentSdk\Exception\MalformedPayloadException;
use Wirecard\PaymentSdk\Mapper\Response\MapperFactory;
use Wirecard\PaymentSdk\Mapper\Response\SeamlessMapper;
use Wirecard\PaymentSdk\Mapper\Response\WithSignatureMapper;

class MapperFactoryUTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider createProvider
     * @param PayloadDataInterface $payload
     * @param string $expectedMapper
     */
    public function testCreate(PayloadDataInterface $payload, $expectedMapper)
    {
        $mapperFactory = new MapperFactory($payload);
        $this->assertInstanceOf($expectedMapper, $mapperFactory->create());
    }

    /**
     * @dataProvider createExceptionProvider
     * @param PayloadDataInterface $payload
     * @param string $expectedException
     */
    /*public function testCreateException(PayloadDataInterface $payload, $expectedException)
    {
        $this->expectException($expectedException);
        $mapperFactory = new MapperFactory($payload);
        $mapperFactory->create();
    }*/

    /**
     * @return array
     * @throws \Http\Client\Exception
     */
    public function createProvider()
    {
        return [
            [
                $this->getNvpPayloadData(),
                SeamlessMapper::class
            ], [
                $this->getPayPalPayloadData(),
                WithSignatureMapper::class
            ], [
                $this->getRatepayPayloadData(),
                WithSignatureMapper::class
            ], [
                $this->getSyncPayloadData(),
                WithSignatureMapper::class
            ]/*, [
                $this->getIdealPayloadData(),
                WithoutSignatureMapper::class
            ]*/
        ];
    }

    public function createExceptionProvider()
    {
        return [
            [
                new NvpPayloadData([]),
                MalformedPayloadException::class
            ], [
                new PayPalPayloadData([], new Config('', '', '')),
                MalformedPayloadException::class
            ], [
                new RatepayPayloadData([], new Config('', '', '')),
                MalformedPayloadException::class
            ], [
                new SyncPayloadData([], new Config('', '', '')),
                MalformedPayloadException::class
            ], [
                new IdealPayloadData([], new Config('', '', '')),
                MalformedPayloadException::class
            ]
        ];
    }

    /**
     * @return NvpPayloadData
     */
    private function getNvpPayloadData()
    {
        return new NvpPayloadData(
            [
                PayloadFields::FIELD_RESPONSE_SIGNATURE => 'test'
            ]
        );
    }

    /**
     * @return IdealPayloadData
     * @throws \Http\Client\Exception
     */
    private function getIdealPayloadData()
    {
        $payloadMock = [
            PayloadFields::FIELD_EC => 'test',
            PayloadFields::FIELD_TRXID => 'test',
            PayloadFields::REQUEST_ID => 'test'
        ];
        $configMock = new Config('https://api-test.wirecard.com', 'user', 'password');

        return new IdealPayloadData($payloadMock, $configMock);
    }

    /**
     * @return PayPalPayloadData
     */
    private function getPayPalPayloadData()
    {
        return new PayPalPayloadData(
            [
                PayloadFields::FIELD_EPP_RESPONSE => 'test'
            ],
            new Config('https://api-test.wirecard.com', 'user', 'password')
        );
    }

    /**
     * @return RatepayPayloadData
     */
    private function getRatepayPayloadData()
    {
        return new RatepayPayloadData(
            [
                PayloadFields::FIELD_BASE64_PAYLOAD => 'test'
            ],
            new Config('https://api-test.wirecard.com', 'user', 'password')
        );
    }

    /**
     * @return SyncPayloadData
     */
    private function getSyncPayloadData()
    {
        return new SyncPayloadData(
            [
                PayloadFields::FIELD_SYNC_RESPONSE => 'test'
            ],
            new Config('https://api-test.wirecard.com', 'user', 'password')
        );
    }
}
