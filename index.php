<!-- dit is het bestand dat wordt geladen zodra je naar de website gaat -->
<?php
include __DIR__ . "/header.php";

$NameResults = 0;
while (!$NameResults) {
    $RandItemID = rand(1,225);
    $Query = '
        SELECT SIMG.ImagePath, SI.TaxRate, SI.StockItemName, SI.RecommendedRetailPrice
        FROM stockitems SI
        JOIN stockitemimages SIMG ON SI.StockItemID = SIMG.StockItemID
        WHERE SI.StockItemID = '.$RandItemID;
    $Statement = mysqli_prepare($databaseConnection, $Query);
    // mysqli_stmt_bind_param($Statement, "s",  $StockItemName);
    mysqli_stmt_execute($Statement);
    $NameResults = mysqli_stmt_get_result($Statement);
    $NameResults = mysqli_fetch_all($NameResults, MYSQLI_ASSOC);
}

?>
<div class="IndexStyle">
    <div class="col-11">
        <div class="TextPrice">
            <a href="view.php?id=<?php print $RandItemID?>">
                <div class="TextMain">
                    <?php print $NameResults[0]['StockItemName']?>
                </div>
                <ul id="ul-class-price">
                    <li class="HomePagePrice">€<?php print round($NameResults[0]['RecommendedRetailPrice']*(1+$NameResults[0]['TaxRate']/100),2)?></li>
                </ul>
            </a>
        </div>
        <div class="HomePageStockItemPicture" style="background-image: url('Public/StockItemIMG/<?php print $NameResults[0]['ImagePath']; ?>');"></div>
    </div>
</div>
<?php
include __DIR__ . "/footer.php";
?>

