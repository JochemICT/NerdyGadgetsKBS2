<?php
include __DIR__ . "/header.php";

if (isset($_SESSION['user']) || isset($_SESSION['successfulRegister'])) {
    unset($_SESSION['successfulRegister']);
    if (isset($_SESSION['cameFromCart'])) {
        unset($_SESSION['cameFromCart']);
        ?> <script>
            window.location.href = "login.php";
        </script> <?php
    } else {
        ?> <script>
            window.location.href = "index.php";
        </script> <?php
    }
} else {
?>

<div>
<form id="registerForm" method="post">
    Emailadres: <input id="emailInput" name="emailRegister"></br>
    Emailadres bevestigen: <input id="emailAgainInput" name="emailAgainRegister"></br>
    </br>
    Wachtwoord: <input type="password" id="passwordInput" name="passwordRegister"></br>
    Wachtwoord bevestigen: <input type="password" id="passwordAgainInput" name="passwordAgainRegister"></br>
    <i>Het wachtwoord moet het volgende bevatten: 1 hoofdletter, 1 getal en minimaal 8 tekens.</i></br>
    </br>
    Voornaam: <input type="text" name="voornaamRegister" required></br>
    Tussenvoegsel: <input type="text" name="tussenvoegselRegister"></br>
    Achternaam: <input type="text" name="achternaamRegister" required></br>
    </br>
    Straatnaam: <input type="text" name="straatRegister" required></br>
    Huisnummer:<input type="text" name="huisnrRegister" required></br>
    Postcode:<input type="text" name="postcodeRegister" placeholder="1234AB" required></br>
    Plaats: <input type="text" name="plaatsRegister" required></br>
    Provincie: <input type="text" name="provincieRegister" required></br>
    </br>
    <button type="button" id="registerButton">Registreer</button></br>
</form>
<?php
if (isset($_SESSION['emailAlreadyExists'])) {
    unset($_SESSION['emailAlreadyExists']);
?><i>Email is al gelinkt aan account.</i></br><?php
}
if (isset($_SESSION['cityNotFound'])) {
    unset($_SESSION['cityNotFound']);
?><i>Adres niet gevonden.</i></br><?php
}
if (isset($_SESSION['Email incorrect format'])) {
    unset($_SESSION['Email incorrect format']);
?><i>Email is niet juist geformatteerd.</i></br><?php
}
?>
<i id="infoText"></i>
<script>
    document.getElementById("registerButton").addEventListener("click", e=> {
        const registerForm = document.getElementById("registerForm")
        const formChildren = [...registerForm.children]
        let infoText = document.getElementById("infoText")
        infoText.innerText = ""
        if (formChildren[0].value != formChildren[2].value || formChildren[0].value == "" || formChildren[2].value == "") {
            infoText.innerText = infoText.innerText.concat("Emails zijn niet gelijk of leeg.\n");
        }
        if (formChildren[5].value != formChildren[7].value || formChildren[5].value == "" || formChildren[7].value == "") {
            infoText.innerText = infoText.innerText.concat("Wachtwoorden zijn niet gelijk of leeg.\n");
        }
        if (formChildren[5].value.length < 8 || !/\d/.test(formChildren[7].value) || !/[A-Z]/.test(formChildren[5].value)) infoText.innerText = infoText.innerText.concat("Het wachtwoord voldoet niet aan de eisen.\n");
        if (!infoText.innerText) {
            registerForm.submit();
        }
    })
</script>
</div>
    
<?php }
?>