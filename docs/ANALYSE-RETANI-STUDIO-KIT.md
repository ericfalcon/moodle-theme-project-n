# Analyse de référence — Retani Studio Kit

Date de l'analyse : 14 novembre 2026  
Dépôt observé : https://github.com/dojoVader/retani-studio-kit  
Branche principale observée : `master`  
Pull request ouverte observée : [#2 — Features/quiz UI](https://github.com/dojoVader/retani-studio-kit/pull/2)

## 1. Synthèse

Retani Studio est une référence intéressante pour l'ergonomie Moodle, en particulier le tableau de bord, la page de cours, la navigation latérale, les repères de progression et la présentation de l'administration.

Ce n'est toutefois pas une base technique directement réutilisable pour Project N :
- le `version.php` de `master` déclare Moodle 5.3 uniquement (`supported = [503, 503]`), avec une maturité beta ;
- le thème déclare `$THEME->parents = []` et possède ses propres layouts, renderers et templates : il porte donc une responsabilité de maintenance bien plus large qu'un thème enfant Boost ;
- aucune détection Iomad ni garantie de cloisonnement multi-entreprise n'a été identifiée ;
- le dépôt `master` contient des tests PHPUnit et Behat, mais aucun workflow GitHub Actions n'a été identifié dans son arborescence ;
- la PR #2 est ouverte et ajoute notamment une interface de quiz, des maquettes et des éléments de licence. Ces éléments ne sont pas encore intégrés à `master`.

**Décision pour Project N :** retenir Retani comme référence fonctionnelle et visuelle, pas comme socle ni comme source de copie de code. Conserver Boost comme parent et adapter uniquement les idées qui respectent les contrats natifs Moodle/Iomad et la matrice de compatibilité du projet.

## 2. Éléments particulièrement intéressants

### 2.1 Tableau de bord et liste des formations

Le dépôt propose un travail approfondi sur le bloc natif `block_myoverview` :
- rendu sous forme de tableau React avec filtres, tri, pagination et vues ;
- données issues du contexte exporté par le bloc natif ;
- conservation de l'état vide natif ;
- colonnes de suivi (formation, catégorie, enseignant, inscription, progression).

**À retenir pour Project N :** une liste de formations peut devenir plus lisible et exploitable qu'une grille de cartes, notamment pour les gestionnaires et les formateurs. Il faudra toutefois éviter de remplacer le composant natif complet avant d'avoir une abstraction stable et des tests de non-régression sur chaque version.

### 2.2 Page de cours

La classe `classes/output/course_header.php` construit un bandeau de cours avec :
- catégorie et nom abrégé ;
- nom de la formation ;
- contact pédagogique principal ;
- nombre de sections et d'activités visibles ;
- progression de l'utilisateur lorsque le suivi d'achèvement est réellement activé.

Le calcul de progression utilise les API de complétion Moodle et exclut les activités non visibles ou non suivies. Le composant AMD `courseprogress.js` s'appuie ensuite sur le système réactif de Moodle pour actualiser les indicateurs de section et la progression globale lorsque l'état des activités change.

**À retenir pour Project N :** le principe d'un en-tête de formation utile, avec des métadonnées et une progression contextualisée, est pertinent. La règle essentielle est de ne jamais afficher un pourcentage lorsque le suivi n'est pas activé. La mise à jour dynamique est intéressante, mais doit rester facultative et compatible avec les API réellement disponibles dans Moodle 4.5 à 5.3.

### 2.3 Navigation et usage mobile

Retani propose :
- une barre latérale gauche pour les repères et raccourcis ;
- un tiroir mobile qui réutilise le mécanisme de tiroir Moodle ;
- une navigation de cours structurée, avec sections et activités ;
- des contrôles de tiroir adaptés au tactile ;
- des adaptations de hauteur et de défilement lorsque des menus s'ouvrent dans un tiroir.

**À retenir pour Project N :** l'idée d'une navigation mobile dédiée et d'un plan de cours toujours repérable mérite d'être étudiée. Project N conserve cependant la décision déjà prise : navigation globale propre au thème, navigation contextuelle et index du cours natifs Moodle, avec conservation des nœuds Iomad.

### 2.4 Administration et personnalisation

Le dépôt contient des réglages organisés par onglets, des presets SCSS, une couleur de marque, des images de fond, du SCSS additionnel et des modes de couleur expérimentaux activables explicitement.

**À retenir pour Project N :** une administration de thème regroupée et lisible, avec peu de paramètres mais bien expliqués, correspond à l'objectif du projet. Le mode sombre ne doit être activé qu'après audit des composants Moodle et des plugins : le choix de Retani de le laisser opt-in est prudent.

### 2.5 Accessibilité JavaScript

Le module `amd/src/aria.js` de Retani traite plusieurs interactions :
- navigation clavier dans les menus déroulants ;
- déplacement du focus et retour du focus au déclencheur ;
- gestion de listes de choix et de combobox ;
- piège et restitution du focus pour certains dialogues ;
- gestion des interactions Bootstrap.

C'est une base de réflexion intéressante pour les interactions complexes. Ce module ne corrige cependant pas à lui seul le défaut de nom accessible du déclencheur utilisateur identifié dans la CI de Project N : le rapport axe-core de Project N porte précisément sur l'inclusion du libellé visible dans le nom accessible (`label-content-name-mismatch`). Notre correctif doit rester ciblé sur le DOM effectivement rendu par les versions Moodle/Iomad testées.

## 3. Architecture et risques de maintenance

La configuration de Retani définit ses propres layouts et n'hérite pas de Boost via `$THEME->parents`. Le thème fournit notamment un renderer central, des renderers de composants, des templates de cours, une barre latérale et une feuille SCSS étendue.

Cette approche donne une grande liberté de présentation, mais implique :
- une veille permanente sur les changements de Boost et du cœur Moodle ;
- des comparaisons de templates à chaque version ;
- une couverture de tests plus importante pour les pages spécialisées (quiz, formulaires, administration, activités, fenêtres sécurisées) ;
- un risque de régression plus élevé lorsqu'un renderer ou un template natif évolue.

Le README du dépôt décrit également l'import et la maintenance d'une copie de Bootstrap (SCSS et JavaScript). Cela augmente encore la surface à maintenir.

Project N a fait un choix différent et volontaire : rester un thème enfant Boost, ne pas recopier les layouts, préserver les mécanismes natifs et limiter les surcharges aux points d'extension justifiés.

## 4. Compatibilité et maturité observées

| Élément | Constat sur `master` |
|---|---|
| Version du plugin | 1.0.0 |
| Maturité | Beta |
| Version Moodle minimale déclarée | Moodle 5.3 |
| Versions prises en charge | 5.3 uniquement |
| Parent Moodle | Aucun parent déclaré |
| Iomad | Aucune compatibilité déclarée ou détection spécifique identifiée |
| Tests présents | PHPUnit et Behat |
| GitHub Actions | Aucun workflow repéré dans l'arborescence observée |
| Licence | Le fichier LICENSE apparaît dans la PR #2 ouverte, pas dans l'arborescence master examinée |

Ces constats concernent la branche et les fichiers consultés à la date de l'analyse. Ils ne constituent pas un audit d'installation runtime.

## 5. État de la PR #2 du dépôt Retani

La PR #2 `Features/quiz UI` est ouverte sur `master`, avec 86 fichiers modifiés (environ 12 774 ajouts et 92 suppressions lors de la consultation). Elle ajoute notamment :
- une interface de quiz et ses maquettes ;
- des évolutions de la page de connexion ;
- des maquettes de tableaux de bord et d'administration ;
- des éléments de documentation d'architecture et de licence.

Il faut distinguer ces ajouts du contenu déjà présent dans `master`. Ils ne doivent pas être considérés comme une fonctionnalité publiée tant que la PR n'est pas fusionnée et testée.

## 6. Transposition proposée dans Project N

| Domaine | Idée à étudier | Transposition recommandée |
|---|---|---|
| Tableau de bord | Liste de formations plus dense et filtrable | Conserver les données Moodle natives ; prototype distinctif sans réécrire tout `block_myoverview` |
| Page de cours | En-tête avec catégorie, contact, sections et progression | Fournisseur de données Project N limité ; API d'achèvement Moodle ; pas de progression artificielle |
| Progression | Indicateurs de section actualisés | Amélioration progressive, sans dépendance bloquante au système React |
| Navigation mobile | Barre latérale / tiroir spécialisé | Compléter la barre mobile Project N en respectant les tiroirs natifs et les préférences Moodle |
| Administration | Réglages par onglets | Reprendre l'organisation et la sobriété, pas le système de layouts |
| Modes de couleur | Activation explicite | À différer jusqu'à une couverture des composants Moodle et plugins |
| Quiz | Présentation dédiée | À étudier après P2/P3 ; préserver le moteur, les états et les contrôles natifs du quiz |
| Accessibilité | Gestion clavier et focus | Réutiliser les principes et tests ; ne pas importer le module entier sans analyse des versions |

## 7. Actions de suivi

- [ ] Comparer les maquettes Retani Dashboard et Retani Course Details aux écrans Project N.
- [ ] Ajouter au prototype Project N un bandeau de formation avec contact pédagogique et progression conditionnelle.
- [ ] Étudier une vue liste des formations pour les espaces formateur et administrateur.
- [ ] Vérifier les parcours clavier et le focus des tiroirs sur les versions cibles.
- [ ] Ne retenir aucun code Retani avant comparaison avec les forks Moodle et Iomad du projet et vérification de licence.

## Sources techniques

- Dépôt : https://github.com/dojoVader/retani-studio-kit
- Configuration : https://github.com/dojoVader/retani-studio-kit/blob/master/config.php
- Version : https://github.com/dojoVader/retani-studio-kit/blob/master/version.php
- Renderer du tableau de bord : https://github.com/dojoVader/retani-studio-kit/blob/master/classes/output/block_myoverview_renderer.php
- En-tête de cours : https://github.com/dojoVader/retani-studio-kit/blob/master/classes/output/course_header.php
- Progression dynamique : https://github.com/dojoVader/retani-studio-kit/blob/master/amd/src/courseprogress.js
- Accessibilité : https://github.com/dojoVader/retani-studio-kit/blob/master/amd/src/aria.js
- Navigation mobile : https://github.com/dojoVader/retani-studio-kit/blob/master/templates/primary-drawer-mobile.mustache
- PR #2 : https://github.com/dojoVader/retani-studio-kit/pull/2
