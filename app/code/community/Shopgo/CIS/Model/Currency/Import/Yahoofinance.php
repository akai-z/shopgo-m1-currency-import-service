<?php

class Shopgo_CIS_Model_Currency_Import_Yahoofinance extends Mage_Directory_Model_Currency_Import_Abstract
{
    protected $_url = 'http://finance.yahoo.com/d/quotes.csv?s={{CURRENCY_FROM}}{{CURRENCY_TO}}=X&f=l1&e=.csv';
    protected $_messages = array();

    protected function _convert($currencyFrom, $currencyTo, $retry = 0)
    {
        $url = str_replace('{{CURRENCY_FROM}}', $currencyFrom, $this->_url);
        $url = str_replace('{{CURRENCY_TO}}', $currencyTo, $url);

        try {
            sleep(Mage::getStoreConfig('currency/yahoo_finance/delay'));

            $handle = fopen($url, "r");

            $exchangeRate = fread($handle, 2000);

            fclose($handle);

            if(!$exchangeRate) {
                $this->_messages[] = Mage::helper('directory')->__('Cannot retrieve rate from %s', $url);
                return null;
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
