<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

use Tygh\Http;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/**
 * Base Request Class holding common functionality for Request Types.
 */
class Request
{
        protected static function GetRequestHash($plainString)
        {
                return md5($plainString);
        }

        protected static function GetFormattedDate()
        {
                return date('d-m-Y:H:i:s:000');
        }

        protected static function SendRequest($requestString, $testMode)
        {
                if ($testMode) {
                    $serverUrl = "https://testpayments.worldnettps.com/merchant/xmlpayment";
                } else {
                    $serverUrl = "https://payments.worldnettps.com/merchant/xmlpayment";
                }

                Registry::set('log_cut_data', array('CARDTYPE', 'CARDNUMBER', 'CARDEXPIRY', 'CARDHOLDERNAME', 'CVV', 'ISSUENO'));

                return Http::post($serverUrl, $requestString);
        }

}

/**
 *  Used for processing XML Authorisations through the WorldNet TPS XML Gateway.
 *
 *  Basic request is configured on initialisation and optional fields can be configured.
 */
class XmlAuthRequest extends Request
{
        private $terminalId;
        private $orderId;
        private $currency;
        private $amount;
        public function Amount()
        {
                return $this->amount;
        }
        private $dateTime;
        private $hash;
        private $autoReady;
        private $description;
        private $email;
        private $cardNumber;
        private $cardType;
        private $cardExpiry;
        private $cardHolderName;
        private $cvv;
        private $issueNo;
        private $address1;
        private $address2;
        private $postCode;
        private $cardCurrency;
        private $cardAmount;
        private $conversionRate;
        private $avsOnly;
            private $mpiRef;

        /**
         *  Creates the standard request less optional parameters for processing an XML Transaction
         *  through the WorldNetTPS XML Gateway
         *
         *  @param $terminalId Terminal ID provided by WorldNet TPS
         *  @param $orderId A unique merchant identifier. Alpha numeric and max size 12 chars.
         *  @param $currency ISO 4217 3 Digit Currency Code, e.g. EUR / USD / GBP
         *  @param $amount Transaction Amount, Double formatted to 2 decimal places.
         *  @param $description Transaction Description
         *  @param $email Cardholder e-mail
         *  @param $cardNumber A valid Card Number that passes the Luhn Check.
         *  @param $cardType
         *  Card Type (Accepted Card Types must be configured in the Merchant Selfcare System.)
         *
         *  Accepted Values :
         *
         *  VISA
         *  MASTERCARD
         *  LASER
         *  SWITCH
         *  SOLO
         *  AMEX
         *  DINERS
         *  MAESTRO
         *  DELTA
         *  ELECTRON
         *
         *  @param cardExpiry Card Expiry formatted MMYY
         *  @param cardHolderName Card Holder Name
         */
        public function XmlAuthRequest($terminalId,
                $orderId,
                $currency,
                $amount,
                $description,
                $email,
                $cardNumber,
                $cardType,
                $cardExpiry,
                $cardHolderName)
        {
                $this->dateTime = $this->GetFormattedDate();

                $this->terminalId = $terminalId;
                $this->orderId = $orderId;
                $this->currency = $currency;
                $this->amount = $amount;
                $this->description = $description;
                $this->email = $email;
                $this->cardNumber = $cardNumber;
                $this->cardType = $cardType;
                $this->cardExpiry = $cardExpiry;
                $this->cardHolderName = $cardHolderName;
        }
       /**
         *  Setter for Auto Ready Value
         *
         *  @param autoReady
         *  Auto Ready is an optional parameter and defines if the transaction should be settled automatically.
         *
         *  Accepted Values :
         *
         *  Y   -   Transaction will be settled in next batch
         *  N   -   Transaction will not be settled until user changes state in Merchant Selfcare Section
         */
        public function SetAutoReady($autoReady)
        {
                $this->autoReady = $autoReady;
        }
       /**
         *  Setter for Card Verification Value
         *
         *  @param cvv Numeric field with a max of 4 characters.
         */
        public function SetCvv($cvv)
        {
                $this->cvv = $cvv;
        }

       /**
         *  Setter for Issue No
         *
         *  @param issueNo Numeric field with a max of 3 characters.
         */
        public function SetIssueNo($issueNo)
        {
                $this->issueNo = $issueNo;
        }

       /**
         *  Setter for Address Verification Values
         *
         *  @param string $address1 First Line of address - Max size 20
         *  @param string $address2 Second Line of address - Max size 20
         *  @param string $postCode Postcode - Max size 9
         */
        public function SetAvs($address1, $address2, $postCode)
        {
                $this->address1 = $address1;
                $this->address2 = $address2;
                $this->postCode = $postCode;
        }
       /**
         *  Setter for Foreign Currency Information
         *
         *  @param cardCurrency ISO 4217 3 Digit Currency Code, e.g. EUR / USD / GBP
         *  @param cardAmount (Amount X Conversion rate) Formatted to two decimal places
         *  @param conversionRate Converstion rate supplied in rate response
         */
        public function SetForeignCurrencyInformation($cardCurrency, $cardAmount, $conversionRate)
        {
                $this->cardCurrency = $cardCurrency;
                $this->cardAmount = $cardAmount;
                $this->conversionRate = $conversionRate;
        }
       /**
         *  Setter for AVS only flag
         *
         *  @param avsOnly Only perform an AVS check, do not store as a transaction. Possible values: "Y", "N"
         */
        public function SetAvsOnly($avsOnly)
        {
                $this->avsOnly = $avsOnly;
        }
       /**
         *  Setter for MPI Reference code
         *
         *  @param mpiRef MPI Reference code supplied by WorldNet TPS MPI redirect
         */
        public function SetMpiRef($mpiRef)
        {
                $this->mpiRef = $mpiRef;
        }
       /**
         *  Method to process transaction and return parsed response from the WorldNet TPS XML Gateway
         *
         *  @param sharedSecret
         *  Shared secret either supplied by WorldNet TPS or configured under
         *  Terminal Settings in the Merchant Selfcare System.
         *
         *  @param testMode
         *  Boolean value defining Mode
         *  true - Test mode active
         *  false - Production mode, all transactions will be processed by Issuer.
         *
         *  @return XmlAuthResponse containing an error or the parsed payment response.
         */
        public function ProcessRequest($sharedSecret, $multicur, $testMode)
        {
                if ($multicur) {
                    $this->hash = $this->GetRequestHash($this->terminalId . $this->orderId . $this->currency . $this->amount . $this->dateTime . $sharedSecret);
                } else {
                    $this->hash = $this->GetRequestHash($this->terminalId . $this->orderId . $this->amount . $this->dateTime . $sharedSecret);
                }
                $responseString = $this->SendRequest($this->GenerateXml(), $testMode);
                $response = new XmlAuthResponse($responseString);

                return $response;
        }

        private function GenerateXml()
        {
                $requestXml = new DOMDocument("1.0");
                $requestXml->formatOutput = true;

                $requestString = $requestXml->createElement("PAYMENT");
                $requestXml->appendChild($requestString);

                $node = $requestXml->createElement("ORDERID");
                $node->appendChild($requestXml->createTextNode($this->orderId));
                $requestString->appendChild($node);

                $node = $requestXml->createElement("TERMINALID");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode($this->terminalId);
                $node->appendChild($nodeText);

                $node = $requestXml->createElement("AMOUNT");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode($this->amount);
                $node->appendChild($nodeText);

                $node = $requestXml->createElement("DATETIME");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode($this->dateTime);
                $node->appendChild($nodeText);

                $node = $requestXml->createElement("CARDNUMBER");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode($this->cardNumber);
                $node->appendChild($nodeText);

                $node = $requestXml->createElement("CARDTYPE");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode($this->cardType);
                $node->appendChild($nodeText);

                $node = $requestXml->createElement("CARDEXPIRY");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode($this->cardExpiry);
                $node->appendChild($nodeText);

                $node = $requestXml->createElement("CARDHOLDERNAME");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode($this->cardHolderName);
                $node->appendChild($nodeText);

                $node = $requestXml->createElement("HASH");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode($this->hash);
                $node->appendChild($nodeText);

                $node = $requestXml->createElement("CURRENCY");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode($this->currency);
                $node->appendChild($nodeText);

                if ($this->cardCurrency !== NULL && $this->cardAmount > 0 && $this->conversionRate > 0) {
                    $dcNode = $requestXml->createElement("FOREIGNCURRENCYINFORMATION");
                    $requestString->appendChild($dcNode );

                    $dcSubNode = $requestXml->createElement("CARDCURRENCY");
                    $dcSubNode ->appendChild($requestXml->createTextNode($this->cardCurrency));
                    $dcNode->appendChild($dcSubNode);

                    $dcSubNode = $requestXml->createElement("CARDAMOUNT");
                    $dcSubNode ->appendChild($requestXml->createTextNode($this->cardAmount));
                    $dcNode->appendChild($dcSubNode);

                    $dcSubNode = $requestXml->createElement("CONVERSIONRATE");
                    $dcSubNode ->appendChild($requestXml->createTextNode($this->conversionRate));
                    $dcNode->appendChild($dcSubNode);
                }

                $node = $requestXml->createElement("TERMINALTYPE");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode('2');
                $node->appendChild($nodeText);

                $node = $requestXml->createElement("TRANSACTIONTYPE");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode('7');
                $node->appendChild($nodeText);

                if ($this->autoReady !== NULL) {
                    $node = $requestXml->createElement("AUTOREADY");
                    $requestString->appendChild($node);
                    $nodeText = $requestXml->createTextNode($this->autoReady);
                    $node->appendChild($nodeText);
                }

                $node = $requestXml->createElement("EMAIL");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode($this->email);
                $node->appendChild($nodeText);

                if ($this->cvv !== NULL) {
                    $node = $requestXml->createElement("CVV");
                    $requestString->appendChild($node);
                    $nodeText = $requestXml->createTextNode($this->cvv);
                    $node->appendChild($nodeText);
                }

                if ($this->issueNo !== NULL) {
                    $node = $requestXml->createElement("ISSUENO");
                    $requestString->appendChild($node);
                    $nodeText = $requestXml->createTextNode($this->issueNo);
                    $node->appendChild($nodeText);
                }

                if ($this->address1 !== NULL  &&$this->address2 !== NULL  &&$this->postCode !== NULL) {
                    $node = $requestXml->createElement("ADDRESS1");
                    $requestString->appendChild($node);
                    $nodeText = $requestXml->createTextNode($this->address1);
                    $node->appendChild($nodeText);

                    $node = $requestXml->createElement("ADDRESS2");
                    $requestString->appendChild($node);
                    $nodeText = $requestXml->createTextNode($this->address2);
                    $node->appendChild($nodeText);

                    $node = $requestXml->createElement("POSTCODE");
                    $requestString->appendChild($node);
                    $nodeText = $requestXml->createTextNode($this->postCode);
                    $node->appendChild($nodeText);
                }

                if ($this->avsOnly !== NULL) {
                    $node = $requestXml->createElement("AVSONLY");
                    $requestString->appendChild($node);
                    $nodeText = $requestXml->createTextNode($this->avsOnly);
                    $node->appendChild($nodeText);
                }

                $node = $requestXml->createElement("DESCRIPTION");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode($this->description);
                $node->appendChild($nodeText);

                if ($this->mpiRef !== NULL) {
                    $node = $requestXml->createElement("MPIREF");
                    $requestString->appendChild($node);
                    $nodeText = $requestXml->createTextNode($this->mpiRef);
                    $node->appendChild($nodeText);
                }

                return $requestXml->saveXML();

        }
}

/**
 *  Used for processing XML Refund Authorisations through the WorldNet TPS XML Gateway.
 *
 *  Basic request is configured on initialisation. There are no coptional fields.
 */
class XmlRefundRequest extends Request
{
        private $terminalId;
        private $orderId;
        private $amount;
        public function Amount()
        {
                return $this->amount;
        }
        private $dateTime;
        private $hash;
        private $operator;
        private $reason;

        /**
         *  Creates the refund request for processing an XML Transaction
         *  through the WorldNetTPS XML Gateway
         *
         *  @param terminalId Terminal ID provided by WorldNet TPS
         *  @param orderId A unique merchant identifier. Alpha numeric and max size 12 chars.
         *  @param currency ISO 4217 3 Digit Currency Code, e.g. EUR / USD / GBP
         *  @param amount Transaction Amount, Double formatted to 2 decimal places.
         *  @param operator An identifier for who executed this transaction
         *  @param reason The reason for the refund
         *  Card Type (Accepted Card Types must be configured in the Merchant Selfcare System.)
         *
         *  Accepted Values :
         *
         *  VISA
         *  MASTERCARD
         *  LASER
         *  SWITCH
         *  SOLO
         *  AMEX
         *  DINERS
         *  MAESTRO
         *  DELTA
         *  ELECTRON
         */
        public function XmlRefundRequest($terminalId,
                $orderId,
                $amount,
                $operator,
                $reason)
        {
                $this->dateTime = $this->GetFormattedDate();
                $this->amount = $amount;
                $this->terminalId = $terminalId;
                $this->orderId = $orderId;
                $this->operator = $operator;
                $this->reason = $reason;
        }
        public function ProcessRequest($sharedSecret, $multicur, $testMode)
        {
                if ($multicur) {
                    $this->hash = $this->GetRequestHash($this->terminalId . $this->orderId . $this->currency . $this->amount . $this->dateTime . $sharedSecret);
                } else {
                    $this->hash = $this->GetRequestHash($this->terminalId . $this->orderId . $this->amount . $this->dateTime . $sharedSecret);
                }
                $responseString = $this->SendRequest($this->GenerateXml(), $testMode);
                $response = new XmlRefundResponse($responseString);

                return $response;
        }

        private function GenerateXml()
        {
                $requestXml = new DOMDocument("1.0");
                $requestXml->formatOutput = true;

                $requestString = $requestXml->createElement("REFUND");
                $requestXml->appendChild($requestString);

                $node = $requestXml->createElement("ORDERID");
                $node->appendChild($requestXml->createTextNode($this->orderId));
                $requestString->appendChild($node);

                $node = $requestXml->createElement("TERMINALID");
                $node->appendChild($requestXml->createTextNode($this->terminalId));
                $requestString->appendChild($node);

                $node = $requestXml->createElement("AMOUNT");
                $node->appendChild($requestXml->createTextNode($this->amount));
                $requestString->appendChild($node);

                $node = $requestXml->createElement("DATETIME");
                $node->appendChild($requestXml->createTextNode($this->dateTime));
                $requestString->appendChild($node);

                $node = $requestXml->createElement("HASH");
                $node->appendChild($requestXml->createTextNode($this->hash));
                $requestString->appendChild($node);

                $node = $requestXml->createElement("OPERATOR");
                $node->appendChild($requestXml->createTextNode($this->operator));
                $requestString->appendChild($node);

                $node = $requestXml->createElement("REASON");
                $node->appendChild($requestXml->createTextNode($this->reason));
                $requestString->appendChild($node);

                return $requestXml->saveXML();

        }
}

/**
 *  Used for processing XML Pre-Authorisations through the WorldNet TPS XML Gateway.
 *
 *  Basic request is configured on initialisation and optional fields can be configured.
 */
class XmlPreAuthRequest extends Request
{
        private $terminalId;
        private $orderId;
        private $currency;
        private $amount;
        public function Amount()
        {
                return $this->amount;
        }
        private $dateTime;
        private $hash;
        private $description;
        private $email;
        private $cardNumber;
        private $cardType;
        private $cardExpiry;
        private $cardHolderName;
        private $cvv;
        private $issueNo;
        private $address1;
        private $address2;
        private $postCode;
        private $cardCurrency;
        private $cardAmount;
        private $conversionRate;

        /**
         *  Creates the pre-auth request less optional parameters for processing an XML Transaction
         *  through the WorldNetTPS XML Gateway
         *
         *  @param terminalId Terminal ID provided by WorldNet TPS
         *  @param orderId A unique merchant identifier. Alpha numeric and max size 12 chars.
         *  @param currency ISO 4217 3 Digit Currency Code, e.g. EUR / USD / GBP
         *  @param amount Transaction Amount, Double formatted to 2 decimal places.
         *  @param description Transaction Description
         *  @param email Cardholder e-mail
         *  @param cardNumber A valid Card Number that passes the Luhn Check.
         *  @param cardType
         *  Card Type (Accepted Card Types must be configured in the Merchant Selfcare System.)
         *
         *  Accepted Values :
         *
         *  VISA
         *  MASTERCARD
         *  LASER
         *  SWITCH
         *  SOLO
         *  AMEX
         *  DINERS
         *  MAESTRO
         *  DELTA
         *  ELECTRON
         *
         *  @param cardExpiry Card Expiry formatted MMYY
         *  @param cardHolderName Card Holder Name
         */
        public function XmlPreAuthRequest($terminalId,
                $orderId,
                $currency,
                $amount,
                $description,
                $email,
                $cardNumber,
                $cardType,
                $cardExpiry,
                $cardHolderName)
        {
                $this->dateTime = $this->GetFormattedDate();

                $this->terminalId = $terminalId;
                $this->orderId = $orderId;
                $this->currency = $currency;
                $this->amount = $amount;
                $this->description = $description;
                $this->email = $email;
                $this->cardNumber = $cardNumber;
                $this->cardType = $cardType;
                $this->cardExpiry = $cardExpiry;
                $this->cardHolderName = $cardHolderName;
        }
       /**
         *  Setter for Card Verification Value
         *
         *  @param cvv Numeric field with a max of 4 characters.
         */
        public function SetCvv($cvv)
        {
                $this->cvv = $cvv;
        }

       /**
         *  Setter for Issue No
         *
         *  @param issueNo Numeric field with a max of 3 characters.
         */
        public function SetIssueNo($issueNo)
        {
                $this->issueNo = $issueNo;
        }

       /**
         *  Setter for Address Verification Values
         *
         *  @param address1 First Line of address - Max size 20
         *  @param address2 Second Line of address - Max size 20
         *  @param postCode Postcode - Max size 9
         */
        public function SetAvs($address1, $address2, $postCode)
        {
                $this->address1 = $address1;
                $this->address2 = $address2;
                $this->postCode = $postCode;
        }
       /**
         *  Setter for Foreign Currency Information
         *
         *  @param cardCurrency ISO 4217 3 Digit Currency Code, e.g. EUR / USD / GBP
         *  @param cardAmount (Amount X Conversion rate) Formatted to two decimal places
         *  @param conversionRate Converstion rate supplied in rate response
         */
        public function SetForeignCurrencyInformation($cardCurrency, $cardAmount, $conversionRate)
        {
                $this->cardCurrency = $cardCurrency;
                $this->cardAmount = $cardAmount;
                $this->conversionRate = $conversionRate;
        }
       /**
         *  Method to process transaction and return parsed response from the WorldNet TPS XML Gateway
         *
         *  @param sharedSecret
         *  Shared secret either supplied by WorldNet TPS or configured under
         *  Terminal Settings in the Merchant Selfcare System.
         *
         *  @param testMode
         *  Boolean value defining Mode
         *  true - Test mode active
         *  false - Production mode, all transactions will be processed by Issuer.
         *
         *  @return XmlPreAuthResponse containing an error or the parsed payment response.
         */
        public function ProcessRequest($sharedSecret, $multicur, $testMode)
        {
                if ($multicur) {
                    $this->hash = $this->GetRequestHash($this->terminalId . $this->orderId . $this->currency . $this->amount . $this->dateTime . $sharedSecret);
                } else {
                    $this->hash = $this->GetRequestHash($this->terminalId . $this->orderId . $this->amount . $this->dateTime . $sharedSecret);
                }
                $responseString = $this->SendRequest($this->GenerateXml(), $testMode);
                $response = new XmlPreAuthResponse($responseString);

                return $response;
        }

        private function GenerateXml()
        {
                $requestXml = new DOMDocument("1.0");
                $requestXml->formatOutput = true;

                $requestString = $requestXml->createElement("PREAUTH");
                $requestXml->appendChild($requestString);

                $node = $requestXml->createElement("ORDERID");
                $node->appendChild($requestXml->createTextNode($this->orderId));
                $requestString->appendChild($node);

                $node = $requestXml->createElement("TERMINALID");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode($this->terminalId);
                $node->appendChild($nodeText);

                $node = $requestXml->createElement("AMOUNT");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode($this->amount);
                $node->appendChild($nodeText);

                $node = $requestXml->createElement("DATETIME");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode($this->dateTime);
                $node->appendChild($nodeText);

                $node = $requestXml->createElement("CARDNUMBER");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode($this->cardNumber);
                $node->appendChild($nodeText);

                $node = $requestXml->createElement("CARDTYPE");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode($this->cardType);
                $node->appendChild($nodeText);

                $node = $requestXml->createElement("CARDEXPIRY");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode($this->cardExpiry);
                $node->appendChild($nodeText);

                $node = $requestXml->createElement("CARDHOLDERNAME");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode($this->cardHolderName);
                $node->appendChild($nodeText);

                $node = $requestXml->createElement("HASH");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode($this->hash);
                $node->appendChild($nodeText);

                $node = $requestXml->createElement("CURRENCY");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode($this->currency);
                $node->appendChild($nodeText);

                if ($this->cardCurrency !== NULL && $this->cardAmount > 0 && $this->conversionRate > 0) {
                    $dcNode = $requestXml->createElement("FOREIGNCURRENCYINFORMATION");
                    $requestString->appendChild($dcNode );

                    $dcSubNode = $requestXml->createElement("CARDCURRENCY");
                    $dcSubNode ->appendChild($requestXml->createTextNode($this->cardCurrency));
                    $dcNode->appendChild($dcSubNode);

                    $dcSubNode = $requestXml->createElement("CARDAMOUNT");
                    $dcSubNode ->appendChild($requestXml->createTextNode($this->cardAmount));
                    $dcNode->appendChild($dcSubNode);

                    $dcSubNode = $requestXml->createElement("CONVERSIONRATE");
                    $dcSubNode ->appendChild($requestXml->createTextNode($this->conversionRate));
                    $dcNode->appendChild($dcSubNode);
                }

                $node = $requestXml->createElement("TERMINALTYPE");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode('2');
                $node->appendChild($nodeText);

                $node = $requestXml->createElement("TRANSACTIONTYPE");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode('7');
                $node->appendChild($nodeText);

                $node = $requestXml->createElement("EMAIL");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode($this->email);
                $node->appendChild($nodeText);

                if ($this->cvv !== NULL) {
                    $node = $requestXml->createElement("CVV");
                    $requestString->appendChild($node);
                    $nodeText = $requestXml->createTextNode($this->cvv);
                    $node->appendChild($nodeText);
                }

                if ($this->issueNo !== NULL) {
                    $node = $requestXml->createElement("ISSUENO");
                    $requestString->appendChild($node);
                    $nodeText = $requestXml->createTextNode($this->issueNo);
                    $node->appendChild($nodeText);
                }

                if ($this->address1 !== NULL  &&$this->address2 !== NULL  &&$this->postCode !== NULL) {
                    $node = $requestXml->createElement("ADDRESS1");
                    $requestString->appendChild($node);
                    $nodeText = $requestXml->createTextNode($this->address1);
                    $node->appendChild($nodeText);

                    $node = $requestXml->createElement("ADDRESS2");
                    $requestString->appendChild($node);
                    $nodeText = $requestXml->createTextNode($this->address2);
                    $node->appendChild($nodeText);

                    $node = $requestXml->createElement("POSTCODE");
                    $requestString->appendChild($node);
                    $nodeText = $requestXml->createTextNode($this->postCode);
                    $node->appendChild($nodeText);
                }

                $node = $requestXml->createElement("DESCRIPTION");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode($this->description);
                $node->appendChild($nodeText);

                return $requestXml->saveXML();

        }
}

/**
 *  Used for processing XML PreAuthorisation Completions through the WorldNet TPS XML Gateway.
 *
 *  Basic request is configured on initialisation and optional fields can be configured.
 */
class XmlPreAuthCompletionRequest extends Request
{
        private $terminalId;
        private $orderId;
        private $amount;
        public function Amount()
        {
                return $this->amount;
        }
        private $dateTime;
        private $hash;
        private $description;
        private $cvv;
        private $cardCurrency;
        private $cardAmount;
        private $conversionRate;

        /**
         *  Creates the standard request less optional parameters for processing an XML Transaction
         *  through the WorldNetTPS XML Gateway
         *
         *  @param terminalId Terminal ID provided by WorldNet TPS
         *  @param orderId A unique merchant identifier. Alpha numeric and max size 12 chars.
         *  @param currency ISO 4217 3 Digit Currency Code, e.g. EUR / USD / GBP
         *  @param amount Transaction Amount, Double formatted to 2 decimal places.
         *  @param description Transaction Description
         *  @param email Cardholder e-mail
         *  @param cardNumber A valid Card Number that passes the Luhn Check.
         *  @param cardType
         *  Card Type (Accepted Card Types must be configured in the Merchant Selfcare System.)
         *
         *  Accepted Values :
         *
         *  VISA
         *  MASTERCARD
         *  LASER
         *  SWITCH
         *  SOLO
         *  AMEX
         *  DINERS
         *  MAESTRO
         *  DELTA
         *  ELECTRON
         *
         *  @param cardExpiry Card Expiry formatted MMYY
         *  @param cardHolderName Card Holder Name
         */
        public function XmlPreAuthCompletionRequest($terminalId,
                $orderId,
                $amount)
        {
                $this->dateTime = $this->GetFormattedDate();

                $this->terminalId = $terminalId;
                $this->orderId = $orderId;
                $this->amount = $amount;
        }
       /**
         *  Setter for Card Verification Value
         *
         *  @param cvv Numeric field with a max of 4 characters.
         */
        public function SetCvv($cvv)
        {
                $this->cvv = $cvv;
        }
       /**
         *  Setter for transaction description
         *
         *  @param cvv Discretionary text value
         */
        public function SetDescription($cdescription)
        {
                $this->description = $description;
        }
       /**
         *  Setter for Foreign Currency Information
         *
         *  @param cardCurrency ISO 4217 3 Digit Currency Code, e.g. EUR / USD / GBP
         *  @param cardAmount (Amount X Conversion rate) Formatted to two decimal places
         *  @param conversionRate Converstion rate supplied in rate response
         */
        public function SetForeignCurrencyInformation($cardCurrency, $cardAmount, $conversionRate)
        {
                $this->cardCurrency = $cardCurrency;
                $this->cardAmount = $cardAmount;
                $this->conversionRate = $conversionRate;
        }
       /**
         *  Method to process transaction and return parsed response from the WorldNet TPS XML Gateway
         *
         *  @param sharedSecret
         *  Shared secret either supplied by WorldNet TPS or configured under
         *  Terminal Settings in the Merchant Selfcare System.
         *
         *  @param testMode
         *  Boolean value defining Mode
         *  true - Test mode active
         *  false - Production mode, all transactions will be processed by Issuer.
         *
         *  @return XmlPreAuthCompletionResponse containing an error or the parsed payment response.
         */
        public function ProcessRequest($sharedSecret, $multicur, $testMode)
        {
                if ($multicur) {
                    $this->hash = $this->GetRequestHash($this->terminalId . $this->orderId . $this->currency . $this->amount . $this->dateTime . $sharedSecret);
                } else {
                    $this->hash = $this->GetRequestHash($this->terminalId . $this->orderId . $this->amount . $this->dateTime . $sharedSecret);
                }
                $responseString = $this->SendRequest($this->GenerateXml(), $testMode);
                $response = new XmlPreAuthCompletionResponse($responseString);

                return $response;
        }

        private function GenerateXml()
        {
                $requestXml = new DOMDocument("1.0");
                $requestXml->formatOutput = true;

                $requestString = $requestXml->createElement("PREAUTHCOMPLETION");
                $requestXml->appendChild($requestString);

                $node = $requestXml->createElement("ORDERID");
                $node->appendChild($requestXml->createTextNode($this->orderId));
                $requestString->appendChild($node);

                $node = $requestXml->createElement("TERMINALID");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode($this->terminalId);
                $node->appendChild($nodeText);

                $node = $requestXml->createElement("AMOUNT");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode($this->amount);
                $node->appendChild($nodeText);

                $node = $requestXml->createElement("DATETIME");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode($this->dateTime);
                $node->appendChild($nodeText);

                $node = $requestXml->createElement("HASH");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode($this->hash);
                $node->appendChild($nodeText);

                if ($this->cardCurrency !== NULL && $this->cardAmount > 0 && $this->conversionRate > 0) {
                    $dcNode = $requestXml->createElement("FOREIGNCURRENCYINFORMATION");
                    $requestString->appendChild($dcNode );

                    $dcSubNode = $requestXml->createElement("CARDCURRENCY");
                    $dcSubNode ->appendChild($requestXml->createTextNode($this->cardCurrency));
                    $dcNode->appendChild($dcSubNode);

                    $dcSubNode = $requestXml->createElement("CARDAMOUNT");
                    $dcSubNode ->appendChild($requestXml->createTextNode($this->cardAmount));
                    $dcNode->appendChild($dcSubNode);

                    $dcSubNode = $requestXml->createElement("CONVERSIONRATE");
                    $dcSubNode ->appendChild($requestXml->createTextNode($this->conversionRate));
                    $dcNode->appendChild($dcSubNode);
                }

                if ($this->cvv !== NULL) {
                    $node = $requestXml->createElement("CVV");
                    $requestString->appendChild($node);
                    $nodeText = $requestXml->createTextNode($this->cvv);
                    $node->appendChild($nodeText);
                }

                if ($this->description !== NULL) {
                    $node = $requestXml->createElement("DESCRIPTION");
                    $requestString->appendChild($node);
                    $nodeText = $requestXml->createTextNode($this->description);
                    $node->appendChild($nodeText);
                }

                return $requestXml->saveXML();

        }
}

/**
 *  Used for processing XML PreAuthorisation Completions through the WorldNet TPS XML Gateway.
 *
 *  Basic request is configured on initialisation and optional fields can be configured.
 */
class XmlRateRequest extends Request
{
        private $terminalId;
        private $cardBin;

        /**
         *  Creates the rate request for processing an XML Transaction
         *  through the WorldNetTPS XML Gateway
         *
         *  @param terminalId Terminal ID provided by WorldNet TPS
         *  @param cardBin First 6 digits of the card number
         */
        public function XmlRateRequest($terminalId,
                $cardBin)
        {
                $this->dateTime = $this->GetFormattedDate();

                $this->terminalId = $terminalId;
                $this->cardBin = $cardBin;
        }
               /**
         *  Setter for Card Verification Value
         *
         *  @param cvv Numeric field with a max of 4 characters.
         */
         private $baseAmount;
        public function SetBaseAmount($baseAmount)
        {
                $this->baseAmount = $baseAmount;
        }
       /**
         *  Method to process transaction and return parsed response from the WorldNet TPS XML Gateway
         *
         *  @param sharedSecret
         *  Shared secret either supplied by WorldNet TPS or configured under
         *  Terminal Settings in the Merchant Selfcare System.
         *
         *  @param testMode
         *  Boolean value defining Mode
         *  true - Test mode active
         *  false - Production mode, all transactions will be processed by Issuer.
         *
         *  @return XmlRateResponse containing an error or the parsed payment response.
         */
        public function ProcessRequest($sharedSecret, $testMode)
        {
                $this->hash = $this->GetRequestHash($this->terminalId . $this->cardBin . $this->dateTime . $sharedSecret);
                $responseString = $this->SendRequest($this->GenerateXml(), $testMode);
                $response = new XmlRateResponse($responseString);

                return $response;
        }

        private function GenerateXml()
        {
                $requestXml = new DOMDocument("1.0");
                $requestXml->formatOutput = true;

                $requestString = $requestXml->createElement("GETCARDCURRENCYRATE");
                $requestXml->appendChild($requestString);

                $node = $requestXml->createElement("TERMINALID");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode($this->terminalId);
                $node->appendChild($nodeText);

                $node = $requestXml->createElement("CARDBIN");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode($this->cardBin);
                $node->appendChild($nodeText);

                $node = $requestXml->createElement("DATETIME");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode($this->dateTime);
                $node->appendChild($nodeText);

                if ($this->baseAmount != NULL) {
                    $node = $requestXml->createElement("BASEAMOUNT");
                    $requestString->appendChild($node);
                    $nodeText = $requestXml->createTextNode($this->baseAmount);
                    $node->appendChild($nodeText);
                }

                $node = $requestXml->createElement("HASH");
                $requestString->appendChild($node);
                $nodeText = $requestXml->createTextNode($this->hash);
                $node->appendChild($nodeText);

                return $requestXml->saveXML();
        }
}

/**
  *  Holder class for parsed response. If there was an error there will be an error string
  *  otherwise all values will be populated with the parsed payment response values.
  *
  *  IsError should be checked before accessing any fields.
  *
  *  ErrorString will contain the error if one occurred.
  */
class XmlAuthResponse
{
        private $isError = false;
        public function IsError()
        {
                return $this->isError;
        }

        private $errorString;
        public function ErrorString()
        {
                return $this->errorString;
        }

        private $responseCode;
        public function ResponseCode()
        {
                return $this->responseCode;
        }

        private $responseText;
        public function ResponseText()
        {
                return $this->responseText;
        }

        private $approvalCode;
        public function ApprovalCode()
        {
                return $this->approvalCode;
        }

        private $dateTime;
        public function DateTime()
        {
                return $this->dateTime;
        }

        private $avsResponse;
        public function AvsResponse()
        {
                return $this->avsResponse;
        }

        private $cvvResponse;
        public function CvvResponse()
        {
                return $this->cvvResponse;
        }

        private $hash;
        public function Hash()
        {
                return $this->hash;
        }

        public function XmlAuthResponse($responseXml)
        {
                $doc = new DOMDocument();
                $doc->loadXML($responseXml);
                try {
                        if (strpos($responseXml, "ERROR")) {
                                $responseNodes = $doc->getElementsByTagName("ERROR");
                                foreach ($responseNodes as $node) {
                                    $this->errorString = $node->getElementsByTagName('ERRORSTRING')->item(0)->nodeValue;
                                }
                                $this->isError = true;
                        } elseif (strpos($responseXml, "PAYMENTRESPONSE")) {
                                $responseNodes = $doc->getElementsByTagName("PAYMENTRESPONSE");

                                foreach ($responseNodes as $node) {
                                    $this->responseCode = $node->getElementsByTagName('RESPONSECODE')->item(0)->nodeValue;
                                    $this->responseText = $node->getElementsByTagName('RESPONSETEXT')->item(0)->nodeValue;
                                    $this->approvalCode = $node->getElementsByTagName('APPROVALCODE')->item(0)->nodeValue;
                                    $this->dateTime = $node->getElementsByTagName('DATETIME')->item(0)->nodeValue;
                                    $this->avsResponse = $node->getElementsByTagName('AVSRESPONSE')->item(0)->nodeValue;
                                    $this->cvvResponse = $node->getElementsByTagName('CVVRESPONSE')->item(0)->nodeValue;
                                    $this->hash = $node->getElementsByTagName('HASH')->item(0)->nodeValue;
                                }
                        } else {
                                throw new Exception("Invalid Response");
                        }
                } catch (Exception $e) {
                        $isError = true;
                        $errorString = $e->getMessage();
                }
        }
}

/**
  *  Holder class for parsed response. If there was an error there will be an error string
  *  otherwise all values will be populated with the parsed payment response values.
  *
  *  IsError should be checked before accessing any fields.
  *
  *  ErrorString will contain the error if one occurred.
  */
class XmlRefundResponse
{
        private $isError = false;
        public function IsError()
        {
                return $this->isError;
        }

        private $errorString;
        public function ErrorString()
        {
                return $this->errorString;
        }

        private $responseCode;
        public function ResponseCode()
        {
                return $this->responseCode;
        }

        private $responseText;
        public function ResponseText()
        {
                return $this->responseText;
        }

        private $approvalCode;
        public function OrderId()
        {
                return $this->orderId;
        }

        private $avsResponse;
        public function TerminalId()
        {
                return $this->terminalId;
        }

        private $cvvResponse;
        public function Amount()
        {
                return $this->amount;
        }

        private $dateTime;
        public function DateTime()
        {
                return $this->dateTime;
        }

        private $hash;
        public function Hash()
        {
                return $this->hash;
        }

        public function XmlRefundResponse($responseXml)
        {
                $doc = new DOMDocument();
                $doc->loadXML($responseXml);
                try {
                        if (strpos($responseXml, "ERROR")) {
                                $responseNodes = $doc->getElementsByTagName("ERROR");
                                foreach ($responseNodes as $node) {
                                    $this->errorString = $node->getElementsByTagName('ERRORSTRING')->item(0)->nodeValue;
                                }
                                $this->isError = true;
                        } elseif (strpos($responseXml, "REFUNDRESPONSE")) {
                                $responseNodes = $doc->getElementsByTagName("REFUNDRESPONSE");

                                foreach ($responseNodes as $node) {
                                    $this->responseCode = $node->getElementsByTagName('RESPONSECODE')->item(0)->nodeValue;
                                    $this->responseText = $node->getElementsByTagName('RESPONSETEXT')->item(0)->nodeValue;
                                    $this->orderId = $node->getElementsByTagName('ORDERID')->item(0)->nodeValue;
                                    $this->terminalId = $node->getElementsByTagName('TERMINALID')->item(0)->nodeValue;
                                    $this->amount = $node->getElementsByTagName('AMOUNT')->item(0)->nodeValue;
                                    $this->dateTime = $node->getElementsByTagName('DATETIME')->item(0)->nodeValue;
                                    $this->hash = $node->getElementsByTagName('HASH')->item(0)->nodeValue;
                                }
                        } else {
                                throw new Exception("Invalid Response");
                        }
                } catch (Exception $e) {
                        $isError = true;
                        $errorString = $e->getMessage();
                }
        }
}

/**
  *  Holder class for parsed response. If there was an error there will be an error string
  *  otherwise all values will be populated with the parsed payment response values.
  *
  *  IsError should be checked before accessing any fields.
  *
  *  ErrorString will contain the error if one occurred.
  */
class XmlPreAuthResponse
{
        private $isError = false;
        public function IsError()
        {
                return $this->isError;
        }

        private $errorString;
        public function ErrorString()
        {
                return $this->errorString;
        }

        private $responseCode;
        public function ResponseCode()
        {
                return $this->responseCode;
        }

        private $responseText;
        public function ResponseText()
        {
                return $this->responseText;
        }

        private $approvalCode;
        public function ApprovalCode()
        {
                return $this->approvalCode;
        }

        private $dateTime;
        public function DateTime()
        {
                return $this->dateTime;
        }

        private $avsResponse;
        public function AvsResponse()
        {
                return $this->avsResponse;
        }

        private $cvvResponse;
        public function CvvResponse()
        {
                return $this->cvvResponse;
        }

        private $hash;
        public function Hash()
        {
                return $this->hash;
        }

        public function XmlPreAuthResponse($responseXml)
        {
                $doc = new DOMDocument();
                $doc->loadXML($responseXml);
                try {
                        if (strpos($responseXml, "ERROR")) {
                                $responseNodes = $doc->getElementsByTagName("ERROR");
                                foreach ($responseNodes as $node) {
                                    $this->errorString = $node->getElementsByTagName('ERRORSTRING')->item(0)->nodeValue;
                                }
                                $this->isError = true;
                        } elseif (strpos($responseXml, "PREAUTHRESPONSE")) {
                                $responseNodes = $doc->getElementsByTagName("PREAUTHRESPONSE");

                                foreach ($responseNodes as $node) {
                                    $this->responseCode = $node->getElementsByTagName('RESPONSECODE')->item(0)->nodeValue;
                                    $this->responseText = $node->getElementsByTagName('RESPONSETEXT')->item(0)->nodeValue;
                                    $this->approvalCode = $node->getElementsByTagName('APPROVALCODE')->item(0)->nodeValue;
                                    $this->dateTime = $node->getElementsByTagName('DATETIME')->item(0)->nodeValue;
                                    $this->avsResponse = $node->getElementsByTagName('AVSRESPONSE')->item(0)->nodeValue;
                                    $this->cvvResponse = $node->getElementsByTagName('CVVRESPONSE')->item(0)->nodeValue;
                                    $this->hash = $node->getElementsByTagName('HASH')->item(0)->nodeValue;
                                }
                        } else {
                                throw new Exception("Invalid Response");
                        }
                } catch (Exception $e) {
                        $isError = true;
                        $errorString = $e->getMessage();
                }
        }
}

/**
  *  Holder class for parsed response. If there was an error there will be an error string
  *  otherwise all values will be populated with the parsed payment response values.
  *
  *  IsError should be checked before accessing any fields.
  *
  *  ErrorString will contain the error if one occurred.
  */
class XmlPreAuthCompletionResponse
{
        private $isError = false;
        public function IsError()
        {
                return $this->isError;
        }

        private $errorString;
        public function ErrorString()
        {
                return $this->errorString;
        }

        private $responseCode;
        public function ResponseCode()
        {
                return $this->responseCode;
        }

        private $responseText;
        public function ResponseText()
        {
                return $this->responseText;
        }

        private $approvalCode;
        public function ApprovalCode()
        {
                return $this->approvalCode;
        }

        private $dateTime;
        public function DateTime()
        {
                return $this->dateTime;
        }

        private $avsResponse;
        public function AvsResponse()
        {
                return $this->avsResponse;
        }

        private $cvvResponse;
        public function CvvResponse()
        {
                return $this->cvvResponse;
        }

        private $hash;
        public function Hash()
        {
                return $this->hash;
        }

        public function XmlPreAuthCompletionResponse($responseXml)
        {
                $doc = new DOMDocument();
                $doc->loadXML($responseXml);
                try {
                        if (strpos($responseXml, "ERROR")) {
                                $responseNodes = $doc->getElementsByTagName("ERROR");
                                foreach ($responseNodes as $node) {
                                    $this->errorString = $node->getElementsByTagName('ERRORSTRING')->item(0)->nodeValue;
                                }
                                $this->isError = true;
                        } elseif (strpos($responseXml, "PREAUTHCOMPLETIONRESPONSE")) {
                                $responseNodes = $doc->getElementsByTagName("PREAUTHCOMPLETIONRESPONSE");

                                foreach ($responseNodes as $node) {
                                    $this->responseCode = $node->getElementsByTagName('RESPONSECODE')->item(0)->nodeValue;
                                    $this->responseText = $node->getElementsByTagName('RESPONSETEXT')->item(0)->nodeValue;
                                    $this->approvalCode = $node->getElementsByTagName('APPROVALCODE')->item(0)->nodeValue;
                                    $this->dateTime = $node->getElementsByTagName('DATETIME')->item(0)->nodeValue;
                                    $this->avsResponse = $node->getElementsByTagName('AVSRESPONSE')->item(0)->nodeValue;
                                    $this->cvvResponse = $node->getElementsByTagName('CVVRESPONSE')->item(0)->nodeValue;
                                    $this->hash = $node->getElementsByTagName('HASH')->item(0)->nodeValue;
                                }
                        } else {
                                throw new Exception("Invalid Response");
                        }
                } catch (Exception $e) {
                        $isError = true;
                        $errorString = $e->getMessage();
                }
        }
}

/**
  *  Holder class for parsed response. If there was an error there will be an error string
  *  otherwise all values will be populated with the parsed payment response values.
  *
  *  IsError should be checked before accessing any fields.
  *
  *  ErrorString will contain the error if one occurred.
  */
class XmlRateResponse
{
        private $isError = false;
        public function IsError()
        {
                return $this->isError;
        }

        private $errorString;
        public function ErrorString()
        {
                return $this->errorString;
        }

        private $terminalCurrency;
        public function TerminalCurrency()
        {
                return $this->terminalCurrency;
        }

        private $cardCurrency;
        public function CardCurrency()
        {
                return $this->cardCurrency;
        }

        private $conversionRate;
        public function ConversionRate()
        {
                return $this->conversionRate;
        }
        private $foreignAmount;
        public function ForeignAmount()
        {
                return $this->foreignAmount;
        }

        private $dateTime;
        public function DateTime()
        {
                return $this->dateTime;
        }

        private $hash;
        public function Hash()
        {
                return $this->hash;
        }

        public function XmlRateResponse($responseXml)
        {
                $doc = new DOMDocument();
                $doc->loadXML($responseXml);
                try {
                        if (strpos($responseXml, "ERROR")) {
                                $responseNodes = $doc->getElementsByTagName("ERROR");
                                foreach ($responseNodes as $node) {
                                    $this->errorString = $node->getElementsByTagName('ERRORSTRING')->item(0)->nodeValue;
                                }
                                $this->isError = true;
                        } elseif (strpos($responseXml, "CARDCURRENCYRATERESPONSE")) {
                                $responseNodes = $doc->getElementsByTagName("CARDCURRENCYRATERESPONSE");

                                foreach ($responseNodes as $node) {
                                    $this->terminalCurrency = $node->getElementsByTagName('TERMINALCURRENCY')->item(0)->nodeValue;
                                    $this->cardCurrency = $node->getElementsByTagName('CARDCURRENCY')->item(0)->nodeValue;
                                    $this->conversionRate = $node->getElementsByTagName('CONVERSIONRATE')->item(0)->nodeValue;
                                    $this->foreignAmount = $node->getElementsByTagName('FOREIGNAMOUNT')->item(0)->nodeValue;
                                    $this->dateTime = $node->getElementsByTagName('DATETIME')->item(0)->nodeValue;
                                    $this->hash = $node->getElementsByTagName('HASH')->item(0)->nodeValue;
                                }
                        } else {
                                throw new Exception("Invalid Response");
                        }
                } catch (Exception $e) {
                        $isError = true;
                        $errorString = $e->getMessage();
                }
        }
}

/**
  *  For backward compatibility with older class names.
  */
class XmlStandardRequest extends XmlAuthRequest { }
class XmlStandardResponse extends XmlAuthResponse { }
