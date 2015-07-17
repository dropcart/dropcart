<?php
session_start();

// Required includes
require_once('includes/php/dc_connect.php');
require_once('_classes/class.database.php');
$objDB = new DB();
require_once('includes/php/dc_config.php');

// Page specific includes
require_once('_classes/class.cart.php');
require_once('includes/php/dc_functions.php');

// Start API
require_once('libraries/Api_Inktweb/API.class.php');

// Set page title, meta
$strPageTitle		= "Javscript aanzetten";
$strMetaDescription	= "Hier vind je een korte uitleg over hoe javascript kan aanzetten in de browser.";

// Start displaying HTML
require_once('includes/php/dc_header.php');


?>

<div class="row">
    <div class="col-lg-12">
        <h1>Javscript activeren</h1>
        <h2>Waarom zou je JavaScript activeren?</h2>
        <p>Als je JavaScript activeert, kun je optimaal genieten van je bezoek aan onze webshop, zonder lastig gevallen te worden door irritante foutmeldingen. De meeste moderne webbrowsers bieden een ingebouwde ondersteuning voor JavaScript.

            Kies uit onderstaande lijst je webbrowser en volg de bijbehorende stappen om in een handomdraai JavaScript te activeren. Zorg ervoor dat je de meest recente versie van je browser geïnstalleerd hebt.</p>
        <ul>
            <li><a href="#firefox">Mozilla Firefox</a></li>

            <li><a href="#ie">Microsoft Internet Explorer</a></li>

            <li><a href="#opera">Opera</a></li>

            <li><a href="#chrome">Google Chrome</a></li>

            <li><a href="#safari">Apple Safari</a></li>
        </ul>

        <h2 id="firefox">Mozilla Firefox</h2>
        <ul>
            <li>Klik op het (oranje) menu <strong>'Firefox'</strong> (Windows) of het menu <strong>'Firefox'</strong> (Mac)</li>
            <li>Kies <strong>'Opties'</strong> (Windows) of <strong>'Voorkeuren'</strong> (Mac)</li>
            <li>Kies <strong>'Inhoud'</strong> in het bovenste navigatievak</li>
            <li>Vink het selectievakje bij <strong>'JavaScript inschakelen'</strong> aan</li>
            <li style="margin-bottom: 1.5em;">Klik op <strong>'OK'</strong></li>
        </ul>
        <h2 id="ie">Microsoft Internet Explorer</h2>
        <ul>
            <li>Klik op het tandwiel rechts boven aan (of het menu <strong>'Extra'</strong>) </li>
            <li>Kies <strong>'Internetopties'</strong></li>
            <li>Klik op het tabblad <strong>'Beveiliging'</strong></li>
            <li>Klik op <strong>'Aangepast niveau'</strong></li>
            <li>Scroll naar het gedeelte over <strong>'Uitvoeren van scripts'</strong></li>
            <li>Selecteer <strong>'Inschakelen'</strong> bij <strong>'Actief uitvoeren van scripts'</strong> en <strong>'Java-applets uitvoeren in scripts'</strong></li>
            <li style="margin-bottom: 1.5em;">Klik op <strong>'OK'</strong></li>
        </ul>

        <h2 id="opera">Opera</h2>
        <ul>
            <li>Klik op het (rode) menu <strong>'Opera'</strong> (Windows) of op het menu <strong>'Opera'</strong> (Mac)</li>
            <li>Kies <strong>'Instellingen'</strong></li>
            <li>Kies<strong> 'Voorkeuren'</strong> in het bovenste navigatievak</li>
            <li>Kies <strong>'Inhoud'</strong> in de navigatielijst links</li>
            <li>Klik op <strong>'JavaScript inschakelen'</strong></li>
            <li style="margin-bottom: 1.5em;">Klik op <strong>'OK'</strong></li>
        </ul>

        <h2 id="chrome">Google Chrome</h2>
        <ul>
            <li>Klik bij de moersleutel rechts boven aan</li>
            <li>Klik op <strong>'Opties'</strong> (Windows) of selecteer <strong>'Voorkeuren'</strong> in het Chrome menu (Mac)</li>
            <li>Klik op de tab <strong>'Geavanceerde opties'</strong></li>
            <li>Klik op <strong>'Instellingen voor inhoud...'</strong> onder <strong>'Privacy'</strong></li>
            <li style="margin-bottom: 1.5em;">Kies <strong>'Alle sites toestaan om Javascript uit te voeren (aanbevolen)'</strong> onder <strong>'JavaScript'</strong></li>
        </ul>

        <h2 id="safari">Apple Safari</h2>
        <ul>
            <li>Klik op het menu <strong>'Safari'</strong> (Mac) of het tandwiel rechts boven aan (of het menu <strong>'Wijzigen'</strong>) (Windows)</li>
            <li>Kies <strong>'Voorkeuren'</strong></li>
            <li>Kies <strong>'Beveiliging'</strong></li>
            <li>Vink het selectievakje naast <strong>'JavaScript activeren'</strong> aan</li>
            <li>Klik op <strong>'JavaScript inschakelen'</strong></li>
            <li style="margin-bottom: 1.5em;">Klik op <strong>'OK'</strong></li>
        </ul>
    </div>
</div>
