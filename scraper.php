<?php
require 'simple_html_dom.php';

class Mandarake
{
    protected $cookies;
    public $exchange;
    public $db;

    // Shipping rates to The Netherlands as of September 27, 2023
    const DHL_SHIPPING = [
        500 => 2100,
        1000 => 2500,
        1500 => 2900,
        2000 => 3300,
        2500 => 3700,
        3000 => 4100,
        3500 => 4400,
        4000 => 4800,
        4500 => 5200,
        5000 => 5600,
        5500 => 6100,
        6000 => 6600,
        6500 => 7100,
        7000 => 7600,
        7500 => 8100,
        8000 => 8700,
        8500 => 9200,
        9000 => 9700,
        9500 => 10200,
        10000 => 10700,
        10500 => 11200,
        11000 => 11700,
        11500 => 12300,
        12000 => 12800,
        12500 => 13300,
        13000 => 13800,
        13500 => 14300,
        14000 => 14800,
        14500 => 15400,
        15000 => 15900,
        15500 => 16400,
        16000 => 16900,
        16500 => 17400,
        17000 => 17900,
        17500 => 18500,
        18000 => 19000,
        18500 => 19500,
        19000 => 20000,
        19500 => 20500,
        20000 => 21000,
        20500 => 22200,
        21000 => 23300,
        21500 => 24500,
        22000 => 25600,
        22500 => 26800,
        23000 => 28000,
        23500 => 29100,
        24000 => 30300,
        24500 => 31400,
        25000 => 32600,
        25500 => 33700,
        26000 => 34900,
        26500 => 36000,
        27000 => 37200,
        27500 => 38300,
        28000 => 39500,
        28500 => 40700,
        29000 => 41800,
        29500 => 43000,
        30000 => 44100,
        31000 => 46600,
        32000 => 49200,
        33000 => 51700,
        34000 => 54200,
        35000 => 56700,
        36000 => 59200,
        37000 => 61700,
        38000 => 64300,
        39000 => 66800,
        40000 => 69300,
        41000 => 71800,
        42000 => 74300,
        43000 => 76800,
        44000 => 79400,
        45000 => 81900,
        46000 => 84400,
        47000 => 86900,
        48000 => 89400,
        49000 => 92000,
        50000 => 94500,
        51000 => 97000,
        52000 => 99500,
        53000 => 102000,
        54000 => 104500,
        55000 => 107100,
        56000 => 109600,
        57000 => 112100,
        58000 => 114600,
        59000 => 117100,
        60000 => 119600,
        61000 => 122200,
        62000 => 124700,
        63000 => 127200,
        64000 => 129700,
        65000 => 132200,
        66000 => 134800,
        67000 => 137300,
        68000 => 139800,
        69000 => 142300,
        70000 => 144800,
        71000 => 147300,
        72000 => 149900,
        73000 => 152400,
        74000 => 154900,
        75000 => 157400,
        76000 => 159900,
        77000 => 162500,
        78000 => 165000,
        79000 => 167500,
        80000 => 170000,
        81000 => 172500,
        82000 => 175000,
        83000 => 177600,
        84000 => 180100,
        85000 => 182600,
        86000 => 185100,
        87000 => 187600,
        88000 => 190100,
        89000 => 192700,
        90000 => 195200,
        91000 => 197700,
        92000 => 200200,
        93000 => 202700,
        94000 => 205300,
        95000 => 207800,
        96000 => 210300,
        97000 => 212800,
        98000 => 215300,
        99000 => 217800,
        100000 => 220400,
    ];


    public function __construct()
    {
        $this->cookies = $this->getCookies();
        $this->exchange = $this->getExchangeRate();
        $this->db = new SQLite3('mandarake.db');
    }

    private function getCookies()
    {
        $ch = curl_init('https://mandarake.co.jp/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_HEADER, 1);
    
        $result = curl_exec($ch);
    
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result, $matches);
        $cookies = [];
        foreach($matches[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }
        
        return $cookies;
    }

    public function getContentWithCookie(string $url) 
    {
        $context = stream_context_create(
            [
                'http' => [
                    'follow_location' => true,
                    'max_redirects' => 20,
                    'header' => "Cookie: initialized_cart=1; tr_mndrk_user=" . $this->cookies['tr_mndrk_user'] . ';'
                ]
            ]
        );
    
        return file_get_html($url, false, $context);
    }

    public function getDataIndexes($html)
    {
        $data_indexes = [];
        foreach($html->find('.thumlarge', 0)->children() as $key => $value) {
            if (!$value->{'data-itemidx'}) continue;
            $data_indexes[$value->{'data-itemidx'}] = 'https://order.mandarake.co.jp/order/detailPage/item?itemCode=' . $value->{'data-itemidx'} . '&ref=list&categoryCode=110107&lang=en';
        }
    
        return $data_indexes;
    }

    public function getExchangeRate()
    {
        $html = file_get_html('https://wise.com/gb/currency-converter/jpy-to-eur-rate');

        return (float) $html->find('.text-success', 0)->plaintext;
    }

    public function create($data)
    {
        // Insert query
        $sql = "INSERT INTO items (
            title, key, price, shipping, total, currency, price_eu, shipping_eu, total_eu, size, weight, full_url, images, item_code, store
        ) VALUES (
            :title, :key, :price, :shipping, :total, :currency, :price_eu, :shipping_eu, :total_eu, :size, :weight, :full_url, :images, :item_code, :store
        )";

        // Prepare and execute statement
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':title', $data['title'], SQLITE3_TEXT);
        $stmt->bindValue(':key', $data['key'], SQLITE3_TEXT);
        $stmt->bindValue(':price', $data['price'], SQLITE3_FLOAT);
        $stmt->bindValue(':shipping', $data['shipping'], SQLITE3_FLOAT);
        $stmt->bindValue(':total', $data['total'], SQLITE3_FLOAT);
        $stmt->bindValue(':currency', $data['currency'], SQLITE3_TEXT);
        $stmt->bindValue(':price_eu', $data['price_eu'], SQLITE3_FLOAT);
        $stmt->bindValue(':shipping_eu', $data['shipping_eu'], SQLITE3_FLOAT);
        $stmt->bindValue(':total_eu', $data['total_eu'], SQLITE3_FLOAT);
        $stmt->bindValue(':size', $data['size'], SQLITE3_TEXT);
        $stmt->bindValue(':weight', $data['weight'], SQLITE3_FLOAT);
        $stmt->bindValue(':full_url', $data['full_url'], SQLITE3_TEXT);
        $stmt->bindValue(':images', json_encode($data['images']), SQLITE3_TEXT);
        $stmt->bindValue(':item_code', $data['item_code'], SQLITE3_TEXT);
        $stmt->bindValue(':store', $data['store'], SQLITE3_TEXT);

        $result = $stmt->execute();

        if ($result) {
            echo "Record inserted successfully\n";
        } else {
            echo "Failed to insert record\n";
        }
    }
}

$url = "https://order.mandarake.co.jp/order/listPage/list?categoryCode=110107&lang=en";
$mandarake = new Mandarake();
$html = $mandarake->getContentWithCookie($url);
$data_indexes = $mandarake->getDataIndexes($html);

$gathered = [];
foreach($data_indexes as $key => $index) {
    $current = $mandarake->getContentWithCookie($index);
    $soldout = $current->find(".soldout", 0)->plaintext ?? false;

    if ($soldout) return;

    $price = str_replace(array('.', ','), '' , $current->find( "meta[itemprop=price]", 0)->content);
    $currency = $current->find( "meta[itemprop=priceCurrency]", 0)->content;
    $title = str_replace('New Arrivals', '', $current->find("h1", 0)->plaintext);
    $item_code = $current->find(".__itemno", 0)->plaintext;
    $store = $current->find(".__shop", 0)->plaintext;
    $size = $current->find(".size", 0)->find("td", 0)->find("p", 0)->plaintext;

    $parts = explode('/', $size);
    $size = trim($parts[0]);
    $weight = trim($parts[count($parts) - 1]);

    $shipping = 0;
    $found = false;
    foreach (Mandarake::DHL_SHIPPING as $k => $value) {
        if ($k >= str_replace('g', '', $weight)) {
            if ($found) {
                $shipping = $value;
                break;
            }
            $found = true;
        }
    }

    $images = [];
    foreach($current->find('.elevatezoom-gallery') as $value) {
        $images[] = $value->{'data-image'};
    }

    $gathered[$key] = [
        'key' => $key,
        'title' => $title,
        'price' => $price,
        'shipping' => $shipping,
        'total' => $price + $shipping,
        'currency' => $currency,
        'price_eu' => round(($mandarake->exchange * $price), 2),
        'shipping_eu' => round(($mandarake->exchange * $shipping), 2),
        'total_eu' => round(($mandarake->exchange * ($price + $shipping)), 2),
        'size' => $size,
        'weight' => $weight,
        'full_url' => $index,
        'images' => $images,
        'item_code' => $item_code,
        'store' => trim($store),
    ];

    $mandarake->create($gathered[$key]);
}
?>
