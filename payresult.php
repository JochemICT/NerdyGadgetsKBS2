<?php
include __DIR__ . "/header.php";
if (isset($_SESSION["paymentSuccess"])) {
    unset($_SESSION["paymentSuccess"]);
    ?>

    <h1>Gefeliciteerd met uw nieuwe aankoop!</h1>

    <?php
} else {
    ?> <script>
        window.location.href = "index.php";
    </script> <?php
}