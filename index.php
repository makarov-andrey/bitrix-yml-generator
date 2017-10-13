<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";
require_once __DIR__ . "/CatalogDOMGenerator.php";

$generator = new CatalogDOMGenerator();
$generator->loadData();

Header('Content-type: text/xml;charset=cp1251');
echo $generator->generate()->saveXML();


require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php";
