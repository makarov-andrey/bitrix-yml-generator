<?php

require_once __DIR__ . "/DOMGenerator.php";

class OfferDomGenerator extends DOMGenerator
{
    /**
     * @var array
     */
    protected $offer;

    /**
     * @var CatalogDOMGenerator
     */
    protected $catalogDomGenerator;

    /**
     * @param array $arOffer
     * @param CatalogDOMGenerator $catalogDomGenerator
     */
    function __construct($arOffer, $catalogDomGenerator)
    {
        parent::__construct();
        $this->offer = $arOffer;
        $this->catalogDomGenerator = $catalogDomGenerator;
        $this->domDocument = $catalogDomGenerator->getDomDocument();
    }

    /**
     * генерирует XML оффера
     * @return DOMElement
     */
    function generate()
    {
        $dnOffer = $this->domDocument->createElement("offer");
        $dnOffer->setAttribute("id", $this->offer["ID"]);
        $dnOffer->setAttribute("available", $this->formatBitrixBoolean($this->offer["ACTIVE"]));

        $dnOffer->appendChild($this->generateUrlDomElement());
        $dnOffer->appendChild($this->generatePriceDomElement());
        $dnOffer->appendChild($this->generateCurrencyIdDomElement());
        $dnOffer->appendChild($this->generateCategoryIdDomElement());
        $dnOffer->appendChild($this->generatePictureDomElement());
        $dnOffer->appendChild($this->generateVendorDomElement());
        $dnOffer->appendChild($this->generateNameDomElement());
        $dnOffer->appendChild($this->generateDescriptionDomElement());
        $dnOffer->appendChild($this->generateCountryOfOriginDomElement());

        return $dnOffer;
    }

    protected function generateUrlDomElement()
    {
        $value = $this->formatLocalURL($this->offer["DETAIL_PAGE_URL"]);
        return $this->domDocument->createElement("url", $value);
    }

    protected function generatePriceDomElement()
    {
        $value = $this->formatPrice($this->offer["PRICE_INFO"]["PRICE"]);
        return $this->domDocument->createElement("price", $value);
    }

    protected function generateCurrencyIdDomElement()
    {
        $value = $this->formatCurrency($this->offer["PRICE_INFO"]["CURRENCY"]);
        return $this->domDocument->createElement("currencyId", $value);
    }

    protected function generateCategoryIdDomElement()
    {
        return $this->domDocument->createElement("categoryId", $this->offer["IBLOCK_SECTION_ID"]);
    }

    protected function generatePictureDomElement()
    {
        $value = $this->formatLocalURL($this->offer["PICTURE_INFO"]["SRC"]);
        return $this->domDocument->createElement("picture", $value);
    }

    protected function generateVendorDomElement()
    {
        $value = $this->prepareForDomDocument($this->catalogDomGenerator->getName());
        return $this->domDocument->createElement("vendor", $value);
    }

    protected function generateNameDomElement()
    {
        $value = $this->prepareForDomDocument($this->offer["NAME"]);
        return $this->domDocument->createElement("name", $value);
    }

    protected function generateDescriptionDomElement()
    {
        $value = $this->prepareForDomDocument($this->offer["PREVIEW_TEXT"]);
        return $this->domDocument->createElement("description", $value);
    }

    protected function generateCountryOfOriginDomElement()
    {
        return $this->domDocument->createElement("country_of_origin", "Австралия");
    }
}