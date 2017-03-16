<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard Central Eastern Europe GmbH
 * (abbreviated to Wirecard CEE) and are explicitly not part of the Wirecard CEE range of
 * products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard CEE does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard CEE does not guarantee their full
 * functionality neither does Wirecard CEE assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard CEE does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

namespace Wirecard\PaymentSdk\Response;

use SimpleXMLElement;
use Wirecard\PaymentSdk\Entity\Status;
use Wirecard\PaymentSdk\Entity\StatusCollection;
use Wirecard\PaymentSdk\Exception\MalformedResponseException;

/**
 * Class Response
 * @package Wirecard\PaymentSdk\Response
 */
abstract class Response
{
    /**
     * @var string
     */
    private $rawData;

    /**
     * @var StatusCollection
     */
    private $statusCollection;

    /**
     * @var string
     */
    private $requestId;

    /**
     * @var SimpleXMLElement
     */
    protected $simpleXml;

    /**
     * @var string
     */
    protected $transactionType;

    /**
     * Response constructor.
     * @param string $rawData
     */
    public function __construct($rawData)
    {
        $decodedResponse = base64_decode($rawData);
        $rawData = (base64_encode($decodedResponse) === $rawData) ? $decodedResponse : $rawData;

        //we need to use internal_errors, because we don't want to throw errors on invalid xml responses
        $oldErrorHandling = libxml_use_internal_errors(true);
        $simpleXml = simplexml_load_string($rawData);
        //reset to old value after string is loaded
        libxml_use_internal_errors($oldErrorHandling);
        if (!$simpleXml instanceof \SimpleXMLElement) {
            throw new MalformedResponseException('Response is not a valid xml string.');
        }

        $this->rawData = $rawData;
        $this->simpleXml = $simpleXml;
        $this->statusCollection = $this->findStatusCollection();
        $this->transactionType = $this->findElement('transaction-type');
        $this->requestId = $this->findElement('request-id');
    }

    /**
     * get the raw response data of the called interface
     *
     * @return string
     */
    public function getRawData()
    {
        return $this->rawData;
    }

    /**
     * @return StatusCollection
     */
    public function getStatusCollection()
    {
        return $this->statusCollection;
    }

    /**
     * @param string $element
     * @return string
     * @throws MalformedResponseException
     */
    public function findElement($element)
    {
        if (isset($this->simpleXml->{$element})) {
            return (string)$this->simpleXml->{$element};
        } else {
            throw new MalformedResponseException('Missing '.$element.' in response.');
        }
    }

    /**
     * @return string
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * get the collection of status returned by elastic engine
     * @return StatusCollection
     * @throws MalformedResponseException
     */
    private function findStatusCollection()
    {
        $collection = new StatusCollection();

        /**
         * @var $statuses \SimpleXMLElement
         */
        if (!isset($this->simpleXml->{'statuses'})) {
            throw new MalformedResponseException('Missing statuses in response.');
        }
        $statuses = $this->simpleXml->{'statuses'};
        if (count($statuses->{'status'}) > 0) {
            foreach ($statuses->{'status'} as $statusNode) {
                /**
                 * @var $statusNode \SimpleXMLElement
                 */
                $attributes = $statusNode->attributes();

                if ((string)$attributes['code'] !== '') {
                    $code = (string)$attributes['code'];
                } else {
                    throw new MalformedResponseException('Missing status code in response.');
                }
                if ((string)$attributes['description'] !== '') {
                    $description = (string)$attributes['description'];
                } else {
                    throw new MalformedResponseException('Missing status description in response.');
                }
                if ((string)$attributes['severity'] !== '') {
                    $severity = (string)$attributes['severity'];
                } else {
                    throw new MalformedResponseException('Missing status severity in response.');
                }
                $status = new Status($code, $description, $severity);
                $collection->add($status);
            }
        }

        return $collection;
    }


    /**
     * @return mixed
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }
}
