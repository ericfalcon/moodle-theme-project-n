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
| Comptes utilisateurs | Points d'entrée + Report Builder comparés | Points d'entrée + Report Builder comparés | Points d'entrée + Report Builder comparés | Points d'entrée + Report Builder comparés | Points d'entrée + Report Builder comparés | Rapport système, entités et actions conditionnelles ; Company Admin : affectation, rôles, hiérarchie parent/enfant, retrait, licences, suspension/réactivation, suppression et contrôleurs/actions | Statique, partiel ; cartographie 66.10–66.22 et synthèse fonctionnelle [66.22](P0-SYNTHESE-66.22-COMPTES-COHORTES.md), comparaison §9.6–9.9. L'écart potentiel affichage/contrôle serveur en 4.5/5.0 reste une hypothèse à revalider dans `blocks/iomad_company_admin/editusers.php` et `classes/tables/editusers_table.php` (4.5/5.0), puis leurs chemins `public/` en 5.1/5.2 ; contrôler aussi les endpoints External API concernés. Aucun défaut d'autorisation n'est établi par la seule différence de code ; tests runtime, rôles, isolation, accessibilité et mobile à réaliser |
| Cohortes | Points d'entrée + Report Builder comparés | Points d'entrée + Report Builder comparés | Points d'entrée + Report Builder comparés | Points d'entrée + Report Builder comparés | Points d'entrée + Report Builder comparés | Entités, colonnes et actions contextuelles ; fichiers Report Builder natifs sélectionnés identiques Moodle/Iomad sur les quatre couples 4.5–5.2 | Statique ; synchronisations, cloisonnement Iomad et permissions runtime à tester |
| Catégories et cours plateforme | Points d'entrée + renderer comparés | Points d'entrée + renderer comparés | Points d'entrée + renderer comparés | Points d'entrée + renderer comparés | Points d'entrée + renderer comparés | Contrôleur stable mais helper/renderer/actionbar/templates évolutifs ; six fichiers cœur identiques Moodle/Iomad sur les couples 5.0 et 5.2 | Statique, partiel ; cartographie 67.9–67.13 (synthèse ergonomique) ; JavaScript, capacités et parcours runtime à tester |
| Méthodes d'inscription | Points d'entrée + plugin Iomad comparés | Points d'entrée + plugins natifs et Iomad comparés | Points d'entrée + plugins natifs et Iomad comparés | Points d'entrée + plugins natifs et Iomad comparés | Points d'entrée Moodle comparés ; Iomad 5.3 absent | Plugins natifs sélectionnés identiques Moodle/Iomad en 5.0 et 5.2 ; plugin Iomad enrol/license spécifique, absent de Moodle | Statique ; cartographie 68.11–68.18 (synthèse ergonomique) ; cron licence, synchronisations et plugins non sélectionnés à tester |
| Rôles et capacités | Contrôleurs natifs de gestion et d'affectation comparés | Contrôleurs natifs de gestion et d'affectation comparés | Contrôleurs natifs de gestion et d'affectation comparés | Contrôleurs natifs de gestion et d'affectation comparés | Non vérifié (Iomad 5.3 absent) | Rôles Moodle et capacités contextuelles ; rôles/fonctions Iomad distincts ; `manage.php` et `assign.php` identiques dans les couples 4.5–5.2 | Statique, partiel ; cartographie 66.19–66.21 et 70.1–70.8 ; matrice exhaustive, `accesslib.php`, héritage et runtime à valider |
| Rapports d'administration | Non vérifié | Non vérifié | Non vérifié | Non vérifié | Non vérifié | Report Builder, exports, permissions | À analyser |
| Paramètres de plateforme | Non vérifié | Non vérifié | Non vérifié | Non vérifié | Non vérifié | Arborescence admin et capacités | À analyser |
| Iomad multi-entreprise | Affectation, retrait et hiérarchie statiquement examinés | Affectation, retrait et hiérarchie statiquement examinés ; personnalisations du fork préservées | Schéma local_iomad, propagation des gestionnaires et tâches ad hoc examinés | Identique à 5.1 pour les classes métier principales examinées | Non disponible | Cloisonnement par entreprise, départements, gestionnaires hérités, rôles, cours partagés, licences et tâches différées | Statique ; cartographie 66.15–66.19 ; scénarios runtime multi-entreprises, accès direct, licences et tâches à tester |

Ce tableau est volontairement conservateur : « cartographie initiale antérieure » ne signifie pas comparaison interversions terminée.

### 3.1 Résultats détaillés des points 65 à 68

Les contenus complets des points d'entrée listés ont été comparés depuis les branches actuelles des forks. Le détail par fichier, génération et couple Moodle/Iomad est conservé dans :
- `P0-CARTOGRAPHIE-NAVIGATION.md`, sections 65.10–65.13, 66.10–66.22, 67.9–67.13 et 68.11–68.18 ;
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

### 7.2.1 Rôles et capacités Iomad 5.0

Rôles ajoutés par Iomad, distincts du rôle Moodle natif Gestionnaire :
- `companymanager` et `companydepartmentmanager` : affectables au contexte entreprise ;
- `companycourseeditor` et `companycoursenoneditor` : affectables au contexte cours ;
- `companyreporter` : affectable au contexte entreprise ;
- `clientadministrator` et `clientreporter` : affectables au contexte système.

Le contexte entreprise (`CONTEXT_COMPANY`) et les capacités du bloc `block_iomad_company_admin` déterminent les actions disponibles. L'interface du thème doit tester les capacités effectives dans le contexte pertinent, pas seulement le shortname du rôle.

### 7.2.2 Statut de gestion et statut d'éducateur

Le champ Iomad `company_users.managertype` n'est pas un rôle Moodle. Il encode une fonction dans l'entreprise (notamment utilisateur, gestionnaire d'entreprise, gestionnaire de département ou reporter). Le champ `company_users.educator` est distinct et peut être combiné à cette fonction. Les valeurs des sélecteurs de formulaire (0, 1, 10, 11, 20, 21, 40, 41) sont converties avant enregistrement ; elles ne sont pas des identifiants de rôle.

La méthode `company::upsert_company_user()` affecte les rôles effectifs au contexte entreprise et, selon `iomad_autoenrol_managers`, peut aussi inscrire les gestionnaires aux cours avec `companycourseeditor` ou `companycoursenoneditor`. Les écrans doivent donc distinguer statut de gestion, statut d'éducateur et rôles réellement attribués. Les capacités sont configurables depuis `company_capabilities.php` et peuvent s'appuyer sur des modèles `company_role_templates` ; les menus ne doivent pas reposer sur une correspondance rôle-fonction figée.

### 7.3 Personnalisations du fork Iomad 5.0

Un inventaire antérieur du fork Iomad 5.0 recense six fichiers supposément personnalisés dans `blocks/iomad_company_admin` (voir cartographie §67.10). **Cette liste et les effets décrits ci-dessous ne sont pas revalidés dans la présente passe** : ils doivent être confirmés par un diff fichier par fichier avec la branche amont exacte avant toute décision de portage. Ils ne doivent donc pas être présentés comme des constats nouvellement vérifiés.

| Fichier recensé dans l'inventaire antérieur | Effet rapporté — à revalider |
|---|---|
| `classes/forms/company_ccu_courses_form.php` | Affichage `[shortname] fullname` dans la liste des cours |
| `classes/forms/company_groups_form.php` | Préfixe `[shortname]` dans le sélecteur |
| `classes/forms/company_gu_courses_form.php` | Préfixe `[shortname]` dans le sélecteur associé |
| `classes/tables/iomad_courses_table.php` | Affichage du shortname et restriction rapportée du clonage |
| `iomad_courses_form.php` | Contrôle rapporté du cours source et suppression d'une attribution temporaire du rôle Manager |
| `lib/course_selectors.php` | Préfixe `[shortname]` dans les sélecteurs |

Points à surveiller avant toute reprise ou refactorisation, sous réserve de revalidation :
- requêtes N+1 pour récupérer les shortnames dans deux formulaires ;
- condition `deletecourses` dupliquée à l'identique dans un `OR` ;
- récupération du cours source redondante dans le flux de clonage ;
- tests de permissions nécessaires pour cours propres, partagés et sous licence.

### 7.4 État des forks

- **Fork Moodle :** l'identité avec les branches officielles 4.5 à 5.3 est une affirmation issue d'une vérification antérieure ; elle n'a pas été recontrôlée dans cette passe. Les commits de tête inscrits en §1 identifient les snapshots des forks, mais ne prouvent pas à eux seuls leur identité avec l'amont.
- **Fork Iomad :** l'alignement des branches 4.5, 5.1 et 5.2 sur l'amont n'a pas été recontrôlé dans cette passe. Le nombre de commits locaux de la branche 5.0 (14 ou 15 selon le périmètre de comptage, fusion comprise ou non) est **à recalculer** ; ne pas réutiliser ce chiffre comme état courant.
- Aucune CI n'a été lancée dans le cadre de cette vérification documentaire. Les tests runtime et de non-régression restent à effectuer.


### 7.5 Capacités et archétypes Iomad — contrôle statique 5.2

| Élément | Résultat contrôlé | Limite |
|---|---|---|
| block_iomad_company_admin/db/access.php | 86 capacités : 84 au contexte entreprise, 2 au contexte bloc | Branche Iomad 5.2 uniquement |
| local/iomad/db/access.php | 7 capacités : 6 au contexte entreprise, 1 au contexte bloc | Branche Iomad 5.2 uniquement |
| Archétypes Company Admin | companymanager dans 41 déclarations, companydepartmentmanager dans 20, clientadministrator dans 83, clientreporter dans 1 | Décompte de métadonnées, pas de permissions effectives |
| local/iomad:importtrackfrommoodle | RISK_SPAM / RISK_XSS | Risque déclaré dans le composant |
| accesslib.php Moodle/Iomad 5.2 | has_capability(), get_assignable_roles(), role_assign(), role_unassign(), accesslib_clear_role_cache() et get_role_access() identiques ; get_default_role_archetype_allows() étendue par Iomad | Comparaison fonction par fonction, pas identité des fichiers complets |

Le contexte CONTEXT_COMPANY et les contrôles de local_iomad\iomad constituent une couche Iomad distincte. Project N doit conserver les API Moodle pour les droits standards et utiliser les API Iomad pour les actions dépendant du périmètre entreprise. Les déclarations et archétypes ne remplacent jamais les permissions effectives calculées.

Sources : [Company Admin access.php](https://github.com/ericfalcon/iomad/blob/IOMAD_502_STABLE/public/blocks/iomad_company_admin/db/access.php), [local_iomad access.php](https://github.com/ericfalcon/iomad/blob/IOMAD_502_STABLE/public/local/iomad/db/access.php), [Iomad accesslib.php](https://github.com/ericfalcon/iomad/blob/IOMAD_502_STABLE/public/lib/accesslib.php).


### 7.6 Capacités natives moodle/role:* — contrôle des déclarations

| Capacité | Contexte | Archétypes par défaut | Moodle 4.5 / 5.2 / 5.3 | Iomad 4.5 / 5.2 |
|---|---|---|---|---|
| moodle/role:manage | Système | manager | Identique | Identique |
| moodle/role:assign | Cours | editingteacher, manager | Identique | Identique |
| moodle/role:review | Cours | teacher, editingteacher, manager | Identique | Identique |
| moodle/role:override | Cours | manager | Identique | Identique |
| moodle/role:safeoverride | Cours | editingteacher | Identique | Identique |
| moodle/role:switchroles | Cours | editingteacher, manager | Identique | Iomad ajoute companycourseeditor |

Les six déclarations ont été lues dans les fichiers core `db/access.php` des branches listées. Les valeurs décrivent les permissions initiales, pas les permissions effectives d'une installation. **Comptage corrigé pour Moodle 5.2 et Iomad 5.2 :** le fichier `public/lib/db/access.php` contient 319 lignes comportant une occurrence de `moodle/`, mais seulement 268 clés de capacités distinctes `moodle/*` (comptage des clés de déclaration). Les 268 clés distinctes sont identiques entre les deux fichiers comparés ; l'écart `companycourseeditor` sur l'archétype de `moodle/role:switchroles` porte sur les métadonnées de cette déclaration, pas sur le nombre de clés. Il ne faut donc pas annoncer « 319 capacités ».


### 7.7.1 Calcul des permissions et contrôle entreprise

| Élément | Moodle 5.2 | Iomad 5.2 | Conséquence |
|---|---|---|---|
| `has_capability()` natif | Fonction native | Corps identique | Conserver le contrôle standard par capacité et contexte |
| Hiérarchie des contextes | Chemin / profondeur et parents | Socle identique | Toujours résoudre le contexte de l'action |
| Attributions de rôles | Rôle, utilisateur, contexte, composant, itemid | API native identique | Préserver l'origine des attributions |
| Retrait de rôle | Retrait ciblé ; inclusion des attributions manuelles explicite | API native identique | Ne pas supprimer les rôles de plugins par défaut |
| Cache d'accès | Données utilisateur, marquage dirty, cache de définitions | Fonctions examinées identiques | Ne jamais manipuler les caches depuis le thème |
| Contrôle entreprise | Sans couche entreprise | `local_iomad\\iomad::has_capability()` ajoute le contrôle de membership et les restrictions par entreprise | Appeler l'API Iomad pour les workflows qui en dépendent |

Le contrôle Iomad dépend du périmètre entreprise explicite ou courant. **Le comportement exact du chemin sans `companyid` explicite et sans entreprise courante n'est pas considéré comme vérifié dans cette matrice** : la branche correspondante de `local_iomad\iomad::has_capability()` doit être relue et testée avant d'affirmer qu'elle autorise ou refuse l'action. Ne pas utiliser cette méthode comme preuve d'appartenance sans vérifier le périmètre transmis. Contrôle statique uniquement, sans test runtime.



### 7.7.2 Résolution effective des permissions et extension Iomad

| Élément | Moodle 4.5 / 5.2 / 5.3 | Iomad 5.2 | Conséquence thème |
|---|---|---|---|
| Résolution des rôles et des chemins de contexte | Moteur natif | Même moteur global Moodle | Ne pas recalculer les droits |
| Rôles cumulés dans plusieurs contextes | Oui | Oui | Ne pas retenir un « rôle principal » unique |
| `CAP_PROHIBIT` | Refus prioritaire dans le moteur Moodle | Sémantique native documentée ; interaction exacte avec le wrapper Iomad et les restrictions entreprise à revalider | Ne pas agréger les droits visuellement |
| Changement temporaire de rôle | Pris en compte par le moteur | Mécanisme Moodle conservé | Ne pas confondre avec une attribution réelle |
| Cache et invalidation accesslib | API native | API native conservée | Ne pas manipuler les caches internes |
| Restrictions entreprise | Sans objet | Contrôle supplémentaire via `local_iomad_company_role_restrictions` | Utiliser l'API Iomad dans les parcours entreprise |
| Contexte entreprise | Absent | `CONTEXT_COMPANY`, branche distincte sous le système | Ne pas le simuler dans Moodle |

Iomad 5.2 conserve les fonctions centrales de calcul et de cache d'accesslib comparées à Moodle 5.2. La méthode `local_iomad\iomad::has_capability()` comporte une couche supplémentaire d'appartenance et de restrictions par entreprise ; **le cas sans entreprise courante et l'articulation précise de `CAP_PROHIBIT` avec cette couche restent à revalider**. La classe `context_company` est une branche distincte du contexte système et ne devient pas le parent des contextes de cours.

Les fonctions `has_capability()` et `has_capability_in_accessdata()` ont le même corps dans les branches Moodle 4.5, 5.2, 5.3 et Iomad 5.2 consultées. La classe Iomad et la classe de contexte entreprise ont un SHA identique entre Iomad 5.1 et 5.2 ; les autres générations restent à vérifier.


### 7.8 Rôles Iomad : stabilité interversions 4.5 à 5.2

Les scripts d'installation consultés définissent les sept rôles suivants avec les mêmes shortnames, archétypes et contextes autorisés sur Iomad 4.5, 5.0, 5.1 et 5.2 :

| Rôle | Archétype | Contexte d'attribution autorisé |
|---|---|---|
| companymanager | companymanager | Entreprise |
| companydepartmentmanager | companydepartmentmanager | Entreprise |
| companycourseeditor | companycourseeditor | Cours |
| companycoursenoneditor | companycoursenoneditor | Cours |
| companyreporter | companyreporter | Entreprise |
| clientadministrator | clientadministrator | Système |
| clientreporter | clientreporter | Système |

Les blocs de création des rôles sont identiques entre les paires 4.5/5.0 et 5.1/5.2. Le fichier Company Admin `db/access.php` est identique sur les quatre branches (86 capacités). Les déclarations `local/iomad/db/access.php` ont été confirmées identiques entre 5.1 et 5.2 (7 capacités) ; leur emplacement antérieur reste à vérifier, il ne faut pas inférer leur absence en 4.5/5.0 à partir d'un chemin non trouvé.

Une analyse antérieure attribue à la tâche ad hoc `resetrolestask` (Iomad 5.1/5.2) la réinitialisation de six rôles, sans `clientadministrator`. **Cette affirmation et le déclenchement effectif de la tâche sont à revalider** dans le fichier de tâche et son enregistrement/planification avant d'en tirer une conséquence sur les permissions après installation. Les types métier `managertype`, notamment l'option `educator`, ne sont pas à confondre avec les sept rôles installés.



### 7.9 Affectation des gestionnaires et périmètre départemental Iomad

| Élément | Iomad 5.1 | Iomad 5.2 | Conséquence Project N |
|---|---|---|---|
| `company.php` | SHA identique | SHA identique | Workflow métier stable sur ces deux branches |
| `company_user.php` | SHA identique | SHA identique | Création/rattachement utilisateur stable sur ces deux branches |
| `iomad.php` | SHA identique | SHA identique | Contrôle complémentaire de capacité stable sur ces deux branches |
| `local/iomad/db/install.xml` | SHA identique | SHA identique | Schéma des rattachements et restrictions stable |
| `blocks/iomad_company_admin/db/access.php` | SHA identique | SHA identique | Déclarations des capacités Company Admin stables |
| Périmètre départemental | Rattachements + filtrage Iomad | Rattachements + filtrage Iomad | Ne pas déduire le périmètre du seul rôle |

Le rattachement utilisateur–entreprise–département est porté par `local_iomad_company_users`. Les types métier `managertype` (0 utilisateur, 1 gestionnaire entreprise, 2 gestionnaire département, 3 éducateur, 4 rapporteur entreprise) sont distincts des rôles Moodle. Les gestionnaires sont affectés dans le contexte entreprise ; le périmètre départemental est calculé à partir des associations départementales et des fonctions Iomad de parcours récursif. Les restrictions de capacités sont stockées par entreprise/rôle/capacité et appliquées en complément des capacités natives.

**Limite de comparaison :** les composants correspondants n'ont pas encore été comparés sur Iomad 4.5 et 5.0. Les personnalisations du fork 5.0 doivent être préservées et examinées séparément. Analyse statique, sans validation runtime.
