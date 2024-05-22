<?php
include __DIR__ . "/header.php";

if (isset($_SESSION['user'])) {
    if (isset($_SESSION['cameFromCart'])) {
        ?> <script>
            window.location.href = "pay.php";
        </script> <?php
    } else {
        ?> <script>
            window.location.href = "index.php";
        </script> <?php
    }
} else {
    ?>
    <form id="loginForm" method="post">
        Email: <input name="emailLogin"></br>
        Password: <input type="password" name="passwordLogin"></br>
        <button type="submit">Login</button>
    </form>
    <?php 
        if (isset($_SESSION["failedLogin"])) {
            if ($_SESSION["failedLogin"] == true) {
                ?>
                    <i>Incorrecte inloggegevens...</i>
                <?php
                $_SESSION["failedLogin"] = false;
            }
        }
    ?>

    <a href="register.php">Nog geen account?</a>
    <?php
}
?>
