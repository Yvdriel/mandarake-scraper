<?php
require 'simple_html_dom.php';


class Mandarake
{
    protected $cookies;
    public $exchange;
    public $db;

    // Popular terms
    const TERMS = [
        'demon slayer',
        // 'naruto',
        'bleach',
        // 'one piece',
        // 'fairy tail',
        'frieren',
        'スパイファミリー',
        // 'fullmetal',
        'death note',
        'deathnote',
        'a silent voice',
        'koe no katachi',
        'komi can',
        'komisan',
        'chainsaw man',
        'chain saw man',
        'shingeki no kyojin',
        'attack on titan',
        'jujutsu kaisen',
        'berserk',
        // 'vinland',
        // 'vinland saga',
        'boku no hero',
        'my hero Academia',
        'tokyo revengers',
        'revengers',
        'horimiya',
        'blue lock',
        'kaguya sama',
        'kaguya-sama',
        'kaguyasama',
        'dragon ball',
        // 'citrus',
        // 'seven deadly',
        'violet evergarden'
    ];

    // Shipping rates to The Netherlands as of September 27, 2023
    const DHL_SHIPPING = [
        500 => 2100,
        1000 => 2600,
        1500 => 3000,
        2000 => 3400,
        2500 => 3900,
        3000 => 4300,
        3500 => 4700,
        4000 => 5100,
        4500 => 5500,
        5000 => 5900,
        5500 => 6400,
        6000 => 7000,
        6500 => 7500,
        7000 => 8100,
        7500 => 8600,
        8000 => 9200,
        8500 => 9700,
        9000 => 10300,
        9500 => 10800,
        10000 => 11400,
        10500 => 11900,
        11000 => 12500,
        11500 => 13000,
        12000 => 13600,
        12500 => 14100,
        13000 => 14700,
        13500 => 15200,
        14000 => 15800,
        14500 => 16300,
        15000 => 16800,
        15500 => 17400,
        16000 => 17900,
        16500 => 18500,
        17000 => 19000,
        17500 => 19600,
        18000 => 20100,
        18500 => 20700,
        19000 => 21200,
        19500 => 21800,
        20000 => 22300,
        20500 => 23500,
        21000 => 24700,
        21500 => 25900,
        22000 => 27100,
        22500 => 28300,
        23000 => 29500,
        23500 => 30600,
        24000 => 31800,
        24500 => 33000,
        25000 => 34200,
        25500 => 35400,
        26000 => 36600,
        26500 => 37800,
        27000 => 38900,
        27500 => 40100,
        28000 => 41300,
        28500 => 42500,
        29000 => 43700,
        29500 => 44900,
        30000 => 46100,
        31000 => 48600,
        32000 => 51200,
        33000 => 53800,
        34000 => 56400,
        35000 => 59000,
        36000 => 61600,
        37000 => 64100,
        38000 => 66700,
        39000 => 69300,
        40000 => 71900,
        41000 => 74500,
        42000 => 77100,
        43000 => 79600,
        44000 => 82200,
        45000 => 84800,
        46000 => 87400,
        47000 => 90000,
        48000 => 92600,
        49000 => 95100,
        50000 => 97700,
        51000 => 100300,
        52000 => 102900,
        53000 => 105500,
        54000 => 108100,
        55000 => 110600,
        56000 => 113200,
        57000 => 115800,
        58000 => 118400,
        59000 => 121000,
        60000 => 123500,
        61000 => 126100,
        62000 => 128700,
        63000 => 131300,
        64000 => 133900,
        65000 => 136500,
        66000 => 139000,
        67000 => 141600,
        68000 => 144200,
        69000 => 146800,
        70000 => 149400,
        71000 => 152000,
        72000 => 154500,
        73000 => 157100,
        74000 => 159700,
        75000 => 162300,
        76000 => 164900,
        77000 => 167500,
        78000 => 170000,
        79000 => 172600,
        80000 => 175200,
        81000 => 177800,
        82000 => 180400,
        83000 => 183000,
        84000 => 185500,
        85000 => 188100,
        86000 => 190700,
        87000 => 193300,
        88000 => 195900,
        89000 => 198400,
        90000 => 201000,
        91000 => 203600,
        92000 => 206200,
        93000 => 208800,
        94000 => 211400,
        95000 => 213900,
        96000 => 216500,
        97000 => 219100,
        98000 => 221700,
        99000 => 224300,
        100000 => 226900
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
            if ($this->get($value->{'data-itemidx'})) continue;

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
            title, key, price, shipping, total, currency, price_eu, shipping_eu, total_eu, size, weight, full_url, images, item_code, store, volume_count
        ) VALUES (
            :title, :key, :price, :shipping, :total, :currency, :price_eu, :shipping_eu, :total_eu, :size, :weight, :full_url, :images, :item_code, :store, :volume_count
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
        $stmt->bindValue(':volume_count', $data['volume_count'], SQLITE3_FLOAT);

        $result = $stmt->execute();

        if (!$result) {
            echo "Failed to insert record <br />";
        }
    }

    public function get($key)
    {
        $sql = "SELECT key FROM items WHERE key = :key";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':key', $key, SQLITE3_TEXT);

        return $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    }

    public function getVolumeCount($title)
    {
        preg_match_all('/\d+/', $title, $matches);
        $numbers = array_map('intval', $matches[0]);
        $maxNumber = max($numbers);

        return $maxNumber;
    }

    public function formatAndCreate($data_indexes)
    {
        foreach($data_indexes as $key => $index) {
            $current = $this->getContentWithCookie($index);

            // Skip Soldout inserts
            $soldout = $current->find(".soldout", 0)->plaintext ?? false;
            if ($soldout) return;
        
            // Format Input
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
        
            $data = [
                'key' => $key,
                'title' => $title,
                'price' => $price,
                'shipping' => $shipping,
                'total' => $price + $shipping,
                'currency' => $currency,
                'price_eu' => round(($this->exchange * $price), 2),
                'shipping_eu' => round(($this->exchange * $shipping), 2),
                'total_eu' => round(($this->exchange * ($price + $shipping)), 2),
                'size' => $size,
                'weight' => $weight,
                'full_url' => $index,
                'images' => $images,
                'item_code' => $item_code,
                'store' => trim($store),
                'volume_count' => $this->getVolumeCount($title),
            ];
        
            $this->create($data);
        }
    }
}
?>