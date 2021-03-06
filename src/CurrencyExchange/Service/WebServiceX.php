<?php

/**
 * CurrencyExchange
 * 
 * A library to retrieve currency exchanges using several web services
 * 
 * @link https://github.com/teknoman/currency-exchange
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace CurrencyExchange\Service;

use DOMDocument;
use CurrencyExchange\Exception\ParseException;
use CurrencyExchange\HttpClient;
use CurrencyExchange\Factory\UriFactory;

/**
 * @package CurrencyExchange
 */
class WebServiceX extends ServiceAbstract
{
	public function __construct()
	{
		/** @var CurrencyExchange\Uri\UriGet */
		$uri = UriFactory::factory(HttpClient::HTTP_GET);
		$uri->setTemplateUri('http://www.webservicex.net/CurrencyConvertor.asmx/ConversionRate?FromCurrency={%FROMCURRENCY%}&ToCurrency={%TOCURRENCY%}');

		// Istantiates and initializes HttpClient and Uri objects
		parent::__construct($uri);
	}

	/**
	 * Implementation of abstract method getExchangeRate
	 * 
	 * @throws CurrencyExchange\Exception\ParseException
	 * @return float
	 */
	public function getExchangeRate()
	{
		$dom = new DOMDocument();

		if (!$dom->loadXML($this->getResponseContent())) {
			throw new ParseException('There was an error processing response');
		}

		/** @var DOMNodeList */
		$objects = $dom->getElementsByTagName('double');

		if (!$objects->item(0)) {
			throw new ParseException('Exchange rate not found');
		}

		return (float) $objects->item(0)->nodeValue;
	}
}
