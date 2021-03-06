<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class CreditCardReserveTokenize extends CreditCardReserve
{
    //include url of current page
    public $URL = '/CreditCard/pay_tokenize.php';

    //page specific text that can be found in the URL
    public $pageSpecific = 'pay';
}
