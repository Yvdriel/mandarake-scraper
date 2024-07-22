<!doctype html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script   src="https://code.jquery.com/jquery-3.7.1.min.js"   integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="   crossorigin="anonymous"></script>
    <script>
        var toggled = false;
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
    </script>
</head>
<?php
$db = new SQLite3('mandarake.db');

$sql = "SELECT * FROM items;";

$result = $db->query($sql);

$newArray = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $newArray[] = $row;
}
?>

<body class="p-12">
    <div class="text-center text-stone-800 pt-10 pb-4 text-6xl font-black">
        <?php echo count($newArray) ?> Entries
    </div>
    <div 
        class="p-4 bg-green-300 rounded-xl cursor-pointer select-none text-center" 
        onclick="toggleAll(<?php echo count($newArray) ?>)"
    >
        Toggle All Images <small class="italic">(at own risk lol)</small>
    </div>

    <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-4 pt-4 text-white">
        <?php
        foreach ($newArray as $value) {
        ?>
            <div class="bg-fuchsia-950 rounded-xl text-center border border-solid border-black">
                <div class="font-bold px-4 pt-4"><?php echo $value['title'] ?></div>
                <div class="p-4">
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