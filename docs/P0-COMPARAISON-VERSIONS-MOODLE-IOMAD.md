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
| 5.3 | `MOODLE_503_STABLE` | — | Iomad 5.3 en cours de développement ; pas de comparaison Iomad 5.3 validable à ce stade |

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

## 2. Évolution du socle Moodle entre les générations

### 2.1 Réorganisation du répertoire public à partir de Moodle 5.1

Les sources des branches 4.5 et 5.0 sont à la racine du dépôt Moodle. À partir de 5.1, les composants web sont placés sous `public/` : par exemple `public/lib/accesslib.php` et `public/theme/boost/`.

Cette réorganisation concerne également Iomad 5.1 et 5.2. Ce n'est pas une différence Moodle/Iomad, mais une évolution du socle à intégrer dans les environnements de développement et de test. Le paquet du thème reste organisé selon la structure normale d'un plugin Moodle ; il ne faut pas ajouter un répertoire `public` dans l'archive du thème.

Moodle 5.2 introduit en outre un moteur de routage et recommande que la racine documentaire du serveur web pointe vers `public/`. Pour Project N, les URL doivent être construites avec les API Moodle (`moodle_url`, routes et liens fournis par les composants) plutôt qu'avec des chemins supposés.

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

Le template `drawers.mustache` évolue entre 4.5 et 5.0, reste identique entre 5.0 et 5.1, puis évolue à nouveau en 5.2. Les changements 5.2 observés comprennent notamment :

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

## 9. Sources de code examinées

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
- [Notes de version développeur Moodle 5.2](https://moodledev.io/general/releases/5.2)
- [Notes de mise à jour développeur Moodle 5.2](https://moodledev.io/docs/5.2/devupdate)
