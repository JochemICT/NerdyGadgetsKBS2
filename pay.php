<?php
include __DIR__ . "/header.php";

$cart = getCart();
$cartCount = getTotalCartCount();
$product = array();
$payUser = 0;

if (isset($_SESSION["paymentSuccess"])) {
    if ($_SESSION["paymentSuccess"] == true) {
        ?> <script>
            window.location.href="payresult.php";
        </script> <?php
    } else {
        unset($_SESSION["paymentSuccess"]);
        ?> <h1>
            De betaling is niet gelukt. Probeer nog een keer.
        </h1> <?php
    }
} 
if (!isset($_SESSION["user"])) {
    $_SESSION['cameFromCart'] = true;
    $payUser = [
        "EmailAddress" => "",
        "Voornaam" => "",
        "Tussenvoegsel" => "",
        "Achternaam" => "",

    ];
    $payAdresses = [
        "Straatnaam" => "",
        "Huisnummer" => "",
        "Postcode" => "",
        "CityName" => ""
    ];
} else {
    $payUser = $_SESSION["user"];
    $payAdresses = $_SESSION["addresses"];
}
if ($cart) {
    //unset($_SESSION['cameFromCart']);
    ?>
    <div id="bestelPagina">

        <form method="post">
            Voornaam: <input type="text" name="voornaam" required value=<?php print $payUser["Voornaam"]?>></br>
            Tussenvoegsel: <input type="text" name="tussenvoegsel" value=<?php print $payUser["Tussenvoegsel"]?>></br>
            Achternaam: <input type="text" name="achternaam" required value=<?php print $payUser["Achternaam"]?>></br>
            Emailadres: <input type="text" name="emailadres" required value=<?php print $payUser["EmailAddress"]?>></br>
            Straatnaam: <input type="text" name="straat" required value=<?php print $payAdresses["Straatnaam"]?>></br>
            Huisnummer:<input type="text" name="huisnr" required value=<?php print $payAdresses["Huisnummer"]?>></br>
            Postcode:<input type="text" name="postcode" placeholder="1234AB" required value=<?php print $payAdresses["Postcode"]?>></br>
            Plaats: <input type="text" name="plaats" required value=<?php print $payAdresses["CityName"]?>  ></br>
            <input name="paySubmit" hidden>
            <button type="submit" value="true">Betalen</button></br>
        </form> 
        <?php 
        if (!isset($_SESSION["user"])) {
            ?>
            <a href="login.php">Afrekenen gaat makkelijker met een account.</a>
            <?php  
        }?>
    </div>
    
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
    <script>
        document.getElementById("bestelDiv").addEventListener("click",()=>{window.location.href = "pay.php";})
    </script>
</div>
    <?php
} else {
    ?> <script>
        window.location.href = 'index.php';
    </script> <?php
}
?>