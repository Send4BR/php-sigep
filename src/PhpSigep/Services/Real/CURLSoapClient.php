<?php
/**
 * Created by IntelliJ IDEA.
 * User: bonfante
 * Date: 08/02/19
 * Time: 15:41
 */

namespace Api\PostalCodes\Soap;


class CURLSoapClient extends \SoapClient
{
    const DEFAULT_TIMEOUT = 3;
    private $timeout;

    public function __construct($wsdl, $options)
    {
        parent::__construct($wsdl, $options);
        $this->timeout = isset($options['timeout']) ? $options['timeout'] : self::DEFAULT_TIMEOUT;
    }

    /**
     * @param string $request
     * @param string $location
     * @param string $action
     * @param int $version
     * @param int $one_way
     * @return bool|string
     * @throws \Exception
     */
    public function __doRequest($request, $location, $action, $version, $one_way = 0)
    {
        // Call via Curl and use the timeout
        if (!function_exists('curl_init')) {
            throw new \RuntimeException('ext-curl missing');
        }
        $curl = \curl_init($location);
        \curl_setopt($curl, CURLOPT_VERBOSE, false);
        \curl_setopt($curl, CURLOPT_RETURNTRANSFER, !$one_way);
        \curl_setopt($curl, CURLOPT_POST, true);
        \curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
        \curl_setopt($curl, CURLOPT_HEADER, false);
        \curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: text/xml; charset=utf-8']);
        \curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        $response = \curl_exec($curl);
        if ($errno = \curl_errno($curl)) {
            $exp = new \Exception(curl_error($curl), $errno);
            \curl_close($curl);
            throw $exp;
        }
        \curl_close($curl);
        if (!$one_way) {
            return $response;
        }
    }
}