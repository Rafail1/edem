<?
AddEventHandler("catalog", "OnSuccessCatalogImport1C", "OnSuccessCatalogImport1CHandler");
require_once($_SERVER['DOCUMENT_ROOT'] . "/local/classes/CCustomTypes.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/local/classes/MSklad.php");
define('PROPERTY_COMPONENTS', 251);
function OnSuccessCatalogImport1CHandler()
{
    if ($_REQUEST['filename'] !== 'offers.xml') {
        return;
    }
    $lastUpdFile = $_SERVER["DOCUMENT_ROOT"] . "/last_update";
    if (!file_exists($lastUpdFile)) {
        $lastUpd = 0;
    } else {
        $lastUpd = file_get_contents($lastUpdFile);
    }
    if (time() - $lastUpd < 60 * 30) {
        file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/last_update2", json_encode($_REQUEST));
        return;
    } else {
        file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/last_update1", json_encode($_REQUEST));
    }
    file_put_contents($lastUpdFile, time());
    $collections = MSkladComponents::getComponents();
    if (CModule::IncludeModule("iblock")) {
        $res = CIBlockElement::GetList(
            ["SORT" => "ASC"],
            ["EXTERNAL_ID" => array_keys($collections)],
            false,
            false,
            ["IBLOCK_ID", "ID", "EXTERNAL_ID"]
        );
        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $collection = $collections[$arFields["EXTERNAL_ID"]];
            $totalPrice = 0;
            $prop = [];
            $prop[PROPERTY_COMPONENTS] = [];
            foreach ($collection as $k => $item) {
                $prop[PROPERTY_COMPONENTS]["n$k"] = ["VALUE" => $item["code"], "DESCRIPTION" => $item["quantity"]];
                $totalPrice += $item["quantity"] * $item["price"];
            }

            $el = new CIBlockElement;
            $arLoadProductArray = ["PROPERTY_VALUES" => $prop];
            $el->Update($arFields["ID"], $arLoadProductArray);
            setPrice($arFields["ID"], $totalPrice);
        }
    }
}