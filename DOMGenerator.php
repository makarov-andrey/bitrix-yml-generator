<?php

require_once __DIR__ . "/Generable.php";

abstract class DOMGenerator implements Generable
{
    /**
     * @var DOMDocument
     */
    protected $domDocument;

    function __construct()
    {
        $this->domDocument = new DOMDocument('1.0', 'CP1251');
    }

    /**
     * @return DOMDocument
     */
    public function getDomDocument()
    {
        return $this->domDocument;
    }

    /**
     * @param string|bool $value
     * @return string
     */
    protected function formatBitrixBoolean($value)
    {
        return $value == "Y" || $value === true ? "true" : "false";
    }

    /**
     * @param string $url
     * @return string
     */
    protected function formatLocalURL($url)
    {
        if (!$url) {
            return "";
        }
        $url = $this->prepareForDomDocument($url);
        $cheme = $_SERVER["HTTPS"] = "On" ? "https" : "http";
        return $cheme . "://" . $_SERVER["HTTP_HOST"] . $url;
    }

    /**
     * @param string $currency
     * @return string
     */
    protected function formatCurrency($currency)
    {
        $currency = $this->prepareForDomDocument($currency);
        switch ($currency) {
            case "RUB":
                return "RUR";
            default:
                return $currency;
        }
    }

    /**
     * @param string $price
     * @return string
     */
    protected function formatPrice($price)
    {
        return (string)(float)$price;
    }

    /**
     * @param string $price
     * @return string
     */
    protected function prepareForDomDocument($value)
    {
        $value = html_entity_decode($value);
        return iconv("cp1251", "UTF-8", $value);
    }
}