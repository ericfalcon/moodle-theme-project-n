# Project N — CI et stratégie de tests

**Statut :** workflows de syntaxe PHP, d'installation réelle Moodle/Iomad, PHPUnit ciblé et axe-core sur navigateur enregistrés dans GitHub. Les premières exécutions réelles ont validé PHPUnit sur les quatre plateformes de référence ; les audits axe 4.5 ont révélé des écarts désormais corrigés dans le code, dont la validation reste à confirmer par la CI du dernier commit.

## 1. Principes

- La CI est exécutée sur les pull requests vers `main` et sur les push vers `main`. Les branches de travail ne déclenchent pas une seconde matrice redondante en plus de leur pull request.
- Une anomalie bloquante de syntaxe, de test ou d'accessibilité doit faire échouer le pipeline.
- Les contrôles sont réalisés sur le thème installé dans Moodle, et pas uniquement sur des fichiers isolés.
- Les versions Moodle et Iomad sont testées séparément : Moodle 4.5 à 5.3 ; Iomad 4.5 à 5.2. Iomad 5.3 sera ajouté lorsqu'une version exploitable sera disponible.
- Les rôles Iomad, notamment `companycourseeditor` et `companycoursenoeditor`, font partie des scénarios de non-régression.

## 2. Dépôts sources et environnements réels

Les environnements de test doivent être construits à partir des forks du compte GitHub du projet, et non depuis des archives téléchargées ou des dépôts amont substitués :

| Environnement | Dépôt utilisé | Branches vérifiées dans le fork |
|---|---|---|
| Moodle | [ericfalcon/moodle](https://github.com/ericfalcon/moodle) | `MOODLE_405_STABLE`, `MOODLE_500_STABLE`, `MOODLE_501_STABLE`, `MOODLE_502_STABLE`, `MOODLE_503_STABLE` |
| Iomad | [ericfalcon/iomad](https://github.com/ericfalcon/iomad) | `IOMAD_405_STABLE`, `IOMAD_500_STABLE`, `IOMAD_501_STABLE`, `IOMAD_502_STABLE` |

Le fork Iomad est la source de référence pour les tests Iomad. **Ne pas le remplacer par le dépôt amont et ne pas réinitialiser ses branches** : la branche Iomad 5.0 contient des modifications personnelles qui doivent être conservées. Les workflows doivent faire un checkout explicite de la branche de version demandée et enregistrer le SHA exact utilisé dans les logs/artefacts.

Matrice cible :
- Moodle : 4.5, 5.0, 5.1, 5.2 et 5.3.
- Iomad : 4.5, 5.0, 5.1 et 5.2, chacune sur la branche correspondante du fork.
- Iomad 5.3 : ne pas créer de job tant qu'une branche/version exploitable n'existe pas dans le fork.

Chaque environnement doit être une **installation réelle et indépendante** : base de données dédiée, installation CLI, thème Project N installé et activé, données de test créées par les API Moodle/Iomad. Le job Iomad doit démarrer le code Iomad lui-même ; un Moodle standard auquel on aurait ajouté quelques plugins Iomad ne constitue pas un test Iomad valide.

Pour maîtriser la durée des pull requests, la stratégie pourra distinguer :
- une matrice courte de validation sur chaque pull request (version Moodle de référence et version Iomad de référence) ;
- la matrice complète des versions sur exécution planifiée et déclenchement manuel ;
- une campagne complète axe-core sur les deux plateformes, avec possibilité d'un contrôle ciblé sur pull request.

Le choix des versions de référence et la fréquence des campagnes complètes devront être explicités dans le workflow, sans masquer les versions non testées.

## 3. Contrôles CI

| Niveau | Contrôle | Déclenchement | Critère |
|---|---|---|---|
| Activé | Syntaxe PHP (`php -l`) | Push, pull request | Aucune erreur |
| À intégrer | Moodle Coding Style / PHPCS | Push, pull request | Aucune violation non justifiée |
| À intégrer | Validation du plugin et des templates | Push, pull request | Validation réussie |
| Activé, exécution à vérifier | PHPUnit sur Moodle et Iomad | Push, pull request (versions de référence) ; matrice complète planifiée/manuelle | Tests du thème réussis |
| À intégrer | Compilation SCSS / vérification des assets | Push, pull request | Compilation réussie |
| Activé, exécution à vérifier | axe-core via Playwright sur Moodle et Iomad | Push, pull request (versions de référence) ; matrice complète planifiée/manuelle | Aucune violation axe-core sur les pages auditées |
| À organiser | Tests Behat et parcours Iomad | Pull request et campagnes ciblées | Parcours critiques réussis |

Le contrôle syntaxique est défini dans `.github/workflows/php-quality.yml` (PHP 8.1, 8.2 et 8.3). Les installations et tests d'intégration sont définis dans `.github/workflows/platform-tests.yml`, qui appelle `.github/workflows/platform-test-reusable.yml`.

## 4. Tests PHPUnit à couvrir

- Navigation globale : visibilité selon l'état de connexion, page courante et exclusion des layouts incompatibles.
- Tableau de bord : état vide, cours apprenant, cours enseignant, compte présent dans les deux sections.
- Rôles Moodle : étudiant, enseignant, enseignant éditeur.
- Rôles Iomad : `companycourseeditor` et `companycoursenoeditor`, vérifiés chacun dans un cours distinct et dans un même compte multi-cours.
- Rôles hérités de catégories et rôles personnalisés sans archétype.
- Achèvement : suivi désactivé, progression disponible, activité suivante visible/non terminée, activité terminée ou non accessible.
- Contrôle des limites de cartes et des liens natifs Moodle.

Les tests doivent utiliser les générateurs et API Moodle. Ils ne doivent pas simuler les capacités en contournant le système de rôles.

## 5. Audit automatisé axe-core

L'audit axe-core sera exécuté avec Playwright sur **deux instances distinctes démarrées pour la CI : une instance Moodle et une instance Iomad**, toutes deux construites depuis les forks indiqués ci-dessus. Le thème Project N doit être installé et activé dans chacune. Chaque instance doit être initialisée avec des comptes, rôles et cours de test reproductibles.

### Pages et états à auditer

- Connexion et récupération de compte lorsque ces pages sont personnalisées.
- Tableau de bord apprenant vide et avec plusieurs formations.
- Tableau de bord enseignant Moodle.
- Tableau de bord enseignant Iomad, avec `companycourseeditor` et `companycoursenoeditor`.
- Page de cours, navigation globale, index du cours et menus contextuels.
- Formulaires et fenêtres modales réellement utilisés dans les parcours couverts.
- États clair et sombre si le thème expose ces deux modes.

### Viewports

- 320 × 568 CSS px : reflow mobile étroit.
- 390 × 844 CSS px : mobile courant.
- 768 × 1024 CSS px : tablette.
- 1280 × 800 CSS px : bureau.

Le zoom à 200 % et les parcours clavier restent également à contrôler. Le viewport ne simule pas à lui seul le zoom navigateur.

### Règles et seuils

- Utiliser axe-core avec les tags WCAG 2.1 niveau A et AA, WCAG 2.2 A/AA lorsque les règles sont pertinentes, et les bonnes pratiques axe.
- Faire échouer la CI sur **toute violation axe-core**, quel que soit son niveau (`critical`, `serious`, `moderate` ou `minor`).
- Aucune violation `moderate` ou `minor` n'est ignorée : elles bloquent également la CI.
- Toute exception doit être documentée avec le sélecteur concerné, la justification, le risque résiduel et une échéance de correction.
- Ne pas neutraliser globalement une règle axe pour faire passer le pipeline.

### Limites

axe-core ne détecte pas tous les problèmes d'accessibilité. Les tests automatisés doivent être complétés par des contrôles manuels : navigation clavier, ordre et visibilité du focus, lecteurs d'écran, intitulés et annonces dynamiques, contraste dans les états interactifs, zoom et reflow.

## 6. Gate axe et limites actuelles

Le gate axe est actif sur les quatre environnements de référence (Moodle 4.5 et 5.3 ; Iomad 4.5 et 5.2), sur les pages et états décrits en section 7. Il bloque désormais **toutes les violations axe-core**, sans distinction de gravité. Il ne constitue pas encore une validation complète de tous les parcours Project N.

Les rapports JSON et captures en cas de violation bloquante sont conservés comme artefacts de CI. Les audits sur les tableaux de bord apprenant/enseignant avec données réelles de test, les rôles Iomad en contexte entreprise, les menus ouverts, les modales et le thème sombre restent à ajouter.


## 7. Workflows actifs

Trois workflows GitHub Actions sont maintenant présents :

- `.github/workflows/php-quality.yml` : contrôle syntaxique PHP sur PHP 8.1, 8.2 et 8.3.
- `.github/workflows/platform-tests.yml` : orchestre les matrices de référence et complète.
- `.github/workflows/platform-test-reusable.yml` : clone les forks `ericfalcon/moodle` ou `ericfalcon/iomad` sur la branche de version indiquée, installe les dépendances Composer, installe Project N dans le code source, crée une base MySQL dédiée, lance l'installation CLI de la plateforme, initialise PHPUnit et exécute les tests du thème.

Déclenchement de la matrice :

- Pull request vers `main` et push sur `main` : Moodle 4.5 et 5.3 ; Iomad 4.5 et 5.2. Les deux générations 4.5 sont exécutées à chaque pull request.
- Exécution hebdomadaire (lundi) et déclenchement manuel : matrice complète Moodle 4.5–5.3 et Iomad 4.5–5.2.
- Chaque job journalise le SHA du fork réellement testé. Le job Iomad clone exclusivement le fork personnel ; la branche Iomad 5.0 n'est ni remplacée ni réinitialisée.

Sur les quatre versions de référence, le workflow démarre aussi un serveur PHP local et lance Playwright + axe-core sur la connexion, le tableau de bord et l'index des cours, aux largeurs 320, 390, 768 et 1280 CSS px. Les rapports JSON et captures d'écran des violations bloquantes sont publiés comme artefacts.

**Périmètre actuel de l'audit axe :** les pages sont visitées avec le compte administrateur créé pour l'installation. Cela valide le rendu initial des pages communes et l'état vide du tableau de bord, mais ne prétend pas encore couvrir les tableaux de bord peuplés apprenant/enseignant, les rôles Iomad en contexte entreprise, les menus ouverts, les modales ni le thème sombre. Ces états seront ajoutés avec des données de test dédiées.

**Statut d'exécution :** les premières exécutions GitHub Actions ont confirmé les installations réelles et PHPUnit sur les quatre environnements de référence. Les audits axe ont ensuite révélé des violations spécifiques aux deux environnements 4.5 ; elles sont documentées en section 9 et font l'objet d'une nouvelle campagne de validation.


## 8. Référence 4.5 exécutée à chaque changement

À compter de la mise à jour du workflow, Moodle 4.5 (`MOODLE_405_STABLE`) et Iomad 4.5 (`IOMAD_405_STABLE`) rejoignent les deux environnements récents dans la matrice déclenchée sur chaque pull request vers `main` et push sur `main`. Les deux branches ont été vérifiées dans les forks personnels avant modification du workflow.

Les quatre jobs de référence exécutent l'installation réelle, PHPUnit et l'audit axe-core. La matrice complète planifiée/manuelle reste utile pour Moodle 5.0, 5.1 et 5.2 ainsi que Iomad 5.0 et 5.1 ; elle ne remplace pas les quatre tests de référence exécutés à chaque changement.

Cette évolution maintient un contrôle de compatibilité sur la base historique 4.5 sans lancer deux matrices identiques pour chaque commit d'une branche associée à une pull request. Les résultats doivent être lus par job et par commit : un succès sur 5.3/5.2 ne vaut pas validation 4.5.


## 9. Diagnostic et corrections axe-core — 11 novembre 2026

### Référence de départ

Le run GitHub Actions `42906823791`, sur le commit `6af403a44b7d7426572b3d44829cbbbd14c6c74d`, fournit les rapports JSON et captures exploitables.

- **PHPUnit :** succès sur Moodle 4.5, Moodle 5.3, Iomad 4.5 et Iomad 5.2.
- **Moodle 5.3 et Iomad 5.2 :** jobs de référence réussis, sans violation bloquante.
- **Moodle 4.5 :** 12 occurrences de violations bloquantes sur les pages auditées :
  - `label-content-name-mismatch` sur les deux boutons du bloc Timeline (`Filter timeline by date`, `Sort timeline items`) aux quatre largeurs du tableau de bord ;
  - `target-size` sur les deux liens de navigation Project N aux quatre largeurs, sur le tableau de bord et l'index des cours.
  - `landmark-unique` sur le repère `core/moremenu` aux largeurs 768 et 1280.
- **Iomad 4.5 :** 8 occurrences bloquantes `target-size` sur les liens de navigation Project N, plus le repère `core/moremenu` aux largeurs 768 et 1280.

Les rapports et captures d'origine sont les artefacts `axe-Moodle-4.5` (ID `13805412307`) et `axe-Iomad-4.5` (ID `13805757714`). Les mesures axe montraient des liens de navigation de 16 à 17 px de haut, alors que le SCSS source annonçait une hauteur minimale de 44 px : la feuille SCSS ne produisait donc pas le rendu attendu sur les deux plateformes 4.5.

### Corrections enregistrées sur `feature/p1-foundation`

- **Fixtures PHPUnit :** les inscriptions des tests du tableau de bord utilisent le générateur de données Moodle ; les tests sont désormais confirmés verts sur les quatre plateformes de référence.
- **Compatibilité SCSS 4.5 :** Moodle 4.5 embarque SCSSPHP 1.12.1 ; son compilateur ne supporte pas correctement le sélecteur complexe `:where(...)` utilisé dans le style de focus. Le callback SCSS échouait donc et les styles Project N n'étaient pas appliqués. Le sélecteur a été remplacé par une liste explicite de sélecteurs `:focus-visible`. Les syntaxes CSS récentes de couleur et de calcul ont également été remplacées par des équivalents compatibles ; les liens de navigation reçoivent une cible minimale de 44 × 44 px.
- **Timeline :** surcharge ciblée des templates `block_timeline/nav-day-filter` et `block_timeline/nav-view-selector`. Le nom accessible reprend maintenant le libellé visible de l'option sélectionnée, en conservant les chaînes traduites de Moodle.
- **Landmarks :** surcharge de `core/moremenu` avec des noms accessibles distincts pour la navigation primaire et la navigation secondaire Moodle, en s'appuyant sur le contexte `istablist` du template.
- **Diagnostic :** le rapport axe conserve désormais le HTML des nœuds et leurs résultats `any/all/none`, ainsi que les dimensions calculées des liens Project N et des boutons Timeline.
- **Exécutions concurrentes :** les workflows sont limités au push sur `main` et aux pull requests vers `main`, avec annulation des exécutions obsolètes d'une même branche afin d'éviter la double matrice push/PR.

### Validation en attente

Le commit `8993c4f8c6544155244aeef540252d117111a8` regroupe les premières corrections ; le commit suivant corrige aussi le sélecteur `:where(...)` qui empêchait la compilation SCSS en Moodle 4.5. Les workflows déclenchés pour le dernier commit doivent confirmer :
1. syntaxe PHP sur PHP 8.1, 8.2 et 8.3 ;
2. PHPUnit sur Moodle 4.5 et 5.3, Iomad 4.5 et 5.2 ;
3. axe-core sans aucune violation, quelle que soit sa gravité, sur les quatre environnements.

Aucune de ces trois validations n'est déclarée réussie pour le commit `8993c4f` avant lecture de ses résultats GitHub Actions réels.


## 10. Correction du contrôle des landmarks — 11 novembre 2026

L'analyse des artefacts du run `42999263534` (commit `5c2c8527a4fbd0a4e58cf2e9e74361ad0e9070e1`) a révélé que le workflow était vert alors que les rapports JSON conservaient des violations modérées `landmark-unique` sur l'index des cours de Moodle 4.5, Iomad 4.5 et Iomad 5.2. La cause était une deuxième navigation `core/moremenu` rendue avec le même nom accessible que la navigation primaire ; le contexte `istablist` ne suffisait pas à distinguer le menu de catégorie rendu avec la classe `nav-tabs`.

Corrections engagées sur `feature/p1-foundation` :

- Le template `core/moremenu` conserve le nom « navigation secondaire de Moodle » pour les véritables tablists et utilise désormais un nom localisé incluant le style de navigation pour les autres menus. Cela différencie notamment `navbar-nav` et `nav-tabs`.
- Les chaînes anglaise et française `navigationlandmark` sont ajoutées.
- Le script axe-core traite maintenant **toutes** les violations comme bloquantes, y compris `moderate` et `minor`. Le précédent seuil limité à `serious` et `critical` expliquait pourquoi les jobs pouvaient réussir malgré ces résultats.
- Les rapports axe restent conservés comme artefacts pour permettre l'inspection des pages, largeurs, nœuds et diagnostics.

La validation de ces corrections doit être effectuée par une nouvelle exécution GitHub Actions sur le commit qui contient l'ensemble des modifications. Le statut vert du run précédent ne vaut pas validation de cette correction. Le critère de clôture est : quatre environnements de référence terminés, PHPUnit réussi, audit axe sans aucune violation et contrôles PHP réussis.
