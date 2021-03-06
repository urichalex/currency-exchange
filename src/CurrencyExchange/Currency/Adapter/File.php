<?php

/**
 * CurrencyExchange
 * 
 * A library to retrieve currency exchanges using several web services
 * 
 * @link https://github.com/teknoman/currency-exchange
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace CurrencyExchange\Currency\Adapter;

use Zend\Json\Json;
use RuntimeException;

/**
 * Concrete class to handle get and set of currency's data in file
 * 
 * @package CurrencyExchange
 */
class File extends AdapterAbstract
{
	/**
	 * Constant for currency's data filename
	 */
	const DATA_FILENAME = 'currency_codes.json';

    /**
     * @var string Alternative filename for currency data
     */
    protected $_dataFilename = null;

    /**
     * @var string Alternative directory in which save currency data file
     */
    protected $_dataDirectory = null;

    /**
	 * Returns default directory in which data file reside
	 * 
	 * @return string
	 */
	protected function _getDefaultDataDirectory()
	{
		$directory = explode(DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR, __DIR__);
		$directory = $directory[0] . DIRECTORY_SEPARATOR . 'data';

		return $directory;
	}

    /**
     * Return data directory
     * 
     * @return string
     */
    public function getDataDirectory()
    {
        return $this->_dataDirectory;
    }

    /**
     * Set data directory
     * 
     * @param string $directory
     * @return \CurrencyExchange\Currency\Adapter\File
     */
    public function setDataDirectory($directory)
    {
        $this->_dataDirectory = (string) $directory;
        return $this;
    }

    /**
     * Return data filename
     * 
     * @return string
     */
    public function getDataFilename()
    {
        return $this->_dataFilename;
    }

    /**
     * Set data filename
     * 
     * @param string $filename
     * @return \CurrencyExchange\Currency\Adapter\File
     */
    public function setDataFilename($filename)
    {
        $this->_dataFilename = (string) $filename;
        return $this;
    }

    /**
	 * Returns the full path of currecy data file
	 * 
	 * @return string
	 */
	public function getFullPath()
	{
        if ($this->getDataDirectory()) {
            $directory = $this->getDataDirectory();
        } else {
            $directory = $this->_getDefaultDataDirectory();
        }

        if ($this->getDataFilename()) {
            $filename = $this->getDataFilename();
        } else {
            $filename = static::DATA_FILENAME;
        }

        return $directory . DIRECTORY_SEPARATOR . $filename;
	}

	/**
	 * Implementation of CurrencyExchange\Currency\Adapter\AdapterInterface::getData
	 * 
	 * @throws RuntimeException
	 * @return array
	 */
	public function getData()
	{
		$fullPath = $this->getFullPath();
		if (!is_readable($fullPath)) {
			throw new RuntimeException("Cannot get data, file $fullPath is not readable or doesn't exists");
		}

		$content = file_get_contents($fullPath);
		return Json::decode($content);
	}

	/**
	 * Implementation of CurrencyExchange\Currency\Adapter\AdapterInterface::saveData
	 * 
	 * @throws RuntimeException
	 * @return CurrencyExchange\Currency\Adapter\File
	 */
	public function saveData()
	{
		if ($this->getDataDirectory()) {
            $directory = $this->getDataDirectory();
        } else {
            $directory = $this->_getDefaultDataDirectory();
        }

        if (!is_writable($directory)) {
			throw new RuntimeException('Cannot save data, directory ' . $directory . ' is not writable');
		}

		if ($this->getDownloader() === null) {
			throw new RuntimeException('Cannot save data, downloader not set');
		}

		$data = $this->getDownloader()->makeRequest()->getCurrencyData();

		$bytes = file_put_contents($this->getFullPath(), $data);
		if ($bytes === false) {
			throw new RuntimeException('Cannot save data, 0 bytes written');
		}

		return $this;
	}
}
