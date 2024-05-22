<?php
include __DIR__ . "/header.php";

$cart = getCart();
$cartCount = getTotalCartCount();
$product = array();

?>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Winkelwagen</title>
</head>
<body>
<div id="cartMain">
<h1>Winkelwagen</h1>
<?php

$totalePrijs = 0;
foreach($cart AS $StockItemId => $product) {
    $prijs = $product["StockItem"]["SellPrice"] * $product["Count"];
    $totalePrijs += $prijs;
    ?>
    <div class="productBox">
        <div class="imgDiv">
        <a href="view.php?id=<?php print $StockItemId?>" class="productLink">
            <img style='max-height:100px'src='./Public/<?php print $product["ImagePath"]?>'/>
        </a>
        </div>
        <div class="productDiv">
        <a href="view.php?id=<?php print $StockItemId?>" class="productLink">
            <text><?php print $product["StockItem"]["StockItemName"].", Prijs: € ".round($product["StockItem"]["SellPrice"],2).", Aantal: " .$product["Count"] . ", Totaal: € ".round($prijs,2) ?></text>
        </a>
        </div>
        <form id="prdtAantalForm_<?php print $StockItemId?>" method="post">
            <input class="productAantal" type="number" id='prdAantal_<?php print $StockItemId?>' name="count"/>
            <input type="hidden" name="changecount" value="<?php print $StockItemId?>"/>
        </form>

        <form id="prdtVerwijderForm_<?php print $StockItemId?>" method="post">
            <button type='submit' name="remove" value=<?php print $StockItemId?>>Verwijder</button>
        </form>

        <i class = "warnText" id="warn_<?php print $StockItemId?>"></i>
        <br>

        <script>
            const max_<?php print $StockItemId?> = <?php print $product["StockItem"]["QuantityOnHand"]?>;
            let old_<?php print $StockItemId?> = '';
            document.getElementById('prdAantal_<?php print $StockItemId?>').addEventListener('input', e=>{
                if (e.target.value > max_<?php print $StockItemId?>) {
                    e.target.value = max_<?php print $StockItemId?>;
                    document.getElementById("warn_<?php print $StockItemId?>").innerText = `Maximum van dit product: ${max_<?php print $StockItemId?>}`;
                }
                if (e.data==0 && e.target.value==0 || e.data=='-' || e.data=='e') e.target.value = old_<?php print $StockItemId?>;
                old_<?php print $StockItemId?> = e.target.value;
            })
            document.getElementById('prdAantal_<?php print $StockItemId?>').addEventListener('keypress', e=>{
                if (event.key === "Enter") {
                    document.getElementById("prdtAantalForm_<?php print $StockItemId?>").submit();
                }
            })
        </script>
    </div><?php
}
$_SESSION['totalePrijsCart'] = round($totalePrijs, 2);
?>
<div id="overzichtBox">
    <div id='titel'>Overzicht</div>
    <div class='billItem'>
        <div class='billName'>Artikelen (<?php print $cartCount?>)</div>
        <div class='billPrice'>€ <?php print $_SESSION['totalePrijsCart']; ?></div> 
    </div>
    <div class='billItem'>
        <div class='billName'>Verzendkosten</div>
        <div class='billPrice'>€ 0.00</div>
    </div>
    <hr id='lineBreak'/>
    <div class="totaalBox">
        <div class='billItem'>
            <div class='billName'>Totaal:</div>
            <div class='billPrice'>€ <?php print $_SESSION['totalePrijsCart']; ?></div>
        </div>
    </div>
    <?php
    if ($cart) {
        ?>
        <div id="bestelDiv"><button id='bestelKnop'>Verder naar bestellen</button></div>
        <?php
    }
    ?>
    <script>
        document.getElementById("bestelDiv").addEventListener("click",()=>{window.location.href = "pay.php";})
    </script>
</div>
</div>
</body>
</html>
