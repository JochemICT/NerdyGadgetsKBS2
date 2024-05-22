# NerdyGadgets

## Installeer stappenplan:
1. Installeer XAMPP.
2. Upgrade de MySql in XAMPP van MariaDB 10.4.x naar MariaDB 11.2.2 of hoger.
3. MariaDB zal prompten naar een wachtwoord, bedenk deze en onthoud hem.
4. Clone NerdyGadgets in de htdocs folder van XAMPP.
5. Zet in XAMPP beide Apache en MySQL aan.
6. Download de NerdyGadgets database.
7. Voer de volgende MySQL query's uit:
   
``` SQL
CREATE TABLE nerdygadgets.people_address (
	PersonAddressID INT PRIMARY KEY AUTO_INCREMENT,
	PersonID int NOT NULL,
	Voornaam varchar(255) NOT NULL,
	Tussenvoegsel varchar(255) NULL,
	Achternaam varchar(255) NOT NULL,
	CityID int NOT NULL,
	Straatnaam varchar(255) NOT NULL,
	Huisnummer varchar(255) NOT NULL,
	Postcode varchar(255) NOT NULL,
	Plaats varchar(255) NOT NULL
);

INSERT INTO nerdygadgets.coldroomtemperatures (ColdRoomSensorNumber, RecordedWhen, Temperature, ValidFrom, ValidTo)
VALUES (
    5, 
    TIMESTAMP("2023-12-21 23:59:24"), 
    6, 
    TIMESTAMP("2023-12-21 23:59:24"), 
    TIMESTAMP("9999-12-31 23:59:59")
);

INSERT INTO nerdygadgets.customercategories (CustomerCategoryID, CustomerCategoryName, LastEditedBy, ValidFrom, ValidTo)
VALUES (
    9, 
    "Webshop Customer", 
    1, 
    TIMESTAMP("2023-11-30"), 
    TIMESTAMP("9999-12-31", 
    "23:59:59")
);
```
7. Maak een bestand aan genaamd ```secret.php``` in de globale NerdyGadgets folder en stop het volgende er in:
``` PHP
<?php
function getSQLPassword(){
    $mySQL_password = ""; // Het gekozen wachtwoord voor de MariaDB moet tussen de aanhalingstekens
    return $mySQL_password;
}

```
8. In de browser bezoek [deze website](http://localhost/).
9. Navigeer hier naar de NerdyGadgets folder.
10. Op deze URL staat de NerdyGadgets website.
