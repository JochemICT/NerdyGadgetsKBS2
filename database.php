<!-- dit bestand bevat alle code die verbinding maakt met de database -->
<?php
include __DIR__."/secret.php";

function connectToDatabase() {
    $Connection = null;

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Set MySQLi to throw exceptions
    try {
        $Connection = mysqli_connect("localhost", "root", getSQLPassword(), "nerdygadgets");
        mysqli_set_charset($Connection, 'latin1');
        $DatabaseAvailable = true;
    } catch (mysqli_sql_exception $e) {
        $DatabaseAvailable = false;
    }
    if (!$DatabaseAvailable) {
        ?><h2>Website wordt op dit moment onderhouden.</h2><?php
        die();
    }

    return $Connection;
}

function getHeaderStockGroups($databaseConnection) {
    $query = "
        SELECT StockGroupID, StockGroupName, ImagePath
        FROM stockgroups 
        WHERE StockGroupID IN (
                                SELECT StockGroupID 
                                FROM stockitemstockgroups
                                ) AND ImagePath IS NOT NULL
        ORDER BY StockGroupID ASC
    ";
    $statement = mysqli_prepare($databaseConnection, $query);
    mysqli_stmt_execute($statement);
    $HeaderStockGroups = mysqli_stmt_get_result($statement);
    return $HeaderStockGroups;
}

function getStockGroups($databaseConnection) {
    $query = "
        SELECT StockGroupID, StockGroupName, ImagePath
        FROM stockgroups 
        WHERE StockGroupID IN (
                                SELECT StockGroupID 
                                FROM stockitemstockgroups
                                ) AND ImagePath IS NOT NULL
        ORDER BY StockGroupID ASC
    ";
    $statement = mysqli_prepare($databaseConnection, $query);
    mysqli_stmt_execute($statement);
    $Result = mysqli_stmt_get_result($statement);
    $StockGroups = mysqli_fetch_all($Result, MYSQLI_ASSOC);
    return $StockGroups;
}

function getStockItem($id, $databaseConnection) {
    $Result = null;

    $query = " 
        SELECT SI.StockItemID, 
        (RecommendedRetailPrice*(1+(TaxRate/100))) AS SellPrice, 
        StockItemName,
        QuantityOnHand,
        SearchDetails,
        IsChillerStock,
        UnitPackageID,
        UnitPrice,
        TaxRate,
        (CASE WHEN (RecommendedRetailPrice*(1+(TaxRate/100))) > 50 THEN 0 ELSE 6.95 END) AS SendCosts, MarketingComments, CustomFields, SI.Video,
        (SELECT ImagePath FROM stockgroups JOIN stockitemstockgroups USING(StockGroupID) WHERE StockItemID = SI.StockItemID LIMIT 1) as BackupImagePath   
        FROM stockitems SI 
        JOIN stockitemholdings SIH USING(stockitemid)
        JOIN stockitemstockgroups ON SI.StockItemID = stockitemstockgroups.StockItemID
        JOIN stockgroups USING(StockGroupID)
        WHERE SI.stockitemid = ?
        GROUP BY StockItemID
    ";

    $statement = mysqli_prepare($databaseConnection, $query);
    mysqli_stmt_bind_param($statement, "i", $id);
    mysqli_stmt_execute($statement);
    $ReturnableResult = mysqli_stmt_get_result($statement);
    if ($ReturnableResult && mysqli_num_rows($ReturnableResult) == 1) {
        $Result = mysqli_fetch_all($ReturnableResult, MYSQLI_ASSOC)[0];
    }

    return $Result;
}

function getStockItemImage($id, $databaseConnection) {

    $query = "
        SELECT G.ImagePath Category_Image, II.ImagePath Item_Image
        FROM stockgroups G
        LEFT JOIN stockitemstockgroups IG USING (StockGroupID)
        LEFT JOIN stockitemimages II USING(StockItemID)
        WHERE StockItemID = ?
        GROUP BY Item_Image;
    ";

    $statement = mysqli_prepare($databaseConnection, $query);
    mysqli_stmt_bind_param($statement, "i", $id);
    mysqli_stmt_execute($statement);
    $R = mysqli_stmt_get_result($statement);
    $R = mysqli_fetch_all($R, MYSQLI_ASSOC);

    return $R;
}

function checkLogin($email, $password, $databaseConnection) {
    if ($email == NULl || $email == "") return false;
    if ($password == NULL || $password == "") return false;
    $hashedPassword = hash('sha256', $password);
    $query = "
        SELECT *
        FROM nerdygadgets.people P JOIN people_address USING(PersonID)
        WHERE P.EmailAddress = ? AND P.HashedPassword = ?;
    ";
    $statement = mysqli_prepare($databaseConnection, $query);
    mysqli_stmt_bind_param($statement, "ss", $email, $hashedPassword);
    mysqli_stmt_execute($statement);
    $R = mysqli_stmt_get_result($statement);
    $R = mysqli_fetch_all($R, MYSQLI_ASSOC);

    $query = "
        SELECT nerdygadgets.cities.*, Straatnaam, Huisnummer, Postcode
        FROM nerdygadgets.cities JOIN people_address USING(CityID)
        WHERE PersonID = ?;
    ";
    $statement = mysqli_prepare($databaseConnection, $query);
    mysqli_stmt_bind_param($statement, "i", $R[0]["PersonID"]);
    mysqli_stmt_execute($statement);
    $R2 = mysqli_stmt_get_result($statement);
    $R2 = mysqli_fetch_all($R2, MYSQLI_ASSOC);

    $_SESSION['addresses'] = $R2[0];

    return $R[0];
}

function registerUser($email, $password, $voornaam, $tussenvoegsel, $achternaam, $straat, $huisnmr, $postcode, $plaats, $databaseConnection) {
    $hashedPassword = hash('sha256', $password);
    $timeStamp = date("Y-m-d H:i:s");
    $name = $voornaam." ".$tussenvoegsel." ".$achternaam;
    $query = "
        SELECT * FROM nerdygadgets.people WHERE EmailAddress = ?
    ";
    $statement = mysqli_prepare($databaseConnection, $query);
    mysqli_stmt_bind_param($statement, 's', $email);
    mysqli_stmt_execute($statement);
    $R = mysqli_stmt_get_result($statement);
    $R = mysqli_fetch_all($R, MYSQLI_ASSOC);

    if ($R != []) {
        $_SESSION['emailAlreadyExists'] = true;
        return 0;
    }

    $query = "
        SELECT CityID FROM cities WHERE CityName = ?;
    ";
    $statement = mysqli_prepare($databaseConnection, $query);
    mysqli_stmt_bind_param($statement, 's', $plaats);
    mysqli_stmt_execute($statement);
    $R2 = mysqli_stmt_get_result($statement);
    $R2 = mysqli_fetch_all($R2, MYSQLI_ASSOC);

    if ($R2 == []) {
        $_SESSION['cityNotFound'] = true;
        return 0;
    }

    $query = "
        INSERT INTO nerdygadgets.people 
        (Fullname,PreferredName,SearchName,IsPermittedToLogon,LogonName,IsExternalLogonProvider,HashedPassword,
        IsSystemUser,IsEmployee,IsSalesperson,EmailAddress,LastEditedBy,ValidFrom,ValidTo) 
        VALUES(?,?,?,1,?,0,?,0,0,0,?,1,TIMESTAMP(?),TIMESTAMP('9999-12-31','23:59:59'));
    ";
    $statement = mysqli_prepare($databaseConnection, $query);
    mysqli_stmt_bind_param($statement, 'sssssss', $name, $name, $name, $name, $hashedPassword, $email, $timeStamp);
    mysqli_stmt_execute($statement);
    $R = mysqli_stmt_get_result($statement);

    $query = "
        INSERT INTO nerdygadgets.people_address
        (PersonID, Voornaam, Tussenvoegsel, Achternaam, CityID, Straatnaam, Huisnummer, Postcode) 
        VALUES((SELECT PersonID FROM people WHERE EmailAddress = ?),
        ?,?,?,?,?,?,?);
    ";
    $statement = mysqli_prepare($databaseConnection, $query);
    mysqli_stmt_bind_param($statement, 'ssssisss', $email, $voornaam, $tussenvoegsel, $achternaam, $R2[0]["CityID"], $straat, $huisnmr, $postcode);
    mysqli_stmt_execute($statement);
    
    $_SESSION['successfulRegister'] = true;
    return $R;
}

function changeAddress($user, $straat, $huisnummer, $postcode, $plaats, $email, $databaseConnection) {

    $query = "
        SELECT CityID FROM cities WHERE CityName = ?;
    ";
    $statement = mysqli_prepare($databaseConnection, $query);
    mysqli_stmt_bind_param($statement, 's', $plaats);
    mysqli_stmt_execute($statement);
    $R2 = mysqli_stmt_get_result($statement);
    $R2 = mysqli_fetch_all($R2, MYSQLI_ASSOC);

    if ($R2 == []) {
        return 0;
    }

    $query = "
        REPLACE INTO nerdygadgets.people_address
        (PersonID, Voornaam, Tussenvoegsel, Achternaam, PersonAddressID, CityID, Straatnaam, Huisnummer, Postcode) 
        VALUES(?,?,?,?,?,?,?,?,?);
    ";
    $statement = mysqli_prepare($databaseConnection, $query);
    mysqli_stmt_bind_param($statement, 'isssiisss', $user["PersonID"], $user["Voornaam"], $user["Tussenvoegsel"], $user["Achternaam"], $user["PersonAddressID"],$R2[0]["CityID"], $straat, $huisnummer, $postcode);
    mysqli_stmt_execute($statement);
    
    $query = "
        SELECT *
        FROM nerdygadgets.People P JOIN people_address USING(PersonID)
        WHERE P.EmailAddress = ?
    ";
    $statement = mysqli_prepare($databaseConnection, $query);
    mysqli_stmt_bind_param($statement, "s", $email);
    mysqli_stmt_execute($statement);
    $R = mysqli_stmt_get_result($statement);
    $R = mysqli_fetch_all($R, MYSQLI_ASSOC);

    $query = "
        SELECT nerdygadgets.cities.*, Straatnaam, Huisnummer, Postcode
        FROM nerdygadgets.cities JOIN people_address USING(CityID)
        WHERE PersonID = ?;
    ";
    $statement = mysqli_prepare($databaseConnection, $query);
    mysqli_stmt_bind_param($statement, "i", $R[0]["PersonID"]);
    mysqli_stmt_execute($statement);
    $R2 = mysqli_stmt_get_result($statement);
    $R2 = mysqli_fetch_all($R2, MYSQLI_ASSOC);

    $_SESSION['addresses'] = $R2[0];

    return $R;
}

function addCity($straat, $huisnummer, $postcode, $plaats, $databaseConnection) {
    // deze functie heeft de volgende stappen:
    // 1. check of er al een rij is in de database met de gegeven parameters
    // 2. zo ja, return 0
    // 3. zo nee, maak deze dan aan en return dan 1
    // 4. als iets mis gaat return dan -1
}

function changeStorageBycart($cart, $databaseConnection) {
    // verander stock met cart
    // cart is een multidimensionale array 
    $valueString = "";
    foreach($cart AS $StockItemID => $product) {
        $valueString .= "(".$StockItemID.",".($product["StockItem"]["QuantityOnHand"]-$product["Count"])."),";
    }
    $valueString = substr($valueString, 0, -1);
    $query = "
        INSERT INTO StockItemHoldings (StockItemID, QuantityOnHand)
        VALUES".$valueString."
        ON DUPLICATE KEY UPDATE
        QuantityOnHand = VALUES(QuantityOnHand)
    ";

    $statement = mysqli_prepare($databaseConnection, $query);
    mysqli_stmt_execute($statement);
    $R = mysqli_stmt_get_result($statement);
    return $R;
}

function processOrder($cart, $user, $name, $databaseConnection) {
    $timeStamp = date("Y-m-d H:i:s");
    $R = [["CityID"=>$user['CityID']]];

    if ($user['CityID'] == false) { // we moeten de CityID opzoeken van een stad als de gebruiker niet is ingelogd omdat we dit dan nooit eerder hebben gedaan
        $query = "                   
            SELECT CityID FROM Cities WHERE CityName = ?
        ";

        $statement = mysqli_prepare($databaseConnection, $query);
        mysqli_stmt_bind_param($statement, 's', $user['CityName']);
        mysqli_stmt_execute($statement);
        $R = mysqli_stmt_get_result($statement);
        $R = mysqli_fetch_all($R, MYSQLI_ASSOC);
        if ($R = []) return [];
    }
    
    $preQueryTime = date("Y-m-d H:i:s");

    $Query = "
        INSERT INTO customers_archive
        SELECT * FROM Customers
        WHERE CustomerName = '".$name."' AND PrimaryContactPersonID = ".$user['PersonID'].";
    ";
    $statement = mysqli_prepare($databaseConnection, $Query);
    mysqli_stmt_execute($statement);
    $R2 = mysqli_stmt_get_result($statement);

    $query = "                   
        INSERT INTO Customers
        (CustomerName,BillToCustomerID,CustomerCategoryID,BuyingGroupID,PrimaryContactPersonID,AlternateContactPersonID,
        DeliveryMethodID,DeliveryCityID,PostalCityID,CreditLimit,AccountOpenedDate,StandardDiscountPercentage,IsStatementSent,IsOnCreditHold,
        PaymentDays,PhoneNumber,FaxNumber,WebsiteURL,DeliveryAddressLine1,DeliveryAddressLine2,DeliveryPostalCode,DeliveryLocation,
        PostalAddressLine1,PostalAddressLine2,PostalPostalCode,LastEditedBy,ValidFrom,ValidTo) 
        VALUES(?,1,9,1,?,1,3,?,?,5000,TIMESTAMP(?),0,0,0,7,'','','','Building',?,?,?,?,?,?,1,TIMESTAMP(?),TIMESTAMP('9999-12-31'))
        ON DUPLICATE KEY UPDATE
        CustomerName = VALUES(CustomerName),
        BillToCustomerID = VALUES(BillToCustomerID),
        CustomerCategoryID = VALUES(CustomerCategoryID),
        BuyingGroupID = VALUES(BuyingGroupID),
        PrimaryContactPersonID = VALUES(PrimaryContactPersonID),
        AlternateContactPersonID = VALUES(AlternateContactPersonID),
        DeliveryMethodID = VALUES(DeliveryMethodID),
        DeliveryCityID = VALUES(DeliveryCityID),
        PostalCityID = VALUES(PostalCityID),
        CreditLimit = VALUES(CreditLimit),
        AccountOpenedDate = VALUES(AccountOpenedDate),
        StandardDiscountPercentage = VALUES(StandardDiscountPercentage),
        IsStatementSent = VALUES(IsStatementSent),
        IsOnCreditHold = VALUES(IsOnCreditHold),
        PaymentDays = VALUES(PaymentDays),
        PhoneNumber = VALUES(PhoneNumber),
        FaxNumber = VALUES(FaxNumber),
        WebsiteURL = VALUES(WebsiteURL),
        DeliveryAddressLine1 = VALUES(DeliveryAddressLine1),
        DeliveryAddressLine2 = VALUES(DeliveryAddressLine2),
        DeliveryPostalCode = VALUES(DeliveryPostalCode),
        DeliveryLocation = VALUES(DeliveryLocation),
        PostalAddressLine1 = VALUES(PostalAddressLine1),
        PostalAddressLine2 = VALUES(PostalAddressLine2),
        PostalPostalCode = VALUES(PostalPostalCode),
        LastEditedBy = VALUES(LastEditedBy),
        ValidFrom = VALUES(ValidFrom),
        ValidTo = VALUES(ValidTo)
    ";     
    
    $deliveryAddressLine2 = $user['Straatnaam']." ".$user['Huisnummer'];
    $deliveryLocation = "E6100000010CB5C93C4DC1E8454074C23A4492BA52C0";
    $postalAddressLine1 = "PO Box 1";
    $statement = mysqli_prepare($databaseConnection, $query);
    mysqli_stmt_bind_param($statement, 'siiississsss', $name, $user['PersonID'], $R[0]['CityID'], $R[0]['CityID'], $timeStamp,
        $$deliveryAddressLine2, $user['Postcode'], $deliveryLocation, $postalAddressLine1, $user['CityName'], $user['Postcode'], $timeStamp
    );
    mysqli_stmt_execute($statement);
    $R = mysqli_stmt_get_result($statement);
    $postQueryTime = date("Y-m-d H:i:s");

    $customerID = mysqli_insert_id($databaseConnection);

    $query = "
        INSERT INTO Orders
        (CustomerID,SalespersonPersonID,PickedByPersonID,ContactPersonID,BackorderOrderID,OrderDate,ExpectedDeliveryDate,
        CustomerPurchaseOrderNumber,IsUndersupplyBackordered,PickingCompletedWhen,LastEditedBy,LastEditedWHen) 
        VALUES(?,1,1,?,1,TIMESTAMP(?),TIMESTAMP(?),1000,1,TIMESTAMP(?),1,TIMESTAMP(?))
    ";

    $statement = mysqli_prepare($databaseConnection, $query);
    mysqli_stmt_bind_param($statement, 'iissss', $customerID, $user['PersonID'], $timeStamp, $timeStamp, $timeStamp, $timeStamp);
    mysqli_stmt_execute($statement);
    $R = mysqli_stmt_get_result($statement);

    $orderID = mysqli_insert_id($databaseConnection);

    $bufferString = "";
    foreach($cart AS $StockItemID => $product) {
        $bufferString .= "("
        .$orderID.","
        .$StockItemID.","
        ."'".$product["StockItem"]["SearchDetails"]."',"
        .$product["StockItem"]["UnitPackageID"].","
        .$product["Count"].","
        .$product["StockItem"]["UnitPrice"].","
        .$product["StockItem"]["TaxRate"].","
        .$product["Count"].","
        ."TIMESTAMP('".$timeStamp."'),"
        ."1,"
        ."TIMESTAMP('".$timeStamp."')"
        ."),";
    }
    $bufferString = substr($bufferString, 0, -1);
    $_SESSION["debug"] = $bufferString;
    $query = "
        INSERT INTO orderlines (OrderID, StockItemID, Description, PackageTypeID, Quantity, UnitPrice, TaxRate, PickedQuantity, PickingCompletedWhen, LastEditedBy, LastEditedWhen)
        VALUES ".$bufferString."
    ";

    $orderlineID = mysqli_insert_id($databaseConnection);

    $statement = mysqli_prepare($databaseConnection, $query);
    mysqli_stmt_execute($statement);
    $R = mysqli_stmt_get_result($statement);

    return $orderlineID;
}

function getTemperature($databaseConnection) {
    $Query = "
        SELECT Temperature FROM coldroomtemperatures WHERE ColdRoomSensorNumber = 5;
    ";
    $statement = mysqli_prepare($databaseConnection, $Query);
    mysqli_stmt_execute($statement);
    $R = mysqli_stmt_get_result($statement);
    $R = mysqli_fetch_all($R, MYSQLI_ASSOC);
    return $R[0];
}