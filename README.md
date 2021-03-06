CurrencyExchange
=================
[![Build Status](https://travis-ci.org/teknoman/currency-exchange.svg?branch=dev)](https://travis-ci.org/teknoman/currency-exchange)

A library to retrieve currency exchanges using several web services.

A simple usage:
```php
<?php

$exchanger = new \CurrencyExchange\Exchanger();

// This method will print the current exchange rate from Euros to U.S. Dollars using default web service (YahooFinance)
$result = $exchanger->getExchangeRate('EUR', 'USD');

// This method will exchange 14 Euros in the correspondant U.S. Dollars, it uses the default exchange service (YahooFinance)
$result = $exchanger->exchange(14, 'EUR', 'USD');
```

Alternatively, you can set your preferred exchange service:
```php
<?php

$exchanger = new \CurrencyExchange\Exchanger('GrandTrunk');

// This method will print the current exchange rate from Euros to U.S. Dollars using GrandTrunk web service
$result = $exchanger->getExchangeRate('EUR', 'USD');

// This method will exchange 14 Euros in the correspondant U.S. Dollars using GrandTrunk web service
$result = $exchanger->exchange(14, 'EUR', 'USD');
```

If you need to use a proxy, you can set it by invoking `setProxy()` method on `$exchanger` instance:
```php
$exchanger->setProxy('example.host.com:8080'); // In the format 'host:port'
```
In release 2.* there is a currencies database (for now, in the form of a JSON file) that is queried each time we set a currency to check the validity of the currency, it's in `data` folder. If you need to skip this check, you can do this by invoking `setSkipCurrencyDataCheck` method with `true`:
```php
$exchanger->setSkipCurrencyDataCheck(true);
```