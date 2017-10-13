<?php

require_once __DIR__ . "/DOMGenerator.php";
require_once __DIR__ . "/OfferDomGenerator.php";

class CatalogDOMGenerator extends DOMGenerator
{
    protected $name = "Tecniice";
    protected $company = "ООО Компания AЛДИ";
    protected $url = "http://www.techniice-russia.ru/";

    protected $currencies = array(
        array("id" => "RUR", "rate" => "1"),
        array("id" => "EUR", "rate" => "CBRF"),
    );

    protected $categories = array();

    protected $offers = array();

    protected $IBlockId = 7;

    /**
     * @var DOMDocument
     */
    protected $domDocument;

    function __construct()
    {
        parent::__construct();
        CModule::IncludeModule("iblock");
        CModule::IncludeModule("catalog");
    }


    /**
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @return array
     */
    public function getOffers()
    {
        return $this->offers;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Загружает данные о каталоге из БД
     * @return $this
     */
    function loadData()
    {
        $this->loadCategories();
        $this->loadOffers();
        return $this;
    }

    protected function loadCategories()
    {
        $this->categories = array();
        $sort = array_merge(array("DEPTH_LEVEL" => "ASC"), $this->getDefaultSort());
        $filter = $this->getIBlockFilter();
        $rsCategories = CIBlockSection::GetList($sort, $filter);
        while ($category = $rsCategories->GetNext()) {
            $this->categories[$category["ID"]] = $category;
        }
    }

    protected function loadOffers()
    {
        $this->loadOffersIBlockInfo();
        $this->loadOffersFileInfo();
        $this->loadOffersCatalogInfo();
        $this->loadOffersPriceInfo();
        $this->filterOffers();
    }

    protected function loadOffersIBlockInfo()
    {
        $this->offers = array();
        $sort = $this->getDefaultSort();
        $filter = $this->getIBlockFilter();
        $rsProducts = CIBlockElement::GetList($sort, $filter);
        while ($offer = $rsProducts->GetNext()) {
            $this->offers[$offer["ID"]] = $offer;
        }
    }

    protected function loadOffersFileInfo()
    {
        $arProductsByPictures = array();
        foreach ($this->offers as $offer) {
            if ($offer["DETAIL_PICTURE"]) {
                $arProductsByPictures[$offer["DETAIL_PICTURE"]] = $offer["ID"];
            }
        }
        if (empty($arProductsByPictures)) {
            return;
        }
        $filter = array("@ID" => implode(",", array_keys($arProductsByPictures)));
        $rsFiles = CFile::GetList(array(), $filter);
        while ($file = $rsFiles->Fetch()) {
            $offerID = $arProductsByPictures[$file["ID"]];
            $this->offers[$offerID]["PICTURE_INFO"] = $file;
            $this->offers[$offerID]["PICTURE_INFO"]["SRC"] = CFile::GetFileSRC($file);
        }
    }

    protected function loadOffersCatalogInfo()
    {
        if (empty($this->offers)) {
            return;
        }
        $sort = $this->getDefaultSort();
        $filter = $this->getIBlockFilter();
        $filter["ID"] = array_keys($this->offers);
        $rsProducts = CCatalogProduct::GetList($sort, $filter);
        while ($product = $rsProducts->GetNext()) {
            $this->offers[$product["ID"]] = array_merge($this->offers[$product["ID"]], $product);
        }
    }

    protected function loadOffersPriceInfo()
    {
        if (empty($this->offers)) {
            return;
        }
        $filter = array(
            "BASE" => "Y",
            "PRODUCT_ID" => array_keys($this->offers)
        );
        $rsPrices = CPrice::GetList(array(), $filter);
        while ($price = $rsPrices->GetNext()) {
            $this->offers[$price["PRODUCT_ID"]]["PRICE_INFO"] = $price;
        }
    }


    protected function filterOffers()
    {
        $this->offers = array_filter($this->offers, function ($arOffer){
            return is_array($arOffer["PRICE_INFO"]) && floatval($arOffer["PRICE_INFO"]["PRICE"]) > 0;
        });
    }

    /**
     * @return array
     */
    protected function getDefaultSort()
    {
        return array("ID" => "DESC");
    }

    /**
     * @return array
     */
    protected function getIBlockFilter()
    {
        return array("IBLOCK_ID" => $this->IBlockId);
    }

    /**
     * генерирует XML-документ
     * @return DOMDocument
     */
    function generate()
    {
        $ymlCatalog = $this->domDocument->createElement("yml_catalog");
        $ymlCatalog->setAttribute("date", date("Y-m-d H:i"));
        $this->domDocument->appendChild($ymlCatalog);

        $shop = $this->domDocument->createElement("shop");
        $ymlCatalog->appendChild($shop);

        $shop->appendChild($this->generateNameDomElement());
        $shop->appendChild($this->generateCompanyDomElement());
        $shop->appendChild($this->generateCurrenciesDomElement());
        $shop->appendChild($this->generateCategoriesDomElement());
        $shop->appendChild($this->generateOffersDomElement());

        return $this->domDocument;
    }

    /**
     * @return DOMElement
     */
    protected function generateNameDomElement()
    {
        return $this->domDocument->createElement("name", $this->name);
    }

    /**
     * @return DOMElement
     */
    protected function generateCompanyDomElement()
    {
        return $this->domDocument->createElement("company", $this->company);
    }

    /**
     * @return DOMElement
     */
    protected function generateCurrenciesDomElement()
    {
        $dnCurrencies = $this->domDocument->createElement("currencies");
        foreach ($this->currencies as $arCurrency) {
            $dnCurrency = $this->domDocument->createElement("currency");
            $dnCurrency->setAttribute("id", $arCurrency["id"]);
            $dnCurrency->setAttribute("rate", $arCurrency["rate"]);
            $dnCurrencies->appendChild($dnCurrency);
        }
        return $dnCurrencies;
    }

    /**
     * @return DOMElement
     */
    protected function generateCategoriesDomElement()
    {
        $dnCategories = $this->domDocument->createElement("categories");
        foreach ($this->categories as $arCategory) {
            $value = $this->prepareForDomDocument($arCategory["NAME"]);
            $dnCategory = $this->domDocument->createElement("category", $value);
            $dnCategory->setAttribute("id", $arCategory["ID"]);
            if ($arCategory["DEPTH_LEVEL"] > 1) {
                $dnCategory->setAttribute("parentId", $arCategory["IBLOCK_SECTION_ID"]);
            }
            $dnCategories->appendChild($dnCategory);
        }
        return $dnCategories;
    }

    /**
     * @return DOMElement
     */
    protected function generateOffersDomElement()
    {
        $dnOffers = $this->domDocument->createElement("offers");
        foreach ($this->offers as $arOffer) {
            $offerGenerator = new OfferDomGenerator($arOffer, $this);
            $dnOffers->appendChild($offerGenerator->generate());
        }
        return $dnOffers;
    }
}