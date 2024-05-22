<script>
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
</script>
<!-- de inhoud van dit bestand wordt bovenaan elke pagina geplaatst -->
<?php
session_start();
include "database.php";
$databaseConnection = connectToDatabase();

include "cartFuncties.php";
include "formHandler.php"
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>NerdyGadgets</title>

    <!-- Javascript -->
    <script src="Public/JS/fontawesome.js"></script>
    <script src="Public/JS/jquery.min.js"></script>
    <script src="Public/JS/bootstrap.min.js"></script>
    <script src="Public/JS/popper.min.js"></script>
    <script src="Public/JS/resizer.js"></script>

    <!-- Style sheets-->
    <link rel="stylesheet" href="Public/CSS/style.css" type="text/css">
    <link rel="stylesheet" href="Public/CSS/index.css" type="text/css">
    <link rel="stylesheet" href="Public/CSS/sidebar.css" type="text/css">
    <link rel="stylesheet" href="Public/CSS/header.css" type="text/css">
    <link rel="stylesheet" href="Public/CSS/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="Public/CSS/cart.css" type="text/css">
    <link rel="stylesheet" href="Public/CSS/pay.css" type="text/css">
    <link rel="stylesheet" href="Public/CSS/register.css" type="text/css">
    <link rel="stylesheet" href="Public/CSS/login.css" type="text/css">
    <link rel="stylesheet" href="Public/CSS/typekit.css">
</head>
<body>
<div id="header">

    <div id="LogoDiv">
        <a href="./" id="LogoA">  
            <img src="Public/Img/company(1).png"/>
        </a>
    </div>

    <?php
    $HeaderStockGroups = getHeaderStockGroups($databaseConnection);
    foreach ($HeaderStockGroups as $HeaderStockGroup) { ?>
        <div class="categoryDiv">
            <a href="browse.php?category_id=<?php print $HeaderStockGroup['StockGroupID']; ?>"
                class="categoryLinks"><?php print $HeaderStockGroup['StockGroupName']; ?></a>
        </div>
        <?php
    }
    ?>

    <div id="categoryDiv"><a href="categories.php">Alle categorieën</a></div>
    <div class="loginDIv">
        <?php
        if (isset($_SESSION['user'])) {
            ?>
            <a href="user.php">Profiel</a>
            <?php
        } else {
            ?>
            <a href="login.php">Login</a>
            <?php
        }
        ?>
    </div>
    <div class="cartDiv">
        <a class="cartLink" href="cart.php">
            <img class="cartButton" src="./Public/svg/cart.svg"></img>
            <?php print getTotalCartCount(); ?>
        </a>
    </div>

    <div class="searchDiv">
        <a href="browse.php?search_focus=1" class="HrefDecoration"><i class="fas fa-search search"> </i>Zoeken</a>
    </div>

</div>
<div id="content">