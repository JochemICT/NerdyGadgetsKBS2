<!-- dit bestand bevat alle code voor het productoverzicht -->
<?php
include __DIR__ . "/header.php";

$ReturnableResult = null;
$SearchString = "";

// dit haalt de manier van sorteren op van de url, bv alfabetisch op op prijs
if (isset($_GET['sort'])) {
    $Sort = $_GET['sort'];
} else {
    $Sort = "SellPrice";
}
$SortName = "price_low_high";

$AmountOfPages = 0;
$queryBuildResult = "";

// dit haalt op van de url of de zoekbalk in focus moet zijn
$SearchFocus = "";
if (isset($_GET['search_focus'])) {
    $SearchFocus = $_GET['search_focus'];
}

// dit haalt de categorie op van de url
if (isset($_GET['category_id'])) {
    $CategoryID = $_GET['category_id'];
} else {
    $CategoryID = "";
}

// dit haalt het aantal producten op van de url
$ProductsOnPage = 25;
$_SESSION['products_on_page'] = 25;
if (isset($_GET['products_on_page'])) {
    $ProductsOnPage = $_GET['products_on_page'];
    $_SESSION['products_on_page'] = $_GET['products_on_page'];
} else if (isset($_SESSION['products_on_page'])) {
    $ProductsOnPage = $_SESSION['products_on_page'];
}

// dit haalt het huidige paginanummer op van de url
if (isset($_GET['page_number'])) {
    $PageNumber = $_GET['page_number'];
} else {
    $PageNumber = 0;
}

// code deel 1 van User story: Zoeken producten
// <voeg hier de code in waarin de zoekcriteria worden opgebouwd>

// dit haalt de zoek zin op van de url
if (isset($_GET['search_string'])) {
    $SearchString = $_GET['search_string'];
}
// maak van zoek zin een lijst van woorden door de string te splitsen op spaties
$searchValues = explode(" ", $SearchString);

// dit bouwt een functioneel sql query van de zoek termen
$queryBuildResult = "";
if ($SearchString != "") {
    for ($i = 0; $i < count($searchValues); $i++) {
        if ($i != 0) {
            $queryBuildResult .= "AND ";
        }
        $queryBuildResult .= "SI.SearchDetails LIKE '%$searchValues[$i]%' ";
    }
    if ($queryBuildResult != "") {
        $queryBuildResult .= " OR ";
    }
    if ($SearchString != "" || $SearchString != null) {
        $queryBuildResult .= "SI.StockItemID ='$SearchString'";
    }
}

// <einde van de code voor zoekcriteria>
// einde code deel 1 van User story: Zoeken producten

$Offset = $PageNumber * $ProductsOnPage;

if ($CategoryID != "") { 
    if ($queryBuildResult != "") {
    $queryBuildResult .= " AND ";
    }
}

// code deel 2 van User story: Zoeken producten
// <voeg hier de code in waarin het zoekresultaat opgehaald wordt uit de database>

if ($CategoryID == "") {
    if ($queryBuildResult != "") {
        $queryBuildResult = "WHERE " . $queryBuildResult;
    }

    $Query = "
                SELECT SI.StockItemID, SI.StockItemName, SI.MarketingComments, TaxRate, RecommendedRetailPrice, ROUND(TaxRate * RecommendedRetailPrice / 100 + RecommendedRetailPrice,2) as SellPrice,
                QuantityOnHand,
                (SELECT ImagePath
                FROM stockitemimages
                WHERE StockItemID = SI.StockItemID LIMIT 1) as ImagePath,
                (SELECT ImagePath FROM stockgroups JOIN stockitemstockgroups USING(StockGroupID) WHERE StockItemID = SI.StockItemID LIMIT 1) as BackupImagePath
                FROM stockitems SI
                JOIN stockitemholdings SIH USING(stockitemid)
                " . $queryBuildResult . "
                GROUP BY StockItemID
                ORDER BY " . $Sort . "
                LIMIT ?  OFFSET ?";

    $Statement = mysqli_prepare($databaseConnection, $Query);
    mysqli_stmt_bind_param($Statement, "ii",  $ProductsOnPage, $Offset);
    mysqli_stmt_execute($Statement);
    $ReturnableResult = mysqli_stmt_get_result($Statement);
    $ReturnableResult = mysqli_fetch_all($ReturnableResult, MYSQLI_ASSOC);

    $Query = "
            SELECT count(*)
            FROM stockitems SI
            $queryBuildResult";
    $Statement = mysqli_prepare($databaseConnection, $Query);
    mysqli_stmt_execute($Statement);
    $Result = mysqli_stmt_get_result($Statement);
    $Result = mysqli_fetch_all($Result, MYSQLI_ASSOC);
}

// <einde van de code voor zoekresultaat>
// einde deel 2 van User story: Zoeken producten

if ($CategoryID !== "") {
$Query = "
           SELECT SI.StockItemID, SI.StockItemName, SI.MarketingComments, TaxRate, RecommendedRetailPrice,
           ROUND(SI.TaxRate * SI.RecommendedRetailPrice / 100 + SI.RecommendedRetailPrice,2) as SellPrice,
           QuantityOnHand,
           (SELECT ImagePath FROM stockitemimages WHERE StockItemID = SI.StockItemID LIMIT 1) as ImagePath,
           (SELECT ImagePath FROM stockgroups JOIN stockitemstockgroups USING(StockGroupID) WHERE StockItemID = SI.StockItemID LIMIT 1) as BackupImagePath
           FROM stockitems SI
           JOIN stockitemholdings SIH USING(stockitemid)
           JOIN stockitemstockgroups USING(StockItemID)
           JOIN stockgroups ON stockitemstockgroups.StockGroupID = stockgroups.StockGroupID
           WHERE " . $queryBuildResult . " ? IN (SELECT StockGroupID from stockitemstockgroups WHERE StockItemID = SI.StockItemID)
           GROUP BY StockItemID
           ORDER BY " . $Sort . "
           LIMIT ? OFFSET ?";

    $Statement = mysqli_prepare($databaseConnection, $Query);
    mysqli_stmt_bind_param($Statement, "iii", $CategoryID, $ProductsOnPage, $Offset);
    mysqli_stmt_execute($Statement);
    $ReturnableResult = mysqli_stmt_get_result($Statement);
    $ReturnableResult = mysqli_fetch_all($ReturnableResult, MYSQLI_ASSOC);

    $Query = "
                SELECT count(*)
                FROM stockitems SI
                WHERE " . $queryBuildResult . " ? IN (SELECT SS.StockGroupID from stockitemstockgroups SS WHERE SS.StockItemID = SI.StockItemID)";
    $Statement = mysqli_prepare($databaseConnection, $Query);
    mysqli_stmt_bind_param($Statement, "i", $CategoryID);
    mysqli_stmt_execute($Statement);
    $Result = mysqli_stmt_get_result($Statement);
    $Result = mysqli_fetch_all($Result, MYSQLI_ASSOC);
}
$amount = $Result[0];
if (isset($amount)) {
    $AmountOfPages = ceil($amount["count(*)"] / $ProductsOnPage);
}

    // laat de voorraad zien of 'ruime voorraad beschikbaar, wanneer meer dan 1000 producten
    function getVoorraadTekst($actueleVoorraad) {
        if ($actueleVoorraad > 1000) {
            return "Ruime voorraad beschikbaar.";
        } else {
            return "Voorraad: $actueleVoorraad";
        }
    }
    function berekenVerkoopPrijs($adviesPrijs, $btw) {
		return $btw * $adviesPrijs / 100 + $adviesPrijs;
    }
?>

<!-- code deel 3 van User story: Zoeken producten : de html -->
<!-- de zoekbalk links op de pagina  -->

<!-- dit is de div voor de hele sidebar-->
<div id="Sidebar" class="Sidebar">

    <!-- dit is de input box waar de gebruiker zoektermen in typt -->
    <input name="SearchBar" id="SearchBar" class="SearchBar" type="text" placeholder="Zoek je product" name="SearchBar" value="<?php print $SearchString?>"></input>
    <!-- <button name="SearchButton" id="SearchButton" class="SearchButton">Zoek</button> -->
    <i name="SearchButton" id="SearchButton" class="fas fa-search search"></i>

    <!-- dit is de select element voor de hoeveelheid producten op 1 pagina -->
    <select name="ProductCountSelector" id="ProductCountSelector" class="ProductCountSelector">
        <option <?php if ($ProductsOnPage == 25) print 'selected'?> value="25">25</option>
        <option <?php if ($ProductsOnPage == 50) print 'selected'?> value="50">50</option>
        <option <?php if ($ProductsOnPage == 100) print 'selected'?> value="100">100</option>
        <option <?php if ($ProductsOnPage == 200) print 'selected'?> value="200">200</option>
    </select>

    <!-- dit is de select element voor de methode van sortering -->
    <select name="SortSelector" id="SortSelector" class="SortSelector">
        <option <?php if ($Sort == "StockItemName") print 'selected'?> value="StockItemName">Name A-Z</option>
        <option <?php if ($Sort == "StockItemName DESC") print 'selected'?> value="StockItemName DESC">Name Z-A</option>
        <option <?php if ($Sort == "SellPrice") print 'selected'?> value="SellPrice">Price Low-High</option>
        <option <?php if ($Sort == "SellPrice DESC") print 'selected'?> value="SellPrice DESC">Price High-Low</option>
    </select>

    <!-- dit is de script element, een onzichtbaar element met daar in javascript code -->
    <script>
        // als SearchFocus waar is dan wordt de zoekbalk gefocust en kan je meteen typen
        if (<?php if ($SearchFocus) {print "1";} else print "0"?>) document.getElementById("SearchBar").focus()

        // hier wordt searchString in de url gestopt
        function putSearchInUrl() {
            const searchString = document.getElementById('SearchBar').value
            // haal de url op en verwijder alle variabelen
            const url = window.location.href.split('?')[0]
            // bouw de url opnieuw op met variabelen
            window.location.href = `${url}?${searchString ? `search_string=${searchString}&` : ""}category_id=<?php print $CategoryID?>&products_on_page=<?php print $ProductsOnPage?>&sort=<?php print $Sort?>`
        }

        // een EventListener wordt aangehaakt aan de SearchBar, de EventListener triggert een functie als die de event ziet gebeuren
        document.getElementById("SearchBar").addEventListener("keypress", event => {
            if (event.key === "Enter") putSearchInUrl()
        })

        // een EventListener wordt aangehaakt aan de SearchButton, de EventListener triggert een functie als die de event ziet gebeuren
        document.getElementById('SearchButton').addEventListener("click", putSearchInUrl)
        
        // een EventListener wordt aangehaakt aan de SortSelector, de EventListener triggert een functie als die de event ziet gebeuren
        document.getElementById('SortSelector').addEventListener("input", () => {
            // haal de url op en verwijder alle variabelen
            const url = window.location.href.split('?')[0]
            // bouw de url opnieuw op met variabelen
            window.location.href = `${url}?search_string=<?php print $SearchString?>&category_id=<?php print $CategoryID?>&products_on_page=<?php print $ProductsOnPage?>&sort=${document.getElementById("SortSelector").value}`
        })

        // een EventListener wordt aangehaakt aan de ProductCountSelector, de EventListener triggert een functie als die de event ziet gebeuren
        document.getElementById('ProductCountSelector').addEventListener("input", () => {
            // haal de url op en verwijder alle variabelen
            const url = window.location.href.split('?')[0]
            // bouw de url opnieuw op met variabelen
            window.location.href = `${url}?search_string=<?php print $SearchString?>&category_id=<?php print $CategoryID?>&products_on_page=${document.getElementById("ProductCountSelector").value}&sort=<?php print $Sort?>`
        })
    </script>
</div>

<!-- einde zoekresultaten die links van de zoekbalk staan -->
<!-- einde code deel 3 van User story: Zoeken producten  -->

<div id="ResultsArea" class="Browse">
    <?php
    if (isset($ReturnableResult) && count($ReturnableResult) > 0) {
        foreach ($ReturnableResult as $row) {
            ?>
            <!--  coderegel 1 van User story: bekijken producten  -->



            <!-- einde coderegel 1 van User story: bekijken producten   -->
                <div id="ProductFrame">
                    <?php
                    if (isset($row['ImagePath'])) { ?>
                        <div class="ImgFrame"
                             style="background-image: url('<?php print "Public/StockItemIMG/" . $row['ImagePath']; ?>'); background-size: 230px; background-repeat: no-repeat; background-position: center;"></div>
                    <?php } else if (isset($row['BackupImagePath'])) { ?>
                        <div class="ImgFrame"
                             style="background-image: url('<?php print "Public/StockGroupIMG/" . $row['BackupImagePath'] ?>'); background-size: cover;"></div>
                    <?php }
                    ?>
                    <div id="StockItemFrameRight">
                        <div class="CenterPriceLeftChild">
                            <h1 class="StockItemPriceText"><?php print sprintf(" %0.2f", berekenVerkoopPrijs($row["RecommendedRetailPrice"], $row["TaxRate"])); ?></h1>
                            <h6>Inclusief BTW </h6>
                        </div>
                    </div>
                    <h1 class="StockItemID">Artikelnummer: <?php print $row["StockItemID"]; ?></h1>
                    <!-- dit is de link naar de productpagina -->
                    <a style="width: 100px; height=100px;" class="ListItem" href='view.php?id=<?php print $row['StockItemID']; ?>'>
                    <p class="StockItemName"><?php print $row["StockItemName"]; ?></p>
                    </a>
                    <p class="StockItemComments"><?php print $row["MarketingComments"]; ?></p>
                    <h4 class="ItemQuantity"><?php print getVoorraadTekst($row["QuantityOnHand"]); ?></h4>
                </div>
            <!--  coderegel 2 van User story: bekijken producten  -->



            <!--  einde coderegel 2 van User story: bekijken producten  -->
        <?php } ?>

        <form id="PageSelector">
		
<!-- code deel 4 van User story: Zoeken producten  -->



<!-- einde code deel 4 van User story: Zoeken producten  -->
            <input type="hidden" name="category_id" id="category_id" value="<?php if (isset($_GET['category_id'])) {
                print ($_GET['category_id']);
            } ?>">
            <input type="hidden" name="result_page_numbers" id="result_page_numbers"
                value="<?php print (isset($_GET['result_page_numbers'])) ? $_GET['result_page_numbers'] : "0"; ?>">
            <input type="hidden" name="products_on_page" id="products_on_page"
                value="<?php print ($_SESSION['products_on_page']); ?>">
            <input type="hidden" name="search_string" id="search_string"
                value="<?php print $SearchString; ?>">
            <input type="hidden" name="sort" id="sort"
                value="<?php print $Sort; ?>">
            
            <?php
            if ($AmountOfPages > 0) {
                for ($i = 1; $i <= $AmountOfPages; $i++) {
                    if ($PageNumber == ($i - 1)) {
                        ?>
                        <div id="SelectedPage"><?php print $i; ?></div><?php
                    } else { ?>
                        <button id="page_number" class="PageNumber" value="<?php print($i - 1); ?>" type="submit"
                            name="page_number"><?php print($i); ?></button>
                    <?php }
                }
            }
            ?>
        </form>
        <?php
    } else {
        ?>
        <h2 id="NoSearchResults">
            Yarr, er zijn geen resultaten gevonden.
        </h2>
        <?php
    }
    ?>
</div>

<?php
include __DIR__ . "/footer.php";
?>
