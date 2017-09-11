<?php

namespace App\Core;

use IZaL\Knet\Billing;
use \ZipArchive;

class KnetBilling implements Billing
{

    public $SUCCESS = 0;
    protected $FAILURE = -1;
    protected $BUFFER = 2320;
    protected $strIDOpen = "<id>";
    protected $strPasswordOpen = "<password>";
    protected $strWebAddressOpen = "<webaddress>";
    protected $strPortOpen = "<port>";
    protected $strContextOpen = "<context>";
    protected $strIDClose = "</id>";
    protected $strPasswordClose = "</password>";
    protected $strWebAddressClose = "</webaddress>";
    protected $strPortClose = "</port>";
    protected $strContextClose = "</context>";
    protected $webAddress;
    protected $port;
    protected $id;
    protected $password;
    protected $passwordHash;
    protected $action;
    protected $transId;
    protected $amt;
    protected $responseURL;
    protected $trackId;
    protected $udf1;
    protected $udf2;
    protected $udf3;
    protected $udf4;
    protected $udf5;
    protected $paymentUrl;
    protected $paymentId;
    protected $result;
    protected $auth;
    protected $ref;
    protected $avr;
    protected $date;
    protected $currency;
    protected $errorURL;
    protected $language;
    protected $context;
    protected $resourcePath;
    protected $alias;
    protected $error;
    protected $rawResponse;
    protected $debugMsg;
    protected $arr = [];
    protected $errorMessages = [];

    public function __construct()
    {
        $this->webAddress = "";
        $this->port = "443";
        $this->id = "";
        $this->password = "";
        $this->action = ""; // 1 = purchase
        $this->transId = "";
        $this->amt = "";
        $this->responseURL = "";
        $this->trackId = "";
        $this->udf1 = "";
        $this->udf2 = "";
        $this->udf3 = "";
        $this->udf4 = "";
        $this->udf5 = "";
        $this->paymentUrl = "";
        $this->paymentId = "";
        $this->result = 0;
        $this->auth = "";
        $this->ref = "";
        $this->avr = "";
        $this->date = "";
        $this->currency = "";
        $this->errorURL = "";
        $this->language = "";
        $this->context = "";
        $this->resourcePath = "";
        $this->alias = "";
        $this->error = "";
        $this->rawResponse = "";
        $this->debugMsg = "";
    }

    public function getWebAddress()
    {
        return $this->webAddress;
    }

    public function setWebAddress($s)
    {
        $this->webAddress = $s;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function setPort($s)
    {
        $this->port = $s;
    }

    public function set($k, $v)
    {
        $this->arr[$k] = $v;
    }

    public function get($k)
    {
        return $this->arr[$k];
    }

    public function setId($s)
    {
        $this->id = $s;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setPassword($s)
    {
        $this->password = $s;
    }

    public function setPasswordHash($s)
    {
        $this->passwordHash = $s;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getPasswordHash()
    {
        return $this->passwordHash;
    }

    public function setAction($s)
    {
        $this->action = $s;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function setTransId($s)
    {
        $this->transId = $s;
    }

    public function getTransId()
    {
        return $this->transId;
    }

    public function setAmt($s)
    {
        $this->amt = $s;
    }

    public function getAmt()
    {
        return $this->amt;
    }

    public function setResponseURL($s)
    {
        $this->responseURL = $s;
    }

    public function getResponseURL()
    {
        return $this->responseURL;
    }

    public function setTrackId($s)
    {
        $this->trackId = $s;
    }

    public function getTrackId()
    {
        return $this->trackId;
    }

    public function setUdf1($s)
    {
        $this->udf1 = $s;
    }

    public function getUdf1()
    {
        return $this->udf1;
    }

    public function setUdf2($s)
    {
        $this->udf2 = $s;
    }

    public function getUdf2()
    {
        return $this->udf2;
    }

    public function setUdf3($s)
    {
        $this->udf3 = $s;
    }

    public function getUdf3()
    {
        return $this->udf3;
    }

    public function setUdf4($s)
    {
        $this->udf4 = $s;
    }

    public function getUdf4()
    {
        return $this->udf4;
    }

    public function setUdf5($s)
    {
        $this->udf5 = $s;
    }

    public function getUdf5()
    {
        return $this->udf5;
    }

    public function getPaymentUrl()
    {
        return $this->paymentUrl;
    }

    public function getPaymentId()
    {
        return $this->paymentId;
    }

    public function setPaymentId($s)
    {
        $this->paymentId = $s;
    }

    public function setPaymentUrl($s)
    {
        $this->paymentUrl = $s;
    }

    public function getRedirectContent()
    {
        return $this->paymentUrl . "&PaymentID=" . $this->paymentId;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function getAuth()
    {
        return $this->auth;
    }

    public function getAvr()
    {
        return $this->avr;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getRef()
    {
        return $this->ref;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function setCurrency($s)
    {
        $this->currency = $s;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setLanguage($s)
    {
        $this->language = $s;
    }

    public function getErrorURL()
    {
        return $this->errorURL;
    }

    public function setErrorURL($s)
    {
        $this->errorURL = $s;
    }

    public function setContext($s)
    {
        $this->context = $s;
    }

    public function getResourcePath()
    {
        return $this->resourcePath;
    }

    public function setResourcePath($s)
    {
        $this->resourcePath = $s;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function setAlias($s)
    {
        $this->alias = $s;
    }

    public function getErrorMsg()
    {
        return $this->error;
    }

    public function getRawResponse()
    {
        return $this->rawResponse;
    }

    public function getDebugMsg()
    {
        return $this->debugMsg;
    }

    /**
     * @return number
     * returns 0 or -1
     * 0 success
     * -1 failed
     */
    public function requestPayment()
    {
        $stringBuffer = "";

        if (!$this->getSecureSettings()) {
            return -1;
        }

        if (strlen($this->id) > 0)
            $stringBuffer .= ("id=" . $this->id . "&");
        if (strlen($this->password) > 0)
            $stringBuffer .= ("password=" . $this->password . "&");
        if (strlen($this->passwordHash) > 0)
            $stringBuffer .= ("passwordhash=" . $this->passwordHash . "&");
        if (strlen($this->amt) > 0)
            $stringBuffer .= ("amt=" . $this->amt . "&");
        if (strlen($this->currency) > 0)
            $stringBuffer .= ("currencycode=" . $this->currency . "&");
        if (strlen($this->action) > 0)
            $stringBuffer .= ("action=" . $this->action . "&");
        if (strlen($this->language) > 0)
            $stringBuffer .= ("langid=" . $this->language . "&");
        if (strlen($this->responseURL) > 0)
            $stringBuffer .= ("responseURL=" . $this->responseURL . "&");
        if (strlen($this->errorURL) > 0)
            $stringBuffer .= ("errorURL=" . $this->errorURL . "&");
        if (strlen($this->trackId) > 0)
            $stringBuffer .= ("trackid=" . $this->trackId . "&");
        if (strlen($this->udf1) > 0)
            $stringBuffer .= ("udf1=" . $this->udf1 . "&");
        if (strlen($this->udf2) > 0)
            $stringBuffer .= ("udf2=" . $this->udf2 . "&");
        if (strlen($this->udf3) > 0)
            $stringBuffer .= ("udf3=" . $this->udf3 . "&");
        if (strlen($this->udf4) > 0)
            $stringBuffer .= ("udf4=" . $this->udf4 . "&");
        if (strlen($this->udf5) > 0)
            $stringBuffer .= ("udf5=" . $this->udf5 . "&");

        $s = $this->sendMessage($stringBuffer, "PaymentInitHTTPServlet");

        if ($s == null) {
            return -1;
        }

        $i = strpos($s, ":");

        if ($i == -1) {
            $this->error = "Payment Initialization returned an invalid response: " . $s;
            return -1;
        }

        $this->paymentId = substr($s, 0, $i);
        $this->paymentUrl = substr($s, $i + 1);
        return 0;
    }

    public function sendMessage($urlParams, $servletConstant)
    {
        $stringBuffer = "";
        $error = "";

        $this->debugMsg .= "---------- " . $servletConstant . ": " . time() . " ----------";


        if (!strlen($urlParams) > 0) {
            $this->clearFields();
            $this->error = "Failed to make connection:\n" . $error;  //. $exception;
            return null;
        }

        if (strlen($this->webAddress) <= 0) {
            $this->error = "No URL specified.";
            return null;
        }


        $port = $this->port == "443" ? "https://" : "http://";

        $stringBuffer .= $port . $this->webAddress . ':' . $this->port;

        if (strlen($this->context) > 0) {

            if (!$this->startsWith($this->context, "/")) {
                $stringBuffer .= "/";
            }

            $stringBuffer .= $this->context;

            if (!$this->endsWith($this->context, "/")) {
                $stringBuffer .= "/";
            }
        } else {
            $stringBuffer .= "/";
        }


        $stringBuffer .= "servlet/" . $servletConstant;

        $this->debugMsg .= "Building URL : " . $stringBuffer . "\n";

        $c = curl_init();
        curl_setopt($c, CURLOPT_HEADER, 0);
        curl_setopt($c, CURLOPT_URL, $stringBuffer);
        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, $urlParams);
        curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, true);
        $this->debugMsg .= "about to write DataOutputSteam\n";
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        $this->debugMsg .= "after DataOutputStream.!!\n";
        $tmp = curl_exec($c);

        if (curl_error($c)) {
            $this->error = 'CURL ERROR: ' . curl_errno($c) . '::' . curl_error($c);
            return null;
        }

        if (!$tmp) {
            $this->error = "No Data To Post!";
            return null;
        }

        curl_close($c);

        $this->rawResponse = $tmp;
        $this->debugMsg .= "Received RESPONSE: " . $this->rawResponse;

        return $this->rawResponse;
    }


    public function clearFields()
    {
        $this->error = "";
        $this->paymentUrl = "";
        $this->paymentId = "";
    }

    /**
     * @return bool
     */
    public function getSecureSettings()
    {
        if (!$this->createReadableZip()) {
            return false;
        }

        $zip = $this->readZip();

        if ($zip == "") {
            return false;
        }

        unlink($this->getResourcePath() . "resource.cgz");

        return $this->parseSettings($zip);
    }

    public function createReadableZip()
    {

        $filenameInput = $this->getResourcePath() . "resource.cgn";
        $handleInput = fopen($filenameInput, "r");
        $contentsInput = fread($handleInput, filesize($filenameInput));

        $filenameOutput = $this->getResourcePath() . "resource.cgz";
        @unlink($filenameOutput);
        $handleOutput = fopen($filenameOutput, "w");

        $inByteArray = $this->getBytes($contentsInput);
        $outByteArray = $this->simpleXOR($inByteArray);

        fwrite($handleOutput, $this->getString($outByteArray));
        fclose($handleInput);
        fclose($handleOutput);

        return true;
    }

    public function readZip()
    {
        $s = "";
        $filenameInput = $this->getResourcePath() . "resource.cgz";
        $zip = new ZipArchive;
        if ($zip->open($filenameInput) === TRUE) {
            $zip->extractTo($this->resourcePath);
            $zip->close();
        } else {
            echo 'failed';
            $this->error = "Failed to unzip file";
        }

        if (strlen($this->error) === 0) {
            $xmlNameInput = $this->resourcePath . $this->getAlias() . ".xml";
            $xmlHandleInput = fopen($xmlNameInput, "r");
            $xmlContentsInput = fread($xmlHandleInput, filesize($xmlNameInput));
            fclose($xmlHandleInput);
            unlink($xmlNameInput);
            $s = $xmlContentsInput;

            $s = $this->getString($this->simpleXOR($this->getBytes($s)));
        } else {
            $this->error = "Unable to open resource";
        }
        return $s;
    }

    public function parseSettings($zip)
    {
        $this->setId($this->extractKey($zip, "<id>", "</id>"));
        $this->setPassword($this->extractKey($zip, "<password>", "</id>"));
        $this->setPasswordHash($this->extractKey($zip, "<passwordhash>", "</passwordhash>"));
        $this->setWebAddress($this->extractKey($zip, "<webaddress>", "</webaddress>"));
        $this->setPort($this->extractKey($zip, "<port>", "</port>"));
        $this->setContext($this->extractKey($zip, "<context>", "</context>"));
        return true;
    }

    public function extractKey($path, $start, $end)
    {
        $i = strpos($path, $start) + strlen($start);
        $j = strpos($path, $end);
        return substr($path, $i, $j - $i);
    }

    public function simpleXOR($abyte0)
    {
        $key = "Those who profess to favour freedom and yet depreciate agitation are men who want rain without thunder and lightning";
        $abyte1 = $this->getBytes($key);
        $abyte2 = [];

        for ($i = 0; $i < sizeof($abyte0);) {
            for ($j = 0; $j < sizeof($abyte1); $j++) {
                $abyte2[$i] = $abyte0[$i] ^ $abyte1[$j];
                if (++$i == sizeof($abyte0))
                    break;
            }
        }

        return $abyte2;
    }

    public function getBytes($s)
    {
        $hexArray = [];

        $size = strlen($s);

        for ($i = 0; $i < $size; $i++) {
            $hexArray[] = chr(ord($s[$i]));
        }

        return $hexArray;
    }

    public function getString($byteArray)
    {
        $s = "";
        foreach ($byteArray as $byte) {
            $s .= $byte;
        }
        return $s;
    }

    public function startsWith($haystack, $needle)
    {
        return strpos($haystack, $needle) === 0;
    }

    public function endsWith($haystack, $needle)
    {
        return strrpos($haystack, $needle) === strlen($haystack) - strlen($needle);
    }

}


?>