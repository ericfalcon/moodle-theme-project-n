# P0 — Comparaison Moodle / Iomad et évolutions de versions

**Périmètre de cette étape :** moteur de rôles et de capacités, adaptations Iomad, socle Boost et changements structurels Moodle.  
**Branches examinées :** `MOODLE_405_STABLE` / `IOMAD_405_STABLE`, `MOODLE_500_STABLE` / `IOMAD_500_STABLE`, `MOODLE_501_STABLE` / `IOMAD_501_STABLE`, `MOODLE_502_STABLE` / `IOMAD_502_STABLE`.

## 1. Règle de comparaison

La comparaison est toujours effectuée entre branches de même génération :

| Génération | Moodle | Iomad | État de l'analyse |
|---|---|---|---|
| 4.5 | `MOODLE_405_STABLE` | `IOMAD_405_STABLE` | Sources disponibles |
| 5.0 | `MOODLE_500_STABLE` | `IOMAD_500_STABLE` | Sources disponibles |
| 5.1 | `MOODLE_501_STABLE` | `IOMAD_501_STABLE` | Sources disponibles |
| 5.2 | `MOODLE_502_STABLE` | `IOMAD_502_STABLE` | Sources disponibles |
| 5.3 | `MOODLE_503_STABLE` | — | Iomad 5.3 absent des forks consultés (statut de développement non vérifié) ; pas de comparaison Iomad 5.3 validable à ce stade |

Une différence n'est attribuée à Iomad que si elle apparaît entre deux branches de même génération. Les changements intervenus entre Moodle 4.5, 5.0, 5.1 et 5.2 sont consignés séparément.

### 1.1 Niveau exact des branches consultées

Les fichiers `version.php` donnent les versions suivantes au moment de l'analyse :

| Couple | Moodle | Iomad |
|---|---|---|
| 4.5 | 4.5.15, build 20261005 | 4.5.15, build 20261005 |
| 5.0 | 5.0.11, build 20261005 | 5.0.11, build 20261005 |
| 5.1 | 5.1.8, build 20261005 | 5.1.8, build 20261005 |
| 5.2 | 5.2.4+, build 20261013 | 5.2.4, build 20261005 |

Les couples 4.5, 5.0 et 5.1 sont alignés jusque dans leur version corrective affichée. Pour 5.2, les branches sont bien de même génération, mais le snapshot Moodle contient des correctifs postérieurs au build Iomad consulté. Les écarts très récents doivent donc être contrôlés avant d'être qualifiés de modifications Iomad.

### 1.2 Têtes de branches consultées pour la comparaison des points 65 à 68

Les têtes de branches ont été relues dans les forks GitHub au moment de cette passe :

| Dépôt | Branche | HEAD consulté |
|---|---|---|
| Moodle | `MOODLE_405_STABLE` | `c0690bbc765fccacd4956452cbff3f6aacad77c5` |
| Moodle | `MOODLE_500_STABLE` | `744cc0c19013e7be831dca24fafd6501d7eb8348` |
| Moodle | `MOODLE_501_STABLE` | `a682566412e1d429b95ac8ef4351a518f61b94c5` |
| Moodle | `MOODLE_502_STABLE` | `28fdef85e7043c8dd758af8c1a146da1419db0d0` |
| Moodle | `MOODLE_503_STABLE` | `236a9d702e62dc11f5aa66e4c8b5239cfc26c3d8` |
| Iomad | `IOMAD_405_STABLE` | `114ac90f25f74d79e4aae7bd72a749d8ab41a50e` |
| Iomad | `IOMAD_500_STABLE` | `40f37899a1d89bf307bd0cc0d205c01c03f78e5e` |
| Iomad | `IOMAD_501_STABLE` | `40cd06130bb227e159fae8df4e4a90b7a0f32adb` |
| Iomad | `IOMAD_502_STABLE` | `88a9a3e9997916a049048db536048918cbdc1e56` |

La branche Iomad 5.0 consultée est bien le fork personnel après intégration de l'amont ; les six fichiers personnalisés décrits dans la section 67.10 de la cartographie restent à préserver. Les SHA ci-dessus identifient les têtes de branches au moment de la comparaison ; ils ne remplacent pas les références de version corrective indiquées en 1.1.

## 2. Évolution du socle Moodle entre les générations

### 2.1 Réorganisation du répertoire public à partir de Moodle 5.1

Les sources des branches 4.5 et 5.0 sont à la racine du dépôt Moodle. À partir de 5.1, les composants web sont placés sous `public/` : par exemple `public/lib/accesslib.php` et `public/theme/boost/`.

Cette réorganisation concerne également Iomad 5.1 et 5.2. Ce n'est pas une différence Moodle/Iomad, mais une évolution du socle à intégrer dans les environnements de développement et de test. Le paquet du thème reste organisé selon la structure normale d'un plugin Moodle ; il ne faut pas ajouter un répertoire `public` dans l'archive du thème.

Moodle 5.2 introduit en outre un moteur de routage et recommande que la racine documentaire du serveur web pointe vers `public/` *(à vérifier)*. Pour Project N, les URL doivent être construites avec les API Moodle (`moodle_url`, routes et liens fournis par les composants) plutôt qu'avec des chemins supposés.

### 2.2 Capacités déclarées dans le cœur Moodle

Comptage des clés `moodle/*` dans `lib/db/access.php` ou `public/lib/db/access.php`, selon la génération :

| Génération | Capacités du cœur Moodle |
|---|---:|
| 4.5 | 265 |
| 5.0 | 269 |
| 5.1 | 270 |
| 5.2 | 268 |

Évolutions constatées entre les branches :

- **4.5 → 5.0 :** ajout de `moodle/course:viewoverview`, `moodle/reportbuilder:configurecustomfields`, `moodle/ai:viewaipolicyacceptancereport` et `moodle/ai:viewaiusagereport`.
- **5.0 → 5.1 :** ajout de `moodle/customfield:configureshared`.
- **5.1 → 5.2 :** retrait de `moodle/moodlenet:shareactivity` et `moodle/moodlenet:sharecourse`.

Ces évolutions peuvent modifier la disponibilité de certaines commandes ou rubriques d'administration. Elles ne justifient pas de créer une logique de droits par version dans le thème : les capacités doivent être interrogées à l'exécution et les entrées d'interface doivent être conditionnées à leur disponibilité.

Le fichier de capacités du cœur Iomad conserve, pour chacune des quatre générations examinées, les mêmes noms de capacités que le fichier Moodle correspondant. Les différences Iomad relevées dans ce fichier portent sur les archétypes auxquels certaines capacités sont accordées par défaut, pas sur l'ajout de nouvelles clés `moodle/*`. Les capacités spécifiques Iomad sont déclarées par les composants Iomad et doivent être cartographiées séparément.

### 2.3 Moteur accesslib

Les empreintes des fichiers `accesslib.php` montrent :

- Moodle 4.5 possède une version différente de celle de Moodle 5.0.
- Le fichier Moodle `accesslib.php` est identique dans les snapshots 5.0, 5.1 et 5.2 examinés.
- Iomad conserve des adaptations propres dans `accesslib.php` sur les quatre générations.

Le moteur de résolution des permissions n'est donc pas à réécrire dans Project N. Les changements de version doivent être surveillés, mais le thème doit consommer les API natives et Iomad plutôt que reproduire l'algorithme d'héritage.

## 3. Écarts Iomad constatés dans le système de rôles et de capacités

### 3.1 Sept archétypes supplémentaires

Dans les quatre branches Iomad examinées, `get_role_archetypes()` complète les archétypes Moodle par les sept archétypes Iomad :

- `companymanager`
- `companydepartmentmanager`
- `companycourseeditor`
- `companycoursenoneditor`
- `clientadministrator`
- `clientreporter`
- `companyreporter`

Ils ne sont pas tous des rôles d'entreprise : les deux rôles `companycourse*` sont des rôles pédagogiques de cours ; les autres sont liés à l'administration ou au reporting multi-entreprises.

Les sept rôles sont créés dans l'installation Iomad et leurs niveaux de contexte d'affectation sont définis séparément. Le rôle `clientadministrator` reste distinct de l'administrateur du site Moodle, qui est identifié par `is_siteadmin()`.

### 3.2 Autorisations par défaut ajoutées aux rôles pédagogiques Iomad

Dans les quatre générations, Iomad ajoute des autorisations par défaut aux archétypes `companycourseeditor` et `companycoursenoneditor` dans les déclarations de capacités du cœur Moodle.

Le relevé comparé donne **63 couples capacité / archétype** :

- 44 autorisations par défaut pour `companycourseeditor` ;
- 19 autorisations par défaut pour `companycoursenoneditor`.

Les domaines concernés incluent notamment :

- gestion du cours, des activités, des sections et des fichiers ;
- visibilité des activités et sections ;
- groupes et participants ;
- consultation de certaines informations de profil ;
- consultation des rapports et des notes ;
- gestion et consultation du carnet de notes ;
- calendrier, notes, blocs, évaluations et compétences.

Cette matrice est stable dans les quatre branches comparées. Les capacités exactes peuvent toutefois évoluer avec Moodle ; il faut donc conserver les contrôles par capacité et ne pas remplacer cette matrice par une liste codée en dur dans le thème.

Les autres archétypes Iomad ne reçoivent pas, dans ce fichier de capacités du cœur, le même ensemble d'autorisations pédagogiques. Leurs permissions d'entreprise sont principalement déclarées dans les composants Iomad.

### 3.3 Matrice par défaut d'affectation, de dérogation, de changement de rôle et de visualisation

Iomad complète aussi les matrices de relations entre archétypes utilisées par les fonctions Moodle de gestion des rôles.

Dans les valeurs par défaut examinées :

- les rôles Iomad de gestion et de reporting n'obtiennent pas de relations générales d'affectation, de dérogation ou de visualisation vers les autres rôles ;
- `companycourseeditor` peut basculer vers `companycoursenoneditor`, `student` et `guest` ;
- `companycoursenoneditor` peut basculer vers `student` et `guest`.

Il s'agit des matrices par défaut. Une installation peut comporter des rôles personnalisés et des dérogations ; l'interface ne doit donc pas considérer ces valeurs comme les droits effectifs de chaque utilisateur.

## 4. Différence importante entre les helpers de contrôle des capacités

### 4.1 `has_capability()` reste la fonction Moodle

Dans les branches examinées, la fonction globale Moodle `has_capability()` conserve son implémentation Moodle dans `accesslib.php`. Elle résout les permissions issues des affectations, des contextes et des définitions de rôles Moodle.

Iomad fournit en parallèle `local_iomad\\iomad::has_capability()`. Cette méthode :

1. vérifie d'abord la capacité avec la fonction Moodle ;
2. traite spécifiquement l'administrateur du site ;
3. détermine l'entreprise courante ou vérifie l'appartenance à l'entreprise demandée ;
4. examine les rôles et permissions applicables ;
5. applique les restrictions enregistrées dans `local_iomad_company_role_restrictions`.

Cette méthode est une API Iomad distincte. Il ne faut pas supposer que tout appel direct à la fonction globale Moodle applique automatiquement les restrictions propres à l'entreprise.

### 4.2 Asymétrie de `has_any_capability()` et `has_all_capabilities()`

Dans les quatre générations Iomad examinées :

- `has_any_capability()` appelle `iomad::has_capability()` ;
- `has_all_capabilities()` continue d'appeler la fonction globale Moodle `has_capability()`.

De plus, la version Iomad de `has_any_capability()` ne transmet pas ses paramètres `$user` et `$doanything` à `iomad::has_capability()`. La vérification porte donc sur l'utilisateur courant et ne conserve pas exactement le contrat de la fonction Moodle pour ces paramètres.

Cette asymétrie doit être prise en compte dans les développements et vérifiée dans les usages des composants Iomad. Elle ne doit pas être contournée par une vérification de shortname de rôle.

**Conséquence Project N :**

- pour les fonctions Moodle natives, utiliser les capacités Moodle dans le contexte approprié ;
- pour les fonctions propres à Iomad, utiliser l'API et les contrôles Iomad du composant concerné ;
- ne pas utiliser `has_any_capability()` comme vérification des droits d'un utilisateur tiers sous Iomad ;
- ne pas supposer que `has_all_capabilities()` applique les restrictions d'entreprise ;
- ne pas reproduire les restrictions Iomad dans le thème.

Ce point doit faire l'objet de tests ciblés sur une instance Iomad, en particulier pour les menus et tableaux de bord multi-entreprises.

## 5. Comparaison du thème parent Boost

### 5.1 Fichiers de configuration

Le fichier `theme/boost/config.php` conserve la même empreinte dans Moodle 4.5, 5.0, 5.1 et 5.2. Le fichier correspondant dans Iomad 5.2 possède exactement la même empreinte que celui de Moodle 5.2.

La déclaration des layouts Boost est donc stable sur cette période dans le fichier de configuration étudié.

### 5.2 Layout et template des tiroirs

Le fichier `layout/drawers.php` est identique dans Moodle 4.5, 5.0 et 5.1. Il évolue dans Moodle 5.2, notamment pour construire le sélecteur de navigation tertiaire et transmettre le nom et l'URL du cours au template.

Le template `drawers.mustache` évolue entre 4.5 et 5.0, reste identique entre 5.0 et 5.1, puis évolue à nouveau en 5.2. Les changements 5.2 observés comprennent notamment *(liste non vérifiée ligne à ligne ; à confirmer)* :

- un bloc de template pour l'en-tête du tiroir d'index du cours ;
- des icônes de fermeture adaptées au sens de lecture RTL/LTR ;
- une harmonisation des boutons d'icône ;
- le remplacement de l'ancien sélecteur d'URL par le sélecteur de navigation tertiaire.

### 5.3 Moodle / Iomad 5.2

Les fichiers suivants ont exactement la même empreinte dans Moodle 5.2 et Iomad 5.2 :

- `theme/boost/config.php` ;
- `theme/boost/layout/drawers.php` ;
- `theme/boost/templates/drawers.mustache`.

Iomad ne maintient donc pas ici une variante de Boost. Les différences d'interface Iomad sont à rechercher dans ses composants, blocs, menus, pages et contrôleurs métier, et non dans une divergence du layout Boost lui-même.

**Décision d'architecture :** Project N conserve un seul thème enfant de Boost. Les adaptations Iomad doivent être conditionnelles et localisées dans des points d'extension, sans dupliquer les templates communs. Toute surcharge d'un template Boost doit être justifiée par un besoin ergonomique réel et comparée à chaque génération Moodle.

## 6. Conséquences pour Project N

1. **L'espace d'interface ne correspond pas à un rôle unique.** Les espaces apprenant, formateur, entreprise et administration sont des regroupements ergonomiques ; les actions restent déterminées par les capacités effectives dans le contexte.
2. **Le contexte doit accompagner chaque contrôle.** Une capacité au niveau système, catégorie, cours ou activité ne doit pas être testée dans un contexte générique.
3. **Les rôles pédagogiques Iomad doivent être traités comme des rôles de cours.** Le rattachement d'entreprise et le statut de formateur ne remplacent pas l'affectation pédagogique.
4. **Le multi-entreprise est un état fonctionnel.** L'entreprise courante, l'appartenance à plusieurs entreprises, la sélection d'entreprise et les restrictions de rôles peuvent modifier les actions visibles.
5. **Les composants Iomad restent responsables de leurs contrôles métier.** Le thème ne doit ni reconstruire leurs règles ni prendre l'affichage d'un lien pour une autorisation.
6. **Les templates Boost sont à surcharger avec parcimonie.** Les changements 5.2 du tiroir et de la navigation tertiaire montrent qu'une surcharge ancienne peut masquer des améliorations natives.
7. **La compatibilité doit être vérifiée sur les couples de même génération.** Les tests ne doivent pas mélanger Moodle 5.2 et Iomad 5.1, même si leurs dates de publication sont proches.

## 7. Tests fonctionnels à ajouter à la matrice P0

| Cas | Vérification attendue |
|---|---|
| Moodle seul, manager | Capacités système/catégorie et accès à l'administration déléguée |
| Moodle seul, enseignant éditeur / non éditeur | Actions pédagogiques au niveau du cours et de l'activité |
| Iomad, company manager | Administration limitée à l'entreprise courante |
| Iomad, department manager | Périmètre limité au département |
| Iomad, educator | Droits pédagogiques issus des affectations de cours |
| Iomad, company course editor / non-editor | Matrice de capacités au niveau du cours |
| Iomad, client administrator / reporter | Portée multi-entreprises et reporting |
| Utilisateur membre de plusieurs entreprises | Changement d'entreprise courante et absence de fuite entre entreprises |
| Utilisateur suspendu ou association entreprise suspendue | Réduction ou refus des accès selon les règles Iomad |
| Restriction dans `local_iomad_company_role_restrictions` | Action et entrée de menu non proposées lorsque la capacité est refusée |
| Cumul de rôles et dérogations | Résultat conforme au moteur de permissions, y compris `CAP_PREVENT` et `CAP_PROHIBIT` |
| Contrôle d'un autre utilisateur | Ne pas utiliser le helper Iomad `has_any_capability()` sans tenir compte de ses paramètres ignorés |
| Navigation 5.2 | Navigation tertiaire et tiroirs compatibles avec le template Boost 5.2 |

## 8. Limites de cette étape et suite de l'analyse

Cette étape est une analyse statique des branches GitHub. Elle ne constitue pas une validation fonctionnelle des permissions sur des instances Moodle/Iomad configurées. Les rôles personnalisés, dérogations locales, plugins tiers et paramètres d'entreprise peuvent modifier les résultats.

La prochaine étape P0 doit cartographier les parcours et composants réellement rendus :

1. cours côté apprenant : index, sections, activités, progression et navigation ;
2. cours côté formateur : mode édition, sélecteur d'activités, participants, notes et corrections ;
3. administration Moodle : navigation, menus, pages et capacités ;
4. administration Iomad : menus dynamiques, blocs et changement d'entreprise ;
5. différences de templates, renderers, APIs et composants entre chaque couple de versions.

La sortie attendue sera une matrice **écran / action / composant natif / capacité / contexte / adaptation Iomad / versions**, avant toute implémentation du thème.

## 9. Comparaison des points 65 à 68 sur les branches actuelles

Cette passe reprend les points d'entrée ciblés des sections 65 à 68 de la cartographie. Les contenus des fichiers ont été récupérés directement depuis les forks Moodle et Iomad, puis comparés intégralement. « Identique » signifie égalité exacte du contenu récupéré ; « différent » ne signifie pas nécessairement changement fonctionnel.

### 9.1 Moodle — évolution entre 4.5 et 5.3

| Point | Fichier | Résultat sur les branches Moodle |
|---|---|---|
| 65 — cours | `course/view.php` | Contenu différent à chaque génération 4.5, 5.0, 5.1, 5.2 et 5.3 |
| 65 — édition | `course/edit.php` | Identique sur les cinq générations |
| 65 — participants | `user/index.php` | 4.5 diffère ; 5.0 = 5.1 = 5.2 ; 5.3 diffère |
| 65 — groupes | `group/index.php`, `group/group.php` | Identiques sur les cinq générations |
| 65 — membres | `group/members.php` | 4.5, 5.0 et 5.1 diffèrent ; 5.1 = 5.2 = 5.3 |
| 65 — carnet de notes | `grade/report/grader/index.php` | 4.5 diffère ; 5.0 = 5.1 = 5.2 = 5.3 |
| 65 — correction devoir | `mod/assign/classes/output/grading_app.php` | 4.5 = 5.0 ; 5.1 diffère ; 5.2 = 5.3 |
| 66 — comptes | `admin/user.php` | 4.5 diffère ; 5.0 = 5.1 = 5.2 ; 5.3 diffère |
| 66 — rapport utilisateurs | `admin/classes/reportbuilder/local/systemreports/users.php` | 4.5 diffère ; 5.0 = 5.1 ; 5.2 diffère ; 5.3 diffère de 5.2 |
| 66 — cohortes | `cohort/index.php` | 4.5 diffère ; 5.0 = 5.1 = 5.2 = 5.3 |
| 66 — actions groupées | `admin/user/user_bulk.php` | Identique sur les cinq générations |
| 67 — gestion catégories/cours | `course/management.php`, `course/editcategory.php` | Identiques sur les cinq générations |
| 68 — instances | `enrol/instances.php` | 4.5 diffère ; 5.0 = 5.1 = 5.2 = 5.3 |
| 68 — configuration instance | `enrol/editinstance.php` | Identique sur les cinq générations |
| 68 — inscription manuelle | `enrol/manual/manage.php` | 4.5, 5.0 et 5.1 diffèrent ; 5.1 = 5.2 = 5.3 |
| 68 — page d'inscription utilisateur | `enrol/index.php` | 4.5 diffère ; 5.0 = 5.1 = 5.2 = 5.3 |

Les chemins des fichiers web sont à la racine du dépôt en 4.5 et 5.0, puis sous `public/` en 5.1, 5.2 et 5.3. Cette réorganisation ne doit pas être transformée en chemins codés en dur dans Project N.

### 9.2 Iomad — comparaison avec Moodle de même génération

| Point d'entrée | 4.5 | 5.0 | 5.1 | 5.2 |
|---|---|---|---|---|
| `course/view.php` | Différent | Différent | Différent | Différent |
| `course/edit.php` | Identique | Identique | Identique | Identique |
| `user/index.php` | Identique | Identique | Identique | Identique |
| `admin/user.php` | Identique | Identique | Identique | Identique |
| `cohort/index.php` | Identique | Identique | Identique | Identique |
| `course/management.php` | Identique | Identique | Identique | Identique |
| `enrol/instances.php` | Identique | Identique | Identique | Identique |
| `enrol/manual/manage.php` | Identique | Identique | Identique | Identique |

Pour les quatre branches Iomad, le fichier `course/view.php` contient l'appel `iomad::iomad_check_course($id, $name, $idnumber, true)` avant la récupération du cours. Le contrôle est donc bien présent dans les quatre générations vérifiées. Il ne s'agit pas d'une simple adaptation de présentation : le contrôle conditionne la récupération du cours dans le périmètre accessible à l'utilisateur.

Les autres fichiers listés sont identiques entre Moodle et Iomad sur les couples de même génération. Cela ne signifie pas que tous les services, renderers, plugins ou contrôles d'accès sont identiques : les adaptations Iomad peuvent être portées par les composants appelés par ces contrôleurs.

### 9.3 Conséquences et limites

1. Les points d'entrée les plus stables pour une intégration commune sont notamment `course/edit.php`, `course/management.php`, `course/editcategory.php`, `enrol/editinstance.php` et les contrôleurs d'inscription comparés entre Moodle et Iomad. La stabilité doit toujours être confirmée au niveau des classes, formulaires, templates et composants appelés.
2. Les points d'entrée les plus sensibles aux versions sont `course/view.php`, le rapport système des utilisateurs, `admin/user.php` en 4.5 et 5.3, la correction des devoirs et la gestion manuelle des inscriptions.
3. Le thème doit s'appuyer sur les API de sortie Moodle et les plugins concernés ; il ne doit pas déduire d'une empreinte identique que le DOM, le JavaScript ou le rendu final sont inchangés.
4. Les personnalisations Iomad 5.0 du fork personnel restent une couche distincte de l'amont. Elles ne sont pas annulées ni requalifiées comme fonctionnalités natives Iomad.
5. Cette comparaison est statique. Aucun scénario d'instance, test de permission, test navigateur, test responsive, test clavier ou test lecteur d'écran n'a été exécuté dans cette passe.

Les résultats détaillés par parcours et leurs impacts sont consignés dans les sections 65.10–65.13, 66.10–66.16, 67.9–67.10 et 68.11–68.13 de `P0-CARTOGRAPHIE-NAVIGATION.md`.

### 9.4 Chaîne de rendu du cours et contrats de l'Output API

La comparaison a été étendue au renderer, aux objets de sortie, aux templates Mustache et au module AMD principal du format de cours. Les classes examinées sont notamment section_renderer, output/local/content, output/local/content/section et output/local/content/cm. Les templates examinés comprennent local/content, local/content/section/content et local/courseindex/drawer.

Constats :
- les classes de sortie du format de cours sont résolues dynamiquement par le format actif au moyen de get_output_classname ; un format tiers peut donc substituer ses classes de section, activité, disponibilité, achèvement ou contrôle d'édition ;
- les objets de sortie exportent des contextes de template ; les templates Mustache sont composés de partials et de blocs nommés, prévus pour être étendus ;
- le module AMD principal s'appuie sur les attributs data-for, data-action et les attributs Bootstrap pour gérer les sections, les activités, l'édition, les mutations et le repli ;
- en Moodle 5.3, le contrôle global de repli/dépli des sections est déplacé au niveau du template principal core_courseformat/local/content ; le contexte collapsemenu dans le template interne de section est déprécié ;
- en 5.3, le champ sectionreturnnum est déprécié dans l'objet de sortie de section et returnoptions est ajouté ;
- le module AMD utilise l'import Bootstrap Collapse depuis bootstrap en 5.3, alors que Moodle 5.0 l'importait depuis theme_boost/bootstrap/collapse ;
- les arbres de code révèlent des ajouts dans le sous-système : classes/templates d'aperçu d'activité en 5.0, architecture du sélecteur d'activité en 5.1, contenu de navigation linéaire en 5.3.

Les fichiers de la chaîne de rendu standard comparés entre Moodle et Iomad sont identiques pour les sept fichiers sélectionnés dans les couples 5.0 et 5.2. Les couples 4.5 et 5.1 restent à vérifier au même niveau.

### 9.5 Correction des devoirs et carnet de notes

Le renderer de mod_assign (mod/assign/classes/output/renderer.php) évolue dans les cinq générations comparées. La classe grading_app évolue entre 5.0 et 5.1, puis en 5.2 ; les versions 5.2 et 5.3 sont identiques pour ce fichier. À partir de Moodle 5.2, la classe prend explicitement en compte le marqueur dans les devoirs à plusieurs évaluateurs.

Le template grading_app est un assemblage de zones fonctionnelles distinctes : navigation entre participants, relecture, notation, actions, sauvegarde et séparateur redimensionnable. En 5.3, ce séparateur porte data-region=resizer, tabindex=0 et un libellé ARIA. Ces éléments sont des contrats d'interaction et d'accessibilité, pas de simples détails de présentation.

Le rapport gradereport_grader connaît un changement de contrat en 5.3 :
- la classe action_bar renvoie core/action_bar au lieu de gradereport_grader/action_bar ;
- le template spécifique gradereport_grader/action_bar est déprécié depuis Moodle 5.3, avec dépréciation finale annoncée pour Moodle 6.0 ;
- les composants de sélection de groupe, d'initiales, d'utilisateur et la barre d'action générale sont réutilisés depuis le cœur.

Conséquence : privilégier les composants communs Moodle et préserver les régions DOM, attributs ARIA, commandes clavier et actions natives dans les adaptations desktop et mobile. Toute surcharge du template spécifique du rapport de notes doit être revue pour Moodle 5.3.

Ces constats sont statiques ; ils ne valident ni les workflows de correction ni l'accessibilité réelle de l'interface.

### 9.6 Report Builder des comptes et cohortes

La comparaison a été étendue des pages d'entrée aux principales entités et classes système Report Builder.

Pour le rapport des comptes, le système assemble les entités utilisateur, cohorte, membre de cohorte et rôle. Les colonnes et filtres sont composés à partir des entités, des champs d'identité configurés, des champs de profil et des extensions. Les actions de ligne sont conditionnées par des callbacks qui contrôlent les capacités, les états du compte, l'identité MNet, le compte courant et le statut administrateur de la cible.

Pour les cohortes, le rapport système utilise les entités cohorte et membre de cohorte. Le contexte est une donnée de portée essentielle. La consultation, la sélection groupée, la modification, l'affichage/masquage, la suppression et l'affectation des membres dépendent des capacités dans ce contexte et de l'origine de la cohorte. Les cohortes synchronisées par un composant ne sont pas des objets librement modifiables.

Les fichiers sélectionnés ont été comparés intégralement entre Moodle et Iomad sur les couples 5.0 et 5.2 :
- rapport système utilisateurs ;
- entité utilisateur et datasource utilisateurs ;
- entités cohorte et membre de cohorte ;
- rapport système des cohortes.

Les fichiers sélectionnés sont identiques entre Moodle et Iomad sur chacun de ces deux couples. Les écrans spécifiques du bloc Iomad Company Admin, les filtres par entreprise/département et les règles de cloisonnement ne sont pas couverts par cette identité.

La comparaison interversions montre que le rapport système utilisateurs change entre 4.5 et 5.0, reste identique entre 5.0 et 5.1, puis évolue en 5.2 et 5.3. Les entités cohorte et membre de cohorte évoluent également au fil des générations. L'interface Project N doit donc exploiter les colonnes, filtres, actions et contextes effectivement fournis par Report Builder plutôt que reproduire un tableau statique.

### 9.7 Iomad Company Admin : gestion des utilisateurs et migration de schéma

Le parcours Iomad de gestion des utilisateurs a été examiné dans le fork Iomad 5.0 et dans la branche Iomad 5.2. Il ne s'agit pas d'une adaptation du Report Builder natif : le parcours s'appuie sur les pages du bloc block_iomad_company_admin, notamment editusers.php, editusers_table, company_users_form et les sélecteurs dédiés.

La page editusers applique le contexte entreprise, les capacités propres au bloc, la validation du département et la hiérarchie des départements. Les listes sont filtrées par entreprise, départements accessibles et type de gestionnaire. Les actions du tableau sont conditionnées par les capacités Iomad et l'état de l'utilisateur cible ; elles couvrent notamment l'édition, la réinitialisation du mot de passe, la suppression, la suspension/réactivation, les inscriptions aux cours et les licences.

La comparaison des fichiers montre des évolutions importantes entre Iomad 5.0 et 5.2 :
- editusers.php et editusers_table.php ont changé de contenu ;
- company_users_form.php et classes/forms/company_users_form.php ont également évolué ;
- en 5.0, les sélecteurs d'utilisateurs sont dans blocks/iomad_company_admin/lib/user_selectors.php ;
- en 5.2, les sélecteurs current_company et potential_company sont déplacés dans local/iomad/classes/user_selector et héritent de company_base.

La migration est structurelle et confirmée dans local/iomad/db/upgrade.php et local/iomad/db/install.xml. Plusieurs tables historiques deviennent des tables préfixées local_iomad_ ; en particulier company, department et company_users deviennent local_iomad_companies, local_iomad_company_departments et local_iomad_company_users. La migration des départements renomme également les champs company en companyid et parent en parentid, puis réorganise les index. Les tables de cours, licences, groupes d'entreprise, rôles, domaines et options suivent le même mouvement de centralisation dans local_iomad.

Cette évolution signifie que le code du bloc 5.0 et celui du bloc 5.2 ne partagent pas le même contrat de persistance. Le thème ne doit jamais interroger ces tables directement ni figer leurs noms. Il doit consommer les pages, classes de sortie et API prévues par la version Iomad active.

Le parcours d'affectation des utilisateurs à l'entreprise est également une opération métier : double sélecteur utilisateurs potentiels/utilisateurs actuels, contrôle du quota, affectation au département par défaut, événements d'affectation/mise à jour, retrait de l'association et événements correspondants. Une simple modification de rôle Moodle ne reproduit pas cette opération.

La comparaison 5.0/5.2 est statique et n'a pas exécuté de migration de base de données. Elle n'établit pas que le passage d'une instance 5.0 à 5.2 a été testé. Les personnalisations propres au fork Iomad 5.0 sont conservées et doivent rester séparées du code amont.

## 10. Sources de code examinées

- [Moodle 4.5 — accesslib.php](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/lib/accesslib.php)
- [Iomad 4.5 — accesslib.php](https://github.com/ericfalcon/iomad/blob/IOMAD_405_STABLE/lib/accesslib.php)
- [Moodle 5.0 — accesslib.php](https://github.com/ericfalcon/moodle/blob/MOODLE_500_STABLE/lib/accesslib.php)
- [Iomad 5.0 — accesslib.php](https://github.com/ericfalcon/iomad/blob/IOMAD_500_STABLE/lib/accesslib.php)
- [Moodle 5.1 — accesslib.php](https://github.com/ericfalcon/moodle/blob/MOODLE_501_STABLE/public/lib/accesslib.php)
- [Iomad 5.1 — accesslib.php](https://github.com/ericfalcon/iomad/blob/IOMAD_501_STABLE/public/lib/accesslib.php)
- [Moodle 5.2 — accesslib.php](https://github.com/ericfalcon/moodle/blob/MOODLE_502_STABLE/public/lib/accesslib.php)
- [Iomad 5.2 — accesslib.php](https://github.com/ericfalcon/iomad/blob/IOMAD_502_STABLE/public/lib/accesslib.php)
- [Moodle 5.2 — capacités du cœur](https://github.com/ericfalcon/moodle/blob/MOODLE_502_STABLE/public/lib/db/access.php)
- [Iomad 5.2 — capacités du cœur adaptées](https://github.com/ericfalcon/iomad/blob/IOMAD_502_STABLE/public/lib/db/access.php)
- [Iomad 5.2 — contrôle des capacités par entreprise](https://github.com/ericfalcon/iomad/blob/IOMAD_502_STABLE/public/local/iomad/classes/iomad.php)
- [Iomad 5.2 — contexte entreprise](https://github.com/ericfalcon/iomad/blob/IOMAD_502_STABLE/public/local/iomad/classes/custom_context/context_company.php)
- [Moodle 5.2 — layout Boost](https://github.com/ericfalcon/moodle/blob/MOODLE_502_STABLE/public/theme/boost/layout/drawers.php)
- [Iomad 5.2 — layout Boost](https://github.com/ericfalcon/iomad/blob/IOMAD_502_STABLE/public/theme/boost/layout/drawers.php)
- [Notes de version développeur Moodle 5.2](https://moodledev.io/general/releases/5.2) *(non consultée)*
- [Notes de mise à jour développeur Moodle 5.2](https://moodledev.io/docs/5.2/devupdate) *(non consultée)*
