<!doctype html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script   src="https://code.jquery.com/jquery-3.7.1.min.js"   integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="   crossorigin="anonymous"></script>
    <script>
        var toggled = false;
        var priceArray = {};
        function sumValues(obj) {
            let sum = 0;
            for (let key in obj) {
                if (obj.hasOwnProperty(key) && typeof obj[key] === 'number') {
                    sum += parseFloat(obj[key]);
                }
            }
            return Math.round(sum * 100) / 100;
        }
        function isEmpty(obj) {
            for (const prop in obj) {
                if (Object.hasOwn(obj, prop)) {
                    return false;
                }
            }

            return true;
        }
        function toggleImages(key) {
            $(`#${key}`).toggle();
        }
        function toggleAll(entries) {
            const msg = `You are about to toggle the images for ${entries} images, are you sure?`;
            if (toggled || confirm((msg))) {
                toggled = true;
                $('.card-content').toggle();
            }
        }
        function toggleTotalPrice(key, total) {
            $(`#${key}`).prop('checked', !$(`#${key}`).is(':checked'))

            if (!$(`#${key}`).is(':checked')) {
                delete priceArray[key];
                if (isEmpty(priceArray)) {
                    $('.total-selected').hide();
                }

                $('.total-selected').html('€' + sumValues(priceArray));
                return;
            };


            $('.total-selected').show();
            priceArray[key] = (parseFloat($(`#total_eu_${key}`).text()));
            $('.total-selected').html('€' + sumValues(priceArray));
        }
        function hideCards() {
            $('.card-parent').each(function() {
                // Check if the child checkbox is not checked
                if (!$(this).find('input[type="checkbox"]').is(':checked')) {
                    // Hide the div if the checkbox is not checked
                    $(this).toggle();
                }
            });
        }
    </script>
</head>
<?php
require 'mandarake.php';

$db = new SQLite3('mandarake.db');
$sql = "SELECT * FROM items ORDER BY id DESC;";

if (isset($_GET['type']) && $_GET['type'] == 'popular') {
    $sql = "SELECT * FROM items WHERE ";
    $conditions = [];
    foreach (Mandarake::TERMS as $index => $term) {
        $placeholder = ":term_$index";
        $conditions[] = "LOWER(title) LIKE $placeholder";
    }
    $sql .= implode(' OR ', $conditions);
    $sql .= ' ORDER BY total DESC';
    // Prepare the statement
    $stmt = $db->prepare($sql);
    
    // Bind the values to the placeholders
    foreach (Mandarake::TERMS as $index => $term) {
        $placeholder = ":term_$index";
        $stmt->bindValue($placeholder, '%' . $term . '%', SQLITE3_TEXT);
    }
} else {
    $stmt = $db->prepare($sql);
}

$result = $stmt->execute();

$newArray = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $newArray[] = $row;
}
?>

<body class="p-12 lg:w-4/5 m-auto">
    <div class="text-center text-stone-800 pt-10 pb-4 text-6xl font-black">
        <?php echo count($newArray) ?> Entries
    </div>
    <div 
        class="hidden cursor-pointer total-selected fixed bottom-10 right-10 px-6 py-3 rounded bg-green-200 z-40 font-bold border border-solid border-1 border-green-600 shadow-md"
        onclick="hideCards()"
    >
        0
    </div>
    <div class="grid md:grid-cols-2 gap-4">
        <?php
        if (isset($_GET['type']) && $_GET['type'] == 'popular') {
            ?>
            <a href="/">
                <div class="text-center bg-red-300 rounded-xl w-full py-4">
                    Browse all entries
                </div>
            </a>
            <?php
        } else {
        ?>
            <a href="?type=popular">
                <div class="text-center bg-blue-300 rounded-xl w-full py-4">
                    Browse popular terms
                </div>
            </a>
        <?php
        }
        ?>
        <div 
            class="p-4 bg-green-300 rounded-xl cursor-pointer select-none text-center" 
            onclick="toggleAll(<?php echo count($newArray) ?>)"
        >
            Toggle All Images <small class="italic">(at own risk lol)</small>
        </div>
    </div>
    

    <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-4 pt-4 text-white">
        <?php
        foreach ($newArray as $value) {
        ?>
            <div 
                class="card-parent bg-fuchsia-950 shadow-md rounded-xl text-center border border-solid border-black" 
                onclick="toggleTotalPrice(<?php echo $value['key'] ?>, <?php echo $value['total_eu'] ?>)"
            >
                <div class="flex items-top px-4 pt-4 gap-4">
                    <div class="font-bold"><?php echo $value['title'] ?></div>
                    <input
                        id="<?php echo $value['key'] ?>" 
                        class="cursor-pointer card-checkbox w-5 h-5" 
                        type="checkbox"
                        onclick="this.checked=!this.checked;"
                    />
                </div>
                <div class="p-4">                
                    <div class="my-6 h-0 border border-dashed border-white w-full">
                    </div>
                    <div class="grid grid-cols-3">
                        <div class="font-semibold">
                            Price
                            <div class="h-0 opacity-40 border border-[0.5px] border-solid border-white w-full">
                            </div>
                        </div>
                        <div class="font-semibold">
                            Shipping
                            <div class="h-0 opacity-40 border border-[0.5px] border-solid border-white w-full">
                            </div>
                        </div>
                        <div class="font-semibold bg-fuchsia-700 rounded-t">
                            Total
                            <div class="h-0 opacity-40 border border-[0.5px] border-solid border-white w-full">
                            </div>
                        </div>
                        <div>
                            ¥<?php echo $value['price'] ?>
                        </div>
                        <div>
                            ¥<?php echo $value['shipping'] ?>
                        </div>
                        <div class="bg-fuchsia-700">
                            ¥<?php echo $value['total'] ?>
                        </div>

                        <div>
                            €<?php echo $value['price_eu'] ?>
                        </div>
                        <div>
                            €<?php echo $value['shipping_eu'] ?>
                        </div>
                        <div class="bg-fuchsia-700 rounded-b font-black pb-1">
                            €<?php echo $value['total_eu'] ?>
                        </div>
                        <div id="total_eu_<?php echo $value['key'] ?>" class="hidden"><?php echo $value['total_eu'] ?></div>
                    </div>

                    <div class="my-6 h-0 border border-dashed border-white w-full">
                    </div>

                    <div class="grid grid-cols-3">
                        <div class="font-semibold">
                            Size
                            <div class="h-0 opacity-40 border border-[0.5px] border-solid border-white w-full">
                            </div>
                        </div>
                        <div class="font-semibold">
                            Weight
                            <div class="h-0 opacity-40 border border-[0.5px] border-solid border-white w-full">
                            </div>
                        </div>
                        <div class="font-semibold">
                            Store
                            <div class="h-0 opacity-40 border border-[0.5px] border-solid border-white w-full">
                            </div>
                        </div>
                        <div>
                            <?php echo $value['size'] ?>
                        </div>
                        
                        <div>
                            <?php echo $value['weight'] ?>g
                        </div>
                        <div>
                            <?php echo $value['store'] ?>
                        </div>
                    </div>

                    <div class="text-blue-600 font-semibold text-center cursor-pointer select-none pt-2" onclick="toggleImages(<?php echo $value['key'] ?>)">
                        Show images
                    </div>
                    <div class="grid grid-cols-2 gap-2 pt-4 card-content hidden" id="<?php echo $value['key'] ?>">
                        <?php
                        foreach (json_decode($value['images'], true) as $image) {
                            if (empty(json_decode($value['images'], true))) echo 'No Images';
                        ?>
                            <img class="m-auto max-h-[250px]" src="<?php echo $image ?>" />
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        <?php
        }

        ?>
    </div>
</body>

</html>