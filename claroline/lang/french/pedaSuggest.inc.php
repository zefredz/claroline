<?php // $Id$ 

unset($titreBloc);
unset($titreBlocNotEditable);
unset($questionPlan);
unset($info2Say);

$titreBloc[] ="Description";
$titreBlocNotEditable[] = FALSE;
$questionPlan[] = "Quelle est la place et la spécificité du cours dans le programme&nbsp;?
Existe-t-il des cours pré-requis&nbsp;?
Quels sont les liens avec les autres cours&nbsp;?";
$info2Say[] = "
Information permettant d'identifier le cours 
(sigle, titre, nombre d'heure de cours, de TP, ...) 
et l'enseignant (nom, prénom, bureau, tél, e-mail, disponibilités éventuelles).
<br>
Présentation générale du cours dans le programme.";


$titreBloc[] ="Compétences et Objectifs";
$titreBlocNotEditable[] = TRUE;
$info2Say[] = "Présentation des objectifs généraux et spécifiques du cours, des compétences auxquelles la maîtrise de tels objectifs pourrait conduire."; // JCC 
$questionPlan[] = "Quels sont les apprentissages visés par l'enseignement&nbsp;?
<br>
Au terme du cours, quelles sont les compétences, les capacités et les connaissances que les étudiants seront en mesure de maîtriser, de mobiliser&nbsp;?";



$titreBloc[] ="Contenu du cours";
$titreBlocNotEditable[] = TRUE;
$questionPlan[] = "Quelle est l'importance des différents contenus à traiter dans le cadre du cours&nbsp;?
Quel est le niveau de difficulté de ces contenus&nbsp;? 
Comment structurer l'ensemble de la matière&nbsp;?  
Quelle sera la séquence des contenus&nbsp;? 
Quelle sera la progression dans les contenus&nbsp;?";
$info2Say[] = "Présentation de la table des matières du cours, de la structuration du 
contenu, de la progression et du calendrier";



$titreBloc[] ="Activités d'enseignement-apprentissage";
$titreBlocNotEditable[] = TRUE;
$questionPlan[] = "Quelles méthodes et quelles activités vont-elles favoriser l'atteinte des 
objectifs définis pour le cours&nbsp;?
Quel est le calendrier des activités&nbsp;?";
$info2Say[] = "Présentation des activités prévues 
(exposés magistraux, participation attendue des étudiants, travaux pratiques, 
séances de laboratoire, visites, recueil d'informations sur le terrain, 
...).";

$titreBloc[] ="Supports";
$titreBlocNotEditable[] = TRUE;
$questionPlan[] = "Existe-t-il un support de cours ? Quel type de support vais-je privilégier ? 
Ouvert ? Fermé ?"; // JCC 
$info2Say[] = "Présentation du ou des supports de 
cours. Présentation de la bibliographie, du portefeuille de documents ou 
d'une bibliographie complémentaire.";


$titreBloc[] ="Ressources humaines et physiques";
$titreBlocNotEditable[] = TRUE;
$questionPlan[] = "
Quelles sont les ressources humaines et physiques disponibles&nbsp;?
Quelle sera la nature de l'encadrement&nbsp;? 
Que peuvent attendre les étudiants de l'équipe d'encadrement ou de l'encadrement de l'enseignant&nbsp;?";
$info2Say[] = "Présentation des autres 
enseignants qui vont encadrer le cours (assistants, chercheurs, 
étudiants-moniteurs,...), des disponibilités des personnes, des locaux et des 
équipements ou matériel informatique disponibles.";

$titreBloc[] ="Modalités d'évaluation";
$titreBlocNotEditable[] = TRUE;
$questionPlan[] = "Quelles modalités d'évaluation choisir afin d'évaluer l'atteinte des objectifs définis au début du cours&nbsp;?  
Quelles sont les stratégies d'évaluation mises en place afin de permettre à l'étudiant d'identifier d'éventuelles lacunes avant la session d'examens&nbsp;?";
$info2Say[] = "Précisions quant aux moyens d'évaluation (examens écrits, oraux, projets, 
travaux à remettre, ...), quant au(x) moment(s) d'évaluation formative prévu(s), 
échéances pour la remise des travaux, aux critères d'évaluation, éventuellement 
la pondération des critères ou des catégories de critères.";


?>