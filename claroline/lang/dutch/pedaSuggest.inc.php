<?php // $Id$ 

unset($titreBloc);
unset($titreBlocNotEditable);
unset($questionPlan);
unset($info2Say);

$titreBloc[] ="Beschrijving";
$titreBlocNotEditable[] = FALSE;
$questionPlan[] = "Wat is de positie en het specifiek karakter van de cursus in het programma ?
Is er vereiste voorkennis ?
Is er overeenstemming met andere cursussen ?";
$info2Say[] = "
Informatie om de cursus te identificeren 
(beginletter, titel, aantal lesuren, praktische oefeningen, ...) 
en de docent te identificeren (naam, voornaam, bureel, telefoonnummer, e-mail, beschikbaarheid).
<br>
Algemene benaming van de cursus in het programma.";


$titreBloc[] ="Voorkennis en doelstellingen";
$titreBlocNotEditable[] = TRUE;
$info2Say[] = "Algemene en specifieke doeleinden van de cursus
	en van de competenties waartoe het studeren van de cursus kan leiden.";
$questionPlan[] = "Welke vorming beoogt het volgen van deze cursus ?
<br>
Aan het einde van de cursus, wat zijn de competenties, capaciteiten en kennis die de studenten zullen beheersen, kunnen gebruiken ?";



$titreBloc[] ="Cursusinhoud";
$titreBlocNotEditable[] = TRUE;
$questionPlan[] = "Wat is het belang van de verschillende inhouden die in het kader van de cursus behandeld worden ?
Wat is de moelijkheidsgraad van deze inhouden ? 
Hoe het geheel van de cursus structureren ?  
Wat is de volgorde van de verschillende inhouden ? 
Wat is de progressie in deze inhouden ?";
$info2Say[] = "Inhoud van de cursus, structureren van deze inhoud, van de progressie en van de kalender";



$titreBloc[] ="Onderwijsactiviteiten";
$titreBlocNotEditable[] = TRUE;
$questionPlan[] = "Welke methodes en activiteiten zullen het bereiken van de doelstellingen van deze cursus vergemakkelijken ?
Wat is de kalender van de geplande activiteiten ?";
$info2Say[] = "Geplande activiteiten (lessen, studentenparticipaties, praktische oefeningen, laboratorium, bezoeken, opzoeken van informatie, ...).";

$titreBloc[] ="Ondersteuning";
$titreBlocNotEditable[] = TRUE;
$questionPlan[] = "Bestaat er al ondersteuning voor deze cursus ? Welke type van ondersteuning geef ik voorrang ? 
Open ? Gesloten ?";
$info2Say[] = "Ondersteuning van de cursus, bibliografie, overzicht van de beschikbare documenten, aanvullende bibliografie.";


$titreBloc[] ="Informatiebronnen";
$titreBlocNotEditable[] = TRUE;
$questionPlan[] = "
Wat zijn de gangbare fysieke hulpmiddelen ?
Wat is de manier van begeleiden ? 
Wat kunnen de studenten verwachten van het begeleidingsteam of de begeleiding door de docent ?";
$info2Say[] = "Begeleiders van de cursus (assistenten, vorsers, studenten-monitors, ...), hun beschikbaarheid, de lokalen en beschikbaar materiaal.";

$titreBloc[] ="Evaluatievormen";
$titreBlocNotEditable[] = TRUE;
$questionPlan[] = "Welke evaluatievormen worden gekozen om na te gaan of de doelstellingen bereikt zijn die bij aanvang van de cursus vastgelegd zijn ?  
Welke testen worden voorzien zodat de student bij zichzelf eventuele lacunes kan identificeren vóór de aanvang van de examenperiode ?";
$info2Say[] = "Evaluatievormen (schriftelijke examens, mondelijke examens, projecten, werken, ...), over de tijdstippen van formatieve testen, datums voor het binnendienen van de opdrachten, evaluatiecriteria, eventueel de gewichten van bepaalde criteria of categorieën van criteria.";


?>
