<?php
class MSklad
{
    const username = "admin@magazen";
    const password = "60552fad43";
    const salePriceId = 0;
    const baseUrl = "https://online.moysklad.ru/api/remap/1.1";

    public static function request($url, $isPost = false, $params = [])
    {
        try {
            if(!$isPost && !empty($params)) {
                if(strpos($url, '?') > 0) {
                    $url .= "&" . http_build_query($params);
                } else {
                    $url .= "?" . http_build_query($params);
                }
            }
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ["Authorization: Basic " . base64_encode(self::username . ":" . self::password)]);
            if($isPost) {
                curl_setopt($curl, CURLOPT_POST, $isPost);
                if (!empty($params)) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
                }
            }
            $output = curl_exec($curl);
            curl_close($curl);
            $res = json_decode($output, true);
            return $res;
        } catch (Exception $e) {
            return [];
        }

    }

}
class MSkladComponents extends MSklad {
    const componentsUrl = MSklad::baseUrl . "/entity/bundle";
    public static function getComponents() {
        $products = self::getAllProductsWithComponents();
        $result = [];
        foreach ($products as $product) {
            foreach ($product['components']['rows'] as $row) {
                $q = $row['quantity'];
                $href = $row['assortment']['meta']['href'];
                $productArr = self::request($href);
                $price = $productArr['salePrices'][self::salePriceId]['value'] / 100;
                $externalCode = $productArr['externalCode'];
                if(!isset($result[$product['externalCode']])) {
                    $result[$product['externalCode']] = [];
                }
                $result[$product['externalCode']][] = ['code' => $externalCode, 'quantity' => $q, 'price' => $price];
            }
        }
        return $result;
    }

    public static function getAllProductsWithComponents() {
        $products = [];
        $href = self::componentsUrl;
        $params = ['expand' => 'components', "offset" => 0, "limit"=>100];
        do {
            $res = MSklad::request($href, false, $params);
            foreach ($res['rows'] as $row) {
                if(isset($row['components'])) {
                    $products[] = $row;
                }
            }
            $params = [];
        } while($href = $res['meta']['nextHref']);
        return $products;
    }
}