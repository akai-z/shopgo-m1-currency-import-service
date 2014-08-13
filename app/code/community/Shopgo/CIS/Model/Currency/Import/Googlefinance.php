<?php

class Shopgo_CIS_Model_Currency_Import_Googlefinance extends Mage_Directory_Model_Currency_Import_Abstract
{
    protected $_url = 'http://www.google.com/finance/converter?a=1&from={{CURRENCY_FROM}}&to={{CURRENCY_TO}}';
    protected $_messages = array();

    /**
     * HTTP client
     *
     * @var Varien_Http_Client
     */
    protected $_httpClient;

    public function __construct()
    {
        $this->_httpClient = new Varien_Http_Client();
    }

    protected function _convert($currencyFrom, $currencyTo, $retry = 0)
    {
        $url = str_replace('{{CURRENCY_FROM}}', $currencyFrom, $this->_url);
        $url = str_replace('{{CURRENCY_TO}}', $currencyTo, $url);

        try {
            sleep(Mage::getStoreConfig('currency/google_finance/delay'));

            $response = $this->_httpClient
                ->setUri($url)
                ->setConfig(array('timeout' => Mage::getStoreConfig('currency/google_finance/timeout')))
                ->request('GET')
                ->getBody();

            $data = explode('bld>', $response);

            if (empty($data[1])) {
                $this->_messages[] = Mage::helper('directory')->__('Cannot retrieve rate from %s', $url);
                return null;
            }

            $data = explode($currencyTo, $data[1]);
            $exchangeRate = 1;

            if(empty($data[0])) {
                $this->_messages[] = Mage::helper('directory')->__('Cannot retrieve rate from %s', $url);
                return null;
            } else {
                $exchangeRate = $data[0];
            }

            return (float) $exchangeRate * 1.0;
        } catch (Exception $e) {
            if($retry == 0) {
                $this->_convert($currencyFrom, $currencyTo, 1);
            } else {
                $this->_messages[] = Mage::helper('directory')->__('Cannot retrieve rate from %s', $url);
            }
        }
    }
}
