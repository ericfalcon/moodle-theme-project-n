# Project N — CI et stratégie de tests

**Statut :** les workflows de syntaxe PHP, d'installation réelle Moodle/Iomad, PHPUnit ciblé et axe-core sont actifs. Le 11 novembre 2026, l'audit a identifié et corrigé la découverte PHPUnit vide, des appels Moodle non compatibles et la normalisation des URL de navigation. Le run `43062446983` a validé PHP 8.1/8.2/8.3, PHPUnit sur les quatre environnements, et axe-core sans violation sur Moodle 4.5, Iomad 4.5 et Iomad 5.2. L'audit Moodle 5.3 était encore en cours lors de la rédaction ; le run de validation du commit final doit confirmer les quatre environnements.

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

- **PHPUnit :** l'étape apparaissait en succès, mais les logs du run `42999263534` montrent `No tests executed!` sur Moodle 5.3 et Iomad 5.2. Ce résultat ne valide aucun test ; la même anomalie doit être considérée comme possible sur les deux environnements 4.5 tant que leurs logs n'ont pas été corrigés et revérifiés.
- **Moodle 5.3 et Iomad 5.2 :** jobs de référence réussis, sans violation bloquante.
- **Moodle 4.5 :** 12 occurrences de violations bloquantes sur les pages auditées :
  - `label-content-name-mismatch` sur les deux boutons du bloc Timeline (`Filter timeline by date`, `Sort timeline items`) aux quatre largeurs du tableau de bord ;
  - `target-size` sur les deux liens de navigation Project N aux quatre largeurs, sur le tableau de bord et l'index des cours.
  - `landmark-unique` sur le repère `core/moremenu` aux largeurs 768 et 1280.
- **Iomad 4.5 :** 8 occurrences bloquantes `target-size` sur les liens de navigation Project N, plus le repère `core/moremenu` aux largeurs 768 et 1280.

Les rapports et captures d'origine sont les artefacts `axe-Moodle-4.5` (ID `13805412307`) et `axe-Iomad-4.5` (ID `13805757714`). Les mesures axe montraient des liens de navigation de 16 à 17 px de haut, alors que le SCSS source annonçait une hauteur minimale de 44 px : la feuille SCSS ne produisait donc pas le rendu attendu sur les deux plateformes 4.5.

### Corrections enregistrées sur `feature/p1-foundation`

- **Fixtures PHPUnit :** les inscriptions des tests du tableau de bord utilisent le générateur de données Moodle. Attention : l'exécution `42999263534` n'a découvert aucun test malgré le statut vert de l'étape. Les tests ne sont donc pas considérés comme validés par cette exécution.
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

## 10. Correction de la découverte PHPUnit — 11 novembre 2026

### Diagnostic confirmé par les logs

Les fichiers de tests du thème suivent la convention Moodle `*_test.php` (`tests/output/global_navigation_test.php` et `tests/output/learner_dashboard_test.php`). La commande précédente lançait PHPUnit sur un répertoire sans lui transmettre ce suffixe. PHPUnit retournait un code de sortie nul tout en affichant `No tests executed!`, ce qui laissait passer une suite vide.

### Correctif CI

Dans `.github/workflows/platform-test-reusable.yml`, l'étape PHPUnit utilise désormais explicitement :

- `--test-suffix _test.php` pour découvrir les fichiers de tests Moodle ;
- `--fail-on-empty-test-suite` pour transformer toute découverte vide en échec CI ;
- `set -euo pipefail` pour conserver un échec bloquant de la commande.

Cette correction s'applique à tous les environnements de la matrice, Moodle 4.5 à 5.3 et Iomad 4.5 à 5.2. Elle ne vaut pas preuve de réussite : la prochaine exécution doit montrer un décompte PHPUnit non nul et zéro échec sur Moodle 5.3 et Iomad 5.2, ainsi que sur les autres versions couvertes.

### Commit de correction

`5440e254e53f6f676e5eb8a22ce2b491e20a8b19` — `Fix PHPUnit test discovery and fail on empty suite`.


## 11. Diagnostic et correction du SCSS du thème — 11 novembre 2026

Le rapport axe-core Moodle 4.5 de l'exécution antérieure (artefact `axe-Moodle-4.5`, ID `13805412307`) a été téléchargé et inspecté. Sur les pages `/my/` et `/course/index.php`, les liens de navigation Project N n'atteignaient que 39 × 16 px ou 55 × 16 px, alors que `scss/projectn.scss` leur déclare une hauteur minimale de 44 px. Le CSS du thème n'était donc pas effectivement pris en compte dans le rendu testé.

La cause identifiée dans `config.php` : le thème enfant déclarait seulement `extrascsscallback`, sans déclarer explicitement le callback SCSS de base de Boost. La configuration a été corrigée pour :
- charger le preset SCSS natif de Boost avec `theme_boost_get_main_scss_content()` ;
- reprendre le callback de pré-SCSS Boost `theme_boost_get_pre_scss` ;
- conserver `theme_projectn_get_extra_scss` pour ajouter les tokens et composants Project N.

Commit : `af979b04b1f906bf544ebc600e2dc1e4fc8023cf` — `Load Boost base SCSS and Project N styles`.

Cette modification est une correction du thème, pas seulement du workflow. Elle doit encore être confirmée sur le rendu réel par la CI : les dimensions des liens doivent être mesurées à 44 px minimum et axe-core doit confirmer la disparition de `target-size`. Les violations de la chronologie Moodle 4.5 (`label-content-name-mismatch`) et les landmarks doivent être revérifiés séparément ; ils ne sont pas déclarés corrigés sur la seule base de cette modification.


## 11. Compatibilité des API Moodle et validation PHPUnit/axe — 11 novembre 2026

### Défauts mis en évidence par l'exécution réelle

Le run `43002769891` sur le commit `73ea29c8a96805900c1296ce79248d04e18fce3a` a fait échouer PHPUnit dans les quatre environnements après activation de la vraie découverte des fichiers `*_test.php`. Les journaux montraient notamment :

- `Undefined constant core_calendar\\local\\event\\data_access\\CALENDAR_EVENT_TYPE_ACTION` : le dashboard appelait l'API d'événements d'action avant le chargement de `calendar/lib.php`, qui déclare cette constante ;
- `Call to undefined method completion_info::get_progress_percentage()` : cette méthode n'existe pas dans `completion_info` sur les versions prises en charge ;
- deux assertions de navigation active échouaient parce que `moodle_url::get_path()` normalise le chemin racine et peut conserver un slash final.

### Corrections du thème

- `classes/output/learner_dashboard.php` charge explicitement `calendar/lib.php` avant d'interroger les événements d'action. Les URL d'action sont vérifiées avant leur sérialisation.
- Le pourcentage de progression est calculé à partir des activités suivies renvoyées par `completion_info::get_activities()` et de leur état `get_data()`. Le calcul et la sélection de la prochaine activité partagent les mêmes données de complétion.
- `classes/output/global_navigation.php` normalise les chemins (racine vide et slash final) avant de déterminer la destination active.
- Le workflow conserve `--test-suffix _test.php` et `--fail-on-empty-test-suite` : une suite vide ne peut plus être annoncée comme réussie.

### Commits de correction

- `34ae3d45fe2003ac5bbbf418fe0d5bf9cba99f45` — API de complétion et calendrier prises en charge.
- `588ab276c4e0edc3635ae34a75ecd1d4f01919b0` — chargement de la bibliothèque calendrier et calcul de progression.
- `fc98716c2dcbddc408e2aecf0dd80a8ccd23846a` — normalisation des URL de navigation.
- `3e6fa050892fa549343b1dcc33572aea7c7f13e9` et `06a7252d580254af28b9d8313d79aa392da3e967` — garde sur les URL d'événements non actionnables et correction de la référence de classe.

### Résultats observés sur le run 43062446983

Tous les jobs PHP 8.1, 8.2 et 8.3 sont verts. PHPUnit a réussi sur Moodle 4.5, Moodle 5.3, Iomad 4.5 et Iomad 5.2. Les journaux consultés confirment `11 tests, 50 assertions` sur Moodle 4.5, Iomad 4.5 et Iomad 5.2.

Les artefacts axe inspectés montrent zéro violation sur les 12 états audités de Moodle 4.5 (`axe-Moodle-4.5`), Iomad 4.5 (`axe-Iomad-4.5`) et Iomad 5.2 (`axe-Iomad-5.2`). Le contrôle axe de Moodle 5.3 n'était pas encore terminé au moment de cette mise à jour documentaire.

Le critère de clôture reste inchangé : une seule exécution finale, sur le commit final, doit terminer avec succès les quatre jobs de référence, chaque audit axe sans violation, les suites PHPUnit non vides et les trois contrôles de syntaxe. Aucun merge vers `main` avant ce résultat.


## 12. Synchronisation de l'audit React Moodle 5.3 — 11 novembre 2026

Le run `43064246301` a confirmé zéro violation sur Moodle 4.5, Iomad 4.5 et Iomad 5.2, mais a relevé trois nœuds critiques sur Moodle 5.3 à 390 px : les boutons de filtre et de tri de `block_timeline` (`button-name`) et le champ de recherche (`label`).

### Cause

Moodle 5.3 ne rend plus le bloc Timeline avec les anciens templates Mustache : `block_timeline.php` monte directement le composant React `@moodle/lms/block_timeline/Timeline`. Les composants `DayFilter`, `ViewSelector` et `Search` chargent leurs libellés via `getString()` de façon asynchrone. L'audit commençait dès `DOMContentLoaded`, avant la résolution de ces chaînes ; à 390 px, axe-core inspectait donc un état transitoire où les attributs `aria-label`, le placeholder et le label étaient encore vides. Les autres largeurs pouvaient être auditées après la résolution des chaînes, d'où le caractère intermittent.

### Correction du banc de test

`tests/ci/axe-audit.cjs` attend désormais, uniquement pour le tableau de bord Moodle 5.3, que le composant React Timeline soit monté et que les deux boutons aient un nom accessible ainsi que le champ de recherche son placeholder et son label. Cette attente est explicite et bornée à 15 secondes : si les libellés ne sont pas chargés, le job échoue. Aucun contrôle axe-core n'est désactivé, aucune violation n'est filtrée et les quatre largeurs sont toujours auditées.

Le résultat précédent n'est pas déclaré vert : la nouvelle exécution sur le commit qui contient cette correction doit confirmer zéro violation sur Moodle 5.3. Le correctif de synchronisation est enregistré dans le commit `92b2f17e2b8f5d65a8a8cba096cadc27aae736f8`.


## 13. Compatibilité PHPUnit 9 et suppression des dépréciations PHPUnit 11 — 12 novembre 2026

Le run de diagnostic `43255001037` a mis en évidence deux comportements distincts :

- PHPUnit 9.6 (Moodle 4.5 et Iomad 4.5) ne reconnaît pas l'option `--display-phpunit-deprecations`. Son ajout inconditionnel provoquait un échec avant même la découverte des tests.
- PHPUnit 11 (Moodle 5.3 et Iomad 5.2) signalait 13 dépréciations, toutes dues aux métadonnées de couverture déclarées dans les docblocks des deux classes de tests : deux `@coversDefaultClass` et onze `@covers`.

Le correctif apporte les changements suivants :

- La commande CI détecte la version majeure de PHPUnit. Elle n'ajoute `--display-phpunit-deprecations` qu'à partir de PHPUnit 11 ; les options `--test-suffix _test.php` et `--fail-on-empty-test-suite` restent systématiques.
- Les métadonnées de couverture des tests de navigation et du tableau de bord sont migrées vers l'attribut de classe `CoversClass`. Les deux suites couvrent chacune une seule classe de fournisseur de contexte ; cette déclaration reste donc cohérente avec leur périmètre. PHPUnit 11 l'interprète sans dépréciation. PHP 8.1 accepte sa syntaxe ; PHPUnit 9 ignore l'attribut, sans empêcher la découverte ni l'exécution des tests Moodle 4.5.
- Les scénarios et assertions des onze tests sont conservés.

La validation attendue sur le nouveau commit est : tests non vides et réussis sur les quatre environnements, aucune option inconnue sur PHPUnit 9, aucune dépréciation PHPUnit 11, et zéro violation axe-core. Cette section ne vaut pas résultat de validation ; seuls les logs de la CI du commit final feront foi.


### Résultat de validation — run GitHub Actions `43258748557`

Le commit `5f60370a78ac929bca20bd0d114a4e2f6ba8832d` a été validé par les deux workflows : `Platform integration and accessibility` et `Project N quality checks` terminés avec succès.

| Plateforme | PHPUnit | Résultat | Dépréciations PHPUnit | axe-core |
|---|---:|---:|---:|---:|
| Moodle 4.5 | 9.6.34 | 11 tests, 50 assertions | 0 | 0 violation |
| Iomad 4.5 | 9.6.34 | 11 tests, 50 assertions | 0 | 0 violation |
| Moodle 5.3 | 11.5.56 | 11 tests, 50 assertions | 0 | 0 violation |
| Iomad 5.2 | 11.5.55 | 11 tests, 50 assertions | 0 | 0 violation |

Les trois contrôles de syntaxe PHP (8.1, 8.2 et 8.3) sont également réussis. Les logs confirment que l'option de diagnostic n'est plus transmise à PHPUnit 9 et que PHPUnit 11 ne signale plus les métadonnées de couverture en doc-comment. Les quatre audits axe-core affichent `no violations in the audited states`.

**Conclusion :** la correction est validée sur les quatre environnements de référence. La matrice complète planifiée/manuelle (Moodle 5.0–5.2 et Iomad 5.0–5.1) n'était pas concernée par ce run et apparaît comme ignorée, conformément au déclenchement pull request.
