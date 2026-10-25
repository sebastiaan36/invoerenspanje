<?php

/*
|--------------------------------------------------------------------------
| Veelgestelde vragen
|--------------------------------------------------------------------------
|
| Single source for every FAQ block on the site. The Vue pages receive these
| as props, and the server-rendered fallback HTML (resources/views/seo)
| prints the same questions for crawlers that don't run JavaScript.
|
*/

return [
    'home' => [
        [
            'id' => 'tijdelijk-nederlands-kenteken',
            'question' => 'Kan ik mijn auto ook tijdelijk op Nederlands kenteken in Spanje houden?',
            'answer' => 'Als toerist of bij kort verblijf is dat toegestaan. Zodra u fiscaal resident bent in Spanje (meer dan 183 dagen per jaar), bent u verplicht de auto in te schrijven. Tijdelijk rijden op een buitenlands kenteken als resident is niet toegestaan en kan leiden tot hoge boetes en inbeslagname van het voertuig.',
        ],
        [
            'id' => 'leaseauto',
            'question' => 'Wat als mijn auto nog op naam van een Nederlandse leasemaatschappij staat?',
            'answer' => 'In dat geval heeft u schriftelijke toestemming nodig van de leasemaatschappij om de auto naar het buitenland te brengen en in te schrijven. Veel leasemaatschappijen werken hier aan mee, maar de voorwaarden variëren. Wij adviseren u graag over de juiste aanpak voor uw specifieke situatie.',
            'links' => [
                ['label' => 'Neem contact op', 'href' => '/contact'],
            ],
        ],
        [
            'id' => 'doorlooptijd',
            'question' => 'Hoe lang duurt het hele traject?',
            'answer' => 'Reken op gemiddeld vier tot acht weken vanaf de exportmelding bij de RDW tot het ontvangen van het definitieve Spaanse kentekenbewijs. De ITV en de administratieve verwerking in Spanje bepalen grotendeels de doorlooptijd. Wij houden u gedurende het hele traject op de hoogte via uw persoonlijke klantenportaal.',
            'links' => [
                ['label' => 'Naar het klantenportaal', 'href' => '/login'],
            ],
        ],
    ],

    'tarieven' => [
        [
            'id' => 'documenten-aanleveren',
            'question' => 'Welke documenten moet ik aanleveren?',
            'answer' => 'Voor de matriculación (de Spaanse inschrijving) heeft u nodig: een geldig paspoort of ID-kaart, uw NIE-nummer (Spaans fiscaal identificatienummer), het Nederlandse kentekenbewijs, de exportverklaring van de RDW, een COC-document (of een Ficha Técnica) en een bewijs van adres in Spanje (empadronamiento, de inschrijving bij de gemeente, of een huurcontract). Afhankelijk van uw pakket begeleiden wij u stap voor stap bij het verzamelen van alle stukken.',
        ],
        [
            'id' => 'itv-inbegrepen',
            'question' => 'Is de ITV-keuring bij de prijs inbegrepen?',
            'answer' => 'Nee. De ITV-keuring betaalt u zelf aan de balie van het keuringsstation, circa € 150 tot € 160 afhankelijk van het station. Bij Compleet en Compleet Plus plannen wij de afspraak voor u, maar de keuring zelf is voor uw eigen rekening.',
        ],
        [
            'id' => 'ficha-tecnica',
            'question' => 'Wat is de Ficha Técnica en wanneer heb ik die nodig?',
            'answer' => 'De Ficha Técnica is het Spaanse technische rapport van uw auto. Levert u een COC-document (Certificaat van Overeenstemming) aan, dan is de Ficha Técnica niet nodig en bespaart u € 85. Zonder COC wordt de Ficha Técnica opgesteld; bij Compleet en Compleet Plus regelen wij dat, inclusief foto\'s op locatie.',
        ],
        [
            'id' => 'matriculas-temporales',
            'question' => 'Wat zijn matrículas temporales?',
            'answer' => 'Dit zijn tijdelijke groene kentekenplaten. Ze zijn niet verplicht, maar wel nodig om met een geëxporteerde auto naar de ITV te rijden en legaal te blijven rijden tot de definitieve platen er zijn. Kosten: € 150.',
        ],
        [
            'id' => 'transportkosten',
            'question' => 'Wat kost het transport van Nederland naar Spanje?',
            'answer' => 'Bij Compleet Plus regelen wij het transport via een van onze partners. De transportkosten komen apart en hangen af van ophaallocatie en bestemming. Vraag een offerte aan via de calculator, dan nemen wij het transport mee.',
            'links' => [
                ['label' => 'Bereken uw kosten en vraag een offerte aan', 'href' => '/#offerte'],
            ],
        ],
        [
            'id' => 'pakket-wisselen',
            'question' => 'Kan ik van pakket wisselen nadat ik een offerte heb aangevraagd?',
            'answer' => 'Ja, zolang de aanvraag nog niet bij de DGT is ingediend. Laat het ons weten per telefoon, via WhatsApp of via het contactformulier.',
            'links' => [
                ['label' => 'Naar het contactformulier', 'href' => '/contact'],
            ],
        ],
    ],

    'categories' => [
        [
            'id' => 'algemeen',
            'title' => 'Algemeen',
            'items' => [
                [
                    'id' => 'wanneer-spaans-kenteken-verplicht',
                    'question' => 'Wanneer ben ik verplicht mijn auto op Spaans kenteken te zetten?',
                    'answer' => 'Zodra u fiscaal resident bent in Spanje — dat wil zeggen: u verblijft meer dan 183 dagen per jaar in Spanje — bent u verplicht uw auto in te schrijven bij de DGT, de Spaanse verkeersdienst (vergelijkbaar met de RDW). Rijdt u als resident langer dan 30 aaneengesloten dagen in Spanje op een buitenlands kenteken, dan kunt u worden beboet. De handhaving is de afgelopen jaren aanzienlijk strenger geworden.',
                ],
                [
                    'id' => 'tijdelijk-rijden-als-toerist',
                    'question' => 'Kan ik mijn Nederlandse auto tijdelijk in Spanje blijven rijden als toerist?',
                    'answer' => 'Als toerist of bij een tijdelijk verblijf (minder dan 183 dagen per jaar) mag u uw Nederlandse auto gewoon in Spanje gebruiken. U hoeft de auto dan niet in te schrijven. Zodra u echter fiscaal resident wordt, vervalt dit recht.',
                ],
                [
                    'id' => 'exportkenteken-of-spaans-kenteken',
                    'question' => 'Wat is het verschil tussen een exportkenteken en een definitief Spaans kenteken?',
                    'answer' => 'Het exportkenteken is een tijdelijk Nederlands kenteken dat u ontvangt nadat u de auto heeft afgemeld bij de RDW. Het is maximaal 14 dagen geldig en bedoeld om de auto legaal naar Spanje te rijden. Het definitieve Spaanse kenteken ontvangt u na afronding van de Spaanse inschrijving bij de DGT.',
                ],
                [
                    'id' => 'doorlooptijd',
                    'question' => 'Hoe lang duurt het hele importproces?',
                    'answer' => 'Reken op gemiddeld vier tot acht weken vanaf de exportmelding bij de RDW tot het ontvangen van het definitieve Spaanse kentekenbewijs. De planning van de ITV-keuring en de verwerking bij de DGT bepalen grotendeels de doorlooptijd. Wij houden u via uw persoonlijke klantenportaal doorlopend op de hoogte.',
                ],
                [
                    'id' => 'regio',
                    'question' => 'Werken jullie alleen in de regio Costa del Sol?',
                    'answer' => 'Wij zijn gespecialiseerd in de Costa del Sol en omgeving (provincie Málaga), maar begeleiden ook klanten in andere delen van Spanje. Neem contact op om te bespreken of wij u ook in uw regio kunnen helpen.',
                    'links' => [
                        ['label' => 'Neem contact op', 'href' => '/contact'],
                    ],
                ],
                [
                    'id' => 'auto-kopen-in-nederland',
                    'question' => 'Kan ik ook een auto laten importeren die ik in Nederland koop en direct naar Spanje wil rijden?',
                    'answer' => 'Ja. U koopt de auto in Nederland, de auto wordt bij de RDW afgemeld voor export en gaat op exportkenteken naar Spanje. In het Compleet- en Compleet Plus-pakket loopt de export via een van onze partners in Nederland. Bij Compleet Plus krijgt u ook aankoopadvies (een belafspraak van 30 minuten) en regelen wij het transport van de auto; de transportkosten komen daar apart bij.',
                    'links' => [
                        ['label' => 'Pakketten vergelijken', 'href' => '/tarieven'],
                    ],
                ],
            ],
        ],
        [
            'id' => 'bpm-teruggave',
            'title' => 'BPM-teruggave',
            'items' => [
                [
                    'id' => 'wat-is-bpm',
                    'question' => 'Wat is BPM en waarom krijg ik geld terug bij export?',
                    'answer' => 'BPM staat voor Belasting van personenauto\'s en motorrijwielen. Dit is een Nederlandse belasting die wordt betaald wanneer een auto voor het eerst in Nederland op kenteken komt. Exporteert u de auto naar een ander EU-land en wordt hij daar definitief ingeschreven, dan krijgt u het deel van die belasting terug dat nog in de waarde van de auto zit: de rest-BPM. Dat kan oplopen van een paar honderd tot enkele duizenden euro\'s, afhankelijk van de oorspronkelijke BPM en de leeftijd van de auto.',
                    'links' => [
                        ['label' => 'Bereken uw BPM-teruggave', 'href' => '/bpm-calculator'],
                    ],
                ],
                [
                    'id' => 'recht-op-bpm-teruggave',
                    'question' => 'Heeft elke auto recht op BPM-teruggave?',
                    'answer' => 'Nee. De voorwaarden zijn: de auto heeft op of na 16 oktober 2006 zijn eerste Nederlandse kenteken gekregen, het is geen schadevoertuig, u meldt de auto bij de RDW af voor export en de Spaanse inschrijving vindt plaats binnen 13 weken na de exportmelding. Auto\'s die vóór 16 oktober 2006 hun eerste Nederlandse kenteken kregen, en auto\'s met schadestatus, komen niet in aanmerking.',
                ],
                [
                    'id' => 'hoogte-bpm-teruggave',
                    'question' => 'Hoe hoog is de BPM-teruggave?',
                    'answer' => 'Dat hangt af van de oorspronkelijke BPM die bij de eerste registratie is betaald en van de leeftijd van de auto in maanden. Op die BPM past de Belastingdienst een vaste afschrijvingstabel toe: hoe jonger de auto, hoe meer u terugkrijgt. Een voorbeeld: bij €6.000 oorspronkelijke BPM en een auto van precies vijf jaar oud krijgt u ca. €1.800 terug. Onze BPM-calculator haalt de oorspronkelijke BPM bij de RDW op en rekent het voor uw kenteken uit.',
                    'links' => [
                        ['label' => 'Naar de BPM-calculator', 'href' => '/bpm-calculator'],
                    ],
                ],
                [
                    'id' => 'forfaitair-of-taxatierapport',
                    'question' => 'Wat is het verschil tussen de forfaitaire methode en een taxatierapport?',
                    'answer' => 'De forfaitaire methode rekent de afschrijving uit met een vaste tabel van de Belastingdienst, op basis van de leeftijd van de auto. Dit is de eenvoudigste en meest gebruikte methode. Is de auto meer in waarde gedaald dan de tabel aanneemt, bijvoorbeeld door een hoge kilometerstand of schade, dan kunt u een taxatierapport van een erkend taxateur indienen. Dat kan tot een hogere teruggave leiden.',
                ],
                [
                    'id' => 'termijn-bpm-teruggave',
                    'question' => 'Binnen welke termijn moet ik de BPM-teruggave aanvragen?',
                    'answer' => 'De Spaanse inschrijving moet plaatsvinden binnen 13 weken na de exportmelding bij de RDW. Daarna heeft u het Spaanse kentekenbewijs nodig als bewijs voor de aanvraag bij de Belastingdienst. De Belastingdienst heeft vervolgens 8 weken de tijd om op uw verzoek te reageren. Bent u te laat, dan verliest u uw recht op teruggave.',
                ],
                [
                    'id' => 'bpm-elektrische-auto',
                    'question' => 'Geldt de BPM-teruggave ook voor elektrische auto\'s?',
                    'answer' => 'Elektrische auto\'s betaalden tot 2025 geen BPM, dus voor die auto\'s valt er bij export niets terug te krijgen. Vanaf 2025 betalen nieuwe elektrische auto\'s wel BPM; voor die auto\'s kan er bij export wel een teruggave zijn. Gebruik onze BPM-calculator om de situatie voor uw kenteken te bekijken.',
                    'links' => [
                        ['label' => 'Naar de BPM-calculator', 'href' => '/bpm-calculator'],
                    ],
                ],
                [
                    'id' => 'bpm-aanvraag-door-ons',
                    'question' => 'Regelen jullie de BPM-aanvraag voor mij?',
                    'answer' => 'Ja, in het Compleet- en Compleet Plus-pakket. Daarin loopt de export via een van onze partners in Nederland, inclusief de aanvraag van de BPM-teruggave bij de Belastingdienst. In het Basis-pakket regelt u de export in Nederland zelf.',
                    'links' => [
                        ['label' => 'Pakketten vergelijken', 'href' => '/tarieven'],
                    ],
                ],
            ],
        ],
        [
            'id' => 'itv-keuring',
            'title' => 'ITV-keuring',
            'items' => [
                [
                    'id' => 'wat-is-de-itv',
                    'question' => 'Wat is de ITV?',
                    'answer' => 'De ITV (Inspección Técnica de Vehículos) is de Spaanse APK. De auto wordt gekeurd op technische staat, uitstoot en veiligheid. Zonder goedgekeurde ITV kunt u de auto niet op een Spaans kenteken inschrijven.',
                ],
                [
                    'id' => 'itv-controle',
                    'question' => 'Wat wordt er bij de ITV gecontroleerd?',
                    'answer' => 'De ITV controleert onder meer de remmen, verlichting, stuurinrichting, uitlaatgassen, banden, carrosserie en elektrische installatie. De eisen zijn vergelijkbaar met de Nederlandse APK, maar er zijn kleine verschillen in meetmethodes en normen.',
                ],
                [
                    'id' => 'itv-afgekeurd',
                    'question' => 'Wat als mijn auto de ITV niet haalt?',
                    'answer' => 'Als de auto wordt afgekeurd, ontvangt u een lijst met gebreken. Lichte gebreken (defectos leves) houden de inschrijving niet direct tegen. Ernstige gebreken (defectos graves of muy graves) moeten worden verholpen voordat de auto opnieuw kan worden aangeboden. Wij adviseren u over de vervolgstappen en kunnen u in contact brengen met een erkend herstelbedrijf.',
                ],
                [
                    'id' => 'itv-geldigheid',
                    'question' => 'Hoe lang is de ITV-goedkeuring geldig voor een importauto?',
                    'answer' => 'De keuring bij invoer is een eenmalige goedkeuring. Daarna volgt de gewone ITV-cyclus op basis van de leeftijd van de auto: auto\'s tot 4 jaar hoeven niet gekeurd, van 4 tot 10 jaar elke 2 jaar, ouder dan 10 jaar elk jaar.',
                ],
                [
                    'id' => 'koplampen',
                    'question' => 'Moeten de koplampen worden aangepast voor Spanje?',
                    'answer' => 'Nee. In Nederland en Spanje rijdt u allebei rechts, dus de koplampen van een Nederlandse auto zijn al geschikt voor het Spaanse verkeer. Bij de ITV worden de werking en afstelling van de verlichting wel gecontroleerd.',
                ],
            ],
        ],
        [
            'id' => 'iedmt',
            'title' => 'Spaanse registratiebelasting (IEDMT)',
            'items' => [
                [
                    'id' => 'wat-is-iedmt',
                    'question' => 'Wat is de IEDMT?',
                    'answer' => 'De IEDMT (Impuesto Especial sobre Determinados Medios de Transporte), ook wel impuesto de matriculación genoemd, is de Spaanse registratiebelasting die u betaalt wanneer een auto in Spanje op kenteken komt. Het tarief hangt af van de CO₂-uitstoot en loopt van 0% voor schone auto\'s tot 14,75% van de fiscale waarde voor auto\'s met een hoge uitstoot.',
                ],
                [
                    'id' => 'iedmt-berekening',
                    'question' => 'Hoe wordt de IEDMT berekend?',
                    'answer' => 'De grondslag is de fiscale waarde van de auto in Spanje, niet de aankoopprijs of de Nederlandse catalogusprijs. De Spaanse belastingdienst (Agencia Tributaria) hanteert eigen tabellen op basis van merk, model, bouwjaar en uitvoering. Het percentage hangt af van de officiële CO₂-uitstoot: 0% tot en met 120 g/km, 4,75% van 121 tot 159 g/km, 9,75% van 160 tot 199 g/km en 14,75% vanaf 200 g/km. Onze calculator geeft u een indicatie van de te verwachten IEDMT.',
                    'links' => [
                        ['label' => 'Bereken uw totale kosten', 'href' => '/#offerte'],
                    ],
                ],
                [
                    'id' => 'iedmt-hybride-elektrisch',
                    'question' => 'Betalen hybride en elektrische auto\'s ook IEDMT?',
                    'answer' => 'Volledig elektrische auto\'s stoten geen CO₂ uit en vallen in het 0%-tarief. Voor een plug-in hybride telt de officiële CO₂-waarde: de meeste plug-in hybrides blijven onder de 120 g/km en betalen dan ook 0%. Ligt de uitstoot hoger, dan geldt 4,75% (121–159 g/km), 9,75% (160–199 g/km) of 14,75% (200 g/km en meer). Milde hybrides en gewone benzine- of dieselauto\'s vallen vaker in een van die hogere tarieven. Het exacte percentage hangt af van de gemeten CO₂-waarde van uw auto.',
                ],
                [
                    'id' => 'iedmt-vrijstelling',
                    'question' => 'Kan ik vrijstelling aanvragen voor de IEDMT?',
                    'answer' => 'Ja, in een aantal situaties. De belangrijkste voor Nederlanders: verhuist u uw hoofdverblijf naar Spanje en staat de auto al minimaal zes maanden op uw naam, dan kunt u vrijstelling aanvragen. Dat moet binnen 60 dagen na uw inschrijving in de Spaanse gemeente (empadronamiento). Daarnaast bestaan er regelingen voor grote gezinnen (familia numerosa) en mensen met een beperking; daarvoor gelden strikte voorwaarden. Wij bekijken graag of uw situatie in aanmerking komt.',
                    'links' => [
                        ['label' => 'Bereken uw kosten met vrijstelling', 'href' => '/#offerte'],
                    ],
                ],
                [
                    'id' => 'iedmt-bij-verhuizing',
                    'question' => 'Betaal ik registratiebelasting (IEDMT) als ik naar Spanje verhuis?',
                    'answer' => 'Niet als u aan de voorwaarden voor vrijstelling voldoet. Verplaatst u uw hoofdverblijf naar Spanje en staat de auto al minimaal zes maanden op uw naam, dan kunt u vrijstelling van de IEDMT aanvragen. Dat moet binnen 60 dagen na uw inschrijving bij de gemeente (empadronamiento). De vaste kosten, zoals leges, kentekenplaten, de gestoría en de ITV-keuring, betaalt u wel. Houdt u alleen een tweede woning in Spanje en blijft uw hoofdverblijf in Nederland, dan geldt deze vrijstelling niet. In het Compleet-pakket vragen wij de vrijstelling voor u aan.',
                    'links' => [
                        ['label' => 'Bereken wat u terugkrijgt', 'href' => '/#offerte'],
                    ],
                ],
            ],
        ],
        [
            'id' => 'documenten',
            'title' => 'Documenten en administratie',
            'items' => [
                [
                    'id' => 'documenten-export',
                    'question' => 'Welke documenten heb ik nodig voor de export uit Nederland?',
                    'answer' => 'Voor de exportmelding bij de RDW heeft u nodig: het Nederlandse kentekenbewijs (deel I en II), een geldig identiteitsbewijs en, bij een gefinancierde of geleaste auto, toestemming van de financier of leasemaatschappij. Na de exportmelding ontvangt u het exportkenteken en het uitschrijfbewijs.',
                ],
                [
                    'id' => 'documenten-spaanse-inschrijving',
                    'question' => 'Welke documenten heb ik nodig voor de Spaanse registratie?',
                    'answer' => 'Voor de inschrijving in Spanje heeft u onder meer nodig: het Nederlandse kentekenbewijs, het uitschrijfbewijs van de RDW, het goedgekeurde ITV-rapport, de aankoopfactuur of een ander eigendomsbewijs, uw NIE-nummer, het bewijs dat de IEDMT is betaald of vrijgesteld en een bewijs van verzekering. Wij controleren of alle documenten compleet en correct zijn voordat de aanvraag wordt ingediend.',
                ],
                [
                    'id' => 'nie-nummer',
                    'question' => 'Wat is een NIE-nummer en heb ik dat nodig?',
                    'answer' => 'Het NIE (Número de Identificación de Extranjero) is uw Spaanse identificatienummer voor buitenlanders. Dit nummer is verplicht voor vrijwel alle officiële handelingen in Spanje, ook voor het inschrijven van een auto. Heeft u nog geen NIE, vraag het dan zo snel mogelijk aan bij de Spaanse politie of via een advocaat.',
                ],
                [
                    'id' => 'gefinancierd-of-lease',
                    'question' => 'Wat als mijn auto nog gefinancierd is of op naam van een leasemaatschappij staat?',
                    'answer' => 'Bij een gefinancierde auto heeft u schriftelijke toestemming van de financier nodig om de auto te exporteren. Bij een leaseauto geldt hetzelfde voor de leasemaatschappij. Veel partijen werken hieraan mee, maar de voorwaarden verschillen. Wij adviseren u over de juiste aanpak voor uw situatie.',
                ],
                [
                    'id' => 'auto-op-naam-van-bv',
                    'question' => 'Wat als de auto op naam staat van mijn Nederlandse BV?',
                    'answer' => 'Dat is mogelijk, maar vraagt om aanvullende documenten: een KvK-uittreksel, een machtiging namens de BV en eventueel bewijs van zakelijk gebruik in Spanje. De procedure is iets ingewikkelder, maar goed uitvoerbaar. Neem contact op voor advies op maat.',
                    'links' => [
                        ['label' => 'Neem contact op', 'href' => '/contact'],
                    ],
                ],
            ],
        ],
        [
            'id' => 'kosten',
            'title' => 'Kosten en pakketten',
            'items' => [
                [
                    'id' => 'totale-kosten',
                    'question' => 'Wat zijn de totale kosten voor het invoeren van mijn auto?',
                    'answer' => 'De totale kosten bestaan uit drie delen. Ten eerste de vaste prijs van uw pakket: Basis €395, Compleet €895 of Compleet Plus €1.495. Ten tweede de vaste kosten in Spanje: leges van de DGT (€125), Spaanse kentekenplaten (€35), de gestoría, het Spaanse administratiekantoor (€181,50), de ITV-keuring (ca. €150–160), de Ficha Técnica, een technisch rapport (€85, vervalt als u een COC-document aanlevert) en eventueel tijdelijke kentekenplaten (€150, optioneel). Ten derde de IEDMT, de Spaanse registratiebelasting, die afhangt van de CO₂-uitstoot en waarvoor bij verhuizing onder voorwaarden vrijstelling mogelijk is. De BPM-teruggave gaat daar weer vanaf.',
                    'links' => [
                        ['label' => 'Bereken wat u terugkrijgt', 'href' => '/#offerte'],
                        ['label' => 'Alle tarieven', 'href' => '/tarieven'],
                    ],
                ],
                [
                    'id' => 'basis-pakket',
                    'question' => 'Wat is inbegrepen in het Basis-pakket?',
                    'answer' => 'Met Basis (€395) houdt u zelf de regie. U regelt zelf de export in Nederland, levert de gevraagde documenten en het COC-document aan (zonder COC kost de Ficha Técnica €85 extra) en regelt zelf de ITV-afspraak en de keuring. Wij verwerken de aanvraag bij de DGT en leveren de groene tijdelijke platen en daarna het officiële kenteken met de platen; die haalt u bij ons op. De Spaanse vaste kosten en de IEDMT zijn niet inbegrepen.',
                    'links' => [
                        ['label' => 'Pakketten vergelijken', 'href' => '/tarieven'],
                    ],
                ],
                [
                    'id' => 'compleet-of-compleet-plus',
                    'question' => 'Wat is het verschil tussen het Compleet- en Compleet Plus-pakket?',
                    'answer' => 'Compleet (€895) bevat alles uit Basis, plus: de export via een van onze partners in Nederland, de Ficha Técnica als het COC-document ontbreekt (wij maken de foto\'s op locatie), een doorverwijzing naar een aangesloten verzekeraar en wij plannen de ITV-afspraak voor u. Compleet Plus (€1.495) bevat alles uit Compleet, plus: aankoopadvies als u de auto nog in Nederland koopt (een belafspraak van 30 minuten), wij komen bij u thuis voor de foto\'s en documenten, wij regelen het transport van de auto (de transportkosten zelf komen apart), wij kijken mee met uw verzekering en wij gaan namens u naar de ITV-keuring.',
                    'links' => [
                        ['label' => 'Pakketten vergelijken', 'href' => '/tarieven'],
                    ],
                ],
                [
                    'id' => 'verborgen-kosten',
                    'question' => 'Zijn er verborgen kosten?',
                    'answer' => 'Nee. Onze pakketprijs is vast. Daarnaast zijn er kosten die niet in het pakket zitten: de Spaanse overheidskosten (leges, kentekenplaten en de IEDMT), de gestoría, de ITV-keuring en, bij Compleet Plus, de transportkosten. Die staan apart en noemen wij vooraf, zodat u weet waar u aan toe bent.',
                    'links' => [
                        ['label' => 'Alle tarieven', 'href' => '/tarieven'],
                    ],
                ],
                [
                    'id' => 'betaling-itv-en-overige-kosten',
                    'question' => 'Hoe betaal ik de ITV-keuring en de overige kosten?',
                    'answer' => 'De ITV-keuring betaalt u zelf rechtstreeks aan de balie van het keuringsstation (ca. €150–160, afhankelijk van het station). Hoe en wanneer u de pakketprijs en de overige kosten betaalt, staat in uw offerte.',
                    'links' => [
                        ['label' => 'Vraag een offerte aan', 'href' => '/#offerte'],
                    ],
                ],
            ],
        ],
        [
            'id' => 'na-de-registratie',
            'title' => 'Na de registratie',
            'items' => [
                [
                    'id' => 'verzekering',
                    'question' => 'Moet ik mijn verzekering aanpassen na de Spaanse inschrijving?',
                    'answer' => 'Ja. Zodra de auto op een Spaans kenteken staat, heeft u een Spaanse verzekering nodig. Uw Nederlandse verzekering vervalt automatisch. Regel tijdig een Spaanse WA-verzekering, of een uitgebreidere dekking, zodat er geen gat in uw dekking ontstaat. In het Compleet-pakket verwijzen wij u door naar een aangesloten verzekeraar.',
                ],
                [
                    'id' => 'rijbewijs',
                    'question' => 'Moet ik mijn rijbewijs omwisselen naar een Spaans rijbewijs?',
                    'answer' => 'Als EU-burger hoeft u uw rijbewijs niet om te wisselen zolang uw Nederlandse rijbewijs geldig is. Verloopt het, dan kunt u het in Spanje verlengen via de DGT zonder opnieuw examen te doen. Woont u permanent in Spanje, dan is het wel praktisch om uw rijbewijs op uw Spaanse adres te laten registreren.',
                ],
                [
                    'id' => 'verkopen-in-spanje',
                    'question' => 'Wat als mijn auto later wordt verkocht in Spanje?',
                    'answer' => 'Een auto die eenmaal op Spaans kenteken staat, verkoopt u in Spanje als een gewone Spaanse auto. De eigendomsoverdracht (transferencia) loopt via de DGT, meestal met hulp van een gestoría, en is relatief eenvoudig. De leges zijn circa €55.',
                ],
                [
                    'id' => 'terug-naar-nederland',
                    'question' => 'Kan ik de auto na inschrijving in Spanje nog terugbrengen naar Nederland?',
                    'answer' => 'Technisch wel, maar dan begint het proces opnieuw: u schrijft de auto uit in Spanje en registreert hem opnieuw in Nederland. Daarbij betaalt u in Nederland BPM over de huidige waarde van de auto. Dat is doorgaans meer dan de BPM-teruggave die u bij export heeft ontvangen.',
                ],
            ],
        ],
    ],
];
