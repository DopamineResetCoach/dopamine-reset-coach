<?php
/**
 * Title: Pagina: Privacybeleid
 * Slug: valkenisse/page-privacy
 * Categories: valkenisse-paginas
 * Post Types: page
 * Description: Privacybeleid afgestemd op deze website (controleren en aanvullen).
 *
 * @package Valkenisse
 */

$valkenisse_sections = array(
	array( null, 'Restaurant Valkenisse vindt uw privacy belangrijk. In dit privacybeleid leest u welke persoonsgegevens wij via deze website verwerken, waarom en hoe lang. <strong>[Laat deze tekst controleren en vul de gegevens tussen blokhaken aan.]</strong>' ),
	array( 'Wie zijn wij?', 'Restaurant Valkenisse, Valkenisseweg 76, 4373 RP Biggekerke, is verantwoordelijk voor de verwerking van uw gegevens. U bereikt ons via 0118-566255 of restvalk@zeelandnet.nl. KvK-nummer: [invullen].' ),
	array( 'Welke gegevens verwerken wij?', 'Als u een formulier op deze website invult (reservering, studio-aanvraag, aanvraag voor een feest of contact), ontvangen wij de gegevens die u invult: naam, e-mailadres, telefoonnummer, gewenste data, aantal personen en uw opmerkingen of bericht.' ),
	array( 'Waarvoor gebruiken wij uw gegevens?', 'Uitsluitend om uw aanvraag of reservering te behandelen en contact met u op te nemen. Wij gebruiken uw gegevens niet voor nieuwsbrieven of reclame en verkopen ze nooit aan derden.' ),
	array( 'Hoe lang bewaren wij uw gegevens?', 'Aanvragen via de website worden automatisch na 90 dagen uit de website verwijderd. E-mails die wij naar aanleiding van uw aanvraag ontvangen of versturen, bewaren wij niet langer dan nodig is voor de afhandeling [en de wettelijke bewaarplicht voor de administratie].' ),
	array( 'Wie hebben toegang tot uw gegevens?', 'Alleen medewerkers van Restaurant Valkenisse. De website wordt gehost door [naam hostingpartij], met wie wij een verwerkersovereenkomst hebben. [Gebruikt u een boekingsplatform voor de studio’s? Vermeld dat hier.]' ),
	array( 'Cookies en externe diensten', 'Deze website plaatst geen tracking- of advertentiecookies. Kaarten van Google Maps worden niet automatisch geladen: pas als u op “Route plannen” klikt, opent u de website van Google en is het privacybeleid van Google van toepassing. Meer informatie leest u in ons <a href="/cookiebeleid/">cookiebeleid</a>.' ),
	array( 'Uw rechten', 'U heeft het recht om uw gegevens in te zien, te laten corrigeren of te laten verwijderen. Stuur hiervoor een e-mail naar restvalk@zeelandnet.nl. Heeft u een klacht over de manier waarop wij met uw gegevens omgaan? Dan kunt u die indienen bij de Autoriteit Persoonsgegevens.' ),
	array( 'Wijzigingen', 'Wij kunnen dit privacybeleid aanpassen. Laatst bijgewerkt: [datum].' ),
);
foreach ( $valkenisse_sections as [ $valkenisse_title, $valkenisse_text ] ) {
	if ( $valkenisse_title ) {
		echo valkenisse_h( $valkenisse_title, 2, 'x-large' ) . "\n\n";
	}
	echo valkenisse_p( $valkenisse_text ) . "\n\n";
}
