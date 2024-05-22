<?php
include __DIR__ . "/header.php";

if (!isset($_SESSION["user"])) {
    ?> <script>
        window.location.href="index.php";
    </script> <?php
} else {
    ?>
    

    <form id="logoutForm" method="post" hidden><input name="logout" value="true" hidden></form>
    <button id="logoutButton">Log uit</button>
    <form method="post">
        Straatnaam: <input type="text" name="straatChange" required value=<?php print $_SESSION["addresses"]["Straatnaam"]?>></br>
        Huisnummer:<input type="text" name="huisnrChange" required value=<?php print $_SESSION["addresses"]["Huisnummer"]?>></br>
        Postcode:<input type="text" name="postcodeChange" placeholder="1234AB" required value=<?php print $_SESSION["addresses"]["Postcode"]?>></br>
        Plaats: <input type="text" name="plaatsChange" required value=<?php print $_SESSION["addresses"]["CityName"]?>></br>
        <button type="submit">Verander Adresgegevens</button>
    </form>
    <script>
        document.getElementById("logoutButton").addEventListener("click", ()=>document.getElementById("logoutForm").submit())
    </script>

    <?php
    if (isset($_SESSION["straatChangeSuccesful"])) {
        unset($_SESSION["straatChangeSuccesful"]);
        ?>
        <i id="addressChangeConfirmationText">Adres succesvol verandert</i>
        <?php
    }

}