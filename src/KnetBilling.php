<?php

namespace IZaL\Knet;

use IZaL\Knet\Billing;
use IZaL\Knet\Exceptions\KeyMissingException;
use \ZipArchive;

class KnetBilling implements Billing
{

    const CIPHER_KEY = "Those who profess to favour freedom and yet depreciate agitation are men who want rain without thunder and lightning";

    protected $webaddress;
    protected $port = "443";
    protected $id;
    protected $password;
    protected $passwordhash;
    protected $action = "1";
    protected $transId;
    protected $amt;
    protected $responseURL;
    protected $trackId;
    protected $paymentUrl;
    protected $paymentId;
    protected $currency = "414";
    protected $errorURL;
    protected $language = "ENG";
    protected $context;
    protected $resourcePath;
    protected $alias;

    protected $requiredConstructorKeys = ['alias', 'resourcePath'];

    public function __construct(array $options = [])
    {
        foreach ($options as $key => $val) {
            $this->{$key} = $val;
        }

        foreach ($this->requiredConstructorKeys as $requiredField) {
            if (empty($this->{$requiredField})) {
                throw new KeyMissingException($requiredField . ' key is missing');
            }
        }

        $this->initResourceFile();
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

    public function getPaymentID()
    {
        return $this->paymentId;
    }

    /**
     * @throws \Exception
     */
    private function initResourceFile()
    {
        $payload = $this->parseResourceFile();
        foreach ($payload as $key => $value) {
            $this->{$key} = $value;
        }
        unlink($this->resourcePath . "resource.cgz");
        return $this;
    }


    /**
     * @return string
     * @throws \Exception
     */
    private function processRequest()
    {
        $url = $this->buildUrl();
        $urlParams = $this->buildUrlParams();

        if (empty($url) || empty($urlParams) || empty($this->webaddress)) {
            throw new \Exception("Failed to make connection to the Target URL");
        }

        $request = curl_init();

        curl_setopt($request, CURLOPT_HEADER, 0);
        curl_setopt($request, CURLOPT_URL, $url);
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

        return $response;
    }


    /**
     * @return array
     * @throws \Exception
     */
    private function parseResourceFile()
    {
        $filenameInput = $this->resourcePath . "resource.cgn";

        if(!is_file($filenameInput)) {
            throw new \Exception('Resource file not found on the path '. $this->resourcePath);
        }

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

        $parsedZip = $this->parseZip($this->getString($this->simpleXOR($this->getBytes($xmlContentsInput))));

        if (!is_array($parsedZip)) {
            throw new \Exception('Parsed Zip file returned Wrong Format, Should be an array');
        }

        return $parsedZip;

    }

    private function getBytes($string)
    {
        $hexArray = [];
        $size = strlen($string);
        for ($i = 0; $i < $size; $i++) {
            $hexArray[] = chr(ord($string[$i]));
        }
        return $hexArray;
    }

    private function simpleXOR($abyte0)
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

    private function getString($byteArray)
    {
        return implode('', $byteArray);
    }

    private function parseZip($zip)
    {
        return json_decode(json_encode((array)simplexml_load_string($zip)), 1);
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function requestPayment()
    {
        $payload = $this->processRequest();
        $paymentURL = strpos($payload, ":");
        $this->paymentId = substr($payload, 0, $paymentURL);
        $this->paymentUrl = substr($payload, $paymentURL + 1);
        return $this;
    }

    private function buildUrl()
    {
        $protocol = $this->port == "443" ? "https://" : "http://";
        $stringBuffer = $protocol . $this->webaddress . ':' . $this->port;
        $stringBuffer .= empty($this->context) ? "/" : "/".$this->context."/";
        $stringBuffer .= "servlet/PaymentInitHTTPServlet";
        return $stringBuffer;
    }

    private function buildUrlParams()
    {
        $params = http_build_query([
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
        ]);
        return $params;
    }

}

?>