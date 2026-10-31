# Project N — Analyse P0 des espaces d'administration Iomad

> Analyse statique complémentaire du dépôt `ericfalcon/iomad`, branche `IOMAD_502_STABLE`. Elle décrit les contrats observés et les points de vigilance pour le thème Project N. Elle ne constitue pas une validation fonctionnelle, de sécurité ou d'accessibilité en exécution.

## 1. Périmètre de cette tranche

Les SHA ci-dessous ont été contrôlés. Les affirmations marquées « à vérifier » ou « non vérifié » ne l'ont pas été.

Fichiers examinés :

| Élément | Fichier | SHA |
|---|---|---|
| Page du tableau de bord | `public/blocks/iomad_company_admin/index.php` | `f4ecf844d8b955cda68cf3ad851e4faca0492ad9` |
| Déclaration des entrées de menu | `public/blocks/iomad_company_admin/db/iomadmenu.php` | `d65ae8da6070eadeba942f72bde1c37108d438c0` |
| Template du tableau de bord | `public/blocks/iomad_company_admin/templates/adminblock.mustache` | `cf3703f953b6e4a658e1f5d027054f5476f70af0` |
| Popover de changement d'entreprise | `public/blocks/iomad_company_admin/templates/company_info_popover.mustache` | `3edd5fff6ae08886d44fc8fb509d2c06e45204ee` |
| Bloc sélecteur d'entreprise | `public/blocks/iomad_company_selector/block_iomad_company_selector.php` | `370547fa9e023fd43cd02002bec3925cf5fd3443` |
| Données du bloc Mes cours | `public/blocks/iomad_mycourses/classes/output/main.php` | `b719beb452002b6f5fcd329dff7dc2476243520a` |
| Template Mes cours | `public/blocks/iomad_mycourses/templates/main.mustache` | `5919164f4f817ac829d71c570acde678fde7e380` |
| API métier entreprise | `public/local/iomad/classes/company.php` | `73d2678b440c6c8806f703243e3d178144661b80` |
| API métier utilisateurs/entreprises | `public/local/iomad/classes/company_user.php` | `f4ef072b9179007dc7ee773166b339ed652039c6` |

## 2. Tableau de bord du gestionnaire d'entreprise

### 2.1 Une page métier distincte de l'administration Moodle

Le tableau de bord est une page dédiée, `/blocks/iomad_company_admin/index.php`, avec son propre contexte de page et son propre rendu. Il ne s'agit pas d'une simple page d'administration Moodle remaquillée.

Le code :
- exige une session authentifiée ;
- détermine l'entreprise courante à partir du paramètre de changement et de la session ;
- initialise un contexte `context_company` lorsque l'entreprise courante est définie ;
- utilise le layout `base` ;
- construit un rendu spécifique à partir de la classe output `adminblock` et de templates Mustache.

**Conséquence Project N :** prévoir une intégration de cette page comme espace métier Iomad à part entière, mais ne pas la confondre avec l'administration système Moodle. Le thème peut harmoniser son enveloppe, ses repères de navigation et sa présentation responsive ; il ne doit pas réimplémenter ses opérations.

### 2.2 Catégories de navigation et droits

Le tableau de bord construit des catégories en fonction de capacités vérifiées dans le contexte de l'entreprise courante :

- gestion de l'entreprise ;
- gestion des utilisateurs ;
- gestion des cours ;
- gestion des licences ;
- gestion des compétences ;
- commerce électronique, sous condition de configuration ;
- microlearning ;
- rapports.

Les catégories ne sont donc pas une liste universelle affichée à tous les gestionnaires. Leur présence dépend des capacités et de certaines options fonctionnelles.

Les entrées sont ensuite collectées dynamiquement depuis les plugins qui déclarent une fonction dans `db/iomadmenu.php`. Chaque entrée est filtrée à nouveau par sa capacité avant d'être ajoutée à une catégorie *(filtrage par entrée à vérifier)*.

**Conséquences Project N :**
1. Ne jamais coder en dur une liste exhaustive des fonctions accessibles au gestionnaire.
2. Ne pas déduire les droits d'un nom de rôle ou de la présence d'un onglet.
3. Préserver la découverte dynamique des entrées de menu et leurs URLs.
4. Toute présentation alternative doit rester pilotée par les capacités effectives et le contexte courant.
5. Conserver l'accès aux pages opérationnelles natives, même si Project N propose un point d'entrée plus lisible.

### 2.3 Anomalies statiques à vérifier dans le tableau de bord

Deux points méritent un contrôle complémentaire dans Iomad :

- La variable de session `iomad_company_admin_tab` est lue et réécrite, mais la valeur `selectedtab` n'est pas utilisée ensuite pour sélectionner la catégorie active. Dans le code examiné, le booléen `selected` est affecté à la première catégorie disposant de la capacité correspondante. La persistance de l'onglet sélectionné semble donc inopérante ou incomplète.
- Les catégories sont stockées dans `$panes` avec des clés fonctionnelles (1 à 8), alors que `$tabs` est construit comme un tableau numérique compact. Lorsqu'une catégorie ne contient aucune entrée et doit être supprimée, le code utilise `$tabs[$paneid - 1]`. Cette correspondance n'est pas garantie si certaines catégories précédentes n'ont pas été créées. Risques : mauvais onglet supprimé, index absent ou sélection incorrecte.

Ces constats sont des anomalies potentielles de la page Iomad, pas des défauts à corriger dans Project N. Ils doivent être reproduits sur une installation avec plusieurs profils de capacités avant toute remontée au mainteneur.

## 3. Sélection et changement d'entreprise

### 3.1 Plusieurs mécanismes à ne pas confondre

L'analyse fait apparaître au moins deux interfaces de sélection :

**A. Bloc `block_iomad_company_selector`**

- Affiche un sélecteur Moodle `single_select` qui redirige vers le tableau de bord de gestion.
- Vérifie la capacité `block/iomad_company_admin:company_add` dans le contexte système.
- La liste des entreprises est fournie par `company::get_companies_select()`, qui applique les règles d'accès.
- Le code contient un test `if (!empty($company))` sans initialisation locale de `$company` dans cette méthode : le contexte reste donc le contexte système dans ce chemin de rendu.

Ce bloc ne doit pas être considéré comme le sélecteur universel de tous les utilisateurs Iomad.

**B. Sélecteur de la barre de navigation**

La méthode `company_user::add_user_popup_selector()` alimente le popover de la barre de navigation :
- récupère l'entreprise courante de l'utilisateur ;
- affiche son nom et son logo ;
- récupère les autres entreprises accessibles à l'utilisateur ;
- trie les entreprises selon `cu.lastused DESC, name ASC` ;
- propose un changement vers le tableau de bord Iomad ;
- limite le menu rapide aux entreprises récentes et propose un accès à une sélection complète au-delà du seuil prévu.

C'est ce mécanisme qui correspond au changement d'entreprise d'un utilisateur rattaché à plusieurs entreprises. Il ne faut pas le remplacer par le bloc précédent ni confondre ses droits avec ceux d'un administrateur global.

### 3.2 Filtrage des entreprises accessibles

Dans `company::get_companies_select()` :
- un utilisateur disposant de `company_view_all` au contexte système peut obtenir la liste globale, sous réserve des filtres de suspension et de structure ;
- les autres utilisateurs obtiennent les entreprises auxquelles ils sont rattachés via `local_iomad_company_users`, avec exclusion des rattachements suspendus ;
- l'option de structure d'entreprise peut ajouter des sociétés filles lorsque la capacité `canviewchildren` est présente dans le contexte correspondant ;
- la recherche et l'ordre de tri sont pris en compte.

Project N ne doit jamais construire sa propre liste d'entreprises à partir d'un accès direct aux tables. Il doit consommer les mécanismes Iomad ou les données déjà exposées par les composants natifs.

### 3.3 Contexte d'entreprise et personnalisation visuelle

Le changement d'entreprise est mémorisé dans `$SESSION->currenteditingcompany`. Le tableau de bord initialise ensuite son contexte métier à partir de cette valeur. Le changement peut également entraîner l'application du thème configuré pour l'entreprise dans la session *(non vérifié dans le code)*.

Le README de `local_iomad` avertit toutefois que le CSS et le logo propres à chaque entreprise ne sont pas automatiquement compatibles avec tous les thèmes : cela dépend notamment de l'utilisation des réglages natifs de logo et des mécanismes de thème *(contenu du README non vérifié)*.

**Conséquences Project N :**
- rendre l'entreprise active clairement identifiable, sans ambiguïté avec l'identité globale de la plateforme ;
- traiter logo, couleurs et éventuels éléments de marque par entreprise comme un sujet d'intégration Iomad explicite ;
- ne pas supposer que le changement de thème de session suffit à appliquer correctement toute la personnalisation multi-entreprise ;
- tester le changement d'entreprise avec un utilisateur membre de plusieurs entreprises, puis vérifier les pages, menus, logos et URLs après redirection ;
- tester les accès directs aux URLs et les données affichées dans chaque contexte, pas seulement la visibilité des liens.

## 4. Points d'accessibilité du sélecteur de la barre de navigation

Le template `company_info_popover.mustache` présente plusieurs points à contrôler :

- `aria-expanded="true"` est écrit en dur sur le bouton d'ouverture du menu, alors que le menu est initialement replié ;
- les liens de choix d'entreprise utilisent `role="menuitem"` et `tabindex="-1"` dans un conteneur `role="menu"` ;
- le composant carousel interne désactive explicitement les interactions clavier et tactiles via ses options ;
- les images de logo des entreprises dans la liste ne déclarent pas de texte alternatif ;
- le nom accessible du bouton est actuellement une chaîne anglaise codée en dur (`Company menu`).

Il s'agit de points de revue du composant Iomad existant. Avant toute surcharge, vérifier le comportement réel du menu avec clavier, lecteur d'écran et Bootstrap : état développé/replié, déplacement du focus, activation, fermeture, retour du focus et annonce du nom de l'entreprise. Project N ne doit pas dupliquer le menu si une amélioration ciblée du composant existant ou de ses styles suffit.

## 5. Bloc Mes cours : anomalie de sélection d'onglet

La classe output `block_iomad_mycourses\\output\\main` calcule les booléens `viewingavailable`, `viewinginprogress`, `viewingcompleted` et `viewingmandatory` à partir de la préférence d'onglet mémorisée.

Après ce calcul, si l'onglet mémorisé est `inprogress`, qu'aucun cours n'est en cours et que des cours disponibles existent, le code modifie `$tab` en `available`. Les booléens de rendu ne sont toutefois pas recalculés.

Le template utilise ces booléens pour affecter les classes `active` aux onglets et panneaux. Le changement tardif de `$tab` ne peut donc pas, à lui seul, activer le panneau « disponibles ». Le parcours peut afficher un onglet « en cours » vide au lieu de basculer vers les cours disponibles.

**Conséquence Project N :** ne pas reproduire cette logique dans le thème. Il faut vérifier le comportement sur une instance avec un utilisateur ayant uniquement des cours disponibles. Si l'anomalie est confirmée, la correction relève du bloc Iomad ou d'un correctif amont, pas d'un recalcul métier dans le thème.

## 6. Accessibilité du template Mes cours

Le template `main.mustache` expose des onglets avec `role="tab"` et des panneaux avec `role="tabpanel"`. Dans le fichier examiné, les relations explicites `aria-controls`, `aria-labelledby` et les identifiants propres aux onglets ne sont pas visibles. Les panneaux n'ont pas non plus de nom accessible explicitement relié à leur onglet.

Les commandes de tri, d'ordre et de changement de vue sont des liens avec `href` et des attributs `data-action`. Elles déclenchent des changements de préférence plutôt qu'une navigation vers une nouvelle ressource. Leur sémantique et leur comportement clavier doivent être vérifiés ; un bouton peut être plus approprié pour une action sans navigation, si le contrat JavaScript le permet.

Le template contient aussi des styles inline et des contrôles alignés horizontalement. Ils nécessitent un test de reflow sur smartphone et à 320 CSS px.

**Conséquence Project N :** conserver les attributs `data-action`, `data-name`, `data-value`, l'identifiant unique de bloc et le conteneur `data-region="iomad_mycourses"` tant que les modules JavaScript correspondants en dépendent. Toute amélioration de la sémantique doit être validée avec le comportement réel du plugin.

## 7. Décisions et suite de l'analyse

| Sujet | Décision Project N |
|---|---|
| Tableau de bord gestionnaire | Préserver la page métier Iomad et ses opérations ; harmoniser l'enveloppe et la navigation |
| Menu gestionnaire | Consommer les entrées dynamiques et vérifier les capacités dans le contexte courant |
| Sélecteur entreprise | Distinguer le sélecteur global du bloc et le sélecteur multi-appartenance de la barre de navigation |
| Multi-entreprise | Respecter le contexte et les API Iomad ; ne pas reconstruire les listes ni les règles d'accès |
| Marque par entreprise | Prévoir une validation dédiée du logo, des couleurs et du thème de session |
| Mes cours | Conserver la logique métier Iomad ; signaler l'anomalie de bascule d'onglet à reproduire |
| Accessibilité | Auditer les menus et onglets en exécution avant de décider d'une surcharge |

### Prochaine tranche recommandée

1. Examiner les pages et formulaires du gestionnaire par familles : entreprises, départements, utilisateurs, gestionnaires, cours, licences et paramètres.
2. Cartographier les capacités de chaque famille et distinguer les fonctions d'administration globale des fonctions déléguées aux gestionnaires d'entreprise.
3. Examiner le sélecteur complet `fullselect.php` et le mécanisme de persistance du contexte après changement d'entreprise.
4. Vérifier les pages personnalisées Iomad (`iomadcustompage`) et les emplacements des blocs sur les tableaux de bord.
5. Compléter l'analyse des plugins de rapports et distinguer les rapports génériques des rapports dépendant des entreprises ou licences.

Aucun test d'installation, de navigation, de sécurité inter-entreprises, axe-core, clavier, NVDA, TalkBack ou smartphone n'a été exécuté dans cette tranche. Les constats d'accessibilité et les anomalies signalées doivent être reproduits en environnement avant d'être qualifiés de défauts confirmés.


## 8. Gestion des entreprises, départements et sélection du tenant — Iomad 5.2

> Analyse statique des sources de la branche `IOMAD_502_STABLE`, complétée par une comparaison ciblée des mêmes fichiers dans les branches 4.5, 5.0 et 5.1. Elle ne vaut ni test d'autorisation en exécution ni audit exhaustif des formulaires.

### 8.1 Création et modification d'une entreprise

Fichiers contrôlés :

| Élément | Fichier | SHA |
|---|---|---|
| Contrôleur de création/modification | `public/blocks/iomad_company_admin/company_edit_form.php` | `b6c985af711eb64c5734cd3bd39d82a0f71a1006` |
| Formulaire Moodle | `public/blocks/iomad_company_admin/classes/forms/company_edit_form.php` | `07aa54436d127532cb3d020f4102d6a991d02b87` |
| API métier | `public/local/iomad/classes/company.php` | `73d2678b440c6c8806f703243e3d178144661b80` |

Le contrôleur ne se limite pas à afficher un formulaire :
- il distingue création, modification et création d'une entreprise enfant ;
- il détermine le contexte système ou le contexte de l'entreprise concernée ;
- il contrôle les capacités `company_edit`, `company_add` et `company_add_child` avec les API Iomad ;
- il prépare les données déjà saisies après certains retours de formulaire, notamment via la session ;
- il prépare les zones de brouillon pour le logo, le logo compact, le favicon et les éléments graphiques des certificats ;
- il associe à l'entreprise des paramètres qui dépassent la simple identité : modèles de rôles et de courriels, page de tableau de bord, notifications, champs de profil et options avancées.

Le formulaire est lui-même conditionnel : les champs et options dépendent du contexte, du statut création/modification, de l'existence d'une entreprise parente et des capacités disponibles. Certaines options sensibles, comme la limite d'utilisateurs, sont conditionnées par une capacité spécifique. Il ne faut donc pas remplacer cette page par un formulaire générique Project N ni présumer qu'un formulaire identique est présenté à tous les gestionnaires.

**Conséquence Project N :** l'écran d'entreprise doit rester la page Iomad native. Le thème peut agir sur le layout, la hiérarchie visuelle, les espacements et le responsive, mais il ne doit pas reconstruire les règles de visibilité des champs, les valeurs par défaut, les transferts de fichiers ou les contrôles de capacité.

### 8.2 Hiérarchie des entreprises

Le code de création distingue explicitement une entreprise racine d'une entreprise enfant. La création enfant utilise le contexte de la société parente et contrôle la capacité `company_add_child`. La page récupère aussi les données de la société parente et peut reprendre certains paramètres et éléments graphiques comme valeurs initiales.

Cette hiérarchie est une relation métier Iomad, distincte de la hiérarchie des départements. Elle doit être représentée sans la confondre avec une arborescence de catégories de cours Moodle.

Points à préserver :
1. distinguer l'entreprise courante, l'entreprise parente et l'entreprise enfant ;
2. ne pas afficher une action « créer une entreprise enfant » sur la seule base d'un rôle ou d'un lien connu ;
3. conserver les contrôles de capacité et le contexte serveur ;
4. ne pas déduire que les valeurs reprises du parent sont nécessairement héritées dynamiquement après la création : le code observé les utilise comme données initiales dans le formulaire.

### 8.3 Gestion des départements

Fichier contrôlé :

| Élément | Fichier | SHA |
|---|---|---|
| Formulaire de département | `public/blocks/iomad_company_admin/classes/forms/department_edit_form.php` | `93b6ba1552b26f10f2237fbf82a1b1f76fc95532` |

Le formulaire reçoit explicitement l'identifiant de l'entreprise, l'identifiant du département et un objet de rendu. Il :
- charge le département à modifier et son parent ;
- propose un sélecteur d'arborescence de départements fourni par le renderer Iomad ;
- gère le nom complet et le nom court ;
- vérifie l'unicité du nom court dans le périmètre de l'entreprise ;
- limite le nom court aux lettres ASCII, chiffres et underscore.

La structure est donc une arborescence de départements à l'intérieur d'une entreprise. Les utilisateurs peuvent être rattachés à plusieurs départements ; les gestionnaires de département et les profils « report only » voient leur département et ses sous-départements selon leurs affectations. Le gestionnaire d'entreprise, lui, est affecté au niveau supérieur de l'entreprise et ne doit pas être assimilé à un gestionnaire de département.

**Conséquence Project N :** ne pas réduire le département à un simple champ de profil ni le représenter comme un rôle Moodle. C'est une structure d'organisation qui influe sur les populations visibles, les rapports et la délégation des tâches. La cartographie des pages d'affectation des utilisateurs aux départements reste à compléter.

### 8.4 Sélection complète d'une entreprise

Fichiers contrôlés :

| Élément | Fichier | SHA |
|---|---|---|
| Page de sélection complète | `public/blocks/iomad_company_admin/fullselect.php` | `84728ca871d267f69ed1785394d309adbf96103d` |
| Objet de données | `public/blocks/iomad_company_admin/classes/output/full_companies_select.php` | `d26825e3ef98c4e9180aeb7c4b1f5a4dc9d922d6` |
| Formulaire de recherche | `public/local/iomad/classes/forms/company_search_form.php` | `842b8d61202ecfa2852196f0bf2c5ccd5ba338b5` |

La page `fullselect.php` :
- exige une authentification ;
- reprend l'entreprise courante depuis la session pour déterminer le contexte de page ;
- utilise le layout `base` ;
- initialise le module AMD `block_iomad_company_admin/admin` ;
- associe un formulaire de recherche et un objet `full_companies_select` ;
- délègue la constitution de la liste à `company_user::get_all_user_companies($search)`.

Le sélecteur complet est donc une vue des entreprises accessibles à l'utilisateur, pas une liste globale autonome. Le formulaire de recherche utilise `PARAM_CLEAN`, tandis que le paramètre d'entrée du contrôleur est lu avec `PARAM_ALPHANUM` : cette différence de nettoyage mérite une vérification fonctionnelle pour les noms contenant espaces, accents ou ponctuation. Il ne s'agit pas, à ce stade, d'un défaut confirmé.

**Conséquence Project N :** le changement d'entreprise doit conserver le flux natif, la liste autorisée et la persistance du contexte. Un sélecteur Project N ne peut être qu'une autre présentation du même contrat métier, pas une requête de recherche indépendante dans les entreprises.

### 8.5 Comparaison ciblée des générations Iomad

Les fichiers de formulaire de création/modification d'entreprise et de département ont été récupérés dans les branches suivantes :

| Branche | Fichier entreprise (SHA) | Fichier département (SHA) |
|---|---|---|
| `IOMAD_405_STABLE` | `265d4ab2c6dab3b776180e2091ffdfab8c47b93d` | `43241363aa9aa058e0b4efebc1e51a33d3e448e6` |
| `IOMAD_500_STABLE` | `fed080e96b062859b587657058966246b78f5574` | `6e46fe829ef3208d918b21713c14508cad451463` |
| `IOMAD_501_STABLE` | `b6c985af711eb64c5734cd3bd39d82a0f71a1006` | `93b6ba1552b26f10f2237fbf82a1b1f76fc95532` |
| `IOMAD_502_STABLE` | `b6c985af711eb64c5734cd3bd39d82a0f71a1006` | `93b6ba1552b26f10f2237fbf82a1b1f76fc95532` |

Résultat strict de cette comparaison :
- les deux fichiers ciblés sont identiques entre Iomad 5.1 et 5.2 (mêmes SHA) ;
- ils diffèrent entre 4.5 et 5.0, et entre 5.0 et 5.1 ;
- en 4.5/5.0, les chemins sont sous `blocks/` et `local/` ; en 5.1/5.2, ils sont sous `public/blocks/` et `public/local/`, conformément à la nouvelle organisation du code Moodle ;
- cette comparaison ne permet pas d'affirmer que toutes les pages d'administration sont identiques entre 5.1 et 5.2, ni de qualifier chaque différence 4.5/5.0/5.1 comme fonctionnelle : il faut comparer les contrôleurs, formulaires, classes métier, templates et modules AMD associés.

### 8.6 Décisions de conception et travaux encore ouverts

| Sujet | Décision / statut |
|---|---|
| Formulaire entreprise | Préserver le formulaire Iomad conditionnel et ses capacités ; ne pas le remplacer |
| Entreprises parentes/enfants | Distinguer cette hiérarchie de celle des départements et des catégories Moodle |
| Département | Conserver l'arborescence et le périmètre d'accès associé |
| Affectation multi-départements | À cartographier dans les pages utilisateurs et département |
| Sélecteur complet | Préserver `get_all_user_companies()`, le filtrage et le contexte de session |
| Téléversements | Cartographier les zones de brouillon, fileareas, validation et affichage des fichiers avant toute surcharge |
| Compatibilité multi-version | Comparaison ciblée effectuée sur deux formulaires seulement ; couverture insuffisante |
| Accessibilité | Pas de test clavier, lecteur d'écran, reflow ou axe-core effectué |

**Prochaine tranche :** cartographier les pages d'affectation et de gestion des utilisateurs (création, modification, suspension, rattachement multi-entreprises, départements, types de gestionnaires et import CSV), puis les cours/licences. Les pages de rapports seront traitées séparément car elles mélangent données Moodle, données entreprise, licences et parfois historiques d'archivage.


## 9. Formulaire de gestion des utilisateurs — Iomad 5.2

> Première lecture statique du formulaire utilisateur et de l'API métier. Le contrôleur qui orchestre l'enregistrement, les pages de liste, l'affectation multi-départements et l'import CSV restent à cartographier.

### 9.1 Formulaire d'ajout/modification

| Élément | Fichier | SHA |
|---|---|---|
| Formulaire utilisateur | `public/blocks/iomad_company_admin/classes/forms/user_edit_form.php` | `a37bb53203db915309c101c8b9111aeeb700055b` |
| API utilisateur/entreprise | `public/local/iomad/classes/company_user.php` | `f4ef072b9179007dc7ee773166b339ed652039c6` |

Le formulaire `user_edit_form` est un formulaire Moodle spécifique Iomad. Son constructeur reçoit l'entreprise sélectionnée, le département, une licence éventuelle et le contexte entreprise. Il prépare notamment :
- l'arborescence des départements visibles pour l'utilisateur courant ;
- le niveau hiérarchique de référence, avec un traitement différent si la capacité `edit_all_departments` est présente ;
- la liste des cours de l'entreprise ;
- le sélecteur de département et les données associées.

Les champs visibles couvrent plusieurs domaines fonctionnels :
- identité et coordonnées utilisateur, avec les champs de nom requis configurés dans Moodle ;
- adresse électronique et, selon la configuration Iomad, nom d'utilisateur ;
- génération éventuelle du mot de passe, changement obligatoire et envoi différé des informations de connexion ;
- rattachement à un département ;
- type de gestionnaire et fonction pédagogique ;
- champs de profil globaux et champs de profil propres à l'entreprise ;
- attribution éventuelle d'une licence et choix de cours associés, si la capacité d'allocation de licences est présente.

Le formulaire ne propose pas un ensemble fixe de rôles. Les options sont construites selon les capacités disponibles dans le contexte de l'entreprise :
- utilisateur standard ;
- gestionnaire d'entreprise ;
- gestionnaire de département ;
- responsable de rapports entreprise ;
- formateur/éducateur ;
- combinaisons de ces fonctions lorsque la configuration autorise leur cumul.

L'option éducateur et les combinaisons correspondantes dépendent notamment du réglage `autoenrol_managers`. Cette logique confirme qu'il faut éviter de reconstruire les profils Iomad à partir d'une simple liste de rôles Moodle ou d'un libellé de fonction.

### 9.2 Départements et périmètre de visibilité

Le formulaire construit la liste des départements à partir du niveau hiérarchique auquel l'utilisateur courant a accès. Un utilisateur ayant `edit_all_departments` reçoit l'ensemble de l'arborescence de l'entreprise ; sinon, la liste est dérivée de ses niveaux d'affectation et de leurs sous-départements.

Le choix d'un département dans ce formulaire ne suffit pas à cartographier toute la gestion des affectations : les pages dédiées à l'affectation d'un utilisateur à plusieurs départements, à la modification des rôles départementaux et à la suppression d'une affectation doivent encore être inspectées.

### 9.3 Licences et accès aux cours

Lorsque la capacité `allocate_licenses` est disponible, le formulaire affiche une zone de gestion des licences. La sélection est filtrée sur l'entreprise concernée et sur des licences non expirées disposant encore de capacité. Le formulaire associe ensuite les licences aux cours correspondants.

Cela confirme que l'ajout d'un utilisateur Iomad peut combiner identité, rattachement organisationnel, fonction de gestion et droit d'accès à la formation. Ces dimensions doivent rester séparées dans la présentation Project N :
- l'identité et le compte Moodle ;
- l'appartenance à l'entreprise et aux départements ;
- les fonctions de gestion/formation ;
- l'accès aux cours, éventuellement conditionné par une licence.

Le thème ne doit ni calculer l'éligibilité d'une licence ni décider quels cours sont accessibles. Il présente les données et actions renvoyées par les composants Iomad.

### 9.4 API de création d'utilisateur

La méthode `local_iomad\\company_user::create()` prépare la création ou la mise à jour du compte à partir des valeurs par défaut de l'entreprise et des données du formulaire. Elle :
- détermine l'entreprise à partir de l'identifiant fourni ou du nom court ;
- place temporairement l'entreprise dans la session pour le traitement des événements ;
- applique les valeurs par défaut de l'entreprise ;
- construit ou reprend le nom d'utilisateur ;
- contrôle les collisions de nom d'utilisateur, d'adresse électronique et, selon la configuration, de triplet nom/prénom/courriel dans une autre entreprise ;
- tient compte du paramétrage des notifications au gestionnaire.

La méthode illustre pourquoi le thème ne doit pas implémenter lui-même la création de comptes, ni présumer qu'un compte utilisateur correspond à une seule entreprise. L'identité Moodle et les rattachements Iomad sont deux objets différents.

### 9.5 Implications pour Project N

1. La fiche utilisateur doit être considérée comme une page métier Iomad, et non comme le formulaire natif Moodle `user/edit.php` simplement remaquillé.
2. Les sections et champs affichés doivent rester pilotés par les capacités, la configuration et le contexte entreprise.
3. Les fonctions « gestionnaire », « formateur » et « utilisateur » ne doivent pas être fusionnées en un rôle unique dans l'interface.
4. Les départements visibles doivent respecter la hiérarchie autorisée ; ne pas charger tous les départements puis filtrer côté navigateur.
5. La licence ne doit pas être présentée comme une simple inscription au cours : elle constitue une condition d'accès et un objet de suivi distinct.
6. Les écrans de liste et d'édition doivent préserver les actions natives, les contrôles de capacité et les éventuelles opérations en lot.

### 9.6 Limites et prochaine vérification

| Point | Statut |
|---|---|
| Composition du formulaire utilisateur | Observée statiquement |
| Options de fonctions et combinaisons éducateur/gestionnaire | Observées statiquement ; cas de droits à vérifier |
| Filtrage de l'arborescence de départements | Observé dans le constructeur ; à vérifier avec plusieurs profils en exécution |
| Attribution des licences | Présence et filtrage de base observés ; workflow complet non cartographié |
| Contrôleur d'enregistrement et contrôles serveur | À inspecter |
| Liste des utilisateurs, suspension et suppression | À inspecter |
| Affectations multi-entreprises et multi-départements | À inspecter |
| Import CSV et traitement des erreurs | À inspecter |
| Compatibilité 4.5/5.0/5.1/5.2 du formulaire | Non comparée dans cette tranche |
| Accessibilité du formulaire | Aucun test axe-core ou manuel effectué |

**Prochaine tranche :** retrouver et cartographier les contrôleurs de liste et d'édition, les affectations départementales, l'import CSV et les opérations de suspension/suppression. Ensuite, traiter la gestion des cours et des licences comme des parcours liés mais distincts.
