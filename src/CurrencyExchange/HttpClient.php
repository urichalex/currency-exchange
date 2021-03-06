<?php

/**
 * CurrencyExchange
 * 
 * A library to retrieve currency exchanges using several web services
 * 
 * @link https://github.com/teknoman/currency-exchange
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace CurrencyExchange;

use InvalidArgumentException;
use Zend\Http\Request as HttpRequest;
use Zend\Http\Client\Adapter\Curl as CurlAdapter;
use Zend\Http\Client as ZfHttpClient;
use Zend\Http\Response as ZfHttpResponse;
use CurrencyExchange\Options;
use CurrencyExchange\Exception\ResponseException;

/**
 * Makes a request to the current Uri
 * 
 * @package CurrencyExchange
 */
class HttpClient
{
	/**
	 * Constant for HTTP method GET
	 */
	const HTTP_GET = 'GET';

	/**
	 * Constant for HTTP method POST
	 */
	const HTTP_POST = 'POST';

	/**
	 * @var string The uri to call
	 */
	protected $_uri = null;

	/**
	 * @var string Can be GET or POST
	 */
	protected $_httpMethod = null;

	/**
	 * @var CurrencyExchange\Options An object for handling options for cURL
	 */
	protected $_curlOptions = null;

	/**
	 * @var array The data to send via Http POST 
	 */
	protected $_postData = array();

	/**
	 * @var Zend\Http\Response
	 */
	protected $_response = null;

	/**
	 * Constructor set default cURL options
	 */
	public function __construct()
	{
		$this->_curlOptions = new Options();
		$this->_curlOptions->setOptions(array(
			CURLOPT_HEADER => false,
			CURLOPT_RETURNTRANSFER => true,
		));
	}

	/**
	 * @return Zend\Http\Response
	 */
	public function getResponse()
	{
		return $this->_response;
	}

	/**
	 * Set Http response in case of successful request
	 * 
	 * @param Zend\Http\Response $response
     * @throws CurrencyExchange\Exception\ResponseException
	 * @return CurrencyExchange\HttpClient
	 */
	public function setResponse(ZfHttpResponse $response)
	{
        if (!$response->isSuccess()) {
			throw new ResponseException('HTTP Error ' . $response->getStatusCode() . ' on ' . $this->getUri());
		}

        $this->_response = $response;
		return $this;
	}

    /**
	 * @return CurrencyExchange\Options
	 */
	public function getCurlOptions()
	{
		return $this->_curlOptions;
	}

	/**
	 * @return array
	 */
	public function getPostData()
	{
		return $this->_postData;
	}

	/**
	 * Checks if Http method is GET
	 * 
	 * @return boolean
	 */
	public function isHttpGet()
	{
		return $this->_httpMethod === static::HTTP_GET;
	}

	/**
	 * Checks if Http method is POST
	 * 
	 * @return boolean
	 */
	public function isHttpPost()
	{
		return $this->_httpMethod === static::HTTP_POST;
	}

    /**
     * Return current uri
     * 
     * @return string
     */
    public function getUri()
    {
        return $this->_uri;
    }

    /**
	 * @param string $uri
	 * @return CurrencyExchange\HttpClient
	 */
	public function setUri($uri)
	{
		$this->_uri = (string) $uri;
		return $this;
	}

    /**
     * Return current http method
     * 
     * @return string
     */
    public function getHttpMethod()
    {
        return $this->_httpMethod;
    }

    /**
	 * Sets the Http method, only GET or POST are actually supported
	 * 
	 * @param string $httpMethod Can be GET or POST
	 * @throws InvalidArgumentException
	 * @return CurrencyExchange\HttpClient
	 */
	public function setHttpMethod($httpMethod)
	{
		$httpMethod = strtoupper((string) $httpMethod);

		if (!in_array($httpMethod, array(static::HTTP_GET, static::HTTP_POST))) {
			throw new InvalidArgumentException('Http method can be GET or POST, ' . $httpMethod . ' given');
		}

		$this->_httpMethod = $httpMethod;
		return $this;
	}

	/**
	 * Set data to be sent via Http POST
	 * 
	 * @param array $postData
	 * @return CurrencyExchange\HttpClient
	 */
	public function setPostData(array $postData)
	{
		$this->_postData = $postData;
		return $this;
	}

	/**
	 * Set proxy for the http client
	 * 
	 * @param string $proxy A string that identifies proxy server, in the format 'host:port'
	 * @throws InvalidArgumentException
	 * @return CurrencyExchange\HttpClient
	 */
	public function setProxy($proxy)
	{
		$proxy = (string) $proxy;

		if (!preg_match('/^[a-z0-9\.]+:[0-9]+$/iu', $proxy)) {
			throw new InvalidArgumentException('Proxy must be a string according to format host:port');
		}

		$this->getCurlOptions()->addOption(CURLOPT_PROXY, $proxy);
		return $this;
	}

	/**
	 * Makes request to the uri currently set
	 * 
	 * @return CurrencyExchange\HttpClient
	 */
	public function makeRequest()
	{
		/** @var Zend\Http\Request */
		$request = new HttpRequest();
		$request->setUri($this->getUri());
		$request->setMethod($this->getHttpMethod());

		if ($this->isHttpPost()) {
			$this->getCurlOptions()->addOption(CURLOPT_POST, true);
			$this->getCurlOptions()->addOption(CURLOPT_POSTFIELDS, $this->getPostData());
		}

		/** @var Zend\Http\Client\Adapter\Curl */
		$adapter = new CurlAdapter();
		$adapter->setOptions(array(
			'curloptions' => $this->getCurlOptions()->getOptions()
		));

		/** @var Zend\Http\Client */
		$client = new ZfHttpClient();
		$client->setAdapter($adapter);

		// setting response
		$this->setResponse($client->dispatch($request));

		return $this;
	}
}
