<?php

namespace IZaL\Knet;

use IZaL\Knet\Billing;
use \ZipArchive;

class KnetBilling implements Billing
{

    const CIPHER_KEY = "Those who profess to favour freedom and yet depreciate agitation are men who want rain without thunder and lightning";

    protected $webaddress;
    protected $port;
    protected $id;
    protected $password;
    protected $passwordhash;
    protected $action;
    protected $transId;
    protected $amt;
    protected $responseURL;
    protected $trackId;
    protected $paymentUrl;
    protected $paymentId;
    protected $currency;
    protected $errorURL;
    protected $language;
    protected $context;
    protected $resourcePath;
    protected $alias;

    public function __construct()
    {
        $this->webaddress = "";
        $this->port = "443";
        $this->id = "";
        $this->password = "";
        $this->action = "1";
        $this->transId = "";
        $this->amt = "";
        $this->responseURL = "";
        $this->trackId = "";
        $this->paymentUrl = "";
        $this->paymentId = "";
        $this->currency = "";
        $this->errorURL = "";
        $this->language = "";
        $this->context = "";
        $this->resourcePath = "";
        $this->alias = "";
    }

    public function setPort($s)
    {
        $this->port = $s;
    }

    public function setId($s)
    {
        $this->id = $s;
    }

    public function setAction($s)
    {
        $this->action = $s;
    }

    public function setTransId($s)
    {
        $this->transId = $s;
    }

    public function setAmt($s)
    {
        $this->amt = $s;
    }

    public function setResponseURL($s)
    {
        $this->responseURL = $s;
    }

    public function setTrackId($s)
    {
        $this->trackId = $s;
    }

    public function setPaymentId($s)
    {
        $this->paymentId = $s;
    }

    public function setPaymentUrl($s)
    {
        $this->paymentUrl = $s;
    }

    public function setCurrency($s)
    {
        $this->currency = $s;
    }

    public function setLanguage($s)
    {
        $this->language = $s;
    }

    public function setErrorURL($s)
    {
        $this->errorURL = $s;
    }

    public function setResourcePath($s)
    {
        $this->resourcePath = $s;
    }

    public function setAlias($s)
    {
        $this->alias = $s;
    }

    public function getPaymentURL()
    {
        return $this->paymentUrl . "&PaymentID=" . $this->paymentId;
    }

    public function requestPayment()
    {

        $payload = $this->initRequest();

        $paymentURL = strpos($payload, ":");

        $this->paymentId = substr($payload, 0, $paymentURL);
        $this->paymentUrl = substr($payload, $paymentURL + 1);

        return $this;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function initRequest()
    {

        $this->readFromResource();

        $args = [
            'id'           => $this->id,
            'password'     => $this->password,
            'passwordhash' => $this->passwordhash,
            'currencycode' => $this->currency,
            'action'       => $this->action,
            'langid'       => $this->language,
            'responseURL'  => $this->responseURL,
            'errorURL'     => $this->errorURL,
            'trackId'      => $this->trackId,
            'amt'          => $this->amt,
        ];

        $urlParams = http_build_query($args);

        if (empty($urlParams)) {
            throw new \Exception("Failed to make connection");
        }

        if (empty($this->webaddress)) {
            throw new \Exception("Could not build URL");
        }

        $protocol = $this->port == "443" ? "https://" : "http://";

        $stringBuffer = $protocol . $this->webaddress . ':' . $this->port;

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

        $stringBuffer .= "servlet/" . "PaymentInitHTTPServlet";

        $request = curl_init();

        curl_setopt($request, CURLOPT_HEADER, 0);
        curl_setopt($request, CURLOPT_URL, $stringBuffer);
        curl_setopt($request, CURLOPT_POST, true);
        curl_setopt($request, CURLOPT_POSTFIELDS, $urlParams);
        curl_setopt($request, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($request);

        if (curl_error($request)) {
            throw new \Exception(sprintf("CURL ERROR %s", curl_errno($request)));
        }

        if (!$response) {
            throw new \Exception("No Data To Post");
        }

        curl_close($request);

        return $response;
    }


    /**
     * @throws \Exception
     */
    public function readFromResource()
    {
        $payload = $this->createReadableZip();

        if(!is_array($payload)) {
            throw new \Exception('Could not parse the Zip file');
        }

        foreach ($payload as $key => $value) {
            $this->{$key} = $value;
        }

        unlink($this->resourcePath . "resource.cgz");

        return $this;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function createReadableZip()
    {
        $filenameInput = $this->resourcePath . "resource.cgn";
        $handleInput = fopen($filenameInput, "r");
        $contentsInput = fread($handleInput, filesize($filenameInput));

        $filenameOutput = $this->resourcePath . "resource.cgz";
        $handleOutput = fopen($filenameOutput, "w");

        $inByteArray = $this->getBytes($contentsInput);
        $outByteArray = $this->simpleXOR($inByteArray);

        fwrite($handleOutput, $this->getString($outByteArray));


        $zip = new ZipArchive;

        if (!$zip->open($filenameOutput)) {
            throw new \Exception('Could not open the Zip file');
        }

        $zip->extractTo($this->resourcePath);
        $zip->close();

        $xmlNameInput = $this->resourcePath . $this->alias . ".xml";
        $xmlHandleInput = fopen($xmlNameInput, "r");
        $xmlContentsInput = fread($xmlHandleInput, filesize($xmlNameInput));

        fclose($xmlHandleInput);
        fclose($handleInput);
        fclose($handleOutput);

        unlink($xmlNameInput);

        $parsedZip = $this->getString($this->simpleXOR($this->getBytes($xmlContentsInput)));

        return $this->parseZip($parsedZip);

    }

    public function getBytes($string)
    {
        $hexArray = [];

        $size = strlen($string);

        for ($i = 0; $i < $size; $i++) {
            $hexArray[] = chr(ord($string[$i]));
        }

        return $hexArray;
    }

    public function simpleXOR($abyte0)
    {
        $key = self::CIPHER_KEY;
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

    public function getString($byteArray)
    {
        return implode('',$byteArray);
    }

    public function startsWith($haystack, $needle)
    {
        return strpos($haystack, $needle) === 0;
    }

    public function endsWith($haystack, $needle)
    {
        return strrpos($haystack, $needle) === strlen($haystack) - strlen($needle);
    }

    private function parseZip($zip)
    {
        return json_decode(json_encode((array)simplexml_load_string($zip)), 1);
    }

}


?>