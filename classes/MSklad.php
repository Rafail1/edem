<?php
class MSklad
{
    const username = 'admin@magazen';
    const password = '60552fad43';
    const salePriceId = 0;
    const baseUrl = 'https://online.moysklad.ru/api/remap/1.1';
    private static $cached = [];
    public static function request($url, $isPost = false, $params = [])
    {
        try {
            if(!$isPost && !empty($params)) {
                if(strpos($url, '?') > 0) {
                    $url .= '&' . http_build_query($params);
                } else {
                    $url .= '?' . http_build_query($params);
                }
            }
            if(isset(self::$cached[$url])) {
                return self::$cached[$url];
            }
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . base64_encode(self::username . ':' . self::password)]);
            if($isPost) {
                curl_setopt($curl, CURLOPT_POST, $isPost);
                if (!empty($params)) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
                }
            }
            $output = curl_exec($curl);
            curl_close($curl);
            $res = json_decode($output, true);
            self::$cached[$url] = $res;
            return self::$cached[$url];
        } catch (Exception $e) {
            return [];
        }

    }
    public static function getAllPages($href, $params = ['offset' => 0, 'limit'=>100]) {
        $result = [];
        do {
            $res = MSklad::request($href, false, $params);
            $result = array_merge($result, $res['rows']);
            $params = [];
            $href = isset($res['meta']['nextHref']) ? $res['meta']['nextHref'] : false;
        } while($href);
        return $result;
    }
}

class MSkladStock extends MSklad {
    const url = MSklad::baseUrl . '/entity/store/';
    public static function getStocks() {
        return self::getAllPages(self::url, ['offset' => 0, 'limit'=>100]);
    }
    public static function getAll($id) {
        return self::getAllPages(self::url.$id, ['offset' => 0, 'limit'=>100]);
    }

}
class MSkladByStore extends MSklad {
    const url = MSklad::baseUrl . '/report/stock/bystore';
    public static function getAll() {
        return self::getAllPages(self::url, ['offset' => 0, 'limit'=>100]);
    }
}
class MSkladAssortment extends MSklad {
    const url = MSklad::baseUrl . '/entity/assortment';
    public static function getAll() {
        return self::getAllPages(self::url, ['offset' => 0, 'limit'=>100]);
    }
}

class MSkladComponents extends MSklad {
    const url = MSklad::baseUrl . '/entity/bundle';
    public static function getAll() {
        return self::getAllPages(self::url, ['expand' => 'components', 'offset' => 0, 'limit'=>100]);
    }
}
class MSkladProducts extends MSklad {
    const url = MSklad::baseUrl . '/entity/product';
    public static function getAll() {
        return self::getAllPages(self::url, [ 'offset' => 0, 'limit'=>100]);
    }
}
class MSkladOffers extends MSklad {
    const url = MSklad::baseUrl . '/entity/variant';
    public static function getAll() {
        return self::getAllPages(self::url, ['offset' => 0, 'limit'=>100]);
    }
}
$root = __DIR__;
$pid = pcntl_fork();

if(!$pid) {
    $stores = MSkladByStore::getAll();
    file_put_contents("{$root}/stores.txt", json_encode($stores));

    $offers = MSkladOffers::getAll();
    file_put_contents("{$root}/offers.txt", json_encode($offers));

    $prods = MSkladProducts::getAll();
    file_put_contents("{$root}/prods.txt", json_encode($prods));

    $components = MSkladComponents::getAll();
    file_put_contents("{$root}/components.txt", json_encode($prods));
}


