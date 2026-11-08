# Project N — Matrice de compatibilité Moodle / Iomad

> Registre vivant des points communs et différences entre les versions cibles. À renseigner au fil de chaque tranche de cartographie, avant de considérer un mécanisme comme spécifié pour Project N.

## 1. Périmètre cible

| Plateforme | Version | Branche/tag exact | Commit examiné | Statut |
|---|---:|---|---|---|
| Moodle | 4.5 | `MOODLE_405_STABLE` | `c0690bbc765fccacd4956452cbff3f6aacad77c5` | Points d'entrée 65–68 comparés ; couverture partielle |
| Moodle | 5.0 | `MOODLE_500_STABLE` | `744cc0c19013e7be831dca24fafd6501d7eb8348` | Points d'entrée 65–68 comparés ; couverture partielle |
| Moodle | 5.1 | `MOODLE_501_STABLE` | `a682566412e1d429b95ac8ef4351a518f61b94c5` | Points d'entrée 65–68 comparés ; couverture partielle |
| Moodle | 5.2 | `MOODLE_502_STABLE` | `28fdef85e7043c8dd758af8c1a146da1419db0d0` | Points d'entrée 65–68 comparés ; couverture partielle |
| Moodle | 5.3 | `MOODLE_503_STABLE` | `236a9d702e62dc11f5aa66e4c8b5239cfc26c3d8` | Points d'entrée 65–68 comparés ; Iomad 5.3 absent du fork |
| Iomad | 4.5 | `IOMAD_405_STABLE` | `114ac90f25f74d79e4aae7bd72a749d8ab41a50e` | Points d'entrée ciblés comparés à Moodle 4.5 |
| Iomad | 5.0 | `IOMAD_500_STABLE` | Fork HEAD `40f37899a1d89bf307bd0cc0d205c01c03f78e5e` ; amont intégré `77c795dca4fd423e7b76c5996413f33b0679ba85` | Points d'entrée ciblés comparés ; personnalisations du fork conservées |
| Iomad | 5.1 | `IOMAD_501_STABLE` | `40cd06130bb227e159fae8df4e4a90b7a0f32adb` | Points d'entrée ciblés comparés à Moodle 5.1 |
| Iomad | 5.2 | `IOMAD_502_STABLE` | `88a9a3e9997916a049048db536048918cbdc1e56` | Points d'entrée ciblés comparés à Moodle 5.2 |
| Iomad | 5.3 | Non disponible selon l'état de référence du projet au 7 novembre 2026 | — | Non testable |

Les versions Iomad et Moodle sont comparées par génération correspondante. Ne jamais déduire la compatibilité d'une branche Iomad avec une autre génération Moodle.

## 2. Légende de preuve

- **Commun confirmé** : même mécanisme vérifié sur toutes les versions concernées.
- **Différence confirmée** : changement localisé et vérifié dans le code.
- **Ajout / retrait** : mécanisme introduit, supprimé ou déprécié.
- **Conditionnel** : dépend d'une capacité, d'un plugin, d'une configuration ou d'un contexte.
- **À tester** : le code statique ne suffit pas ; test runtime, mobile, clavier ou lecteur d'écran nécessaire.
- **Non vérifié** : branche non examinée, source inaccessible ou constat incomplet.

Un champ vide n'est pas une preuve d'identité entre versions. Il signifie « non renseigné ».

## 3. Registre des domaines

| Domaine | Moodle 4.5 | Moodle 5.0 | Moodle 5.1 | Moodle 5.2 | Moodle 5.3 | Écart / impact thème | Preuve / statut |
|---|---|---|---|---|---|---|---|
| Navigation globale et secondaire | Non vérifié | Cartographie initiale antérieure | Non vérifié | Non vérifié | Non vérifié | Vérifier layouts, navbar, tiroirs, sélecteurs DOM et JS | À comparer |
| Layouts Boost et templates | Non vérifié | Référence initiale | Non vérifié | Non vérifié | Non vérifié | Compatibilité des surcharges et templates | À comparer |
| Tableau de bord apprenant | Non vérifié | Cartographie initiale antérieure | Non vérifié | Non vérifié | Non vérifié | Widgets, blocs, cours et responsive | À comparer |
| Page de cours et formats | Non vérifié | Cartographie initiale antérieure | Non vérifié | Non vérifié | Non vérifié | Output API, formats et édition | À comparer |
| Activités et ressources | Non vérifié | Cartographie initiale antérieure | Non vérifié | Non vérifié | Non vérifié | Contrats de rendu par activité | À comparer |
| Profil et préférences | Non vérifié | Cartographie initiale antérieure | Non vérifié | Non vérifié | Non vérifié | Formulaires, champs, notifications | À comparer |
| Disponibilité et achèvement | Non vérifié | Cartographie initiale antérieure | Non vérifié | Non vérifié | Non vérifié | États, indicateurs et conditions | À comparer |
| Fichiers et éditeurs | Non vérifié | Cartographie initiale antérieure | Non vérifié | Non vérifié | Non vérifié | File API, éditeurs, sélecteurs | À comparer |
| Espace formateur | Points d'entrée + structure Output API comparés | Points d'entrée + structure Output API comparés | Points d'entrée + structure Output API comparés | Points d'entrée + structure Output API comparés | Points d'entrée + structure Output API comparés | Évolutions de `core_courseformat\\base`, renderers, classes de sortie, templates Mustache et AMD ; dépréciation du template grader en 5.3 ; contrôle d'accès Iomad au cours distinct | Statique et partiel ; chaîne de rendu sélectionnée identique Moodle/Iomad sur les quatre couples 4.5–5.2 ; voir cartographie 65.10–65.13 et comparaison 9.4–9.5 |
| Comptes utilisateurs | Points d'entrée + Report Builder comparés | Points d'entrée + Report Builder comparés | Points d'entrée + Report Builder comparés | Points d'entrée + Report Builder comparés | Points d'entrée + Report Builder comparés | Rapport système, entités et actions conditionnelles ; Company Admin : affectation, rôles, hiérarchie parent/enfant, retrait et licences | Statique, partiel ; cartographie 66.10–66.19, comparaison §9.6–9.7.3 ; parcours runtime, tâches différées, permissions, accessibilité et mobile à tester |
| Cohortes | Points d'entrée + Report Builder comparés | Points d'entrée + Report Builder comparés | Points d'entrée + Report Builder comparés | Points d'entrée + Report Builder comparés | Points d'entrée + Report Builder comparés | Entités, colonnes et actions contextuelles ; fichiers Report Builder natifs sélectionnés identiques Moodle/Iomad sur les quatre couples 4.5–5.2 | Statique ; synchronisations, cloisonnement Iomad et permissions runtime à tester |
| Catégories et cours plateforme | Points d'entrée + renderer comparés | Points d'entrée + renderer comparés | Points d'entrée + renderer comparés | Points d'entrée + renderer comparés | Points d'entrée + renderer comparés | Contrôleur stable mais helper/renderer/actionbar/templates évolutifs ; six fichiers cœur identiques Moodle/Iomad sur les couples 5.0 et 5.2 | Statique, partiel ; cartographie 67.9–67.12 ; JavaScript et parcours runtime à tester |
| Méthodes d'inscription | Points d'entrée + plugin Iomad comparés | Points d'entrée + plugins natifs et Iomad comparés | Points d'entrée + plugins natifs et Iomad comparés | Points d'entrée + plugins natifs et Iomad comparés | Points d'entrée Moodle comparés ; Iomad 5.3 absent | Plugins natifs sélectionnés identiques Moodle/Iomad en 5.0 et 5.2 ; plugin Iomad enrol/license spécifique, absent de Moodle | Statique ; cartographie 68.11–68.17 ; cron licence, synchronisations et plugins non sélectionnés à tester |
| Rôles et capacités | Affectation des rôles entreprise et cours examinée dans le flux Company Admin | Affectation des rôles entreprise et cours examinée dans le flux Company Admin | Contextes entreprise personnalisés et tâches d'affectation différées examinés | Identique à 5.1 sur les fichiers métier comparés | Non vérifié | Rôles `companymanager`, `companydepartmentmanager`, `companyreporter`, `companycourseeditor`, `companycoursenoneditor` ; fonctions de gestion et educator distincts | Statique, partiel ; cartographie 66.19 et comparaison §9.7.3 ; capacités effectives et héritage à valider en runtime |
| Rapports d'administration | Non vérifié | Non vérifié | Non vérifié | Non vérifié | Non vérifié | Report Builder, exports, permissions | À analyser |
| Paramètres de plateforme | Non vérifié | Non vérifié | Non vérifié | Non vérifié | Non vérifié | Arborescence admin et capacités | À analyser |
| Iomad multi-entreprise | Affectation, retrait et hiérarchie statiquement examinés | Affectation, retrait et hiérarchie statiquement examinés ; personnalisations du fork préservées | Schéma local_iomad, propagation des gestionnaires et tâches ad hoc examinés | Identique à 5.1 pour les classes métier principales examinées | Non disponible | Cloisonnement par entreprise, départements, gestionnaires hérités, rôles, cours partagés, licences et tâches différées | Statique ; cartographie 66.15–66.19 ; scénarios runtime multi-entreprises, accès direct, licences et tâches à tester |

Ce tableau est volontairement conservateur : « cartographie initiale antérieure » ne signifie pas comparaison interversions terminée.

### 3.1 Résultats détaillés des points 65 à 68

Les contenus complets des points d'entrée listés ont été comparés depuis les branches actuelles des forks. Le détail par fichier, génération et couple Moodle/Iomad est conservé dans :
- `P0-CARTOGRAPHIE-NAVIGATION.md`, sections 65.10–65.11, 66.10–66.12, 67.9–67.10 et 68.11–68.13 ;
- `P0-COMPARAISON-VERSIONS-MOODLE-IOMAD.md`, section 9.

Le statut « comparé : points d'entrée » ne signifie pas que l'ensemble des classes, templates, renderers, modules JavaScript, plugins, permissions et parcours utilisateurs est couvert. Il ne vaut pas validation fonctionnelle ni déclaration de compatibilité.

## 4. Fiche obligatoire pour chaque différence

Pour chaque écart constaté, ajouter une entrée avec ce format :

### [ID] Domaine — intitulé court

- **Plateformes / versions :**
- **Branche, tag et commit :**
- **Fichiers et symboles concernés :**
- **Comportement commun confirmé :**
- **Différence constatée :**
- **Version d'apparition / disparition :**
- **Capacités et contextes concernés :**
- **Contrat technique concerné :** API, renderer, template Mustache, AMD/JavaScript, événement, attribut DOM…
- **Impact sur Project N :**
- **Stratégie :** code commun, branche conditionnelle, surcharge dédiée, adaptation CSS, absence de surcharge…
- **Tests à exécuter :**
- **Niveau de preuve :** code comparé / test exécuté / hypothèse / non vérifié.
- **Référence dans la cartographie :**

## 5. Règle de mise à jour continue

À chaque tranche de travail :
1. examiner les branches cibles pertinentes, et pas seulement la version de référence ;
2. noter les points communs et différences dans ce registre ;
3. enrichir la cartographie fonctionnelle avec les références de code exactes ;
4. mettre à jour la roadmap et le README si l'état ou les priorités changent ;
5. relire les fichiers depuis GitHub après commit ;
6. ne déclarer une compatibilité acquise qu'après les validations prévues.

Les sections 65 à 68 ont été initialement rédigées à partir de Moodle 5.0. Une comparaison statique ciblée des points d'entrée a maintenant été menée sur Moodle 4.5 à 5.3 et sur les couples Moodle/Iomad 4.5 à 5.2. La couverture reste partielle : les composants appelés, permissions effectives, plugins, rendus et parcours runtime restent à examiner.

## 6. Principe de conception multi-version

Le thème doit rester installable et fonctionnel sur Moodle 4.5, 5.0, 5.1, 5.2 et 5.3. Les adaptations doivent être minimisées et documentées :
- privilégier les API stables et les points d'extension publics ;
- éviter les copies de templates entiers lorsqu'une surcharge ciblée suffit ;
- isoler les adaptations de version dans des classes/fichiers dédiés lorsque nécessaire ;
- ne pas se baser uniquement sur le numéro de version si une détection de capacité ou de fonctionnalité est plus robuste ;
- ne jamais masquer une différence fonctionnelle par du CSS ;
- tester les parcours essentiels sur chaque génération cible.


## 7. Résultats vérifiés — sections 65 à 68

### 7.1 Comparaison Moodle / Iomad

| Domaine / fichier | 4.5 | 5.0 | 5.1 | 5.2 | Conclusion limitée |
|---|---|---|---|---|---|
| `course/view.php` | Différence Iomad | Différence Iomad | Différence Iomad | Différence Iomad | Iomad ajoute `iomad_check_course()` pour vérifier l'accès au cours dans le périmètre entreprise |
| `course/edit.php` | Identique | Identique | Identique | Identique | Contrôleur commun sur les versions vérifiées |
| `user/index.php` | Identique | Identique | Identique | Identique | Participants : point d'entrée commun |
| `admin/user.php` | Identique | Identique | Identique | Identique | Administration des comptes : point d'entrée commun |
| `course/management.php` | Identique | Identique | Identique | Identique | Gestion plateforme : point d'entrée commun sur les cinq versions Moodle vérifiées |
| `enrol/instances.php`, `editinstance.php`, `manual/manage.php` | Identiques | Identiques | Identiques | Identiques | Les contrôleurs standards d'inscription ne sont pas remplacés par Iomad |
| `grade/report/grader/index.php`, `mod/assign/classes/output/grading_app.php` | Non vérifiés | Identiques | Non vérifiés | Non vérifiés | Vérification Moodle/Iomad faite en 5.0 uniquement |
| Report Builder users/cohortes — sélection de fichiers | Non vérifiés | Identiques | Non vérifiés | Identiques | Rapports et entités natifs identiques Moodle/Iomad sur les couples 5.0 et 5.2 |
| `admin/user/user_bulk.php` | Non vérifié | Identique | Non vérifié | Non vérifié | Comparaison Moodle/Iomad 5.0 uniquement |
Ces résultats concernent les points d'entrée listés, pas l'ensemble des dépendances de rendu ni les plugins Iomad.

### 7.2 Rôles et capacités Iomad 5.0

Rôles ajoutés par Iomad, distincts du rôle Moodle natif Gestionnaire :
- `companymanager` et `companydepartmentmanager` : affectables au contexte entreprise ;
- `companycourseeditor` et `companycoursenoneditor` : affectables au contexte cours ;
- `companyreporter` : affectable au contexte entreprise ;
- `clientadministrator` et `clientreporter` : affectables au contexte système.

Le contexte entreprise (`CONTEXT_COMPANY`) et les capacités du bloc `block_iomad_company_admin` déterminent les actions disponibles. L'interface du thème doit tester les capacités effectives dans le contexte pertinent, pas seulement le shortname du rôle.

### 7.2 bis Statut de gestion et statut d'éducateur

Le champ Iomad `company_users.managertype` n'est pas un rôle Moodle. Il encode une fonction dans l'entreprise (notamment utilisateur, gestionnaire d'entreprise, gestionnaire de département ou reporter). Le champ `company_users.educator` est distinct et peut être combiné à cette fonction. Les valeurs des sélecteurs de formulaire (0, 1, 10, 11, 20, 21, 40, 41) sont converties avant enregistrement ; elles ne sont pas des identifiants de rôle.

La méthode `company::upsert_company_user()` affecte les rôles effectifs au contexte entreprise et, selon `iomad_autoenrol_managers`, peut aussi inscrire les gestionnaires aux cours avec `companycourseeditor` ou `companycoursenoneditor`. Les écrans doivent donc distinguer statut de gestion, statut d'éducateur et rôles réellement attribués. Les capacités sont configurables depuis `company_capabilities.php` et peuvent s'appuyer sur des modèles `company_role_templates` ; les menus ne doivent pas reposer sur une correspondance rôle-fonction figée.

### 7.3 Personnalisations du fork Iomad 5.0

Le fork conserve six fichiers modifiés dans `blocks/iomad_company_admin`. Ils ajoutent notamment le préfixe `[shortname]` dans les listes/sélecteurs de cours et restreignent le clonage aux cours appartenant à l'entreprise avec la capacité `createcourse`. Les fichiers sont détaillés dans la section 67.10 de la cartographie.

Points à surveiller avant toute reprise ou refactorisation :
- requêtes N+1 pour récupérer les shortnames dans deux formulaires ;
- condition `deletecourses` dupliquée à l'identique dans un `OR` ;
- récupération du cours source redondante dans le flux de clonage ;
- tests de permissions nécessaires pour cours propres, partagés et sous licence.

### 7.4 État des forks

- Fork Moodle : branches 4.5, 5.0, 5.1, 5.2 et 5.3 identiques aux branches officielles contrôlées.
- Fork Iomad : branches 4.5, 5.1 et 5.2 alignées sur l'amont ; branche 5.0 synchronisée avec l'amont tout en conservant les 14 commits locaux, plus le commit de fusion.
- Aucune CI n'a été lancée. Les résultats sont des comparaisons statiques de code ; les tests runtime et de non-régression restent à effectuer.

