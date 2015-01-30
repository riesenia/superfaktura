# Superfaktura API PHP klient

[![Latest Version](https://img.shields.io/packagist/v/rshop/superfaktura.svg?style=flat-square)](https://packagist.org/packages/rshop/superfaktura)
[![Total Downloads](https://img.shields.io/packagist/dt/rshop/superfaktura.svg?style=flat-square)](https://packagist.org/packages/rshop/superfaktura)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

PHP klient k API rozhraniu online ekonomického systému [SuperFaktúra](http://www.superfaktura.sk/o-nas/).

## Inštalácia

Jednoducho cez command line: `composer require rshop/superfaktura`

Alebo pridaním do *composer.json*:

```json
{
    "require": {
        "rshop/superfaktura": "~1.0"
    }
}
```

## Úvodom

Jednotlivé entity je možné vytvárať prostredníctvom triedy Superfaktura.

```php
use Rshop\Synchronization\Superfaktura;

$superfaktura = new Superfaktura('EMAIL', 'API_KEY');
```

## Vytvorenie faktúry

Novú faktúru je možné vytvoriť metódou *createInvoice*.

```php
$invoice = $superfaktura->createInvoice(array(
    'name' => 'Názov faktúry',
    'invoice_no_formatted' => '12345'
));

// parametre je možné nastaviť aj zadaním požadovaného atribútu
$invoice['already_paid'] = true;
```

Dostupné atribúty:
* **already_paid** - bola už faktúra uhradená? *true/false*
* **created** - dátum vystavenia
* **comment** - komentár
* **constant** - konštantný symbol
* **delivery** - dátum dodania
* **delivery_type** - spôsob dodania, číselník hodnôt
* **deposit** - uhradená záloha
* **discount** - zľava v %
* **due** - dátum splatnosti
* **estimate_id** - ID cenovej ponuky, na základe ktorej je faktúra vystavená
* **header_comment** - text nad položkami faktúry
* **internal_comment** - interná poznánka, nezobrazuje sa klientovi
* **invoice_currency** - mena, v ktorej je faktúra vystavená. Možnosti: *EUR, USD, GBP, HUF, CZK, PLN, CHF, RUB*
* **invoice_no_formatted** - číslo faktúry
* **issued_by** - faktúru vystavil
* **issued_by_phone** - faktúru vystavil telefón
* **issued_by_email** - faktúru vystavil email
* **name** - názov faktúry
* **payment_type** - spôsob úhrady, číselník hodnôt
* **proforma_id** - ID proforma faktúry, na základe ktorej sa vystavuje ostrá faktúra (ostrá faktúra tak preberie údaje o uhradenej zálohe)
* **rounding** - spôsob zaokrúhľovania DPH: *document* - za celý dokument, *item* - po položkaćh (predvolená hodnota)
* **specific** - špecifický symbol
* **sequence_id** - ID číselníka
* **type** - typ faktúry: *regular* - bežná faktúra, *proforma* - zálohová faktúra, *cancel* - dobropis, *estimate* - cenová ponuka, *order* - prijatá objednávka
* **variable** - variabilný symbol

### Nastavenie zákazníka

Zákazníka na faktúru je možné pridať metódou *setClient*.

```php
$invoice->setClient(array(
    'name' => 'Meno zákazníka'
));

// parametre je možné nastaviť aj zadaním požadovaného atribútu
$client = $invoice->getClient();
$client['city'] = 'Mesto';
```

Dostupné atribúty:
* **address** -  adresa
* **bank_account** -  bankový účet
* **city** -  mesto
* **comment** -  komentár
* **country_id** -  ID krajiny, číselník krajín
* **country** -  vlastný názov krajiny
* **delivery_address** -  dodacia adresa
* **delivery_city** -  dodacie mesto
* **delivery_country** -  vlastná dodacia krajina
* **delivery_country_id** -  ID dodacej krajiny
* **delivery_name** -  názov klienta pre dodanie
* **delivery_zip** -  dodacie PSČ
* **dic** -  DIČ
* **email** -  email
* **fax** -  fax
* **ic_dph** -  IČ DPH
* **ico** -  IČO
* **name** -  názov klienta
* **phone** -  telefón
* **zip** -  PSČ

### Pridanie položky

Položku na faktúru je možné pridať metódou *addItem*.

```php
$invoice->addItem(array(
    'name' => 'Názov položky',
    'quantity' => 1,
    'unit_price' => 40.83,
    'tax' => 20
));
```

Dostupné atribúty:
* **name** -  názov položky
* **description** -  popis
* **quantity** -  množstvo
* **unit** -  jednotka
* **unit_price** -  cena bez DPH
* **tax** -  sadzba DPH v %
* **stock_item_id** -  ID skladovej polozky
* **sku** -  skladove oznacenie

### Uloženie

```php
try {
    $invoice->save();

    // $invoice obsahuje všetky parametre uloženej faktúry
    var_dump($invoice['token'], $invoice->getSummary());
}
catch (Exception $e) {
    // chyby, ktoré nastali pri komunikácii, je možné získať metódou getErrors
    var_dump($e->getErrors());
}
```

## Získanie existujúcej faktúry

Existujúcu faktúru je možné stiahnuť prostredníctvom jej ID metódou *getInvoice*.

```php
$invoice = $superfaktura->getInvoice(616575);
```

### Označenie faktúry ako odoslanej

Označenie metódou *markAsSent*. Užitočné, pokiaľ vytvorené faktúry odosielate vlastným systémom, avšak chcete toto odoslanie evidovať aj v SuperFaktúre.

```php
$invoice->markAsSent(array('email' => 'email@zakaznika.sk'));
```

Dostupné atribúty:
* **email** - mailová adresa, kam bola faktúra odoslaná
* **subject** - predmet emailu
* **body** - text emailu

### Odoslanie faktúry emailom

Odoslanie metódou *sendByEmail*. Nenastavené atribúty sa nastavia automaticky podľa nastavení v SuperFaktúre.

```php
$invoice->sendByEmail(array('to' => 'email@zakaznika.sk'));
```

Dostupné atribúty:
* **to** - na akú emailovú adresu sa má faktúra odoslať (povinné)
* **cc** - otvorená kópia (*array*)
* **bcc** - skrytá kópia (*array*)
* **subject** - predmet emailu
* **body** - text emailu

### Zaplatenie faktúry

Pridanie úhrady k faktúre metódou *pay*.

```php
$invoice->pay(array('amount' => 10.34));
```

Dostupné atribúty:
* **amount** - uhradená suma (povinné)
* **currency** - mena úhrady, predvolené EUR
* **date** - dátum úhrady, predvolený aktuálny dátum
* **payment_type** - spôsob úhrady, predvolený typ transfer, možné hodnoty *cash, transfer, credit, paypal, cod*

### Získanie linky k PDF

Adresu, na ktorej je možné stiahnuť PDF faktúru, je možné získať metódou *getPdf*.

```php
$invoice->getPdf();
```

### Zmazanie faktúry

Odstránenie faktúry je možné metódou *delete*.

```php
$invoice->delete();
```

## Spustenie testov

Pre testovanie je potrebné najprv skopírovať súbor *TestConfig.php.tpl* na *TestConfig.php* a vyplniť
testovací *email* a *API kľúč*. Následne cez command line:

    $ cd path/to/rshop/superfaktura
    $ composer install
    $ vendor/bin/phpspec run