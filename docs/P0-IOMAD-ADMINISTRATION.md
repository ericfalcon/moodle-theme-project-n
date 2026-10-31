# Project N — Analyse P0 des espaces d'administration Iomad

> Analyse statique complémentaire du dépôt `ericfalcon/iomad`, branche `IOMAD_502_STABLE`. Elle décrit les contrats observés et les points de vigilance pour le thème Project N. Elle ne constitue pas une validation fonctionnelle, de sécurité ou d'accessibilité en exécution.

## 1. Périmètre de cette tranche

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

Les entrées sont ensuite collectées dynamiquement depuis les plugins qui déclarent une fonction dans `db/iomadmenu.php`. Chaque entrée est filtrée à nouveau par sa capacité avant d'être ajoutée à une catégorie.

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

Le changement d'entreprise est mémorisé dans `$SESSION->currenteditingcompany`. Le tableau de bord initialise ensuite son contexte métier à partir de cette valeur. Le changement peut également entraîner l'application du thème configuré pour l'entreprise dans la session.

Le README de `local_iomad` avertit toutefois que le CSS et le logo propres à chaque entreprise ne sont pas automatiquement compatibles avec tous les thèmes : cela dépend notamment de l'utilisation des réglages natifs de logo et des mécanismes de thème.

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
