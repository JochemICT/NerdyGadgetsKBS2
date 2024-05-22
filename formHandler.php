<?php
$cart = getCart();

if (isset($_POST["straatChange"]) && isset($_POST["huisnrChange"]) && isset($_POST["postcodeChange"]) && isset($_POST["plaatsChange"])) {
    if ($_POST["straatChange"]=="" || $_POST["huisnrChange"]=="" || $_POST["postcodeChange"]=="" || $_POST["plaatsChange"]=="") return;
    $_SESSION["user"] = changeAddress($_SESSION["user"], $_POST["straatChange"], $_POST["huisnrChange"], $_POST["postcodeChange"], $_POST["plaatsChange"], $_SESSION["user"]["EmailAddress"], $databaseConnection)[0];
    $_SESSION["straatChangeSuccesful"] = true; 
}

if (isset($_POST["paySubmit"])) {
    if ($_POST["voornaam"] == "" || 
        $_POST["achternaam"] == "" || 
        $_POST["emailadres"] == "" || 
        $_POST["straat"] == "" || 
        $_POST["huisnr"] == "" || 
        $_POST["postcode"] == "" || 
        $_POST["plaats"] == "") return;
    if (!changeStorageByCart($cart, $databaseConnection)) $_SESSION["paymentSuccess"] = false;
    //if (addCity($_POST["straat"],$_POST["huisnr"],$_POST["postcode"],$_POST["plaats"],$databaseConnection) < 0) $_SESSION["paymentSuccess"] = false;
    
    $userPaySubmit = [
        "PersonID" => 1,
        "FullName" => $_POST["voornaam"]." ".$_POST["tussenvoegsel"]." ".$_POST["achternaam"],
        "EmailAddress" => $_POST["emailadres"],
        "CityName" => $_POST["plaats"],
        "Postcode" => $_POST["postcode"],
        "CityID" => isset($_SESSION["user"]) ? $_SESSION["addresses"]["CityID"] : false,
        "Straatnaam" => $_POST["straat"],
        "Huisnummer" => $_POST["huisnr"]
    ];
    
    if (processOrder($cart, $userPaySubmit, "".$_POST["voornaam"]." ".$_POST["achternaam"], $databaseConnection) == []) {
        $_SESSION["paymentSuccess"] = false;
        return;
    }
    $_SESSION["cart"] = [];
    $_SESSION["paymentSuccess"] = true;
}
if (isset($_POST["logout"])) {
    unset($_SESSION['user']);
    unset($_SESSION['addresses']);
}
if (isset($_POST["emailRegister"])&&isset($_POST["passwordRegister"])&&isset($_POST["voornaamRegister"])&&isset($_POST["plaatsRegister"])&&isset($_POST["postcodeRegister"])) {
    $_SESSION["debug"] = true;
    if (isset($_SESSION['user'])) return;
    if ($_POST["emailRegister"] == NULL || $_POST["emailRegister"] == "" ) return;

    if (!filter_var($_POST['emailRegister'], FILTER_VALIDATE_EMAIL)) {
        $emailErr = "Invalid email format";
        $_SESSION['Email incorrect format'] = true;
        return;
    }

    if ($_POST["passwordRegister"] == NULL || $_POST["passwordRegister"] == "" || !preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d|\W).+$/", $_POST["passwordRegister"])) return;
    registerUser($_POST["emailRegister"], $_POST["passwordRegister"], $_POST["voornaamRegister"], $_POST["tussenvoegselRegister"], 
        $_POST["achternaamRegister"], $_POST["straatRegister"], $_POST["huisnrRegister"], $_POST["postcodeRegister"], 
        $_POST["plaatsRegister"], $databaseConnection);
    
}
if (isset($_POST["emailLogin"]) && isset($_POST["passwordLogin"])) {
    unset($_SESSION['user']);
    unset($_SESSION['addresses']);
    $userInformation = checkLogin($_POST["emailLogin"], $_POST["passwordLogin"], $databaseConnection);
    if ($userInformation == [] || $userInformation["PersonID"] == NULL) {
       $_SESSION['failedLogin'] = true;
    }
    else {
       $_SESSION['failedLogin'] = false;
       $_SESSION['user'] = $userInformation;
    }
}
if (isset($_POST["submit"])) {              // zelfafhandelend formulier
    $stockItemID = $_POST["stockItemID"];
    if (!isset($_SESSION['CurrentItem'])) return;
    addProductToCart($_SESSION['CurrentItem'][0], $_SESSION['CurrentItem'][1]);         // maak gebruik van geïmporteerde functie uit cartfuncties.php
}
if (isset($_POST["count"])) {              // zelfafhandelend formulier
    if ($_POST["count"] && is_numeric($_POST["count"]) && $_POST["count"]>0){
        if ($cart[$_POST["changecount"]]["StockItem"]['QuantityOnHand'] < $_POST['count']) return;
        $cart[$_POST["changecount"]]["Count"] = $_POST['count'];
    }
    saveCart($cart);
}
if (isset($_POST["subtract"])) {              // zelfafhandelend     formulier
    if ($cart[$_POST["subtract"]]["Count"] > 1) $cart[$_POST["subtract"]]["Count"]--;
    else unset($cart[$_POST["subtract"]]);
    saveCart($cart);
}
if (isset($_POST["add"])) {              // zelfafhandelend formulier
    if ($cart[$_POST["add"]]["Count"] > 0) $cart[$_POST["add"]]["Count"]++;
    else unset($cart[$_POST["add"]]);
    saveCart($cart);
}
if (isset($_POST["remove"])) {              // zelfafhandelend formulier
    unset($cart[$_POST["remove"]]);
    saveCart($cart);
}
?>