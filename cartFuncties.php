<?php

function getCart() {
    if(isset($_SESSION['cart'])){               //controleren of winkelmandje (=cart) al bestaat
        $cart = $_SESSION['cart'];                  //zo ja:  ophalen
    } else{
        $cart = array();                        //zo nee: dan een nieuwe (nog lege) array
    }
    return $cart;                               // resulterend winkelmandje terug naar aanroeper functie
}

function saveCart($cart) {
    $_SESSION["cart"] = $cart;                  // werk de "gedeelde" $_SESSION["cart"] bij met de meegestuurde gegevens
}

function addProductToCart($StockItem, $imagePath) {
    $cart = getCart();  // eerst de huidige cart ophalen
    if (array_key_exists(strval($StockItem["StockItemID"]), $cart)) {  //controleren of $stockItemID(=key!) al in array staat
        if ($cart[strval($StockItem["StockItemID"])]["Count"] < $cart[strval($StockItem["StockItemID"])]["StockItem"]["QuantityOnHand"]) $cart[strval($StockItem["StockItemID"])]["Count"]++;               //zo ja:  aantal met 1 verhogen
    } else {
        $cart[strval($StockItem["StockItemID"])] = ["StockItem"=>$StockItem, "ImagePath"=>$imagePath,"Count"=>1];                   //zo nee: key toevoegen en aantal op 1 zetten.
    }

    saveCart($cart);// werk de "gedeelde" $_SESSION["cart"] bij met de bijgewerkte cart
}

function getTotalCartCount() {
    $cart = getCart();
    $totaalArtikelen = 0;
    foreach( $cart AS $item ) $totaalArtikelen += $item["Count"];
    return $totaalArtikelen;
}