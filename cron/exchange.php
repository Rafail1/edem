<?php
//require_once("../classes/MSklad.php");
$usedStores = ['559a9eee-a865-11e7-7a69-8f550015ec59'];
$stores = json_decode(file_get_contents("../classes/stores.txt"), true);
$assortment = json_decode(file_get_contents("../classes/assortment.txt"), true);
$offers = json_decode(file_get_contents("../classes/offers.txt"), true);
$prods = json_decode(file_get_contents("../classes/prods.txt"), true);

$arResult = [];
foreach ($prods as $prod) {
    if (isset($prod['salePrices']) && is_array($prod['salePrices'])) {
        $price = $prod['salePrices'][0]['value'];
    } else {
        $price = false;
    }
    if (isset($prod['components'])) {
        $components = $prod['components'];
    } else {
        $components = false;
    }
    if (isset($prod['code'])) {
        $code = $prod['code'];
    } else {
        $code = false;
    }
    $arResult[$prod['id']] = [
        'id' => $prod['id'],
        'name' => $prod['name'],
        'price' => $price,
        'barcodes' => $prod['barcodes'],
        'code' => $code,
        'externalCode' => $prod['externalCode'],
        'pathName' => $prod['pathName'],
        'components' => $components,
        'modificationsCount' => $prod['modificationsCount']
    ];
}
$arOffers = [];
$offersCharacteristics = [];
foreach ($offers as $offer) {
    if (isset($offer['salePrices']) && is_array($offer['salePrices'])) {
        $price = $offer['salePrices'][0]['value'];
    } else {
        $price = false;
    }

    $characteristics = [];
    foreach ($offer['characteristics'] as $characteristic) {
        if (!isset($offersCharacteristics[$characteristic['id']])) {
            $offersCharacteristics[$characteristic['id']] = [
                'id' => $characteristic['id'],
                'name' => $characteristic['name']
            ];
        }
        $characteristics[$characteristic['id']] = $characteristic['value'];
    }

    if (isset($offer['product']) && isset($offer['product']['meta'])) {
        $tmp = explode('/', $offer['product']['meta']['href']);
        $prodId = array_pop($tmp);
        $limit = strpos($prodId, '?');
        if ($limit) {
            $prodId = substr($prodId, 0, $prodId);
        }
    } else {
        $prodId = null;
    }

    $arOffers[$offer['id']] = [
        'id' => $offer['id'],
        'name' => $offer['name'],
        'price' => $price,
        'barcodes' => $offer['barcodes'],
        'code' => $offer['code'],
        'externalCode' => $offer['externalCode'],
        'prodId' => $prodId
    ];
};
foreach ($stores as $store) {
    $tmp = explode('/', $store['meta']['href']);
    $chunk = array_pop($tmp);
    $itemId = substr($chunk, 0, strpos($chunk, '?'));
    $quantity = 0;
    foreach ($store['stockByStore'] as $stock) {
        $tmp = explode('/', $stock['meta']['href']);
        $stockId = array_pop($tmp);
        $limit =  strpos($stockId, '?');
        if($limit) {
            $stockId = substr($chunk, 0, $limit);
        }
        if (in_array($stockId, $usedStores)) {
            $quantity += $stock['stock'];
        }
    }
    switch ($store['meta']['type']) {
        case 'product': {
            if (isset($arResult[$itemId])) {
                $arResult[$itemId]['quantity'] = $quantity;
            } else {
                echo $store['meta']['href'] . "\n";
            }
            break;
        }
        case 'variant' : {
            if (isset($arOffers[$itemId])) {
                $arOffers[$itemId]['quantity'] = $quantity;
            } else {
                echo $store['meta']['href'] . "\n";
            }
            break;
        }
    };
}


foreach ($arOffers as $offer) {
    if ($offer['prodId'] && isset($arResult[$offer['prodId']])) {
        $arOffers[$offer['id']]['product'] = $arResult[$offer['prodId']];
    }
}
print_r($arOffers);