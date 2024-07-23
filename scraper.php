<?php
require 'mandarake.php';

// $url = "https://order.mandarake.co.jp/order/listPage/list?soldOut=1&categoryCode=110107&lang=en&keyword=chain%20saw%20man";
$url = "https://order.mandarake.co.jp/order/listPage/list?soldOut=1&categoryCode=110107&lang=en";
$mandarake = new Mandarake();
if (isset($_GET['type']) && $_GET['type'] == 'popular') {
    foreach (Mandarake::TERMS as $term) {
        $u = $url;
        $u .= '&keyword=' . urlencode($term);

        $html = $mandarake->getContentWithCookie($u);
        $data_indexes = $mandarake->getDataIndexes($html);
        $mandarake->formatAndCreate($data_indexes);
    }
}
if (
    (isset($_GET['type']) && $_GET['type'] == 'all') ||
    (!isset($_GET['type']) || $_GET['type'] != 'popular')
) {
    // Scrape first 20 pages
    for ($i = 1 ; $i < 20; $i++) {
        $u = $url;
        if ($i > 1) {
            $u = $url . '&page=' . $i;
        }

        $html = $mandarake->getContentWithCookie($u);
        $data_indexes = $mandarake->getDataIndexes($html);
        $mandarake->formatAndCreate($data_indexes);
    }
}



?>
