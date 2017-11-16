<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/local/classes/CCustomTypes.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/local/classes/MSklad.php");
define('PROPERTY_COMPONENTS', 251);

function setPrice($PRODUCT_ID, $PRICE, $CURRENCY = "BYN", $PRICE_TYPE_ID = 1) {
    $arFields = Array(
        "PRODUCT_ID" => $PRODUCT_ID,
        "CATALOG_GROUP_ID" => $PRICE_TYPE_ID,
        "PRICE" => $PRICE,
        "CURRENCY" => $CURRENCY
    );

    $res = CPrice::GetList(
        array(),
        array(
            "PRODUCT_ID" => $PRODUCT_ID,
            "CATALOG_GROUP_ID" => $PRICE_TYPE_ID
        )
    );

    if ($arr = $res->Fetch())
    {
        CPrice::Update($arr["ID"], $arFields);
    }
    else
    {
        CPrice::Add($arFields);
    }
}