
<?php
include __DIR__ . "/header.php";

$StockItem = getStockItem($_GET['id'], $databaseConnection);
$StockItemImagePaths = getStockItemImage($_GET['id'], $databaseConnection);
$ImagesPathVals = $StockItemImagePaths[0]["Item_Image"]?["Item_Image","StockItemIMG/"]:["Category_Image","StockGroupIMG/"];
$_SESSION['CurrentItem'] = [$StockItem, $ImagesPathVals[1] . $StockItemImagePaths[0][$ImagesPathVals[0]]];

?>

<?php
// als stockitem gekoeld is update de koelcel temperatuur live elke 3 seconden
if($StockItem['IsChillerStock'] == true) { ?> 
    <script>
    setInterval(async () => {
        const response = await fetch("http://localhost/KBS/NerdyGadgets/API/getTemp.php", {
            method: "POST",
            mode: "cors", 
            cache: "no-cache",
            credentials: "same-origin", 
            headers: { "Content-Type": "application/json" },
            redirect: "follow",
            referrerPolicy: "no-referrer",
            body: JSON.stringify(0)
        })
        const resJson = (await response.json()).Temperature
        console.log(resJson)
        document.getElementById("TemperatureText").innerText = `Temperatuur vriesruimte: ${resJson}`;
    }, 3000)
    </script> 
    <?php
}

?>
<div id="CenteredContent">
    <?php
    if ($StockItem != null) {
        ?>
        <?php
        if (isset($StockItem['Video'])) {
            ?>
            <div id="VideoFrame">
            <iframe width="100%" height="100%" src=<?php print $StockItem['Video']; ?> frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen>
            </iframe>
            </div>
        <?php }
        ?>

        <div id="ArticleHeader">
            <?php
            if (isset($StockItemImagePaths)) {
                // één plaatje laten zien
                if (count($StockItemImagePaths) == 1) {
                    ?>
                    <div id="ImageFrame"
                         style="background-image: url('Public/<?php 
                         print $ImagesPathVals[1].$StockItemImagePaths[0][$ImagesPathVals[0]]; 
                         ?>'); background-size: 300px; background-repeat: no-repeat; background-position: center; background-size: cover;"></div>
                    <?php
                } else if (count($StockItemImagePaths) >= 2) { ?>
                    <!-- meerdere plaatjes laten zien -->
                    <div id="ImageFrame">
                        <div id="ImageCarousel" class="carousel slide" data-interval="false">
                            <!-- Indicators -->
                            <ul class="carousel-indicators">
                                <?php for ($i = 0; $i < count($StockItemImagePaths); $i++) {
                                    ?>
                                    <li data-target="#ImageCarousel"
                                        data-slide-to="<?php print $i ?>" <?php print (($i == 0) ? 'class="active"' : ''); ?>></li>
                                    <?php
                                } ?>
                            </ul>

                            <!-- slideshow -->
                            <div class="carousel-inner">
                                <?php for ($i = 0; $i < count($StockItemImagePaths); $i++) {
                                    ?>
                                    <div class="carousel-item <?php print ($i == 0) ? 'active' : ''; ?>">
                                        <img src="Public/<?php 
                                            print $ImagesPathVals[1].$StockItemImagePaths[$i][$ImagesPathVals[0]]; 
                                        ?>">
                                    </div>
                                <?php } ?>
                            </div>

                            <!-- knoppen 'vorige' en 'volgende' -->
                            <a class="carousel-control-prev" href="#ImageCarousel" data-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </a>
                            <a class="carousel-control-next" href="#ImageCarousel" data-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </a>
                        </div>
                    </div>
                    <?php
                }
            } else {
                ?>
                <div id="ImageFrame"
                     style="background-image: url('Public/StockGroupIMG/<?php print $StockItem['BackupImagePath']; ?>'); background-size: cover;"></div>
                <?php
            }
            ?>

            <h2 class="StockItemNameViewSize StockItemName">
                <?php print $StockItem['StockItemName']; ?>
            </h2>
            <!-- formulier via POST en niet GET om te zorgen dat refresh van pagina niet het artikel onbedoeld toevoegt-->
            <form method="post">
                <input type="number" name="stockItemID" value="<?php $stockItemID ?>" hidden>
                <button type="submit" name="submit" value="Voeg toe aan winkelmandje">Voeg to aan winkelwagen</button>
            </form>
            <div class="QuantityText">
                <?php
                if ($StockItem['QuantityOnHand'] > 1000) print ("Ruime voorraad beschikbaar");
                else print ("Voorraad: " . $StockItem['QuantityOnHand']);
                ?>
            </div>
            <div id="TemperatureText" class="TemperatureText">
                <?php
                if($StockItem['IsChillerStock'] == true){
                    print("Temperatuur vriesruimte: " . getTemperature($databaseConnection)['Temperature']);
                }
            ?>
            </div>
            <div id="StockItemHeaderLeft">
                <div class="CenterPriceLeft">
                    <div class="CenterPriceLeftChild">
                        <p class="StockItemPriceText"><b><?php print sprintf("€ %.2f", $StockItem['SellPrice']); ?></b></p>
                        <h6> Inclusief BTW </h6>
                    </div>
                </div>
            </div>
        </div>

        <div id="StockItemDescription">
            <h3>Artikel beschrijving</h3>
            <p><?php print $StockItem['SearchDetails']; ?></p>
            <a href=<?php print $StockItem['Video']; ?> target=_blankx>
                Product filmpje
        </a>
        </div>
        <div id="StockItemSpecifications">
            <h3>Artikel specificaties</h3>
            <?php
            $CustomFields = json_decode($StockItem['CustomFields'], true);
            if (is_array($CustomFields)) { ?>
                <table>
                <thead>
                <th>Naam</th>
                <th>Data</th>
                </thead>
                <?php
                foreach ($CustomFields as $SpecName => $SpecText) { ?>
                    <tr>
                        <td>
                            <?php print $SpecName; ?>
                        </td>
                        <td>
                            <?php
                            if (is_array($SpecText)) {
                                foreach ($SpecText as $SubText) {
                                    print $SubText . " ";
                                }
                            } else {
                                print $SpecText;
                            }
                            ?>
                        </td>
                    </tr>
                <?php } ?>
                </table><?php
            } else { ?>

                <p><?php print $StockItem['CustomFields']; ?>.</p>
                <?php
            }
            ?>
        </div>
        <?php
    } else {
        ?><h2 id="ProductNotFound">Het opgevraagde product is niet gevonden.</h2><?php
    } ?>
</div>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Artikelpagina (geef ?id=.. mee)</title>
</head>
<body>

<?php
//?id=1 handmatig meegeven via de URL (gebeurt normaal gesproken als je via overzicht op artikelpagina terechtkomt)
if (isset($_GET["id"])) {
    $stockItemID = $_GET["id"];
} else {
    $stockItemID = 0;
}
if (isset($_POST["submit"])){
    print("Product toegevoegd aan <a href='cart.php'> winkelwagen!</a>");
}
?>

</body>
</html>