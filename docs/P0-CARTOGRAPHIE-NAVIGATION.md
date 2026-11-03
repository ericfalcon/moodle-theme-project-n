# Project N — Cartographie technique de l'interface Moodle / Iomad

> Étude P0 réalisée à partir des forks personnels `ericfalcon/moodle` et `ericfalcon/iomad`. Elle documente les points d'appui techniques et les risques de compatibilité ; elle ne vaut ni validation d'installation ni test fonctionnel.

## 1. Périmètre réellement inspecté

| Dépôt | Branche | Éléments examinés |
|---|---|---|
| `ericfalcon/moodle` | `MOODLE_405_STABLE` | templates Boost, templates core, `lib/navigationlib.php`, classes `primary` et `secondary` |
| `ericfalcon/moodle` | `MOODLE_503_STABLE` | templates Boost sous `public/`, templates core sous `public/lib/templates`, classes de vues primaire/secondaire |
| `ericfalcon/iomad` | `IOMAD_405_STABLE` | `lib/navigationlib.php`, templates Boost |
| `ericfalcon/iomad` | `IOMAD_501_STABLE` | classes navigation sous `public/lib/classes/navigation`, templates Boost |
| `ericfalcon/iomad` | `IOMAD_502_STABLE` | classes navigation sous `public/lib/classes/navigation`, templates Boost |

Les branches sont présentes dans les forks. Leur présence ne prouve pas qu'un environnement de test correspondant est installé, disponible ou officiellement publié. Les conclusions ci-dessous sont limitées aux fichiers effectivement consultés.

## 2. Moodle 4.5 — organisation observée

### 2.1 Templates Boost

Le dossier `theme/boost/templates` contient notamment :
- `drawers.mustache` : composition générale du layout à tiroirs ;
- `drawer.mustache` : composant générique de tiroir ;
- `navbar.mustache` : barre supérieure Boost ;
- `flat_navigation.mustache` et `nav-drawer.mustache` : anciens éléments encore présents dans cette branche ;
- `primary-drawer-mobile.mustache`, `courseindexdrawercontrols.mustache`, `blocks-drawer.mustache`.

Le fichier `nav-drawer.mustache` est marqué deprecated depuis Moodle 4.0. Il ne doit pas devenir un point d'extension central de Project N.

Dans `drawers.mustache`, Boost assemble notamment le tiroir d'index du cours, le tiroir des blocs, la navbar, le menu secondaire et le contenu. Le layout est donc un point de composition majeur, mais sa surcharge complète aurait un coût de maintenance élevé.

### 2.2 Templates du cœur

Dans `lib/templates`, les composants de navigation comprennent notamment :
- `navbar.mustache` (fil d'Ariane) ;
- `moremenu.mustache` et `moremenu_children.mustache` ;
- `drawer.mustache` ;
- `user_menu.mustache` et ses sous-composants ;
- `action_menu*.mustache`.

Il faut distinguer la navbar Boost (barre supérieure et sorties de plugins) du template core `navbar.mustache` qui rend le fil d'Ariane.

### 2.3 Classes

La navigation historique est notamment structurée dans `lib/navigationlib.php`. Les vues primaire et secondaire sont aussi matérialisées par :
- `lib/classes/navigation/views/primary.php` ;
- `lib/classes/navigation/views/secondary.php`.

La vue secondaire construit des entrées contextuelles et, en contexte cours, sélectionne des nœuds issus des objets de navigation et de paramètres. Cette logique doit rester la source des entrées contextuelles Moodle.

## 3. Moodle 5.3 — évolutions observées

La restructuration du cœur place les chemins web sous `public/` :
- `public/theme/boost/templates/` ;
- `public/lib/templates/` ;
- `public/lib/classes/navigation/views/`.

Dans Boost, les templates `flat_navigation.mustache` et `nav-drawer.mustache` ne figurent plus dans le dossier templates 5.3 inspecté. Les fichiers `drawers.mustache`, `drawer.mustache`, `navbar.mustache` et les contrôles de tiroir restent présents.

Dans les templates core 5.3, les composants suivants sont présents :
- `primarymoremenu.mustache` ;
- `secondarymoremenu.mustache` ;
- `navigation_action_bar.mustache` ;
- `moremenu.mustache` et `moremenu_children.mustache`.

Les templates primaire et secondaire décrivent une navigation rendue par les composants React de Moodle, avec un fallback serveur/non-JS. Le template secondaire précise que le composant produit lui-même le landmark `nav` : un thème ne doit donc pas ajouter un second `nav` englobant ce composant.

Les classes `primary.php` et `secondary.php` restent présentes dans `public/lib/classes/navigation/views/`. Le code de la vue secondaire contient une cartographie des nœuds par contexte (notamment cours) et une gestion du menu de débordement. Ce mécanisme est plus qu'un simple rendu visuel : il sélectionne et ordonne des nœuds issus de la navigation Moodle.

**Conséquence :** ne pas construire Project N autour d'une surcharge des anciens templates de navigation plate. À partir de 5.3, préserver les contrats des composants React, leur fallback sans JavaScript et leurs landmarks accessibles.

## 4. Iomad — écarts observés dans les forks

### 4.1 Iomad 4.5

Le fichier `lib/navigationlib.php` contient un chargement explicite de `local/iomad/lib/iomad.php` avant la définition de la classe `navigation_node`. Cela confirme que la navigation Iomad 4.5 est encore articulée autour de la structure historique Moodle, avec une intégration Iomad dans le chargement de cette couche.

Le dossier `lib/classes/navigation` est plus réduit que celui des versions récentes ; les classes historiques restent notamment dans `lib/navigationlib.php`.

### 4.2 Iomad 5.1 et 5.2

Les classes sont désormais sous `public/lib/classes/navigation/`. Dans `global_navigation.php`, les imports comprennent explicitement :

```php
use local_iomad\\{company_user, iomad};
```

La navigation globale est donc directement adaptée à l'environnement Iomad et à ses notions d'utilisateur/entreprise. Les classes `global_navigation.php` et `settings_navigation.php` existent dans les branches inspectées.

Iomad 5.1 contient encore `flat_navigation.php` et `flat_navigation_node.php` dans le dossier de navigation inspecté ; ils ne figurent plus dans la liste du dossier correspondant d'Iomad 5.2. Project N ne doit donc pas dépendre de ces classes historiques.

La présence de ces imports et de ces adaptations dans le cœur Iomad signifie qu'une navigation globale conçue uniquement à partir de Moodle standard ne suffit pas à garantir le bon comportement Iomad.

### 4.3 Conséquence architecturale

- Ne pas réimplémenter le filtrage des destinations d'entreprise dans le thème.
- Ne pas lire directement `mdl_company`, `mdl_companies` ou d'autres tables Iomad.
- Ne pas considérer qu'un utilisateur appartient à une seule entreprise sans interroger les mécanismes Iomad prévus à cet effet.
- Consommer les API/classes publiques disponibles dans chaque version, puis laisser les pages cibles vérifier les capacités et le périmètre.
- Ajouter des tests d'accès direct URL et de cloisonnement multi-entreprises.
- Isoler les éventuelles différences Iomad dans un adaptateur/versionnement limité, sans dupliquer l'arbre complet de navigation.

## 5. Architecture Project N recommandée à la lumière du code

### 5.1 Ce que le thème doit posséder

- Le modèle de navigation globale Project N : destinations de premier niveau, espace de travail et accès transversaux.
- La présentation responsive de cette navigation.
- Une couche de composition qui reçoit des destinations déjà filtrées selon le contexte et les capacités.
- Des adaptations visuelles ciblées des composants natifs, avec surcharge uniquement lorsque les points d'extension ne suffisent pas.

### 5.2 Ce que Moodle doit continuer à posséder

- L'arbre de navigation et les nœuds contextuels.
- La construction de la navigation secondaire.
- Le fil d'Ariane.
- L'index du cours et la navigation d'activité.
- Les actions de page, les menus d'administration et les menus utilisateur.
- Les contrôles d'accès, capacités, contextes et vérifications côté serveur.

### 5.3 Ce que l'adaptation Iomad doit prendre en charge

- La découverte des fonctions disponibles pour un gestionnaire/administrateur d'entreprise.
- La prise en compte du contexte et de l'appartenance à l'entreprise via les API Iomad.
- Le cloisonnement des destinations et données.
- Les différences de structure entre Iomad 4.5 et Iomad 5.x, notamment le passage à `public/` et les évolutions des classes de navigation.

Une classe d'adaptation Iomad ne doit pas devenir un deuxième moteur de navigation. Elle doit seulement fournir à Project N les informations utiles à l'affichage global.

## 6. Points de vigilance techniques

1. **Surcharge des layouts :** `drawers.mustache` est central mais sensible aux évolutions. Éviter sa copie intégrale ; préférer les layouts personnalisés minimaux et documentés si le prototype l'exige.
2. **Navigation secondaire :** en 5.3, le composant `core/secondarymoremenu` a ses propres responsabilités sémantiques et fallback. Ne pas l'envelopper dans un landmark redondant.
3. **Navigation primaire :** en 5.3, le rendu React et le fallback serveur doivent rester cohérents ; tester JavaScript activé et désactivé.
4. **Anciennes vues :** ne pas dépendre de `flat_navigation`, `flat_navigation_node`, `flat_navigation.mustache` ou `nav-drawer.mustache` pour le modèle Project N.
5. **Iomad :** la navigation globale est modifiée dans le cœur ; tester les parcours avec des comptes de plusieurs entreprises et des comptes multi-rôles.
6. **Plugins :** vérifier les sorties de plugins dans la navbar, les menus et les actions contextuelles ; éviter de neutraliser les points d'extension.
7. **Mobile :** vérifier les tiroirs, menus de débordement, navigation secondaire et focus avec clavier virtuel et lecteurs d'écran.

## 7. Matrice de validation technique à compléter

| Version | Structure web | Navigation globale | Navigation secondaire | Tiroirs | Iomad entreprise | Statut |
|---|---|---|---|---|---|---|
| Moodle 4.5 | Racine | À tester | Classes primary/secondary inspectées | Templates Boost inspectés | — | Analyse statique partielle |
| Moodle 5.3 | `public/` | À tester | Composants React + fallback inspectés | Templates Boost inspectés | — | Analyse statique partielle |
| Iomad 4.5 | Racine | Code historique Iomad repéré | À approfondir | Templates Boost inspectés | À tester | Analyse statique partielle |
| Iomad 5.1 | `public/` | Adaptations `local_iomad` repérées | À approfondir | Boost inspecté | À tester | Analyse statique partielle |
| Iomad 5.2 | `public/` | Adaptations `local_iomad` repérées | À approfondir | Boost inspecté | À tester | Analyse statique partielle |

La validation nécessitera les installations correspondantes et des scénarios réels ; l'analyse de code seule ne permet pas de conclure à la compatibilité fonctionnelle.

## 8. Sources dans les forks personnels

- Moodle 4.5 — [drawers.mustache](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/theme/boost/templates/drawers.mustache)
- Moodle 4.5 — [navbar.mustache Boost](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/theme/boost/templates/navbar.mustache)
- Moodle 4.5 — [navigationlib.php](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/lib/navigationlib.php)
- Moodle 4.5 — [vue primaire](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/lib/classes/navigation/views/primary.php)
- Moodle 4.5 — [vue secondaire](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/lib/classes/navigation/views/secondary.php)
- Moodle 5.3 — [drawers.mustache](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/theme/boost/templates/drawers.mustache)
- Moodle 5.3 — [navigation primaire](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/lib/templates/primarymoremenu.mustache)
- Moodle 5.3 — [navigation secondaire](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/lib/templates/secondarymoremenu.mustache)
- Moodle 5.3 — [vue secondaire](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/lib/classes/navigation/views/secondary.php)
- Iomad 4.5 — [navigationlib.php](https://github.com/ericfalcon/iomad/blob/IOMAD_405_STABLE/lib/navigationlib.php)
- Iomad 5.1 — [global_navigation.php](https://github.com/ericfalcon/iomad/blob/IOMAD_501_STABLE/public/lib/classes/navigation/global_navigation.php)
- Iomad 5.1 — [settings_navigation.php](https://github.com/ericfalcon/iomad/blob/IOMAD_501_STABLE/public/lib/classes/navigation/settings_navigation.php)
- Iomad 5.2 — [global_navigation.php](https://github.com/ericfalcon/iomad/blob/IOMAD_502_STABLE/public/lib/classes/navigation/global_navigation.php)

## 9. Suite de l'étude

Avant de fermer P0, approfondir :
- les méthodes et hooks de construction des vues primaire/secondaire ;
- les ajouts Iomad dans `global_navigation` et `settings_navigation` (diffs exacts par rapport au Moodle de base) ;
- les fonctions d'API Iomad accessibles aux thèmes/plugins pour déterminer le périmètre entreprise ;
- les points d'extension de Boost et les sorties de plugins ;
- la matrice complète Moodle 4.5–5.3 / Iomad 4.5–5.2 sur installations réelles.

Aucun prototype installable ni développement fonctionnel de Project N ne doit démarrer avant que cette cartographie et les parcours P0 soient suffisamment stabilisés.


## 10. Points d'extension Moodle / Boost vérifiés

### 10.1 Navigation primaire et secondaire

Dans Moodle 5.3, la classe `core\\navigation\\views\\primary` :
- expose `initialise(): void` ;
- construit les destinations standard (accueil, tableau de bord, cours, administration) ;
- déclenche le hook `\\core\\hook\\navigation\\primary_extend` via le gestionnaire de hooks ;
- gère ensuite la sélection du nœud actif.

Le hook `primary_extend` constitue donc un point d'extension à étudier en priorité pour ajouter des entrées au menu primaire sans remplacer la classe core. Il faut encore vérifier l'enregistrement effectif d'un callback depuis un plugin de type thème dans les versions cibles ; si cette possibilité n'est pas garantie, le plugin compagnon local sera le lieu de l'adaptateur, pas une surcharge du cœur.

La classe `core\\navigation\\views\\secondary` reste responsable de la navigation contextuelle. Plusieurs méthodes sont `protected` (cartographies par contexte, chargement et tri des nœuds) : ce ne sont pas des contrats stables à appeler depuis Project N. Le thème doit consommer le résultat rendu par Moodle et éviter d'étendre ces classes par héritage.

### 10.2 Boost : ce qui est réellement configurable

Dans le `config.php` Boost 5.3 inspecté :
- les layouts standards sont déclarés dans `$THEME->layouts` et utilisent principalement `drawers.php` ;
- `$THEME->rendererfactory = 'theme_overridden_renderer_factory'` active la résolution des renderers surchargés par un thème ;
- `$THEME->usescourseindex = true` active l'index de cours ;
- les callbacks SCSS permettent d'injecter des variables/styles, sans remplacer le pipeline de rendu ;
- Boost gère ses préférences utilisateur pour l'ouverture des tiroirs et le mode de couleur.

Les fonctions observées dans `theme/boost/lib.php` concernent principalement SCSS, CSS compilé, fichiers du thème et préférences. Elles ne fournissent pas une API générale pour remplacer la navigation.

**Conséquence pour Project N :** déclarer Boost comme parent, conserver les layouts natifs au départ et ne créer un layout Project N que si les parcours le justifient. Utiliser les renderers surchargés pour des adaptations ciblées, pas pour refaire l'ensemble du rendu. Les templates Mustache peuvent être redéfinis par le thème enfant, mais chaque surcharge doit être justifiée par un besoin UX et suivie par version Moodle.

### 10.3 Hooks et callbacks : stratégie de compatibilité

Moodle dispose d'une API Hooks PSR-14 depuis 4.3. Les callbacks sont enregistrés dans `db/hooks.php`, avec des méthodes statiques et une classe de hook précise. Les anciens callbacks `lib.php` restent présents dans le cœur et peuvent coexister avec les hooks selon le point d'extension.

Stratégie retenue :
1. privilégier un hook public documenté lorsqu'il couvre le besoin ;
2. sinon utiliser un callback legacy officiellement documenté et présent dans toutes les versions ciblées ;
3. sinon déplacer l'intégration dans `local_projectn` si elle nécessite logique métier ou enregistrement transversal ;
4. ne jamais appeler une méthode `private` ou dépendre d'une méthode `protected` interne d'une classe core ;
5. conserver le fallback sans JavaScript et les composants natifs, en particulier à partir de Moodle 5.3.

La documentation Moodle confirme que les hooks sont prévus pour étendre le comportement standard via callbacks déclarés dans `db/hooks.php` et que le gestionnaire les distribue selon leur priorité. Cela ne dispense pas de vérifier la disponibilité exacte du hook dans chaque branche ciblée.

## 11. API Iomad repérées — usage et limites
Les fichiers Iomad 5.2 inspectés exposent notamment les méthodes publiques statiques suivantes dans `local_iomad\\iomad` :

| Méthode | Signature observée | Usage possible dans Project N | Précaution |
|---|---|---|---|
| `get_my_companyid` | `(object $context, bool $required = true): int` | Connaître l'entreprise courante dans le contexte d'administration | Peut rediriger si `$required` vaut true ; prévoir le comportement sans entreprise sélectionnée |
| `is_company_user` | `(?object $user): bool|int` | Détecter une association entreprise | Le retour n'est pas un simple booléen : il peut être un identifiant |
| `is_company_admin` | `(object $user): bool|int` | Détecter un rôle de gestionnaire d'entreprise | Ne remplace pas les capacités |
| `companyid` | `() : int` | Obtenir l'entreprise associée au contexte utilisateur courant | Ne pas en déduire une prise en charge complète des utilisateurs multi-entreprises |
| `companyshortname` | `() : string` | Information d'affichage/identification | Pas un contrôle d'accès |
| `has_capability` | `(string $capability, bool|context $context, int $companyid = 0): bool` | Vérifier une capacité en appliquant les restrictions Iomad | À utiliser pour les contrôles Iomad, en plus des contrôles Moodle pertinents |
| `require_capability` | `(string $capability, bool|context $context, int $companyid = 0)` | Imposer un contrôle côté serveur | À réserver aux pages/actions qui en ont la responsabilité, pas au rendu du thème |

La méthode Iomad `has_capability` commence par appeler la fonction Moodle `has_capability`, traite le cas administrateur du site, vérifie l'appartenance à l'entreprise lorsqu'un identifiant est fourni, puis applique les restrictions de capacités Iomad. C'est un point déterminant : une simple vérification Moodle peut ne pas suffire pour une action explicitement rattachée à une entreprise.

La classe `local_iomad\\company_user` expose aussi `can_see_company(int|object $company): bool`, ainsi que des méthodes de consultation d'association. À ce stade, ne pas l'utiliser comme unique mécanisme d'autorisation : les pages et actions doivent conserver les contrôles de capacité Iomad appropriés.

### 11.1 Règles de conception Iomad

- Détection du contexte Iomad : vérifier l'existence de la classe/API attendue et la version, plutôt que tester un nom de table.
- Rendu : utiliser les API Iomad pour déterminer les destinations affichables ; une destination masquée n'est jamais une protection.
- Autorisation : chaque page/action appelée doit contrôler ses capacités côté serveur avec le mécanisme adapté au contexte entreprise.
- Données : ne jamais construire une requête métier directement sur les tables Iomad depuis le thème.
- Multi-entreprises : ne pas figer l'hypothèse « un utilisateur = une entreprise ». L'API `companyid()` reflète l'entreprise courante ; les associations, sélections de session et droits doivent être testés séparément.
- Dégradation : si l'API Iomad attendue n'existe pas dans une version, ne pas basculer silencieusement vers une logique permissive. Désactiver la fonctionnalité concernée ou afficher une erreur contrôlée.

Les signatures ci-dessus ont été relevées dans Iomad 5.2. Elles doivent être comparées à Iomad 4.5 et 5.1 avant d'être figées dans un adaptateur multi-version. La branche 5.2 du fork reste une base d'analyse statique, pas une preuve de disponibilité d'un environnement de validation.

## 12. Décisions techniques affinées

1. **Navigation Project N :** construire une liste de destinations globales dans une couche propre au thème, sans modifier l'arbre Moodle.
2. **Ajout de liens Moodle :** étudier en premier le hook `core\\hook\\navigation\\primary_extend` ; ne pas remplacer `primary.php`.
3. **Navigation contextuelle :** consommer les vues primaire/secondaire et le fil d'Ariane natifs. Ne pas recopier la logique de tri et de sélection des nœuds.
4. **Boost :** hériter de Boost et privilégier les réglages, layouts et renderers existants. Toute surcharge Mustache ou layout doit être recensée dans une matrice de maintenance par version.
5. **Compagnon local :** le créer uniquement si un callback/hook ne peut pas être enregistré proprement depuis le thème, ou si une logique métier/serveur est nécessaire. Le thème doit rester utilisable sans ce compagnon.
6. **Sécurité Iomad :** ne jamais assimiler visibilité d'un lien et autorisation. Les vérifications entreprise restent côté serveur, au plus près de la ressource/action.
7. **Tests à ajouter :** utilisateur sans entreprise, utilisateur d'une entreprise, gestionnaire d'une entreprise, administrateur global, utilisateur ayant plusieurs associations, entreprise sélectionnée en session, accès direct à une URL d'une autre entreprise, capacité Moodle accordée mais restriction Iomad refusée.

## 13. Références complémentaires

- [Moodle 5.3 — classe primary](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/lib/classes/navigation/views/primary.php)
- [Moodle 5.3 — classe secondary](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/lib/classes/navigation/views/secondary.php)
- [Moodle 5.3 — Boost config](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/theme/boost/config.php)
- [Moodle 5.3 — Boost lib](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/theme/boost/lib.php)
- [Iomad 5.2 — API iomad](https://github.com/ericfalcon/iomad/blob/IOMAD_502_STABLE/public/local/iomad/classes/iomad.php)
- [Iomad 5.2 — API company_user](https://github.com/ericfalcon/iomad/blob/IOMAD_502_STABLE/public/local/iomad/classes/company_user.php)
- [Iomad 5.2 — navigation globale](https://github.com/ericfalcon/iomad/blob/IOMAD_502_STABLE/public/lib/classes/navigation/global_navigation.php)
- [Moodle Developer — Theme plugins](https://moodledev.io/docs/5.1/apis/plugintypes/theme)
- [Moodle Developer — Hooks API](https://moodledev.io/docs/5.3/apis/core/hooks)
- [Moodle Developer — Navigation API](https://moodledev.io/docs/5.1/apis/core/navigation)
- [Moodle Developer — Output API](https://docs.moodle.org/dev/Output_API)


### 11.2 Compatibilité de l'API entreprise entre Iomad 4.5 et 5.x

La comparaison du code confirme une continuité fonctionnelle intéressante, mais avec une rupture d'organisation PHP :

| Version | Emplacement | Classe | Capacités |
|---|---|---|---|
| Iomad 4.5 | `local/iomad/lib/iomad.php` | `iomad` (globale, chargée par require_once) | `iomad::has_capability(string, context, int $companyid = 0)` |
| Iomad 5.1 / 5.2 | `public/local/iomad/classes/iomad.php` | `local_iomad\\iomad` (autoloadée) | `\\local_iomad\\iomad::has_capability(string, bool|context, int $companyid = 0)` |

Les méthodes `get_my_companyid`, `is_company_user`, `is_company_admin`, `companyid`, `has_capability` et `require_capability` existent dans les deux organisations. Les signatures de `get_my_companyid`, `is_company_user`, `is_company_admin` et `companyid` sont identiques dans les fichiers examinés. La signature de `has_capability` est plus permissive en 5.x (`bool|context`) qu'en 4.5 (`context`).

Le fichier `iomad.php` a le même contenu/blob dans les branches 5.1 et 5.2 examinées ; cela ne prouve pas que tous les composants Iomad sont identiques entre ces versions.

**Conséquence pour l'adaptateur :** le point de compatibilité à isoler est d'abord le chargement et le nom de classe (`iomad` global en 4.5, `\\local_iomad\\iomad` en 5.x). Ne pas multiplier les adaptations métier. Les fonctions de capacité ont une continuité de comportement apparente, mais les appels doivent être testés sur les versions réelles et avec des contextes/entreprises variés.

En Iomad 4.5, les anciennes tables portent notamment les noms `company` et `company_users`; en 5.x, l'organisation est sous `local_iomad` et les noms de tables ont évolué. Cette différence renforce la règle : Project N ne doit faire aucune requête directe sur le schéma Iomad.


## 14. Cartographie approfondie des layouts et des renderers

### 14.1 Déclaration des layouts Boost

Les fichiers `theme/boost/config.php` de Moodle 4.5 et 5.3 examinés ont le même contenu (même blob Git). La liste des layouts et leur répartition n'ont donc pas changé entre ces deux branches dans le fork.

Layouts utilisant `drawers.php` :
- `base`, `standard`, `course`, `coursecategory`, `incourse` ;
- `frontpage`, `admin`, `mycourses`, `mydashboard`, `mypublic` ;
- `report`.

Layouts particuliers :
- `login` → `login.php` ;
- `popup` et `frametop` → `columns1.php` ;
- `embedded` et `redirect` → `embedded.php` ;
- `maintenance` → `maintenance.php` ;
- `print` → `columns1.php` ;
- `secure` → `secure.php`.

Les régions de blocs et options diffèrent selon le layout : certains masquent la navbar, d'autres le pied de page ou l'en-tête d'activité. Le layout maintenance est explicitement sensible : il ne doit déclencher ni accès base de données, ni cache, ni liens susceptibles de rendre l'installation ou la mise à niveau indisponible.

**Décision :** Project N doit conserver une couverture explicite de tous les layouts, y compris login, maintenance, popup, embedded, print, redirect et secure. Le nouveau shell ne doit pas être imposé à des pages qui nécessitent volontairement une sortie minimale ou isolée.

### 14.2 Différences du layout drawers.php entre 4.5 et 5.3

Le squelette reste le même : il prépare les tiroirs, blocs, index de cours, navigation primaire et secondaire, puis transmet le contexte à `theme_boost/drawers`.

Différences relevées :
- Moodle 5.3 ajoute `coursefullname` et `courseurl` au contexte du template ;
- le menu de débordement secondaire est rendu avec `core/url_select` en 4.5, puis avec un objet `core\\output\\select_menu` et le template `core/url_select` en 5.3 ;
- les données primaire/secondaire continuent d'être préparées par les objets de sortie Moodle.

Le template Mustache `drawers` contient directement la structure principale de page, les toggles des tiroirs, les points d'insertion du contenu, l'en-tête d'activité et le footer. Une surcharge complète aurait donc un impact transversal sur presque toutes les pages.

**Conséquence :** ne pas copier le layout `drawers.php` ni le template `drawers.mustache` pour une simple modification de navigation. Si une structure Project N nécessite un shell distinct, créer un layout enfant minimal et maintenir la compatibilité des variables/points d'insertion du parent. La décision doit être prise après les maquettes et les parcours P0.

### 14.3 Renderers Boost

La classe `theme_boost\\output\\core_renderer` étend `\\core_renderer`. Dans Moodle 4.5, ses méthodes publiques spécifiques observées sont notamment :
- `edit_button()` ;
- `navbar()` ;
- `context_header()` ;
- `firstview_fakeblocks()`.

Moodle 5.3 ajoute notamment :
- `search_box()` ;
- `colour_mode_menu()`.

La classe Boost reste une couche de rendu, pas un gestionnaire de droits ni un moteur de navigation. Project N pourra étendre les renderers ciblés, mais devra éviter de remplacer les méthodes cœur sans nécessité démontrée.

### 14.4 Factory et résolution des renderers

Boost déclare `theme_overridden_renderer_factory`. Moodle résout les renderers en fonction du thème courant et des composants demandés. La documentation Output API confirme qu'un thème peut fournir une classe renderer personnalisée pour un composant, afin d'adapter le rendu de ses renderables.

Règles Project N :
- activer la factory de surcharge ;
- placer les surcharges dans les espaces de noms et emplacements attendus par Moodle ;
- surcharger uniquement les méthodes utiles, avec signatures compatibles ;
- tester les renderers des plugins tiers et les renderables React/Mustache ;
- ne pas supposer qu'une surcharge de `core_renderer` contrôle les composants React qui rendent leur propre interface.

### 14.5 Compatibilité du shell Iomad

Vérifié sur les branches inspectées :
- le fichier `public/theme/boost/layout/drawers.php` de Moodle 5.3 et d'Iomad 5.2 a le même blob Git ;
- le fichier `public/theme/boost/amd/src/drawers.js` de Moodle 5.2 et d'Iomad 5.2 a le même blob Git ;
- le template `public/theme/boost/templates/drawers.mustache` **diffère** entre Moodle 5.3 et Iomad 5.2 : l'identité n'est pas acquise pour ce template et doit être examinée avant toute surcharge.

Le shell Boost n'est donc pas identique partout : il faut vérifier chaque surcharge de `drawers.mustache` contre la branche Iomad ciblée. Les adaptations Iomad propres restent principalement dans le cœur Iomad et les renderers.

## 15. Hooks Moodle : disponibilité depuis un thème confirmée

Un point important peut maintenant être tranché : **un thème peut enregistrer des callbacks de hooks dans son propre fichier `db/hooks.php`**.

Dans Moodle 4.5 et 5.3, le gestionnaire de hooks :
1. récupère tous les types de plugins via `core_component::get_plugin_types()` ;
2. parcourt les plugins de chaque type via `get_plugin_list()` ;
3. recherche un fichier `db/hooks.php` pour chaque composant ;
4. charge et met en cache les callbacks déclarés.

Le type `theme` fait partie des types de plugins Moodle. Il n'est donc pas nécessaire de créer systématiquement `local_projectn` uniquement pour enregistrer le hook de navigation primaire.

Le hook `core\\hook\\navigation\\primary_extend` est présent et déclenché dans la classe primaire en Moodle 4.5 et 5.3. Son objet expose la vue primaire par `$hook->primaryview` et `get_primaryview()`. La vue est un objet de navigation ; le callback peut y ajouter des nœuds via l'API de navigation, sans remplacer la classe core.

**Décision affinée :** Project N pourra enregistrer directement son callback dans `theme/projectn/db/hooks.php` (ou `public/theme/projectn/db/hooks.php` selon l'arborescence de l'installation) et le traiter dans une classe du thème. Cette option devra être testée lors de la première installation. Un plugin compagnon ne sera requis que pour les traitements métier/serveur ou les points d'extension non accessibles au thème.

Attention : les callbacks sont mis en cache. Les modifications de `db/hooks.php` pendant le développement nécessitent une purge de caches ou une évolution de version du composant pour rafraîchir les enregistrements.

## 16. Différences Iomad dans le renderer cœur

La comparaison des classes `core_renderer` Moodle 5.3 et Iomad 5.2 montre que les méthodes publiques sont quasiment identiques (Iomad n'ajoute pas de méthode publique dans le relevé), mais le fichier Iomad contient des adaptations internes.

Deux adaptations notables :

1. **Menu personnalisé :** Iomad surcharge le comportement de `custom_menu()` pour sélectionner le contexte entreprise, vérifier plusieurs capacités de gestion, ajouter un lien vers le tableau de bord entreprise, charger les éléments de menu personnalisés de l'entreprise et éventuellement le lien boutique/e-commerce.
2. **Favicon :** Iomad peut servir le favicon propre à l'entreprise courante, avant de revenir au favicon global.

Le layout Boost ne porte donc pas ces adaptations : elles se trouvent dans le renderer du cœur Iomad.

**Conséquence Project N :** ne pas neutraliser `custom_menu()`, les sorties de menu personnalisées ni le mécanisme de favicon. Si le menu global Project N remplace visuellement le menu Boost, il doit préserver les entrées personnalisées Iomad et le lien de gestion entreprise lorsque les capacités le permettent. L'intégration doit utiliser les API Iomad ou les sorties déjà produites par Iomad, sans lire ses tables depuis le thème.

## 17. Synthèse des décisions après cette étape

| Sujet | Décision |
|---|---|
| Layouts Boost | Conserver l'ensemble des layouts et leurs cas particuliers |
| Shell global | Ne pas copier `drawers.php` / `drawers.mustache` avant validation des maquettes |
| Navigation primaire | Utiliser `primary_extend` depuis le thème via `db/hooks.php` |
| Navigation secondaire | Laisser Moodle la construire et la rendre |
| Renderers | Surcharges ciblées via la factory Boost |
| Hooks | Le thème peut enregistrer ses callbacks ; compagnon local non systématique |
| Iomad custom menu | Préserver menu entreprise, lien de gestion, boutique et favicon |
| Sécurité | L'affichage des liens ne remplace jamais le contrôle serveur |

## 18. Sources de code ajoutées

- [Moodle 4.5 — Boost config](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/theme/boost/config.php)
- [Moodle 5.3 — Boost config](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/theme/boost/config.php)
- [Moodle 4.5 — layout drawers](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/theme/boost/layout/drawers.php)
- [Moodle 5.3 — layout drawers](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/theme/boost/layout/drawers.php)
- [Moodle 4.5 — template drawers](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/theme/boost/templates/drawers.mustache)
- [Moodle 5.3 — template drawers](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/theme/boost/templates/drawers.mustache)
- [Moodle 4.5 — Boost renderer](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/theme/boost/classes/output/core_renderer.php)
- [Moodle 5.3 — Boost renderer](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/theme/boost/classes/output/core_renderer.php)
- [Moodle 4.5 — primary navigation](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/lib/classes/navigation/views/primary.php)
- [Moodle 5.3 — hook manager](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/lib/classes/hook/manager.php)
- [Moodle 5.3 — primary_extend hook](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/lib/classes/hook/navigation/primary_extend.php)
- [Iomad 5.2 — Boost renderer](https://github.com/ericfalcon/iomad/blob/IOMAD_502_STABLE/public/theme/boost/classes/output/core_renderer.php)
- [Iomad 5.2 — core renderer](https://github.com/ericfalcon/iomad/blob/IOMAD_502_STABLE/public/lib/classes/output/core_renderer.php)
- [Iomad 5.2 — layout drawers](https://github.com/ericfalcon/iomad/blob/IOMAD_502_STABLE/public/theme/boost/layout/drawers.php)
- [Iomad 5.2 — template drawers](https://github.com/ericfalcon/iomad/blob/IOMAD_502_STABLE/public/theme/boost/templates/drawers.mustache)


## 19. Navigation primaire : différence directe Moodle / Iomad

La comparaison des fichiers `primary.php` confirme une adaptation Iomad présente dès Iomad 4.5 et maintenue dans les branches 5.1 et 5.2.

Après le déclenchement du hook `core\\hook\\navigation\\primary_extend`, Iomad ajoute lui-même une entrée « Tableau de bord » entreprise si l'utilisateur possède au moins une des capacités de gestion pertinentes :
- gestion entreprise ;
- gestion utilisateurs ;
- gestion cours ;
- gestion licences ;
- gestion compétences ;
- administration e-commerce ;
- microlearning ;
- rapports Iomad.

Le code utilise :
- Iomad 4.5 : classe globale `iomad` et `\\core\\context\\company` ;
- Iomad 5.1/5.2 : `local_iomad\\iomad` et `local_iomad\\custom_context\\context_company`.

Le hook est déclenché **avant** l'ajout du nœud Iomad. Project N peut donc ajouter ses destinations globales via le hook sans empêcher l'ajout du tableau de bord Iomad, à condition de ne pas remplacer la vue primaire ni de supprimer les nœuds inconnus.

Le point d'attention est l'identifiant du nœud Iomad `ioaddashboardnode`. Le thème doit le laisser disponible ou le présenter dans l'espace gestionnaire sans créer un doublon. L'entrée Iomad reste conditionnée par les capacités et le contexte d'entreprise construits par Iomad.

## 20. JavaScript des tiroirs : différences de comportement et d'accessibilité

### 20.1 Évolution Moodle 4.5 → 5.2 → 5.3

Les modules AMD `theme_boost/drawers` gèrent :
- ouverture/fermeture des tiroirs ;
- synchronisation des attributs ARIA ;
- gestion du focus ;
- mémorisation des préférences utilisateur ;
- fermeture lors de l'ouverture d'un autre tiroir ou d'un changement de taille d'écran ;
- événements permettant aux autres composants de réagir.

Différence importante :
- Moodle 4.5 et Moodle/Iomad 5.2 utilisent une logique avec verrouillage du focus (`core/local/aria/focuslock`) dans certaines configurations ;
- Moodle 5.3 fait évoluer cette logique. Le code indique explicitement que sur petit écran les tiroirs ne sont pas modaux et que le focus n'est pas piégé à l'intérieur. Le module surveille le focus et évite qu'un tiroir ouvert recouvre l'élément qui reçoit le focus.

Le fichier `drawers.js` Moodle 5.3 est donc plus récent et plus long que celui de Moodle/Iomad 5.2. Le fichier Iomad 5.2 possède exactement le même blob que Moodle 5.2 pour ce module ; il ne faut pas comparer Iomad 5.2 directement au comportement Moodle 5.3 et conclure à une divergence propre à Iomad.

### 20.2 Conséquences pour Project N

- Ne pas réimplémenter le moteur de tiroirs Boost.
- Ne pas supposer que le comportement du focus est identique sur toutes les versions.
- Si Project N introduit un tiroir global, le construire sur les composants/contrats disponibles dans la version cible, ou prévoir une adaptation isolée par version.
- Tester ouverture, fermeture, retour du focus, déplacement du focus hors du tiroir, redimensionnement, rotation mobile, clavier et TalkBack/NVDA.
- Vérifier les cas où un tiroir est déjà ouvert au chargement : Moodle 5.3 documente précisément le risque de déplacer le focus sans interaction utilisateur.
- Ne pas transformer arbitrairement un tiroir non modal mobile en dialogue modal : cela changerait le modèle d'interaction et le comportement d'accessibilité natif.

Les attributs `aria-controls`, `aria-expanded`, les boutons de fermeture et les libellés accessibles sont gérés par les templates et modules Boost. Une surcharge Mustache doit conserver leur synchronisation et les attributs attendus par le JavaScript.

## 21. Conséquence spécifique Iomad pour la navigation Project N

L'ajout du tableau de bord entreprise après le hook primaire signifie que la navigation Project N doit être conçue comme une extension et une réorganisation visuelle, pas comme une liste exhaustive qui remplacerait les destinations natives.

Règles :
1. conserver les entrées primaires natives non explicitement remplacées ;
2. conserver le nœud `ioaddashboardnode` et ses conditions d'affichage ;
3. ne pas recalculer la liste des capacités Iomad dans le thème ;
4. ne pas dupliquer les menus entreprise, boutique et menus personnalisés déjà traités par Iomad ;
5. vérifier l'ordre visuel et la hiérarchie entre espace gestionnaire Project N et tableau de bord Iomad ;
6. tester Moodle standard et Iomad séparément, car la vue primaire n'est pas identique dans les forks.

## 22. Sources de code — navigation et tiroirs

- [Iomad 4.5 — primary.php](https://github.com/ericfalcon/iomad/blob/IOMAD_405_STABLE/lib/classes/navigation/views/primary.php)
- [Moodle 4.5 — primary.php](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/lib/classes/navigation/views/primary.php)
- [Iomad 5.1 — primary.php](https://github.com/ericfalcon/iomad/blob/IOMAD_501_STABLE/public/lib/classes/navigation/views/primary.php)
- [Iomad 5.2 — primary.php](https://github.com/ericfalcon/iomad/blob/IOMAD_502_STABLE/public/lib/classes/navigation/views/primary.php)
- [Moodle 5.2 — drawers.js](https://github.com/ericfalcon/moodle/blob/MOODLE_502_STABLE/public/theme/boost/amd/src/drawers.js)
- [Moodle 5.3 — drawers.js](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/theme/boost/amd/src/drawers.js)
- [Iomad 5.2 — drawers.js](https://github.com/ericfalcon/iomad/blob/IOMAD_502_STABLE/public/theme/boost/amd/src/drawers.js)
- [Moodle 4.5 — drawer.js](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/theme/boost/amd/src/drawer.js)
- [Moodle 5.3 — template drawer](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/theme/boost/templates/drawer.mustache)


## 23. Navigation secondaire et composants de navigation

### Moodle 4.5

La vue secondaire est construite par `core\\navigation\\views\\secondary` et reste une vue de navigation contextuelle Moodle. Elle est alimentée par l'arbre de navigation et le contexte de page (cours, activité, catégorie, administration, etc.). Elle ne doit pas être remplacée par une navigation Project N reconstruite indépendamment.

Le répertoire de templates Boost 4.5 ne contient pas de template `secondary_navigation.mustache` au niveau du thème. Il ne faut donc pas concevoir l'intégration 4.5 à partir d'un nom de template supposé : le rendu passe par les mécanismes de sortie natifs de la version.

### Moodle 5.3

Les templates `core/primarymoremenu` et `core/secondarymoremenu` sont présents dans `public/lib/templates`. Ils préparent un montage React (`core/nav/PrimaryNav` et `core/nav/Nav`) et conservent un rendu serveur de repli pour les navigateurs sans JavaScript et les tests non-JS.

Le template secondaire crée lui-même son landmark `nav`; Project N ne doit pas l'envelopper dans un second landmark de navigation. Le composant gère aussi les rôles de menu/tablist, le débordement et les éléments du menu « plus ».

### Décisions Project N

1. Ne pas remplacer les vues primaire/secondaire Moodle par une liste codée en dur.
2. Ne pas envelopper un composant qui produit déjà son propre landmark `nav`.
3. Préserver le fallback serveur et le fonctionnement sans JavaScript.
4. Ne pas manipuler directement le DOM généré par React ni injecter des éléments dans son arbre après montage.
5. Utiliser le hook de navigation pour ajouter des destinations compatibles, puis traiter leur présentation par CSS ou par un point d'extension officiellement disponible.
6. Conserver les menus contextuels de cours/activité et le menu secondaire ; Project N fournit les destinations de haut niveau, pas une deuxième copie de chaque page Moodle.

Sources de code :
- [Moodle 4.5 — vue secondaire](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/lib/classes/navigation/views/secondary.php)
- [Moodle 5.3 — vue secondaire](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/lib/classes/navigation/views/secondary.php)
- [Moodle 5.3 — template secondaire React](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/lib/templates/secondarymoremenu.mustache)
- [Moodle 5.3 — template primaire React](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/lib/templates/primarymoremenu.mustache)

## 24. Index de cours et commandes du tiroir

L'index de cours est un composant Moodle/format de cours distinct de la navigation globale. Boost fournit des contrôles propres à cet index.

En Moodle 4.5, `theme_boost/courseindexdrawercontrols` utilise un menu déroulant avec commandes explicites d'expansion et de réduction des sections. Le template expose notamment `aria-haspopup`, `aria-expanded`, `aria-controls`, un libellé accessible et des rôles de menu/menuitem. Le module AMD s'appuie sur `core/reactive`.

En Moodle 5.3, le même point d'entrée fonctionnel est conservé, mais l'interface du contrôle évolue : un bouton unique bascule l'état de toutes les sections (`toggleallcourseindexsections`). Le module synchronise notamment `aria-expanded` et `aria-controls` avec l'index de cours.

Conséquences :
1. Ne pas reconstruire l'index de cours dans la navigation Project N.
2. Ne pas réutiliser le HTML 4.5 tel quel en 5.3 : les commandes et attributs ont évolué.
3. Laisser le module Boost gérer l'état et les interactions ; ne pas ajouter un second contrôleur d'ouverture/réduction.
4. Si Project N modifie l'apparence, préserver les identifiants, sélecteurs `data-action`, attributs ARIA et initialisations AMD attendus.
5. Valider l'index sur smartphone, au clavier et avec TalkBack/NVDA, y compris après changement d'orientation et lorsque des sections sont ouvertes ou repliées.

Sources de code :
- [Moodle 4.5 — contrôles index cours](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/theme/boost/templates/courseindexdrawercontrols.mustache)
- [Moodle 4.5 — module contrôles index cours](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/theme/boost/amd/src/courseindexdrawercontrols.js)
- [Moodle 5.3 — contrôles index cours](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/theme/boost/templates/courseindexdrawercontrols.mustache)
- [Moodle 5.3 — module contrôles index cours](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/theme/boost/amd/src/courseindexdrawercontrols.js)

## 25. Notifications et messages système

Les notifications Moodle sont des objets de sortie (`core\\output\\notification`) rendus par les templates génériques `core/notification`, `notification_success`, `notification_info`, `notification_warning`, `notification_error` et `notification_base`.

Le template de base porte une partie du contrat d'accessibilité : rôle `alert` lorsque l'annonce aux lecteurs d'écran est demandée, attribut `data-aria-autofocus`, bouton de fermeture avec nom accessible et structure de titre optionnelle. En Moodle 5.3, le bouton de fermeture utilise `data-bs-dismiss="alert"` et la classe `visually-hidden`; Moodle 4.5 utilise les conventions Bootstrap antérieures (`data-dismiss`, `sr-only`).

Décisions Project N :
1. Ne pas remplacer les notifications Moodle par un système de toast indépendant.
2. Ne pas transformer toutes les notifications en annonces assertives : respecter le paramètre `announce` et le niveau de message.
3. Préserver le contenu, le titre, l'ordre de lecture, le bouton de fermeture et les attributs attendus par Bootstrap.
4. Ne pas supprimer les notifications natives de validation, d'erreur ou de confirmation dans les parcours.
5. Les notifications Project N éventuelles doivent passer par les API Moodle et les mêmes conventions d'accessibilité ; leur couleur ne doit jamais être le seul indicateur du type de message.
6. Tester annonces répétées, fermeture clavier, focus, messages longs et affichage mobile.

Sources de code :
- [Moodle 4.5 — objet notification](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/lib/classes/output/notification.php)
- [Moodle 4.5 — template de base](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/lib/templates/notification_base.mustache)
- [Moodle 5.3 — template de base](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/lib/templates/notification_base.mustache)

## 26. Formulaires Moodle : stratégie de modernisation

La bibliothèque `formslib.php` demeure présente en Moodle 4.5 et 5.3. Les formulaires Moodle ne sont pas de simples blocs HTML : ils reposent sur les classes de formulaires, les éléments QuickForm, les règles de validation côté serveur, les contrôles de capacité et des comportements JavaScript associés.

Le thème doit donc améliorer la présentation sans se substituer au moteur de formulaires.

Décisions :
1. Conserver les formulaires natifs et leurs règles de validation serveur ; ne jamais considérer la validation JavaScript comme une protection suffisante.
2. Moderniser prioritairement la mise en page, les espacements, les libellés, les aides, les erreurs et les états de focus via les styles et templates officiellement surchargeables.
3. Préserver les associations label/champ, les identifiants, les descriptions, les indications de champ obligatoire et les messages d'erreur.
4. Ne pas remplacer globalement les éléments Moodle par des contrôles HTML personnalisés si cela fait perdre les types de champs, les sélecteurs, les sélecteurs de fichiers, les éditeurs, les aides ou les intégrations de plugins.
5. Tester les formulaires natifs Moodle, les formulaires de plugins tiers et les formulaires Iomad : un thème ne peut pas présumer que tous les formulaires utilisent le même jeu de composants.
6. Pour les parcours guidés Project N, utiliser les formulaires Moodle sous-jacents ou les API de formulaire adaptées ; le guidage est une couche d'accompagnement, pas un moteur de formulaire parallèle.
7. Prévoir des tests clavier, lecteur d'écran, erreurs de validation, champs dynamiques, téléversement de fichiers, sélecteurs et affichage à 320 CSS px.

L'analyse effectuée ici confirme la présence de `lib/formslib.php` sur les branches Moodle 4.5 et 5.3. Elle ne constitue pas un audit exhaustif de tous les types de champs, templates et formulaires de plugins ; cette cartographie devra être complétée par un inventaire ciblé des composants utilisés dans les parcours apprenant et formateur.

Sources de code :
- [Moodle 4.5 — formslib.php](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/lib/formslib.php)
- [Moodle 5.3 — formslib.php](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/lib/formslib.php)

## 27. Déclaration d'un thème Iomad

L'analyse des forks Iomad confirme l'existence d'une propriété spécifique dans `core\\output\\theme_config` :

```php
public $isiomadtheme = false;
```

Le thème Iomad Boost la déclare explicitement dans son `config.php` :

```php
$THEME->isiomadtheme = true;
```

Emplacements vérifiés :
- Iomad 4.5 : `theme/iomadboost/config.php`
- Iomad 5.2 : `public/theme/iomadboost/config.php`

Une différence d'héritage a également été relevée :
- Iomad 4.5 : `$THEME->parents = []`
- Iomad 5.2 : `$THEME->parents = ['boost']`

La propriété existe aussi dans `theme_config.php` en Iomad 4.5 et 5.2, avec une valeur par défaut à `false`. Cette déclaration identifie le thème comme thème Iomad au niveau de sa configuration. L'analyse menée ici ne démontre toutefois pas encore l'ensemble des endroits où Iomad exploite cette propriété, ni qu'elle constitue à elle seule un filtre de sélection dans le sélecteur de thèmes.

Décision Project N :
1. Le thème déclare `$THEME->isiomadtheme = true` dans son mode Iomad.
2. La compatibilité ne se limite pas à cette propriété : elle implique les menus entreprise, logos/favicon, capacités, contextes et règles de cloisonnement déjà cartographiés.
3. Le thème reste installable et utilisable sur Moodle standard ; il ne doit pas imposer de dépendance à Iomad.
4. Le mode Iomad doit être détecté de manière sûre et compatible avec les versions cibles, sans supposer que la simple présence d'une classe ou d'un fichier garantit le contexte d'exécution.
5. Le choix de l'héritage Boost doit être validé par version : le fork Iomad 5.2 utilise Boost comme parent dans Iomad Boost, ce qui conforte l'orientation Project N, sans prouver que toutes les versions intermédiaires ont exactement le même comportement.

Sources de code :
- [Iomad 4.5 — Iomad Boost config.php](https://github.com/ericfalcon/iomad/blob/IOMAD_405_STABLE/theme/iomadboost/config.php)
- [Iomad 4.5 — theme_config.php](https://github.com/ericfalcon/iomad/blob/IOMAD_405_STABLE/lib/classes/output/theme_config.php)
- [Iomad 5.2 — Iomad Boost config.php](https://github.com/ericfalcon/iomad/blob/IOMAD_502_STABLE/public/theme/iomadboost/config.php)
- [Iomad 5.2 — theme_config.php](https://github.com/ericfalcon/iomad/blob/IOMAD_502_STABLE/public/lib/classes/output/theme_config.php)

## 28. Limites de cette tranche de cartographie

Cette tranche est une analyse statique de fichiers des forks, pas une validation en exécution. Elle ne prouve pas le comportement complet des menus React, des notifications, des formulaires tiers ou du sélecteur de thèmes sur une installation réelle.

Les prochains relevés doivent notamment couvrir :
- les API Bootstrap réellement importées et utilisées dans les modules AMD de Boost entre 4.5 et 5.3 ;
- les templates de champs et les composants de formulaire utilisés par les parcours retenus ;
- les points d'extension disponibles pour les composants React et leurs limites de personnalisation ;
- le comportement effectif de la propriété `isiomadtheme` dans les parcours Iomad de sélection et de configuration des thèmes ;
- la compatibilité des personnalisations avec Iomad 4.5 et les branches Iomad 5.x ciblées.


## 29. Bootstrap : coexistence des générations 4 et 5

### Moodle 4.5

Boost embarque Bootstrap 4.6.2. Le point d'entrée AMD `theme_boost/index` exporte les composants Bootstrap (Alert, Button, Carousel, Collapse, Dropdown, Modal, Popover, Tab, Toast, Tooltip, etc.). Les modules sont également présents sous `theme/boost/amd/src/bootstrap/`.

Les conventions DOM correspondantes sont celles de Bootstrap 4 : par exemple `data-toggle`, `data-target`, `data-dismiss`, et les classes utilitaires historiques.

### Moodle 5.3

Boost utilise Bootstrap 5.3.8. Les attributs ont évolué vers `data-bs-toggle`, `data-bs-target`, `data-bs-dismiss`, etc. Les classes d'accessibilité et utilitaires ont aussi évolué (par exemple `visually-hidden` à la place de `sr-only`).

Le module `theme_boost/bs4-compat` adapte encore une liste définie d'éléments Bootstrap 4 vers Bootstrap 5. Cette compatibilité est transitoire et explicitement dépréciée : elle ne doit pas servir de fondation aux nouveaux composants Project N.

Le point d'entrée des composants change également :
- Moodle 5.2 et antérieurs : `theme_boost/index`.
- Moodle 5.3 et ultérieur : `bootstrap`.

Les imports directs des sous-modules Bootstrap depuis `theme_boost/bootstrap/*` sont dépréciés en 5.3. Les modules internes `util` et `dom` restent des exceptions non couvertes par l'API publique et sont à utiliser à ses risques.

### Décisions Project N

1. Éviter d'écrire des interactions dépendant directement des attributs Bootstrap dans les templates Project N.
2. Ne pas mélanger les attributs Bootstrap 4 et 5 dans un même composant.
3. Ne pas utiliser `bs4-compat` pour rendre compatibles de nouveaux composants ; il s'agit d'une couche de transition.
4. Pour les composants JS propres au thème, isoler les appels Bootstrap derrière un module Project N et choisir l'import selon la version Moodle.
5. Pour les interactions déjà gérées par Moodle/Boost (modales, dropdowns, tabs, tiroirs), réutiliser leurs API et leur initialisation plutôt que créer un deuxième contrôleur.
6. Tester les états clavier, focus, fermeture, échappement, scroll et annonces accessibles après chaque changement de version Bootstrap.
7. Prévoir une matrice distincte pour Moodle 4.5, Moodle 5.3 et Iomad 5.2 : le fork Iomad 5.2 observé est encore sur les imports historiques `theme_boost/bootstrap/*` et ne doit pas être assimilé à Moodle 5.3.

Sources de code :
- [Moodle 4.5 — index Bootstrap Boost](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/theme/boost/amd/src/index.js)
- [Moodle 4.5 — modal Bootstrap 4](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/theme/boost/amd/src/bootstrap/modal.js)
- [Moodle 5.3 — compatibilité Bootstrap 4/5](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/theme/boost/amd/src/bs4-compat.js)
- [Iomad 5.2 — compatibilité Bootstrap](https://github.com/ericfalcon/iomad/blob/IOMAD_502_STABLE/public/theme/boost/amd/src/bs4-compat.js)
- [Boost 5.3 — notes de migration](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/theme/boost/UPGRADING.md)


## 30. Personnalisation des composants React à partir de Moodle 5.2

La documentation développeur Moodle 5.3 décrit un mécanisme de résolution dynamique des composants React en fonction du thème actif. Un thème peut fournir un override sous :

```
public/theme/projectn/js/esm/src/overrides/<composant>/<chemin>
```

Le système cherche d'abord l'override du thème actif, puis retombe sur le composant original du cœur ou du plugin.

Point d'attention majeur : **les overrides React ne suivent pas automatiquement la chaîne d'héritage des thèmes**. Un composant fourni par un parent Boost n'est donc pas automatiquement repris par Project N comme le sont certains templates ou fichiers hérités. Il faut fournir l'override dans le thème enfant ou utiliser explicitement les alias prévus pour charger le composant original ou celui d'un thème parent.

Le mécanisme propose deux stratégies :
- `wrap` : envelopper le composant original et conserver son comportement ;
- `eject` : reprendre son implémentation pour la modifier plus profondément.

Depuis Moodle 5.3, le CLI `swizzle` peut générer ces overrides. Chaque composant dispose d'un niveau de risque déclaré dans `swizzle.json` : `safe`, `risky` ou `prohibited`. Les composants non déclarés sont considérés comme risqués.

Dans le code Moodle 5.3 inspecté, `core/nav/Nav` est déclaré `risky` en wrap comme en eject. Il ne faut donc pas considérer la navigation React comme un point d'extension stable et sans coût.

### Décisions Project N

1. Ne pas prévoir une réécriture globale des composants React Moodle.
2. Privilégier CSS, tokens, configuration et points d'extension publics avant un override React.
3. Utiliser `wrap` quand une modification d'enveloppe suffit ; réserver `eject` aux besoins démontrés et documentés.
4. Avant chaque override, vérifier le niveau de risque du composant dans la version exacte ciblée.
5. Garder une liste explicite des overrides Project N et des versions Moodle/Iomad pour lesquelles ils sont maintenus.
6. Ne jamais supposer que l'héritage Boost couvre un override React ; le prévoir explicitement.
7. Pour Moodle 4.5, qui précède ce mécanisme, utiliser les points d'extension Mustache/AMD disponibles dans cette version ; ne pas introduire un système React propre au thème qui ferait doublon avec l'évolution du cœur.
8. Vérifier séparément Iomad 5.2 : la branche inspectée n'expose pas les mêmes fichiers React de navigation que Moodle 5.3. Ne pas appliquer automatiquement les conclusions Moodle 5.3 à Iomad 5.2.

Sources :
- [Moodle — Theming React components](https://moodledev.io/docs/5.3/guides/javascript/react/theming)
- [Moodle 5.3 — manifeste swizzle du cœur](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/lib/js/esm/src/swizzle.json)
- [Moodle 5.3 — composant Nav](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/lib/js/esm/src/nav/Nav.tsx)

## 31. Sélecteur d'activités et de ressources : rupture entre 4.5 et 5.1+

Le sélecteur d'activités est un point central de l'expérience formateur. Son évolution est suffisamment importante pour interdire une surcharge unique supposée compatible sur toute la plage.

### Moodle 4.5

Les templates sont sous `course/templates`, notamment :
- `core_course/activitychooser` ;
- `core_course/activitychooserbutton` ;
- les sous-templates `core_course/local/activitychooser/*`.

La logique AMD est principalement sous `course/amd/src/activitychooser.js` et `course/amd/src/local/activitychooser/*`. Le contrôleur orchestre les données, les favoris, les onglets, la modale, le pied de modale et les événements.

Le template du bouton utilise les conventions Bootstrap 4 (`data-toggle="dropdown"`) et des attributs spécifiques au sélecteur.

### Moodle 5.1 et 5.3

À partir de Moodle 5.1, les templates et la logique du sélecteur sont déplacés de `core_course` vers `core_courseformat`. Les emplacements deviennent notamment :
- `course/format/templates/activitychooser.mustache` ;
- `course/format/templates/local/activitychooser/*` ;
- `course/format/templates/local/content/activitychooserbutton.mustache` ;
- `course/format/amd/src/local/activitychooser/*`.

Le pied de modale est désormais géré par le contrôleur de dialogue du format de cours. Le bouton ajoute des attributs de contexte tels que `data-section-id`, `data-returnsectionid`, `data-action` et utilise les conventions Bootstrap 5 dans la branche 5.3.

Les formats de cours peuvent aussi fournir leurs propres sorties et templates. Le sélecteur n'est donc pas uniquement une interface globale que le thème pourrait redessiner sans tenir compte du format actif.

### Décisions Project N

1. Le parcours de création guidée du formateur doit s'appuyer sur le sélecteur natif, pas le remplacer.
2. Les catégories pédagogiques « transmettre / pratiquer / évaluer / collaborer » peuvent constituer une couche de présentation ou d'aide à la décision, mais le choix final doit conserver les activités et ressources réellement disponibles et autorisées.
3. Ne jamais supprimer les favoris, recommandations, recherche, onglets, aides, résumé de sélection, pied de modale ou états de chargement.
4. Ne pas recopier les templates 4.5 dans le thème en espérant qu'ils fonctionneront en 5.1+ : le namespace et le propriétaire fonctionnel ont changé.
5. Toute surcharge doit être versionnée et testée avec les formats de cours natifs et tiers.
6. Préserver les attributs `data-region`, `data-action`, identifiants uniques, associations ARIA et événements attendus par les modules.
7. Le guidage Project N ne doit pas masquer les activités disponibles selon les capacités, les plugins installés ou les restrictions du cours.
8. Sur smartphone, contrôler la hauteur de la modale, le scroll du contenu, la visibilité du pied d'action et le focus après ajout d'une activité.

Sources de code :
- [Moodle 4.5 — template activité](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/course/templates/activitychooser.mustache)
- [Moodle 4.5 — bouton du sélecteur](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/course/templates/activitychooserbutton.mustache)
- [Moodle 4.5 — contrôleur AMD](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/course/amd/src/activitychooser.js)
- [Moodle 5.3 — template activité](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/course/format/templates/activitychooser.mustache)
- [Moodle 5.3 — bouton du sélecteur](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/course/format/templates/local/content/activitychooserbutton.mustache)
- [Moodle 5.3 — contrôleur de dialogue](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/course/format/amd/src/local/activitychooser/dialogue.js)
- [Moodle 5.1 — migration du sélecteur](https://moodledev.io/docs/5.1/devupdate)

## 32. Conséquences sur l'architecture et la matrice de compatibilité

Les relevés montrent que la plage de versions visée ne peut pas être traitée comme une seule plateforme technique homogène.

| Sujet | Moodle 4.5 | Iomad 5.2 (fork) | Moodle 5.3 |
|---|---|---|---|
| Bootstrap | 4.6.2 | Bootstrap 5, imports historiques Boost | 5.3.8, imports `bootstrap` |
| Navigation primaire/secondaire | rendu historique | rendu historique hérité Moodle 5.2 | composants React dans les templates de navigation |
| Overrides React par thème | non disponibles via le mécanisme 5.2+ | présence à vérifier selon composants de la branche | mécanisme d'override et swizzle |
| Sélecteur d'activités | `core_course` | migration 5.1+ à vérifier dans le fork | `core_courseformat` |
| Templates | racine historique | répertoire `public/` | répertoire `public/` |

Cette matrice est un instantané des branches inspectées, et non une affirmation sur toutes les versions publiées d'Iomad.

Conséquences :
1. Définir une couche de compatibilité frontale interne au thème, avec des implémentations par génération lorsque nécessaire.
2. Éviter les conditions de version dispersées dans les templates ; centraliser les différences dans les fichiers de configuration et modules ciblés.
3. Maintenir une matrice de tests distincte Moodle 4.5, Moodle 5.3 et Iomad 4.5/5.2.
4. Ne pas retarder la conception mobile-first en attendant les composants React : l'expérience doit être cohérente même avec le rendu historique de Moodle 4.5.
5. Prévoir que les adaptations de composants React soient des améliorations progressives pour Moodle 5.2+, et non une dépendance nécessaire au fonctionnement du thème.

## 33. Limites et prochaines vérifications

Cette tranche repose sur les fichiers des branches identifiées et sur la documentation développeur. Elle n'est pas un test d'exécution. Les éléments à confirmer dans les prochaines étapes :
- vérifier les templates et modules du sélecteur d'activités dans Iomad 4.5 et 5.2, notamment les éventuelles adaptations Iomad ;
- cartographier les éditeurs TinyMCE/TinyMCE premium et Atto selon les versions réellement supportées ;
- cartographier le sélecteur de fichiers, le gestionnaire de fichiers et les téléversements dans les formulaires ;
- établir une liste des composants React réellement pertinents pour les espaces apprenant, formateur et gestionnaire ;
- vérifier les éventuels overrides React dans les plugins tiers Iomad utilisés par les clients ;
- définir les tests de non-régression par version et par rôle, sans présumer de la disponibilité de tous les plugins.


## 34. Éditeurs de texte : TinyMCE, Atto et texte brut

### 34.1 Moodle 4.5

Le formulaire natif `MoodleQuickForm_editor` sélectionne l'éditeur préféré avec `editors_get_preferred_editor()`, lui transmet le texte, les options et les paramètres du sélecteur de fichiers, puis rend le champ via le template `core_form/editor_textarea`.

Le moteur d'édition n'est donc pas un composant visuel isolé : il est lié au formulaire, au format du texte, au contexte Moodle, aux fichiers brouillons et aux dépôts disponibles. L'élément prend aussi en compte les utilisateurs non connectés/invités en désactivant le téléversement.

TinyMCE est présent dans `lib/editor/tiny/`. Atto est encore présent dans la branche 4.5 du fork (`lib/editor/atto/`) et s'appuie sur YUI et des sous-plugins `atto_*`. Il ne faut donc pas confondre la version 4.5 avec les versions 5.x.

### 34.2 Moodle 5.3 et Iomad 5.2

Dans Moodle 5.3, TinyMCE reste l'éditeur intégré de référence. Le fichier `public/lib/editor/tiny/classes/editor.php` possède le même blob Git que celui de Moodle 4.5 et que celui d'Iomad 5.2 dans les branches consultées. Cela indique une continuité de cette classe, pas une identité de l'ensemble des réglages, plugins ou interfaces.

Le chemin `public/lib/editor/atto/` n'existe pas dans la branche Moodle 5.3 consultée. La documentation développeur Moodle indique qu'Atto a été retiré du cœur à partir de Moodle 5.0. En conséquence :
- Moodle 4.5 : Atto peut encore être présent dans le cœur, mais TinyMCE est l'éditeur à privilégier ;
- Moodle 5.0 et versions ultérieures : ne pas dépendre d'Atto ni de ses sous-plugins ;
- Moodle 5.3 / Iomad 5.2 : concevoir et valider les parcours sur TinyMCE et le champ texte brut, sans supposer la disponibilité d'Atto.

### 34.3 Conséquences pour Project N

1. Ne pas remplacer TinyMCE par un éditeur Project N ni modifier directement son DOM après initialisation.
2. Préserver les préférences d'éditeur, la configuration administrateur, les plugins et boutons activés, les formats supportés et les comportements d'autosauvegarde.
3. Garder l'accès aux fonctions avancées de l'éditeur dans les parcours formateur ; le guidage pédagogique ne doit pas appauvrir les possibilités de création.
4. Adapter le conteneur, la largeur, les espacements et le comportement responsive autour de l'éditeur, sans écraser ses propres contrôles.
5. Ne pas supposer que TinyMCE Premium est disponible : les fonctions Premium dépendent de la licence et de la configuration de l'instance.
6. Tester clavier, lecteur d'écran, navigation dans les menus/barres d'outils, focus, édition de tableaux, liens, images, médias, plein écran, changement de taille et affichage mobile.
7. Contrôler le contraste et la visibilité du focus dans l'interface de l'éditeur elle-même : un audit axe-core de la page englobante ne suffit pas à garantir l'accessibilité de toutes les interactions de l'éditeur.

Sources :
- [Moodle 4.5 — élément de formulaire editor.php](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/lib/form/editor.php)
- [Moodle 4.5 — moteur TinyMCE](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/lib/editor/tiny/classes/editor.php)
- [Moodle 4.5 — intégration Atto](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/lib/editor/atto/lib.php)
- [Moodle 5.3 — élément de formulaire editor.php](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/lib/form/editor.php)
- [Moodle 5.3 — moteur TinyMCE](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/lib/editor/tiny/classes/editor.php)
- [Iomad 5.2 — moteur TinyMCE](https://github.com/ericfalcon/iomad/blob/IOMAD_502_STABLE/public/lib/editor/tiny/classes/editor.php)
- [Documentation Moodle — Atto](https://moodledev.io/docs/5.1/apis/plugintypes/atto)
- [Documentation Moodle — TinyMCE 5.3](https://docs.moodle.org/503/en/TinyMCE_editor)

## 35. File picker, file manager et dépôts

Moodle distingue plusieurs besoins, qui ne doivent pas être confondus dans les parcours Project N :

| Élément | Usage fonctionnel | Conséquence UX |
|---|---|---|
| `filepicker` | Sélectionner/téléverser un fichier unique, souvent pour le traiter puis ne pas le conserver comme pièce jointe durable | Interface de sélection ponctuelle, retour d'état et possibilité de remplacer le fichier |
| `filemanager` | Gérer une collection de fichiers attachés à une zone de fichiers Moodle | Liste/gestion de fichiers, ajout, suppression, dossiers si autorisés |
| `editor` | Éditer un contenu riche avec médias et fichiers intégrés | Éditeur associé à une zone brouillon et traitement des fichiers lors de l'enregistrement |

Les éléments de formulaire `MoodleQuickForm_filepicker` et `MoodleQuickForm_filemanager` s'appuient sur `repository/lib.php`, l'API des fichiers et des options de contexte, de types acceptés, de tailles et de nombre de fichiers. Le file manager 5.3 expose notamment les options `accepted_types`, `return_types` et les limites de taille de zone ; le sélecteur configure les types acceptés et initialise le sélecteur avec le contexte prévu.

Le sélecteur de dépôts est extensible : les dépôts disponibles et leurs possibilités dépendent de l'installation, de leur activation et des droits. Project N ne doit donc jamais coder en dur une liste supposée de sources (ordinateur, fichiers privés, OneDrive, etc.) ni promettre une source qui n'est pas activée.

### 35.1 Cycle des fichiers et sécurité

La documentation Moodle précise que l'éditeur utilise une zone de fichiers brouillons pendant l'édition. À la soumission, le code applicatif doit traiter le contenu, remplacer les URL temporaires par les références de fichiers attendues et déplacer les fichiers vers la zone définitive appropriée. Le file picker, à lui seul, ne signifie pas nécessairement que le fichier est conservé dans l'API File.

Pour Project N :
- conserver les contextes, capacités, limites de taille, types acceptés, nombre maximal de fichiers et restrictions des dépôts ;
- ne pas contourner le traitement des brouillons ni fabriquer directement des URL de fichiers ;
- conserver les protections contre les téléversements anonymes/non autorisés ;
- ne pas exposer un fichier en se fondant uniquement sur le fait qu'il apparaît dans une liste ou une carte ;
- respecter les contrôles d'accès de l'API File et les permissions des dépôts ;
- ne pas déplacer ou supprimer un fichier sans passer par les mécanismes Moodle attendus.

### 35.2 Présentation et accessibilité

Le thème peut moderniser l'habillage autour du sélecteur et du gestionnaire, mais doit conserver leur comportement natif et leurs contrats JavaScript.

Critères de conception :
1. libellé explicite du fichier sélectionné, type et taille lorsque disponibles ;
2. état de téléversement, réussite et erreur perceptibles sans dépendre uniquement de la couleur ;
3. action de remplacement/suppression identifiable et utilisable au clavier ;
4. annonces accessibles lors des opérations asynchrones ;
5. focus cohérent à l'ouverture/fermeture des dialogues et après une erreur ;
6. contrôles tactiles utilisables sur smartphone, y compris dans les listes d'actions ;
7. gestion des fichiers volumineux et des erreurs réseau sans perte silencieuse du travail ;
8. prise en compte des lecteurs d'écran et des noms accessibles des boutons/icônes ;
9. affichage sans débordement à 320 CSS px et zoom à 200 %.

Le file picker est souvent affiché dans un dialogue : il faudra tester le dialogue complet (titre, description, focus initial, ordre de tabulation, fermeture, retour du focus), et pas seulement le bouton qui l'ouvre.

Sources :
- [Moodle 4.5 — filemanager.php](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/lib/form/filemanager.php)
- [Moodle 4.5 — filepicker.php](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/lib/form/filepicker.php)
- [Moodle 5.3 — filemanager.php](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/lib/form/filemanager.php)
- [Moodle 5.3 — filepicker.php](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/lib/form/filepicker.php)
- [Documentation Moodle — fichiers dans les formulaires](https://moodledev.io/docs/5.3/apis/subsystems/form/usage/files)

## 36. Écarts Iomad observés sur les éditeurs et les fichiers

Dans les fichiers consultés :
- Iomad 4.5 `lib/form/editor.php` possède le même blob Git que Moodle 4.5 ;
- Iomad 5.2 `public/lib/form/editor.php` possède le même blob Git que Moodle 5.3 ;
- Iomad 5.2 `public/lib/editor/tiny/classes/editor.php` possède le même blob Git que Moodle 4.5 et Moodle 5.3.

Cela ne prouve pas que tous les dépôts, plugins d'éditeur, réglages d'administration ou personnalisations d'instance Iomad sont identiques. Cela indique simplement qu'aucune divergence propre à Iomad n'a été relevée dans ces classes centrales lors de cette tranche.

Décisions :
1. Ne pas ajouter de logique Iomad dans le rendu des éditeurs et sélecteurs sans preuve d'un besoin fonctionnel.
2. Les restrictions d'entreprise restent applicables aux pages, dépôts et fichiers selon les capacités/contextes et mécanismes Iomad.
3. Ne jamais interroger les tables Iomad directement pour déterminer la visibilité d'un fichier ou d'un dépôt.
4. Tester les fichiers avec plusieurs comptes et entreprises, y compris les cas de partage, de fichiers privés, de dépôts externes et d'accès direct URL.
5. La présence d'un même blob dans les forks ne remplace pas les tests de cloisonnement sur une instance Iomad réelle.

## 37. Conséquences sur les parcours apprenant et formateur

### Apprenant
- Les dépôts de devoirs, pièces jointes, réponses avec éditeur et fichiers remis doivent conserver les consignes, limites et confirmations natives.
- La progression de téléversement et le résultat de remise doivent être explicites ; éviter qu'un apprenant pense avoir remis un fichier alors qu'il est seulement présent dans une zone brouillon.
- Les contenus riches doivent rester lisibles et navigables sur smartphone, sans imposer un éditeur complet lorsque la saisie simple suffit.

### Formateur
- La création de cours, descriptions de sections, consignes, pages, livres, feedbacks et questions peut mobiliser l'éditeur riche et les dépôts.
- Les parcours guidés « transmettre, pratiquer, évaluer, collaborer » doivent sélectionner les activités/ressources natives, mais conserver l'accès aux paramètres avancés, aux éditeurs et à leurs plugins.
- Les consignes et contenus doivent pouvoir intégrer images, médias, liens et documents lorsque les capacités et réglages l'autorisent.
- Les fichiers déposés dans les zones de brouillon ne doivent pas être présentés comme publiés ou durablement attachés avant la soumission effective du formulaire.

## 38. Limites et prochaines vérifications

Cette tranche reste une analyse statique partielle des forks personnels. Elle n'a pas exécuté les éditeurs, dialogues, dépôts ni téléversements. Les points à approfondir avant clôture de P0 :
- inventorier les templates et modules AMD/ESM réels du file picker, file manager et des dialogues sur chaque version cible ;
- comparer les classes et plugins de dépôts Moodle/Iomad, y compris les éventuels dépôts spécifiques d'entreprise ;
- vérifier le comportement mobile de TinyMCE sur appareils réels et les limites des barres d'outils ;
- cartographier la création/modification des activités et ressources, notamment les formulaires complexes et les réglages avancés ;
- examiner l'affichage apprenant des principales activités natives (devoir, test, forum, page, fichier, H5P) ;
- établir une liste des plugins éditeurs/dépôts présents chez les clients et leur compatibilité ;
- exécuter les tests clavier, NVDA/TalkBack, axe-core, zoom et reflow sur des installations réelles.

Aucun résultat d'exécution ou de test utilisateur n'est revendiqué par cette mise à jour.


## 39. Création et modification des activités — structure des formulaires

Les formulaires de configuration restent construits avec Moodle Forms / QuickForm (definition(), addElement(), règles de validation et aides contextuelles). Les paramètres ne sont donc pas un simple ensemble de champs HTML que le thème pourrait réordonner librement sans risque.

### Devoir (mod_assign)

Les sections observées dans les quatre formulaires sont : général, disponibilité, paramètres de remise, remise en groupes et notifications.

| Branche | Taille du fichier | Éléments addElement() | Aides addHelpButton() |
|---|---:|---:|---:|
| Moodle 4.5 | 18,7 Ko | 32 | 24 |
| Moodle 5.3 | 30,2 Ko | 43 | 28 |
| Iomad 4.5 | 18,7 Ko | 32 | 24 |
| Iomad 5.2 | 25,3 Ko | 39 | 27 |

Moodle 4.5 et Iomad 4.5 ont exactement le même blob pour ce formulaire. Les versions 5.x ont évolué ; les écarts de taille et de nombre d'éléments ne suffisent pas à établir la nature de chaque évolution ni une amélioration ergonomique.

Le formulaire mobilise notamment des éditeurs et gestionnaires de fichiers. La remise de l'apprenant peut dépendre du type de remise activé (texte en ligne, fichiers ou plugins), des dates, des tentatives, des groupes et des notifications.

### Test (mod_quiz)

Les sections de formulaire observées couvrent : général, temps et dates, mise en page, comportement des questions, options de relecture, affichage, sécurité et feedback global.

| Branche | Taille du fichier | Éléments addElement() | Aides addHelpButton() |
|---|---:|---:|---:|
| Moodle 4.5 | 32,0 Ko | 34 | 22 |
| Moodle 5.3 | 33,7 Ko | 36 | 25 |
| Iomad 4.5 | 32,0 Ko | 34 | 22 |
| Iomad 5.2 | 32,8 Ko | 35 | 23 |

Le formulaire expose des paramètres pédagogiques et des paramètres de sécurité/accès qui ne doivent pas être masqués par une création guidée : durée, dates, tentatives, méthode de notation, comportement des questions, relecture, mot de passe et restrictions d'accès notamment.

### Décisions Project N

1. Créer un parcours guidé en deux niveaux : choix de l'intention et configuration essentielle, puis accès explicite à tous les réglages natifs.
2. Ne pas supprimer les champs, options, validations, règles conditionnelles ni aides Moodle.
3. Ne pas réécrire les formulaires QuickForm dans le thème et ne pas injecter de formulaires parallèles.
4. Si un regroupement visuel est souhaité, le réaliser seulement après vérification des groupes natifs, de l'ordre DOM, des dépendances JS, des erreurs et de la restauration des valeurs.
5. Conserver un accès permanent et compréhensible aux paramètres avancés ; ne pas les rendre dépendants d'un survol ou d'un geste uniquement tactile.
6. Ne pas interpréter le nombre de champs comme une mesure de complexité pédagogique : les types de remise, plugins de questions et réglages conditionnels modifient les besoins.

## 40. Affichage apprenant — devoirs

Le point d'entrée mod/assign/view.php est très mince : il vérifie la connexion, récupère le cours et le module, exige mod/assign:view, met à jour la consultation et les accès effectifs, puis délègue l'affichage à l'objet métier assign. Le rendu réel ne se trouve donc pas dans ce contrôleur ni dans l'ancien renderer.php (déprécié depuis Moodle 4.0).

Conséquences :
- ne pas reconstruire une page de devoir à partir du seul contrôleur ;
- cartographier les classes output, templates et actions AMD réellement utilisées avant toute personnalisation ;
- distinguer clairement consigne, disponibilité, état de remise, fichiers déposés, soumission définitive, statut de correction, note et feedback ;
- ne jamais présenter un fichier encore en brouillon comme une remise finalisée ;
- préserver les conditions d'accès, dates limites, tentatives, groupes et notifications.

Le même fichier d'entrée view.php a le même blob dans Moodle 4.5, Moodle 5.3, Iomad 4.5 et Iomad 5.2. Cette identité ne prouve pas que toutes les classes de rendu et dépendances du module sont identiques.

## 41. Affichage apprenant — tests

Le contrôleur du test prépare un objet de page, vérifie les capacités de consultation, tentative, prévisualisation et relecture, contrôle les règles d'accès, récupère les tentatives précédentes et une éventuelle tentative inachevée, calcule les notes et prépare le formulaire de contrôle préalable.

Éléments métier à conserver impérativement dans l'interface :
- nombre de tentatives et possibilité réelle d'en commencer/reprendre une ;
- dates, durée, délai, restrictions d'accès et éventuelles conditions préalables ;
- présence d'une tentative inachevée et action de reprise ;
- note, feedback et informations de relecture selon les droits et réglages ;
- distinction entre prévisualisation formateur et tentative apprenant ;
- état d'accès refusé ou test indisponible, avec explication exploitable.

Le rendu doit rester piloté par les capacités et règles d'accès natives. Le thème peut améliorer la hiérarchie visuelle et la lisibilité mobile, mais ne doit jamais afficher un bouton « commencer » lorsque le moteur d'accès refuse la tentative.

Différences repérées dans les fichiers d'entrée :
- Moodle 4.5 et Iomad 4.5 ont le même blob mod/quiz/view.php ;
- Iomad 5.2 possède un fichier différent de Moodle 5.3, à analyser au niveau du diff avant d'attribuer cet écart à une adaptation Iomad.

## 42. Affichage apprenant — forums, pages et H5P

### Forums

Le contrôleur du forum s'appuie sur des services internes (vaults, factories, capability manager), tient compte du type de forum, du mode d'affichage, des groupes, des discussions, des abonnements et du suivi de lecture. Il utilise des templates dédiés et des renderers spécialisés. Le type « forum unique » possède un traitement distinct des forums à discussions multiples.

Pistes d'amélioration visuelle :
- faciliter la lecture des fils longs sur petit écran ;
- distinguer visuellement sujet, auteur, date, réponse et actions ;
- rendre les actions répondre, s'abonner/se désabonner et suivre accessibles sans menus difficiles à atteindre ;
- préserver les groupes, discussions privées, permissions, évaluations et notifications ;
- éviter de remplacer le moteur de rendu ou de simplifier les modes de discussion sans tests fonctionnels.

Le contrôleur mod/forum/view.php observé possède le même blob dans Moodle 4.5 et Moodle 5.3, ainsi que dans les branches Iomad 4.5 et 5.2 examinées.

### Pages et ressources

La page (mod_page) et la ressource fichier (mod_resource) doivent être traitées comme des contenus pédagogiques, non comme de simples cartes de liens. Le thème peut améliorer le conteneur, la largeur de lecture, les métadonnées et les actions, mais ne doit pas altérer le contenu, les filtres, les restrictions d'accès, les téléchargements, les paramètres d'ouverture ou les exigences de sécurité.

Le fichier mod/page/view.php d'Iomad 5.2 a été consulté ; une comparaison exhaustive des contrôleurs, templates et rendus de page/ressource sur les quatre branches reste à effectuer avant de conclure à une identité de comportement.

### H5P

Le contrôleur mod/h5pactivity/view.php d'Iomad 5.2 a été repéré. La personnalisation doit distinguer le conteneur Moodle et le contenu interactif H5P lui-même. Project N peut améliorer le contexte, les consignes et les informations de suivi autour de l'activité, mais ne doit pas casser le rendu embarqué, les événements de complétion, les tentatives, les rapports ni les interactions clavier propres au contenu H5P.

Le rendu et les contrôles d'accessibilité d'un contenu H5P dépendent aussi du type de contenu et de sa version : un audit du seul thème ou du seul conteneur n'est pas suffisant.

## 43. Synthèse de conception et limites de cette tranche

### Ce que Project N peut raisonnablement transformer

- le regroupement visuel des informations de l'activité ;
- la hiérarchie titre / consigne / disponibilité / action principale ;
- la lisibilité et la largeur de lecture sur smartphone ;
- la présentation des états (à faire, disponible, remis, en cours, corrigé, indisponible) à condition qu'ils proviennent de données natives fiables ;
- la position et la taille des actions principales, sans supprimer les actions secondaires ;
- l'accès aux réglages avancés du formateur, sans en changer la sémantique.

### Ce que Project N doit laisser au moteur Moodle

- contrôles d'accès, capacités, contextes et groupes ;
- règles d'ouverture/fermeture et restrictions ;
- gestion des tentatives, soumissions, notes et feedbacks ;
- validation des formulaires et sauvegarde ;
- rendu des questions, éditeurs, dépôts et contenus interactifs ;
- suivi d'achèvement et événements pédagogiques.

### Accessibilité à valider pour chaque parcours

- ordre de lecture et de tabulation cohérent ;
- titres et landmarks non redondants ;
- labels, descriptions, erreurs et aides associés aux champs ;
- état des contrôles communiqué sans dépendre uniquement de la couleur ;
- actions principales accessibles au clavier et aux lecteurs d'écran ;
- focus visible et restitué après dialogues, soumission et navigation ;
- zones tactiles confortables, sans action uniquement au survol ;
- reflow à 320 CSS px et zoom 200 % ;
- test des contenus H5P et éditeurs séparément du shell du thème.

### Limites

Cette tranche est une analyse statique des contrôleurs et formulaires dans les forks personnels. Elle ne constitue pas un audit exhaustif des templates, classes output, modules AMD/ESM, plugins tiers, dépôts, types de questions ou contenus H5P. Aucun test runtime, axe-core, clavier, NVDA, TalkBack ou test sur smartphone réel n'a été exécuté.

Les prochaines lectures ciblées doivent couvrir :
- les classes output et templates de mod_assign et mod_quiz ;
- les formulaires et rendus mod_forum, mod_page, mod_resource et mod_h5pactivity sur les quatre branches ;
- les composants AMD/ESM et les dialogues de remise/tentative ;
- les plugins de remise, types de questions et types de contenus H5P réellement utilisés dans les environnements clients.


## 44. Rendu des devoirs et tests — classes output, templates et JavaScript

Cette tranche descend du contrôleur vers les objets de présentation, les templates Mustache et les modules AMD. Elle confirme que les activités ne sont pas des pages HTML statiques : une partie du rendu est pilotée par des objets PHP, et les interfaces de correction et de tentative reposent sur des contrats DOM/JavaScript précis.

### 44.1 Devoirs — état de remise et feedback

La classe `mod/assign/classes/output/assign_submission_status.php` fournit un objet de présentation qui transporte notamment :
- les dates d'ouverture des remises ;
- la remise individuelle ou en groupe et les informations de groupe ;
- les membres d'un groupe qui doivent encore remettre ;
- l'activation des remises et les plugins de remise ;
- la méthode de réouverture et le nombre maximal de tentatives ;
- le statut de correction et les contraintes d'appartenance à un groupe.

Cette classe possède le même blob dans Moodle 4.5, Moodle 5.3 et Iomad 5.2. Cela indique une base de présentation partagée pour ces données, sans prouver que tout le comportement du module est identique.

Le renderer du devoir expose des méthodes distinctes pour les fichiers, formulaires, résumés utilisateur, confirmations de soumission, synthèses de correction, feedback et statuts compacts. Il ne faut donc pas assimiler le rendu complet à une simple surcharge de template de page.

### 44.2 Interface de correction du formateur

Le template `mod_assign/grading_app` compose plusieurs régions fonctionnelles :
- navigation entre les participants ;
- panneau de relecture ;
- panneau de notation ;
- actions de correction ;
- zone de superposition pendant les opérations.

Le module AMD `grading_panel.js` pilote notamment l'enregistrement asynchrone, la sérialisation du formulaire, la conservation de l'état des champs, les notifications de soumission et le rechargement de la correction. La zone de correction est donc un composant interactif avec un contrat de données et des sélecteurs `data-region` utilisés par le JavaScript.

Évolution observée entre Moodle 4.5 et Moodle 5.3 :
- le template 5.3 ajoute une poignée de redimensionnement avec `tabindex="0"` et un nom accessible ;
- le module de correction 5.3 prend en compte les marqueurs (markers) dans la soumission de la note et comporte des contrôles supplémentaires ;
- L'observation antérieure selon laquelle le template Iomad 5.2 correspond au template Moodle 5.3, tandis que le module AMD diffère, est une comparaison intergénérationnelle. Elle ne permet pas de qualifier une adaptation Iomad ; la comparaison Moodle 5.2 / Iomad 5.2 est documentée en 44.6.

Conséquences Project N :
1. Ne pas renommer, déplacer ou supprimer les régions `data-region` utilisées par les modules natifs.
2. Ne pas transformer un panneau redimensionnable en interaction souris uniquement ; conserver clavier, focus visible et nom accessible.
3. Préserver les états de sauvegarde, erreurs, confirmation et notification étudiant.
4. Tester la navigation entre participants, les changements non enregistrés, les marqueurs, les annotations et les différents plugins de feedback.
5. Sur smartphone, préférer une progression séquentielle entre relecture et notation à une réduction miniature de deux panneaux côte à côte ; conserver toutefois les fonctions natives.

### 44.3 Actions de remise de l'apprenant

Le template `user_submission_actionmenu` expose des actions conditionnelles de remise, consultation de la remise précédente, modification et suppression. Les actions sont rendues via les composants natifs `core/single_button` et des formulaires. Leur présence dépend des données préparées par la classe output.

Le thème peut hiérarchiser une action principale et clarifier les états, mais ne doit pas afficher une action de soumission, modification ou suppression indépendamment des conditions fournies par Moodle. La confirmation de soumission définitive doit rester explicite.

### 44.4 Tests — tentatives, questions et confirmation

La classe `mod_quiz/classes/output/view_page.php` transporte notamment :
- les tentatives précédentes et leurs objets ;
- le nombre de tentatives et la possibilité d'en commencer une autre ;
- l'existence d'une tentative inachevée ;
- la dernière tentative terminée ;
- les notes et feedbacks affichables ;
- le libellé et l'URL d'une action de démarrage autorisée.

Elle possède le même blob dans Moodle 4.5, Moodle 5.3 et Iomad 5.2. Les constats antérieurs sur les templates rapprochant Iomad 5.2 de Moodle 5.3 sont intergénérationnels. Pour éviter de les interpréter comme des écarts Iomad, les templates principaux de tentative ont été comparés en génération égale et sont récapitulés en 44.6.

Points techniques :
- `list_of_attempts` délègue chaque ligne au template `attempt_summary_information` ;
- `timer` expose une région `role="timer"` et un bouton permettant de masquer/afficher le temps ;
- `submission_confirmation` est rendu à la demande par le module AMD de confirmation avant la soumission définitive ;
- `question_slot` porte des contrôles de banque de questions, de sélection de version et d'édition, avec des différences de classes Bootstrap entre versions.

Le module AMD `submission_confirmation.js` intercepte l'action de fin de tentative, construit la confirmation et ne soumet le formulaire qu'après confirmation. Project N ne doit pas neutraliser cette étape ni déclencher une soumission à partir d'une simple navigation ou d'un changement de présentation.

### 44.5 Accessibilité et responsive — critères complémentaires

- Pour la minuterie, conserver l'identification sémantique du temps et vérifier que les mises à jour fréquentes ne produisent pas une annonce vocale excessive. Tester le comportement réel avec NVDA et TalkBack.
- Pour la confirmation de fin de test, vérifier titre, description, focus initial, fermeture, retour du focus et annonce du nombre de questions sans réponse.
- Pour la correction, vérifier que les panneaux, la navigation participant, les actions et les erreurs restent compréhensibles lorsque le JavaScript recharge une région.
- Pour les actions de remise, vérifier que les libellés distinguent sans ambiguïté « modifier », « supprimer », « consulter » et « remettre définitivement ».
- Pour les questions, vérifier les labels des sélecteurs, cases à cocher et boutons d'aperçu, ainsi que l'ordre clavier dans la banque de questions.
- En affichage étroit, les tables de tentatives et de correction doivent rester lisibles sans perte de colonnes essentielles ni défilement horizontal incontrôlé.

### 44.6 Comparaison correcte Moodle 5.2 / Iomad 5.2

La comparaison des branches de même génération a été reprise sur les forks personnels. Les fichiers suivants ont été récupérés dans MOODLE_502_STABLE et IOMAD_502_STABLE ; les SHA de blob sont strictement identiques :

| Fichier | SHA commun | Conclusion limitée au fichier |
|---|---|---|
| mod/assign/mod_form.php | ecc81dbe149bc20ac72e3435a49d14c2d5e5daa9 | Formulaire natif de configuration du devoir identique |
| mod/quiz/mod_form.php | fb9ecc92016d4ebbfd9c4a0cdc991b19d2255ab1 | Formulaire natif de configuration du test identique |
| mod/assign/classes/output/assign_submission_status.php | 7be979424df357c17978984daaa6cbc91be83ad7 | Objet de présentation du statut de remise identique |
| mod/assign/amd/src/grading_panel.js | 441476cb1da0adcf6e2f0379609170c114be1df1 | Gestion JavaScript du panneau de correction identique |
| mod/quiz/classes/output/view_page.php | a1e9ed55b39a20c7a8bb55b96f702fb158c8d9b8 | Objet de présentation de la page du test identique |
| mod/assign/templates/grading_app.mustache | 2c537cd8fa08d70ced3a322d236b6d97232d6450 | Interface de correction du formateur identique |
| mod/assign/templates/user_submission_actionmenu.mustache | 1ebad273e804ca24e5fd9ee39f2244e53139f07c | Menu des actions de remise identique |
| mod/quiz/amd/src/submission_confirmation.js | 8b23e9135f691aba34a1942e9c4a2420426120fa | Confirmation de fin de tentative identique |
| mod/quiz/templates/list_of_attempts.mustache | 7886548d95691a81ffe36a38a3f2bc9fe4dacec5 | Liste des tentatives identique |
| mod/quiz/templates/attempt_summary_information.mustache | d13a4adf531ef7d299e8fee4e553b1980e295344 | Résumé de tentative identique |
| mod/quiz/templates/question_slot.mustache | fe24e3ccc3059187087bfed7da635f9c23750b6c | Emplacement de question identique |

Pour ce périmètre, aucune divergence Iomad n'est donc observée dans ces onze fichiers entre Moodle 5.2 et Iomad 5.2. Les constats précédents reposant sur Moodle 5.3 face à Iomad 5.2 (notamment le template de correction et le module AMD) décrivaient un écart de génération et ne doivent pas être interprétés comme des adaptations propres à Iomad.

Cela ne prouve pas que tout le module assign/quiz est identique : les autres templates, bibliothèques, contrôleurs, services, événements, APIs et éventuels ajouts Iomad doivent être examinés séparément.

### Sources de code consultées

- [Moodle 4.5 — assign_submission_status.php](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/mod/assign/classes/output/assign_submission_status.php)
- [Moodle 4.5 — grading_app.mustache](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/mod/assign/templates/grading_app.mustache)
- [Moodle 4.5 — grading_panel.js](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/mod/assign/amd/src/grading_panel.js)
- [Moodle 5.3 — grading_app.mustache](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/mod/assign/templates/grading_app.mustache)
- [Moodle 5.3 — grading_panel.js](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/mod/assign/amd/src/grading_panel.js)
- [Moodle 5.3 — quiz view_page.php](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/mod/quiz/classes/output/view_page.php)
- [Moodle 5.3 — submission_confirmation.js](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/mod/quiz/amd/src/submission_confirmation.js)
- [Iomad 5.2 — grading_panel.js](https://github.com/ericfalcon/iomad/blob/IOMAD_502_STABLE/public/mod/assign/amd/src/grading_panel.js)

Analyse statique uniquement : aucune exécution de l'interface de correction, aucune soumission réelle, aucun test axe-core, clavier, lecteur d'écran ou appareil mobile n'a été effectué.


## 45. Forums, H5P, pages et ressources — composants de rendu

### 45.1 Forum : rendu fragmenté et interactions nombreuses

Le module forum dispose d'un ensemble important de templates Mustache, notamment :
- listes de discussions par type de forum (standard, questions/réponses, social, actualités, discussion unique) ;
- affichage des discussions et des messages en mode imbriqué ou fil de discussion ;
- publication/réponse en ligne ;
- actions de discussion (favori, verrouillage, épinglage, abonnement) ;
- barre d'action, paramètres dans un tiroir, recherche, suivi de lecture et évaluations.

Les modules AMD correspondants gèrent les discussions, listes, réponses en ligne, favoris, verrouillage, épinglage, abonnements, événements et sélecteurs. L'interface n'est donc pas un bloc monolithique que Project N pourrait remplacer sans reprendre un grand nombre de comportements.

Comparaison des listes :
- Moodle 4.5 et Moodle 5.3 conservent la majorité des templates et modules AMD ;
- Moodle 5.3 ajoute notamment un template de navigation dans une discussion et un module de bascule de vue d'ensemble ;
- plusieurs templates de discussion et modules sont identiques par blob entre les deux versions ;
- les templates d'action, de recherche, de liste et de paramètres ont aussi évolué.

Les quatre branches Iomad 4.5/5.2 et Moodle 4.5/5.3 présentent les mêmes familles de classes output du forum. Cela ne signifie pas que chaque template et module est strictement identique : une comparaison fichier par fichier reste nécessaire avant de qualifier un écart d'adaptation Iomad.

**Décisions Project N :**
1. Conserver les différents types et modes de forum, notamment les discussions imbriquées et filées.
2. Ne pas supprimer les actions natives de favori, abonnement, verrouillage, épinglage, suivi de lecture ou évaluation.
3. Sur mobile, privilégier une lecture verticale des messages, avec auteur/date/actions clairement identifiables.
4. Pour les fils longs, conserver une hiérarchie perceptible entre message initial, réponses et niveaux d'imbrication ; ne pas s'appuyer uniquement sur l'indentation ou la couleur.
5. Conserver les tiroirs et dialogues natifs, leurs sélecteurs JS et la gestion du focus.
6. Vérifier les notifications de réponse, les annonces des bascules d'état et le retour du focus après les actions asynchrones.

### 45.2 H5P : shell Moodle et activité interactive

Le module H5P Activity possède des classes output et templates propres, notamment pour :
- l'affichage d'une tentative ;
- la liste des tentatives ;
- le résultat ;
- les liens et pages de rapport ;
- les résultats et rapports de tentatives.

Les templates ciblés sont largement identiques entre Moodle 4.5 et Moodle 5.3. Le template `result.mustache` diffère cependant. Aucun répertoire `amd/src` propre à `mod_h5pactivity` n'a été trouvé dans les chemins inspectés : cela ne signifie pas qu'aucun JavaScript n'intervient, car le lecteur H5P et ses bibliothèques ont leurs propres ressources et dépendances.

Project N doit donc distinguer :
- le shell Moodle (titre, consigne, contexte, disponibilité, progression, tentatives, résultat) ;
- le lecteur H5P et le contenu interactif ;
- les données de suivi et de complétion transmises au moteur Moodle.

Le thème ne doit pas encapsuler le lecteur dans un conteneur qui contraint sa largeur/hauteur, masque ses contrôles, coupe ses contenus ou perturbe le focus. Le test d'accessibilité doit porter sur le lecteur et le type de contenu, pas uniquement sur le shell du thème.

Comparaison complémentaire Moodle 5.2 / Iomad 5.2 : le template mod_h5pactivity/templates/result.mustache possède le SHA commun 71768c563c8367f4f3339018f95900f2ce5e872c dans les deux branches. Le rendu de résultat ciblé est donc identique pour cette génération. Cela ne permet pas de conclure sur les bibliothèques H5P, les types de contenus, le lecteur embarqué ni les rapports complets.

### 45.3 Page et ressource fichier : rendu principalement porté par le cœur

Les chemins `mod/page/classes/output` et leurs répertoires `templates` ne sont pas présents dans les branches Moodle 4.5 et 5.3 consultées. Pour `mod/resource`, seul un composant de format de cours existe (`classes/output/courseformat/activitybadge.php`, présent en 4.5 et 5.3) ; il n'existe pas de jeu de templates dédiés à l'affichage de la ressource. Leurs pages s'appuient principalement sur les mécanismes de rendu génériques du cœur Moodle et les formats de cours, plutôt que sur un ensemble autonome de templates propres à chaque module.

Conséquences :
- ne pas supposer qu'un template propre à `mod_page` ou `mod_resource` peut être surchargé pour refaire l'intégralité de l'affichage ;
- analyser le layout, les output components du cœur, le contenu filtré et les éventuels plugins avant d'intervenir ;
- pour une page, préserver le contenu HTML filtré et sa sémantique ;
- pour une ressource fichier, préserver les paramètres d'ouverture, le téléchargement, les restrictions, les types MIME et les contrôles d'accès ;
- ne pas confondre l'icône ou la carte d'une ressource avec le contrôle réel d'accès au fichier.

### 45.4 Accessibilité transversale

Les parcours forum et H5P doivent être audités en tenant compte de leurs composants interactifs, pas seulement de leur apparence :
- titres et niveaux de titres cohérents dans les discussions ;
- auteur, date, statut et actions correctement associés au message ;
- état favori/abonnement/verrouillage/épinglage exposé aux technologies d'assistance ;
- dialogues, tiroirs et réponses en ligne utilisables au clavier ;
- focus visible et retour de focus après action ;
- annonces pertinentes des changements d'état sans répétitions excessives ;
- contenus H5P testés par type d'activité, version de bibliothèque et appareil ;
- absence de débordement horizontal et de contenu masqué à 320 CSS px et à 200 % de zoom.

### 45.5 Limites de cette tranche

Les inventaires de répertoires ont été réalisés sur les forks personnels. Les templates et modules AMD ont été recensés, mais pas tous lus intégralement. Les répertoires de templates/output dédiés à page et resource étant absents, leur rendu devra être approfondi au niveau du cœur Moodle et des layouts. Les plugins tiers, formats de cours, types H5P et bibliothèques embarquées restent hors de cette lecture.

Aucun test d'exécution, de navigation clavier, axe-core, NVDA, TalkBack ou smartphone n'a été effectué.


## 46. Comparaison ciblée des forums — Moodle 5.2 / Iomad 5.2

Cette section remplace les comparaisons croisées de générations précédemment utilisées. Les fichiers ont été récupérés dans les forks ericfalcon/moodle (MOODLE_502_STABLE) et ericfalcon/iomad (IOMAD_502_STABLE). Les SHA complets sont identiques pour chacun des fichiers ci-dessous.

### 46.1 Message de forum et réponses imbriquées

| Fichier | SHA commun Moodle 5.2 / Iomad 5.2 | Résultat |
|---|---|---|
| mod/forum/templates/forum_discussion_post.mustache | 9ab4de95951b1743ee47a3492f68ea9697d3e107 | Identique |
| mod/forum/templates/forum_discussion_nested_v2_post_reply.mustache | b517fdb6548fab643f64b99f8437b8ec90004266 | Identique |

Les contrats DOM et d'accessibilité présents dans ces templates sont donc partagés dans les deux branches de même génération : régions data-region, identifiants de message, associations aria-labelledby/aria-describedby, actions conditionnées par les capacités et données nécessaires aux réponses privées. Le thème doit préserver ces contrats, sans conclure que tous les composants du forum sont identiques.

### 46.2 Discussions, abonnements et bascule de vue

| Fichier | SHA commun Moodle 5.2 / Iomad 5.2 | Résultat |
|---|---|---|
| mod/forum/amd/src/discussion_list.js | 97bf7273373e9320bc169800addd0866955db8b2 | Identique |
| mod/forum/amd/src/forum_overview_toggle.js | 89631cdd9626f766211c794bf2f2832c05948b4d | Identique |

La gestion JavaScript ciblée de la liste des discussions et de la bascule de vue est donc également identique entre Moodle 5.2 et Iomad 5.2. Les différences antérieurement signalées entre Moodle 5.3 et Iomad 5.2 relevaient de générations différentes ; elles ne permettent pas d'affirmer que le portage Iomad avait omis ou modifié ces fonctions.

### 46.3 Conséquences pour Project N

1. Conserver les templates natifs et leurs contrats DOM, notamment data-region, data-action, data-post-id et les associations ARIA.
2. Préserver les actions de favori, abonnement, verrouillage, épinglage, suivi de lecture et évaluation lorsqu'elles sont fournies par Moodle.
3. Conserver la hiérarchie sémantique des discussions et réponses ; ne pas s'appuyer uniquement sur la couleur ou l'indentation.
4. Préserver le comportement clavier, le focus après action asynchrone et la cohérence entre libellé visible et nom accessible.
5. Ne pas extrapoler ces résultats à l'ensemble du forum, aux plugins tiers, aux événements ou aux fonctions multi-entreprises non inclus dans les fichiers comparés.

### 46.4 Portée et limites de la comparaison

Quatre fichiers ciblés ont été comparés par SHA complet dans les branches de même génération. Leur identité établit une égalité de contenu, pas une équivalence fonctionnelle de toute l'activité forum. Les contrôleurs, classes output, services, autres templates, modules AMD, plugins et comportements d'accès doivent faire l'objet d'une analyse distincte. Aucun test d'exécution, de navigation clavier, axe-core, NVDA, TalkBack ou smartphone n'a été effectué.

## 47. Stratégie de surcharge du rendu — règles d'architecture pour Project N

### 47.1 État actuel du dépôt

Au moment de cette vérification, la branche `main` du dépôt Project N contient :
- `README.md` et `ROADMAP.md` ;
- `docs/P0-CADRAGE.md`, `docs/P0-NAVIGATION.md` et `docs/P0-CARTOGRAPHIE-NAVIGATION.md`.

Aucun répertoire de code de thème (`theme/`) n'est encore présent. Les recommandations qui suivent sont donc des règles de conception à appliquer pendant P0/P1, et non la description d'une implémentation déjà codée.

### 47.2 Ordre de préférence des interventions

La documentation officielle Moodle distingue les layouts, les templates Mustache, les renderers et les objets output. Les templates peuvent être surchargés dans le thème sous `templates/<componentname>/<templatename>.mustache`, sans modifier le plugin d'origine. Les renderers sont un point d'extension plus puissant, mais exigent l'activation d'une renderer factory adaptée.

Pour limiter le coût de maintenance de Project N, retenir l'ordre suivant :

1. Variables CSS/SCSS, classes et composants Bootstrap déjà fournis par Boost.
2. SCSS du thème et styles ciblés, sans sélectionner des éléments uniquement par leur position DOM.
3. Surcharge Mustache d'un template précis, seulement si la structure HTML doit réellement changer.
4. Renderer de thème ciblé, uniquement si le template ne suffit pas ou si l'API output l'exige.
5. Layout complet, uniquement lorsqu'un changement de structure de page est indispensable.
6. JavaScript/ESM de remplacement, en dernier recours, après vérification des API publiques et des points d'extension.

Cette hiérarchie ne doit pas devenir une règle absolue : une adaptation d'accessibilité peut exiger une intervention structurelle. Elle sert à éviter de réécrire un composant natif lorsque CSS ou un template ciblé suffit.

### 47.3 Règles Mustache

- Copier uniquement le template nécessaire et conserver son nom de composant exact.
- Conserver les variables et structures de données exportées par la classe output.
- Préserver les identifiants, attributs `data-region`, `data-action`, `data-*` et contrats de fragments utilisés par les modules AMD/ESM.
- Ne pas supprimer les attributs ARIA, les noms accessibles, les labels, les descriptions ou les éléments de focus sans justification vérifiée.
- Éviter de figer dans le template des états que le serveur fournit déjà (droits, statut de remise, disponibilité, tentative, abonnement).
- Documenter chaque surcharge : raison, fichiers sources, versions Moodle/Iomad vérifiées, dépendances JS et critères de retrait éventuel.

### 47.4 Règles Renderer et Output API

La documentation Moodle indique que les renderers sont un point d'extension pour modifier le rendu d'un composant. Le rendu peut toutefois être assuré par Mustache, un composant React ou du HTML construit manuellement. Un thème ne doit donc pas présumer qu'une méthode renderer intercepte tous les chemins d'affichage.

Avant toute surcharge :
- identifier la classe renderable et son interface (`renderable`, `templatable`, `named_templatable`, ou `react_component_renderable`) ;
- identifier la méthode renderer réellement appelée ;
- vérifier si le composant est rafraîchi en fragments ;
- vérifier le comportement sans JavaScript et avec JavaScript ;
- vérifier les différences de version et les extensions Iomad.

Ne pas créer de renderer générique qui modifie le HTML de façon globale si une surcharge Mustache ciblée répond au besoin.

### 47.5 Politique de compatibilité et de maintenance

Chaque surcharge doit être enregistrée dans une matrice de compatibilité indiquant :
- fichier source amont et chemin de surcharge Project N ;
- version Moodle et version Iomad vérifiées ;
- SHA du fichier source analysé ;
- raison fonctionnelle et résultat UX attendu ;
- sélecteurs/attributs DOM consommés par JavaScript ;
- tests fonctionnels, responsive et accessibilité à exécuter ;
- risque de régression et méthode de contrôle après mise à jour.

Une surcharge doit être supprimée lorsqu'une évolution du cœur fournit nativement le résultat attendu, ou lorsqu'elle n'est plus nécessaire. Le but est de garder Project N fin et maintenable, et non de maintenir un fork parallèle de l'interface Moodle.

### 47.6 Références développeur Moodle

- [Theme plugins — structure, parents, layouts, templates et renderer factory](https://docs.moodle.org/dev/Themes_overview)
- [Output API — cycle de rendu, renderers, renderables et templates](https://docs.moodle.org/dev/Output_API)
- [Templates — organisation, surcharge et contexte Mustache](https://moodledev.io/docs/5.0/guides/templates)
- [Course format — rendu des sections et fragments du cours](https://docs.moodle.org/en/Development%3ACourse_formats)

Les liens de cette section n'ont pas été consultés (accès à moodledev.io et docs.moodle.org non vérifié) et l'interface `react_component_renderable` n'a pas été recherchée dans le code : à vérifier. Ces références décrivent les mécanismes généraux Moodle. Elles ne remplacent pas la vérification du code exact de chaque branche ciblée, en particulier pour les changements de chemins introduits dans Moodle 5.x et les adaptations propres à Iomad.


## 48. Rendu natif des cours — sections, activités et formats de cours

### 48.1 Architecture de rendu observée

Le rendu des sections et activités ne relève pas d'un simple template de thème. Il s'appuie sur le composant core_courseformat :

- les classes output de section et d'activité implémentent renderable et named_templatable ;
- les classes output exportent les données destinées aux templates Mustache ;
- la classe de format de cours fournit les classes output spécialisées via get_output_classname() ;
- les templates sont organisés en composants réutilisables : section, en-tête, disponibilité, résumé, liste d'activités, activité, menu de contrôle et visibilité.

La classe output content\section délègue plusieurs parties du rendu à des classes distinctes, et non à un unique renderer monolithique. Elle calcule notamment l'état de visibilité, les sections orphelines/stealth, les restrictions, la section courante, les contrôles d'édition et l'état replié. La classe output content\cm prépare les données de chaque activité : nom, disponibilité, contenu alternatif, dates, achèvement, mode groupe, visibilité, menu de contrôle et bouton d'ajout.

**Conséquence :** Project N doit travailler avec cette architecture de composants. Il ne faut pas reconstruire la liste des activités ni recalculer côté thème les droits, les restrictions, les états de complétion ou la disponibilité.

### 48.2 Templates et points d'extension réels

Le template core_courseformat/local/content/section est un wrapper autour de section/content. Il expose notamment un bloc Mustache nommé core_courseformat/local/content/section/content, ainsi qu'un bloc pour l'insertion d'une section. Cette composition permet de personnaliser une partie du rendu sans recopier nécessairement tout le template.

Les fichiers examinés montrent les responsabilités suivantes :

| Composant | Responsabilité observée |
|---|---|
| section.mustache | Conteneur de section, attributs d'identification, composition du contenu |
| section/content.mustache | En-tête, badges, restrictions, repli/dépli, résumé et liste d'activités |
| section/cmitem.mustache | Élément d'activité dans la liste |
| cm.mustache | Carte/conteneur d'activité, contrôles d'édition et activity chooser |
| section/header.mustache | Titre et métadonnées de section |
| section/cmlist.mustache | Liste des activités d'une section |
| section/availability.mustache | Informations de disponibilité et restrictions |
| section/controlmenu.mustache | Actions de gestion de section |

Les blocs Mustache et les composants partiels constituent des points d'extension plus fins que la copie de la page complète. Leur disponibilité et leur contenu doivent néanmoins être contrôlés dans chaque branche cible.

### 48.3 Contrats DOM à préserver

Les templates contiennent des attributs consommés par les mécanismes de cours et les modules JavaScript. Parmi ceux relevés :

- section : data-for="section", data-sectionid, data-id, data-number, data-sectionname ;
- informations de section : data-region="sectionbadges", data-for="sectioninfo" ;
- activité : data-region="activity-card", data-for="cmitem", data-id et l'ancre d'activité ;
- édition et sélection groupée : structures et attributs propres aux contrôles de l'éditeur de cours.

Ils ne sont pas des attributs décoratifs. Une surcharge qui les supprime ou les déplace peut casser le déplacement d'activités, l'édition, les mises à jour partielles, les sélecteurs Behat ou les interactions du cours.

Les contrôles de repli/dépli utilisent également un état accessible (aria-expanded) et des attributs d'interaction. Les libellés, l'état annoncé et le comportement clavier doivent rester cohérents. La présence d'un lien portant role="button" dans le template historique doit être réévaluée dans le contexte exact de la version *(présence de ce motif non vérifiée)* ; ne pas reproduire ce motif par défaut dans les nouveaux composants Project N.

### 48.4 Comparaison correcte : Moodle 5.2 / Iomad 5.2

La comparaison de référence pour évaluer les adaptations propres à Iomad doit opposer des versions de même génération : Moodle 5.2 à Iomad 5.2. Iomad est construit à partir d'une version Moodle correspondante ; comparer Iomad 5.2 à Moodle 5.3 confond les adaptations Iomad avec le décalage de génération entre les deux bases.

Les SHA complets ci-dessous ont été vérifiés dans les forks personnels :

| Fichier | Moodle 5.2 | Iomad 5.2 | Résultat |
|---|---|---|---|
| Output section.php | bf6ea5c5e877d436ec2d63255067a33482b7b7f5 | bf6ea5c5e877d436ec2d63255067a33482b7b7f5 | Identique |
| Output cm.php | 914b28b4643d6b71fda2d6e52911404bdad6690e | 914b28b4643d6b71fda2d6e52911404bdad6690e | Identique |
| Template section.mustache | 4138fd2116f1b3b4e3855b37aca309792b317d11 | 4138fd2116f1b3b4e3855b37aca309792b317d11 | Identique |
| Template section/content.mustache | b57667d80b8dcda59832611333f3a97ed49bae47 | b57667d80b8dcda59832611333f3a97ed49bae47 | Identique |
| Template cm.mustache | 0c5f73d9310d493d21b92ea60f0927f3d7a85d80 | 0c5f73d9310d493d21b92ea60f0927f3d7a85d80 | Identique |
| Template section/cmitem.mustache | f95bacb4e36a7529b5759005a8daa2fed2d4f649 | f95bacb4e36a7529b5759005a8daa2fed2d4f649 | Identique |
| Renderer section_renderer.php | 1714cf3e0ec7036798e425420d93bb0ff6d88e56 | 1714cf3e0ec7036798e425420d93bb0ff6d88e56 | Identique |
| Classe core_courseformat/base.php | 0987552e5b00d2f5bb87c4b6caa6e441ac8423f9 | 0987552e5b00d2f5bb87c4b6caa6e441ac8423f9 | Identique |

**Résultat : sur tous les fichiers de rendu comparés dans ce périmètre, Moodle 5.2 et Iomad 5.2 sont strictement identiques.** Les différences précédemment signalées entre Moodle 5.3 et Iomad 5.2 correspondent au changement de génération Moodle 5.2 → 5.3, et ne doivent pas être présentées comme des divergences propres à Iomad.

### 48.5 Lecture du décalage de génération

Le cycle de publication Iomad prévoit une version correspondante à chaque version Moodle, avec une cible de publication Iomad environ trois mois après Moodle. Il existe donc une période transitoire pendant laquelle la dernière branche Iomad publiée repose sur la génération Moodle précédente.

Conséquences pour la cartographie Project N :

1. **Comparaison de portage / adaptation Iomad :** comparer Moodle X.Y et Iomad X.Y. C'est la comparaison qui permet d'identifier les modifications spécifiques à Iomad.
2. **Comparaison de migration / anticipation :** comparer Iomad X.Y à Moodle X.Y+1 uniquement pour identifier les changements du cœur Moodle à anticiper lors du futur portage Iomad. Les différences doivent être étiquetées « écart de génération », pas « divergence Iomad ».
3. **Compatibilité Project N :** maintenir une matrice distincte pour Moodle 5.2, Moodle 5.3, Iomad 5.2 et, lorsqu'elle sera disponible, Iomad 5.3.
4. **Validation du support :** ne pas déclarer Iomad 5.3 compatible ou disponible avant que la branche et la version correspondantes aient été effectivement publiées et vérifiées.

La documentation officielle Iomad décrit explicitement ce cycle *(à vérifier : liens externes non consultés)* : une version Iomad correspond à chaque version Moodle et la cible de sortie se situe environ trois mois après Moodle. Les notes de version Iomad 5.2 indiquent que cette version a été retardée et que le travail sur Iomad 5.3 était déjà engagé. (Voir [cycle de publication Iomad](https://docs.iomad.org/wiki/Releases) et [notes Iomad 5.2](https://www.iomad.org/documentation/documentation/release-notes/iomad-5-2/).)

## 49. Éditeur de cours et sélecteur d'activités — événements, clavier et hooks

### 49.1 Éditeur de cours : un contrat JavaScript commun

Le fichier course/format/amd/src/courseeditor.js a le même SHA dans les quatre branches consultées : Moodle 4.5, Moodle 5.3, Iomad 4.5 et Iomad 5.2 (6560c35e). Le module expose notamment dispatchStateChangedEvent() et émet l'événement core_course/state_changed.

Ce constat établit que ce fichier précis est commun aux quatre branches. Il ne prouve pas que tous les modules de l'éditeur, ses dépendances ou ses consommateurs sont identiques. Project N doit éviter de détourner ou de remplacer cet événement : il sert de contrat de notification de changement d'état du cours.

### 49.2 Sélecteur d'activités : composant natif à conserver

Dans Moodle 5.3 et Iomad 5.2, le sélecteur d'activités est porté par core_courseformat :

- module AMD activitychooser.js ;
- classe output activitychooserbutton ;
- template activitychooserbutton.mustache ;
- dialogue, dépôt et sélecteurs associés dans core_courseformat/local/activitychooser.

Le module écoute les événements d'activation standard et clavier (activate et keyboardActivate), repère le bouton de sélection d'activité, puis détermine la position dans le cours à partir des données de section. Le code signale explicitement que l'usage du seul numéro de section est déprécié et demande l'attribut data-section-id. Le bouton et le conteneur de section restent donc liés par un contrat de données.

La classe output activitychooserbutton déclenche aussi le hook public before_activitychooserbutton_exported avant l'export des données. Ce hook est un point d'extension à privilégier pour ajouter une action complémentaire au sélecteur, plutôt que de recopier le template ou de remplacer son JavaScript, sous réserve de vérifier son enregistrement dans chaque version ciblée.

### 49.3 Comparaison correcte : Moodle 5.2 / Iomad 5.2

Les mêmes fichiers ont été récupérés dans les branches Moodle 5.2 et Iomad 5.2. Tous les SHA sont identiques :

| Fichier | Moodle 5.2 | Iomad 5.2 | Résultat |
|---|---|---|---|
| courseeditor.js | 6560c35ea4d728a3273405eb3e6954cc1c912022 | 6560c35ea4d728a3273405eb3e6954cc1c912022 | Identique |
| activitychooser.js | 90029a00fd5d1d14bb63e9097f6e8b10fba2823a | 90029a00fd5d1d14bb63e9097f6e8b10fba2823a | Identique |
| activitychooserbutton.php | 6cc02422fa543f478fa1c5fa257f71cb3d8ba2b0 | 6cc02422fa543f478fa1c5fa257f71cb3d8ba2b0 | Identique |
| activitychooserbutton.mustache | 07c65be9d84289711e0c7484cf80f501c897c5f2 | 07c65be9d84289711e0c7484cf80f501c897c5f2 | Identique |

**Conclusion : aucun écart propre à Iomad n'a été observé dans ces quatre fichiers en comparant les deux versions 5.2.** Les différences mentionnées dans l'analyse précédente provenaient de la comparaison avec Moodle 5.3 ; elles relèvent du décalage de génération et non d'une adaptation Iomad.

### 49.4 Conséquences pour Project N

1. Ne pas remplacer le sélecteur natif par un composant Project N : le sélecteur gère les plugins, les dépôts d'activités, les actions complémentaires et les événements clavier.
2. Utiliser le hook before_activitychooserbutton_exported lorsqu'une action additionnelle est nécessaire et lorsque sa disponibilité est confirmée sur la version concernée.
3. Ne pas dépendre du seul numéro de section : préserver les identifiants de section et les attributs attendus par le module.
4. Ne pas intercepter ou neutraliser les événements activate, keyboardActivate et core_course/state_changed.
5. Préserver les boutons natifs, leurs noms accessibles, leur état développé/replié et leur activation clavier.
6. Tester l'ajout d'activité en mode édition, l'ajout avant une activité existante, l'ajout en fin de section, les formats de cours différents et les extensions qui ajoutent des actions au sélecteur.

### 49.5 Sources comparées

- [Moodle 4.5 — courseeditor.js](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/course/format/amd/src/courseeditor.js)
- [Moodle 5.3 — courseeditor.js](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/course/format/amd/src/courseeditor.js)
- [Iomad 5.2 — courseeditor.js](https://github.com/ericfalcon/iomad/blob/IOMAD_502_STABLE/public/course/format/amd/src/courseeditor.js)
- [Moodle 5.3 — activitychooser.js](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/course/format/amd/src/activitychooser.js)
- [Iomad 5.2 — activitychooser.js](https://github.com/ericfalcon/iomad/blob/IOMAD_502_STABLE/public/course/format/amd/src/activitychooser.js)
- [Moodle 5.3 — activitychooserbutton.php](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/course/format/classes/output/local/content/activitychooserbutton.php)
- [Iomad 5.2 — activitychooserbutton.php](https://github.com/ericfalcon/iomad/blob/IOMAD_502_STABLE/public/course/format/classes/output/local/content/activitychooserbutton.php)

Analyse statique des fichiers cités uniquement. Les interactions du sélecteur, la gestion réelle du focus, l'accessibilité du dialogue et les extensions tierces restent à tester en environnement Moodle/Iomad.

## 50. Composants génériques d'interface — notifications, modales, menus et tiroirs

Cette tranche compare des composants du cœur utilisés transversalement par les trois espaces Project N. Les SHA complets des fichiers ci-dessous sont identiques entre MOODLE_502_STABLE et IOMAD_502_STABLE.

### 50.1 Templates Mustache

| Composant | Fichier | SHA commun |
|---|---|---|
| Notifications | public/lib/templates/notification.mustache | d61040498230b6a8214fc936f356c3ff4773bbea |
| Modale | public/lib/templates/modal.mustache | e457fbfc9efb27ac5976f5ed4745efd7342e7233 |
| Menu d'actions | public/lib/templates/action_menu.mustache | 34998b9291cf13c0288b8d171cd54203eaf4d2c2 |
| Tiroir générique | public/lib/templates/drawer.mustache | 2c2d9f3a3958bf38f8cef98fea792e6e0e5c051b |

### 50.2 Modules JavaScript AMD

| Composant | Fichier | SHA commun |
|---|---|---|
| Gestion des modales | public/lib/amd/src/modal.js | 661f9ae640dbfc775cdbae958671c92baa667bf3 |
| Gestion des tiroirs | public/lib/amd/src/drawer.js | 2f9a3e4c4a16e0ef16d8b7262501c67fe7b9b1a0 |
| Notifications | public/lib/amd/src/notification.js | 9b86298c4c75d8aa7009e0b2308b7eb1ee3efd9f |

Pour ces composants génériques, aucune divergence Iomad n'est observée dans les sept fichiers comparés. La conclusion est strictement limitée à ces fichiers ; elle ne couvre pas les composants spécifiques de navigation, les menus propres à Iomad, les scripts tiers ni tous les usages de Bootstrap.

### 50.3 Contrats à préserver

- **Notifications :** conserver la distinction succès/information/avertissement/erreur, les messages nettoyés, la possibilité de fermeture lorsqu'elle est autorisée et le mécanisme d'annonce aux lecteurs d'écran. Ne pas supprimer les annonces pour gagner de la place.
- **Modales :** préserver le rôle dialog, aria-modal, le lien aria-labelledby vers le titre, les régions d'en-tête/corps/pied, le bouton de fermeture nommé et la gestion du focus assurée par le composant JavaScript. Tester ouverture, fermeture, Échap lorsque prévu, piège/restauration du focus et contenu long sur smartphone.
- **Menus d'actions :** préserver la distinction entre actions prioritaires et menu secondaire, les attributs générés et les interactions natives. Ne pas remplacer les liens d'action par des contrôles visuellement équivalents mais sémantiquement incorrects.
- **Tiroirs :** préserver l'identifiant unique, le rôle et le nom accessibles fournis par le contexte, l'état masqué, le tabindex et les attributs/slots transmis par le composant. Tester l'ouverture, la fermeture, le focus et l'absence de contenu masqué encore atteignable au clavier.

### 50.4 Conséquences pour Project N

Project N doit d'abord personnaliser les variables, classes et styles autour de ces composants plutôt que les réécrire. Toute surcharge Mustache doit conserver les contrats attendus par le JavaScript. Les comportements de modalité, de focus et d'annonce ne doivent pas être reconstruits dans le thème si le cœur les fournit déjà. Les parcours doivent être testés dans Moodle et Iomad, même lorsque les fichiers de base sont identiques, car les contextes d'utilisation, les extensions et les contenus affichés peuvent différer.

Aucune validation runtime, axe-core, clavier, NVDA, TalkBack ou smartphone n'a été réalisée sur cette tranche.

## 51. Architecture fonctionnelle Iomad — plugins métier et blocs

### 51.1 Principe d'architecture confirmé

L'analyse du fork ericfalcon/iomad, branche IOMAD_502_STABLE, confirme que les fonctionnalités métier Iomad ne résident pas uniquement dans des modifications du cœur Moodle. Elles sont largement distribuées dans une suite de plugins dédiés, avec un plugin local central et des dépendances entre plugins.

Le README de public/local/iomad *(non relu dans cette analyse)* décrit explicitement local_iomad comme le fournisseur principal de classes nécessaires au fonctionnement des autres plugins Iomad. Il indique également que la suite requiert des adaptations du cœur Moodle pour activer le multi-tenant. Il faut donc distinguer :
- les adaptations ponctuelles du cœur et les API/contextes qu'elles rendent disponibles ;
- local_iomad, socle technique et métier commun ;
- les blocs Iomad, qui fournissent des interfaces et points d'entrée fonctionnels ;
- les plugins locaux, notamment les rapports et pages spécialisées ;
- les éventuelles intégrations de thèmes et autres types de plugins.

Les README des plugins précisent que les composants de la suite sont interdépendants et doivent être installés ensemble. Project N ne doit donc ni traiter les blocs comme de simples gadgets facultatifs ni tenter de reproduire leur logique métier dans le thème.

### 51.2 Inventaire des blocs Iomad identifiés

Le répertoire public/blocks de IOMAD_502_STABLE contient les douze blocs spécifiques suivants :

| Bloc | Fonction principale observée | Enjeu UX pour Project N |
|---|---|---|
| block_iomad_company_admin | Tableau de bord principal de gestion de l'entreprise : utilisateurs, départements, cours, licences, gestionnaires et paramètres | Espace gestionnaire Iomad, navigation métier et accès aux opérations |
| block_iomad_company_selector | Sélection de l'entreprise selon les appartenances et capacités de l'utilisateur | Contexte d'entreprise explicite, changement de contexte sécurisé |
| block_iomad_mycourses | Réinterprétation de l'aperçu des cours Moodle, accès par licence, cours disponibles/en cours/terminés/obligatoires et certificats | Espace apprenant, cartes/listes, filtres et états de formation |
| block_iomad_learningpath | Parcours d'apprentissage prescrits reliant plusieurs cours | Progression et séquençage pédagogique |
| block_iomad_reports | Point d'entrée vers les plugins de rapports Iomad | Accès aux tableaux de bord et rapports selon droits |
| block_iomad_commerce | Boutique et vente de cours, tarification par entreprise, licences ou inscription manuelle, passerelles de paiement Moodle | Catalogue et parcours d'achat, uniquement si commerce activé |
| block_iomad_approve_access | Validation/refus par les gestionnaires des demandes d'accès aux activités de formation événementielle | Workflow de validation, notifications et statut de demande |
| block_iomad_microlearning | Diffusion progressive de séquences de micro-apprentissage planifiées | Vue apprenant, calendrier de diffusion et accès conditionnel |
| block_iomad_html | Variante multi-tenant du bloc HTML Moodle | Contenu éditorial contextualisé par entreprise |
| block_iomad_onlineusers | Variante multi-tenant du bloc utilisateurs en ligne Moodle | Présence et visibilité strictement limitée au périmètre autorisé |
| block_iomad_link | Lien d'accès au tableau de bord Iomad | Point d'entrée de navigation |
| block_iomad_welcome | Informations de bienvenue à l'installation | Contenu d'accueil, probablement secondaire après configuration |

Cet inventaire recense les blocs fournis dans la suite de la branche inspectée. Les README Iomad indiquent que les plugins de la suite sont interdépendants et doivent être installés ensemble ; en revanche, cela ne signifie pas que chaque bloc est activé, placé sur une page, visible pour chaque rôle ou pertinent dans toutes les configurations.

### 51.3 Le bloc d'administration comme agrégateur de navigation

Un point architectural important ressort de block_iomad_company_admin :
- le bloc constitue le tableau de bord principal des opérations de gestion d'entreprise ;
- il récupère dynamiquement les fonctions de menu déclarées par les plugins au moyen de get_plugins_with_function() sur le fichier db/iomadmenu.php ;
- il assemble les entrées issues des plugins, puis les organise par catégories et capacités ;
- le plugin local_iomad déclare lui-même des entrées dans public/local/iomad/db/iomadmenu.php ;
- le bloc d'administration déclare ses propres entrées dans public/blocks/iomad_company_admin/db/iomadmenu.php.

Ce mécanisme signifie que le menu de gestion Iomad est extensible par les plugins. Project N doit préserver ce mécanisme d'extension et ne pas coder en dur une liste exhaustive de pages Iomad. La navigation Project N peut réorganiser visuellement les destinations, mais doit consommer les entrées/capacités disponibles et maintenir les liens fournis par les plugins.

Les catégories, icônes, libellés, URLs et capacités déclarés dans ces menus doivent être traités comme des données d'extension, pas comme un contrat figé sur les noms actuels des plugins.

### 51.4 Les rapports sont des plugins locaux, pas des pages du bloc

Le bloc block_iomad_reports agit comme un répertoire d'accès. Son code parcourt les répertoires local/report_* et n'affiche un rapport que si l'utilisateur possède la capacité local/<rapport>:view. Le rapport lui-même est donc porté par un plugin local autonome.

Le fork inspecté contient notamment :
- local/report_attendance ;
- local/report_companies ;
- local/report_completion ;
- local/report_completion_monthly ;
- local/report_completion_overview ;
- local/report_emails ;
- local/report_license_usage ;
- local/report_user_license_allocations ;
- local/report_user_logins ;
- local/report_users.

Conséquences pour Project N :
1. Ne pas intégrer les rapports dans le thème ni supposer que le bloc constitue leur logique métier.
2. Préserver les URLs et capacités de chaque plugin local.
3. Prévoir une présentation unifiée des points d'entrée, sans fusionner les modèles de données ni les autorisations.
4. Ne pas présumer que tous les rapports s'appliquent à Moodle standard : certains dépendent de concepts Iomad tels que l'entreprise, les licences ou l'allocation de licences.
5. Pour les rapports Moodle-only, distinguer les rapports transposables des rapports intrinsèquement multi-entreprises.

### 51.5 Conséquences architecturales pour Project N

- **Ne pas remplacer les blocs Iomad par des pages maison.** Le thème doit améliorer leur enveloppe visuelle, leur intégration et leur lisibilité, tout en conservant leur logique, leurs contrôles et leurs permissions.
- **Ne pas supposer que chaque fonction Iomad est un bloc visible.** Certains blocs servent de tableau de bord ou de point d'entrée, tandis que les pages opérationnelles et rapports sont des pages de plugins.
- **Respecter l'extensibilité.** Les entrées de menu dynamiques déclarées via db/iomadmenu.php doivent rester découvrables et accessibles.
- **Respecter le contexte d'entreprise.** Un changement d'entreprise active un contexte de travail qui doit être visible ; les données, listes, rapports, liens et actions doivent rester limités à l'entreprise autorisée.
- **Ne pas lire les tables Iomad depuis le thème.** Les blocs eux-mêmes peuvent utiliser les APIs/classes Iomad et, dans certains cas, leurs propres requêtes métier ; Project N doit s'appuyer sur les APIs publiques et capacités, pas recopier leurs requêtes.
- **Gérer les blocs non affichés.** Le thème doit rester fonctionnel lorsque certains blocs ne sont pas placés sur une page ou ne sont pas visibles pour un rôle donné. Ne pas confondre cette absence d'affichage avec une installation partielle de la suite, que la documentation Iomad ne présente pas comme le mode normal de déploiement.
- **Conserver l'administration Moodle native.** Le tableau de bord Iomad est une surface de gestion métier, mais ne remplace pas l'administration système Moodle.

### 51.6 Points à analyser dans la prochaine tranche

1. Étudier les pages et formulaires du bloc iomad_company_admin par familles fonctionnelles : entreprises, départements, utilisateurs, gestionnaires, cours, licences et paramètres.
2. Examiner les classes output, templates et modules AMD de iomad_mycourses et learningpath pour déterminer les contrats DOM et les possibilités de présentation mobile.
3. Cartographier les plugins locaux de rapports et leurs capacités, en identifiant ceux qui reposent sur les licences ou les entreprises.
4. Analyser le sélecteur d'entreprise : persistance du contexte, utilisateurs multi-appartenances, navigation après changement et contrôles d'accès.
5. Vérifier comment les blocs sont placés sur les pages My, tableaux de bord personnalisés et pages Iomad custompage.

Cette section est une cartographie statique de la branche IOMAD_502_STABLE et de ses README/code. Aucun test d'installation, de navigation, de changement d'entreprise, de cloisonnement inter-entreprises, de rendu mobile ou d'accessibilité n'a été exécuté.

## 52. Rendu apprenant Iomad — blocs Mes cours et Parcours d'apprentissage

### 52.1 Bloc Mes cours : logique fonctionnelle

Le bloc block_iomad_mycourses n'est pas un simple habillage du bloc core_myoverview. Son README le décrit comme une réécriture de l'aperçu des cours Moodle, qui ajoute notamment les cours accessibles par licence et le téléchargement des certificats enregistrés.

La classe output main.php prépare quatre vues fonctionnelles :
- cours disponibles ;
- cours en cours ;
- cours terminés/archivés ;
- cours obligatoires, lorsque l'option est activée pour l'entreprise.

Elle prend également en charge les préférences utilisateur de tri, ordre, affichage liste/cartes et onglet mémorisé. Le rendu prévoit un téléchargement des certificats lorsque la capacité correspondante est accordée et que des certificats existent pour l'utilisateur dans le contexte de son entreprise.

Le template principal main.mustache expose des commandes de tri, d'ordre, de présentation liste/cartes, un filtre cours obligatoires et un accès au téléchargement des certificats. Les onglets sont rendus comme une navigation Bootstrap avec rôle tab. Les vues carte/liste sont séparées dans des templates dédiés ; coursecard.mustache affiche notamment le caractère obligatoire et délègue la progression au template progress-bar.mustache.

### 52.2 Contrats et points de vigilance accessibilité

Les éléments à préserver ou à vérifier dans le rendu actuel :
- le conteneur de bloc identifié par data-region="iomad_mycourses" ;
- les attributs data-action/data-name/data-value consommés par les interactions de tri et de préférence ;
- les identifiants uniques de conteneurs et les relations entre onglets et panneaux ;
- l'état sélectionné des onglets et la synchronisation aria-selected / classe active ;
- les états de cours obligatoire, de cours indisponible, de cours accessible par licence et de cours terminé ;
- le texte numérique de progression en complément de la barre graphique.

Points d'audit à vérifier avant toute surcharge :
1. Les onglets doivent respecter le modèle clavier ARIA tabs (tablist, tab, tabpanel, sélection, flèches, Home/End selon le modèle retenu). Vérifier que les panneaux sont correctement nommés et associés aux onglets.
2. Le template progress-bar.mustache fournit role=progressbar et les valeurs min/max/now, mais l'association de la barre à un nom accessible explicite doit être vérifiée avec l'arbre d'accessibilité ; le texte visuel du pourcentage ne doit pas être le seul moyen d'identifier le cours concerné.
3. Les commandes de tri et de présentation sont des liens déclenchant des actions via data-action. Vérifier qu'elles sont correctement annoncées et utilisables au clavier ; un bouton peut être plus approprié si l'action ne constitue pas une navigation.
4. Les styles inline et la disposition en ligne des commandes peuvent provoquer des débordements sur smartphone. Le thème doit permettre le retour à la ligne et préserver des cibles tactiles suffisamment grandes.
5. Vérifier le contraste et la visibilité des états actif, obligatoire, indisponible et terminé, sans dépendre uniquement de la couleur.

Le template mobile_view_block.mustache est destiné à l'application mobile Moodle (composants Ionic et filtres Angular), et non au rendu navigateur du thème. Il ne faut pas le prendre pour une variante responsive web ni le modifier pour résoudre un problème d'affichage dans Project N.

### 52.3 Bloc Parcours d'apprentissage

Le bloc block_iomad_learningpath propose des parcours prescrits et séquencés. Sa classe de bloc récupère le contexte entreprise via local_iomad, construit les parcours visibles pour l'utilisateur et ne rend pas le bloc lorsqu'il n'y a aucun parcours à présenter. Les pages de gestion et d'administration sont également portées par le plugin : gestion des parcours, édition, groupes, cours et consultation des apprenants.

Le template principal présente chaque parcours sous forme de carte avec image, nom, description, progression et bouton d'ouverture. Il rend ensuite les groupes et cours du parcours. Les templates coursecard et groupcard portent les cours, leur disponibilité, leurs prérequis et leur progression.

Points d'attention :
- l'image du parcours et les images de cours doivent avoir un texte alternatif pertinent si elles transmettent une information ; sinon elles doivent être explicitement décoratives ;
- la progression doit être compréhensible sans couleur ni seule largeur graphique ; le pourcentage doit rester textuellement disponible ;
- les cours non disponibles et les prérequis doivent être explicitement annoncés, sans dépendre seulement d'une couleur ou d'un style atténué ;
- les boutons d'ouverture et de retour doivent conserver un nom compréhensible, un ordre de focus cohérent et un retour de focus après changement de vue ;
- la bascule entre la liste des parcours et le détail d'un parcours doit rester utilisable au clavier et avec TalkBack/NVDA ;
- les grilles Bootstrap en colonnes doivent se réorganiser en une seule colonne sur smartphone sans perte d'information.

### 52.4 Décisions de conception pour Project N

1. Le thème peut améliorer l'enveloppe visuelle et responsive de ces blocs, mais ne doit pas recalculer les cours disponibles, les licences, les obligations, les prérequis ou les progressions.
2. Le bloc Mes cours Iomad et le bloc core_myoverview Moodle doivent être traités comme deux sources de données différentes, même si Project N cherche à leur donner une présentation cohérente.
3. La cohérence visuelle ne doit pas masquer les différences de sémantique : disponibilité par licence, cours obligatoire, archive, certificat et parcours prescrit sont des états métier distincts.
4. Les préférences de vue/tri doivent rester persistantes si le plugin les gère ; ne pas créer un second stockage concurrent dans le thème.
5. Les blocs peuvent être affichés dans des pages My ou des pages personnalisées Iomad ; le thème doit fonctionner avec les deux placements et ne pas supposer une position unique.

### 52.5 État de vérification

Analyse statique de templates Mustache et classes PHP dans IOMAD_502_STABLE. Les points d'accessibilité ci-dessus sont des risques et contrôles à effectuer, pas des non-conformités axe-core confirmées. Aucun parcours n'a été exécuté ; aucune validation axe-core, clavier, NVDA, TalkBack ou test de reflow mobile n'a été réalisée.

---

## Annexe A — Version précédente des sections 44.6 et 46 (comparaison inter-génération)

> Texte conservé tel quel depuis la version `2dae1d3`. Les comparaisons Moodle 5.3 / Iomad 5.2 et Moodle 4.5 / Iomad 4.5 ci-dessous sont des écarts de génération, non des divergences propres à Iomad. La comparaison de référence est celle de la section 44.6 et de la section 46 révisées.

### 44.6 Écarts Moodle / Iomad et portée des conclusions

Les fichiers ciblés montrent des réutilisations importantes entre Moodle et Iomad :
- la classe de statut de remise et la classe de page du test sont identiques par blob entre Moodle 4.5/5.3 et Iomad 5.2 ;
- plusieurs templates de test et de remise sont identiques ;
- le template de l'application de correction Iomad 5.2 correspond au template Moodle 5.3 ;
- le module AMD de correction Iomad 5.2 diffère de celui de Moodle 5.3.

Ces constats ne permettent pas d'attribuer chaque différence à la logique multi-entreprise. Il faut comparer les diffs complets et surtout les appels aux APIs/capacités Iomad avant toute conclusion.

## 46. Comparaison ciblée des forums — écarts de version et d'Iomad

Cette section compare les mêmes fichiers dans les quatre branches du périmètre : Moodle 4.5, Moodle 5.3, Iomad 4.5 et Iomad 5.2.

### 46.1 Template d'un message de forum

Le template `forum_discussion_post.mustache` conserve dans les quatre branches les repères structurants suivants :
- `data-region="post"` et `data-post-id` ;
- un identifiant de cible et les régions de contenu ;
- `tabindex="0"` sur le conteneur de message ;
- `aria-labelledby` reliant le message à son en-tête ;
- `aria-describedby` reliant le message à son contenu ;
- un en-tête contenant les informations de l'auteur.

Les différences sont principalement de présentation et de structure CSS :
- Moodle 5.3 adopte des marges et espacements différents, des bordures arrondies et une structure d'en-tête simplifiée ;
- Moodle 4.5 et Iomad 4.5 partagent exactement le même blob ;
- Iomad 5.2 reste proche de Moodle 4.5 sur ce template, avec quelques écarts mineurs, et ne reprend pas les changements de présentation de Moodle 5.3.

Cela montre que la version Iomad 5.2 du fork ne suit pas nécessairement toutes les évolutions de présentation de Moodle 5.3, même si les deux branches partagent certaines bases.

### 46.2 Réponses imbriquées et titres accessibles

Le template `forum_discussion_nested_v2_post_reply.mustache` est identique entre Moodle 4.5 et Iomad 4.5, et identique entre Moodle 5.3 et Iomad 5.2. Il conserve :
- les actions de réponse conditionnées par `capabilities.reply` ;
- les données nécessaires à la réponse privée ;
- les attributs `data-action` et `data-post-id` utilisés par le JavaScript ;
- le compteur de réponses et le contrôle d'affichage/masquage des réponses.

Une différence utile est le passage des classes utilitaires d'accessibilité `sr-only` et `font-weight-bold` à `visually-hidden` et `fw-bold` entre Bootstrap 4 et Bootstrap 5. Le thème doit donc éviter de réintroduire les anciennes classes dans les templates ou CSS personnalisés, et s'aligner sur la version Bootstrap de chaque branche.

### 46.3 Liste de discussions et abonnements

Le module `discussion_list.js` a le même blob dans Moodle 4.5, Moodle 5.3, Iomad 4.5 et Iomad 5.2. Ce fichier n'a donc pas évolué entre ces branches : la gestion des abonnements dans les listes de discussions n'est pas un écart établi par cette comparaison, et ne doit pas être présentée comme une nouveauté de Moodle 5.3.

Moodle 5.3 introduit également `forum_overview_toggle.js`, qui gère les bascules d'abonnement/suivi dans la vue d'ensemble, avec une attention explicite à la cohérence entre libellé visible et `aria-label`. Le fichier Iomad 5.2 existe mais présente un contenu différent et plus ancien ; il ne faut donc pas supposer que cette fonctionnalité de vue d'ensemble est portée de la même façon dans cette branche.

### 46.4 Conséquences techniques pour Project N

1. **Ne pas copier un template Moodle 5.3 sans vérifier les dépendances de la branche Iomad ciblée.** Les changements Bootstrap et les classes d'accessibilité doivent être conditionnés par la version réelle.
2. **Ne pas remplacer les attributs de liaison DOM** (`data-region`, `data-action`, `data-post-id`, `data-target`) sans analyse du module AMD associé.
3. **Préserver la relation accessible message/en-tête/contenu.** Les attributs ARIA sont structurels et ne doivent pas être supprimés au nom d'une simplification visuelle.
4. **Vérifier les annonces d'état des bascules.** Le libellé visible et le nom accessible doivent décrire le même état, en particulier pour les abonnements.
5. **Ne pas traiter Iomad 5.2 comme un simple alias de Moodle 5.3.** La comparaison doit rester explicite pour les templates, modules AMD, APIs et comportements multi-entreprises.

### 46.5 Fichiers comparés

- `mod/forum/templates/forum_discussion_post.mustache` : quatre branches ;
- `mod/forum/templates/forum_discussion_nested_v2_post_reply.mustache` : quatre branches ;
- `mod/forum/amd/src/discussion_list.js` : quatre branches ;
- `mod/forum/amd/src/forum_overview_toggle.js` : Moodle 5.3 et Iomad 5.2.

Les constats ci-dessus proviennent des blobs et extraits comparés dans les forks personnels. Ils ne constituent pas un audit complet des différences de toutes les classes, templates et dépendances du forum. Aucune exécution de ces interfaces ni validation d'accessibilité n'a été effectuée.

### Compléments de la version précédente (§44.2 et §44.4)

- Iomad 5.2 reprend le template Moodle 5.3 à l'identique (même blob), mais son module AMD de correction possède un blob différent.
Elle possède le même blob dans Moodle 4.5, Moodle 5.3 et Iomad 5.2. Les templates de liste des tentatives, de résumé d'une tentative, de confirmation de remise et de minuterie sont également identiques entre les deux branches Moodle 4.5/5.3 consultées pour les fichiers ciblés, et Iomad 5.2 reprend les templates 5.3 correspondants.


---

## 53. Architecture fonctionnelle de `local_iomad` — portée pour Project N

> Complément d'analyse statique. Cette section ne remplace pas les sections précédentes sur les menus d'administration, les rôles/capacités ou la comparaison des versions. Elle rassemble les implications fonctionnelles déjà relevées dans les sources Iomad 5.2 et signale les points qui doivent encore être confrontés aux branches antérieures.

### 53.1 Responsabilités de `local_iomad`

Dans Iomad 5.1/5.2, le composant `local_iomad` porte notamment les classes namespacées de gestion des entreprises et des utilisateurs, le contexte personnalisé entreprise et les règles d'autorisation propres au périmètre Iomad. Cette organisation correspond à une évolution structurelle par rapport aux versions 4.5/5.0, où plusieurs classes et chemins historiques se trouvent sous `local/iomad/lib/`.

Project N ne doit pas traiter `local_iomad` comme une simple bibliothèque facultative de thème : ses API peuvent participer à la détermination du contexte et du périmètre d'entreprise. La présence du plugin, la présence d'une entreprise courante, l'appartenance d'un utilisateur et ses droits effectifs sont des faits distincts.

### 53.2 Adaptateur de compatibilité

| Génération Iomad | Organisation observée | Conséquence pour l'adaptateur Project N |
|---|---|---|
| 4.5 / 5.0 | Classes historiques `iomad` et `company` dans `local/iomad/lib/`; noms de tables historiques. | Encapsuler les appels historiques sans les propager dans les renderers et templates. |
| 5.1 / 5.2 | Classes `local_iomad\\iomad`, `local_iomad\\company`, `local_iomad\\company_user`; contexte `local_iomad\\custom_context\\context_company`; tables renommées avec préfixe logique `local_iomad_`. | Encapsuler les nouveaux namespaces et le contexte personnalisé ; ne pas déduire la version depuis le nom d'une table. |
| Moodle standard | Pas de classes ni de contexte métier Iomad. | Le chemin d'exécution standard ne doit charger aucune classe Iomad ni exiger de configuration tenant. |

L'adaptateur doit exposer uniquement les besoins d'interface nécessaires, par exemple : disponibilité des fonctions Iomad, informations de contexte destinées à l'affichage, destinations autorisées à proposer et informations de marque. Il ne doit ni recopier l'arbre de navigation ni devenir une couche d'accès aux données métier complète.

### 53.3 Autorisation et périmètre entreprise

Les contrôles Iomad complètent les capacités Moodle et peuvent appliquer des restrictions spécifiques au couple entreprise/rôle/capacité. Le thème ne doit donc pas reconstruire le calcul des droits à partir des noms de rôles, des archétypes ou de la présence d'un menu.

Règles de conception :
1. Les liens visibles sont des aides à la navigation, jamais des protections.
2. Les pages et actions restent responsables de leurs contrôles serveur Moodle/Iomad.
3. Les destinations tenant sont construites à partir d'API adaptées à la version et au contexte ; les données ne sont pas chargées par des requêtes SQL improvisées dans les renderers.
4. Le test doit inclure un administrateur du site, des gestionnaires Iomad, un utilisateur pédagogique, un utilisateur multi-rôles et un utilisateur rattaché à plusieurs entreprises.
5. Vérifier l'accès direct aux URL et les changements d'entreprise, pas seulement l'affichage des menus.

### 53.4 Menus Iomad dynamiques

Le tableau de bord `block_iomad_company_admin` agrège des entrées déclarées par les plugins Iomad, notamment via leurs fichiers `db/iomadmenu.php`. Les entrées et catégories disponibles dépendent des capacités et de la configuration. Project N doit préserver cette découverte dynamique et éviter une liste statique de destinations.

Le thème peut harmoniser l'enveloppe, les regroupements visuels et l'affichage responsive, mais les URL, les données d'action et les capacités doivent rester issus des composants Iomad. Un éventuel tableau de bord Project N doit être un point d'entrée complémentaire, pas une réimplémentation de l'administration d'entreprise.

### 53.5 `iomad_mycourses` et espace apprenant

Le bloc Iomad « Mes cours » possède un modèle métier distinct de `core_myoverview`. Les cours disponibles, en cours, terminés ou obligatoires, la progression, les certificats et les accès conditionnés par les licences ne sont pas de simples variantes cosmétiques d'une même liste.

Project N peut réutiliser un langage visuel commun entre Moodle et Iomad, mais doit conserver les états métier et les données fournis par le composant source. Les tests doivent vérifier les transitions entre onglets, les listes vides, les cours indisponibles, les cours terminés, les certificats et les droits de lancement.

### 53.6 Limites de preuve et vérifications à terminer

| Point | Statut |
|---|---|
| Organisation historique et namespacée des classes Iomad | Observée statiquement sur les branches consultées ; tous les contrats ne sont pas comparés exhaustivement. |
| Règles d'autorisation et restrictions tenant | Décrites par lecture des sources ; non validées par tests d'accès runtime. |
| Menus d'administration dynamiques | Structure et mécanisme identifiés ; non testés avec un ensemble représentatif de plugins installés. |
| Isolation multi-entreprises | Exigence architecturale documentée ; aucune preuve runtime de cloisonnement à ce stade. |
| Compatibilité 4.5/5.0/5.1/5.2 | Analyse par génération en cours ; ne pas extrapoler à Iomad 5.3, qui n'est pas une cible vérifiée. |
| Accessibilité | Aucun résultat axe-core ou test manuel ne peut être déduit de cette cartographie statique. |

**Conclusion :** l'intégration Iomad de Project N doit rester un adaptateur mince au service de la présentation, en s'appuyant sur les API et contrôles natifs. Le développement reste bloqué jusqu'à la clôture de la cartographie P0 des espaces apprenant, formateur, administration Moodle et administration Iomad.


## 54. Espace apprenant Moodle natif — tableau de bord, cours et suivi

**Périmètre de cette tranche :** Moodle `MOODLE_503_STABLE`, analyse statique des contrôleurs, blocs, classes output et templates. Cette section reprend les constats apprenant déjà présents aux sections 37, 40 à 43, 44.3, 45 et 52, les recentre sur Moodle standard et ajoute les mécanismes du tableau de bord et de l'aperçu des cours. Elle ne constitue pas encore une cartographie exhaustive de toutes les activités.

### 54.1 Distinguer les pages personnelles Moodle

Moodle expose plusieurs destinations qu'il ne faut pas confondre :

| Destination | Entrée | Fonction |
|---|---|---|
| Tableau de bord personnel | `/my/index.php` | Page personnelle configurable, constituée de blocs ; l'utilisateur peut organiser les blocs selon ses droits et les réglages du site |
| Vue « Mes cours » | `/my/courses.php` | Page dédiée à l'aperçu des cours, avec le bloc `block_myoverview` fixé dans la zone centrale ; la page verrouille les blocs |
| Page d'accueil du site | `/` | Page du site, dont l'accès et le rôle dépendent du paramétrage Moodle |
| Page de cours | `/course/view.php?id=...` | Accès au contenu pédagogique, aux sections et aux activités du cours |

Le tableau de bord et « Mes cours » sont donc deux pages différentes, avec des layouts et des règles de personnalisation distincts. La page `/my/index.php` peut être désactivée ou redirigée selon la configuration de la page d'accueil ; `/my/courses.php` peut également être désactivée. Project N doit respecter ces réglages et les redirections natives, plutôt que supposer que chaque utilisateur dispose toujours des deux pages.

### 54.2 Tableau de bord personnel : architecture par blocs

Le contrôleur `public/my/index.php` :
- exige une authentification, sauf traitement spécifique de l'invité ;
- détermine le contexte utilisateur et les droits de gestion des blocs ;
- récupère la page personnelle ou copie la page système par défaut ;
- utilise le layout `mydashboard` et la région de blocs `content` ;
- gère l'état d'édition et la réinitialisation de la page personnelle.

Le tableau de bord n'est donc pas une page monolithique dont le thème pourrait fixer seul les contenus. Il s'appuie sur le système de pages et de blocs Moodle. La personnalisation doit préserver les régions, les possibilités de déplacement et les droits d'édition des blocs, ainsi que la distinction entre page système par défaut et page privée de l'utilisateur.

### 54.3 « Mes cours » : bloc `block_myoverview`

La page `public/my/courses.php` utilise le layout `mycourses`, ajoute la classe de page `page-mycourses`, verrouille les blocs et rend la région centrale. Le contenu principal est produit par `block_myoverview`, qui délègue à une classe output puis à des templates Mustache.

La classe `block_myoverview\\output\\main` prend en charge des préférences utilisateur distinctes :
- regroupement des cours (tous, en cours, à venir, passés, favoris, masqués, selon les options activées et les champs personnalisés) ;
- tri ;
- mode d'affichage ;
- pagination ;
- valeur de regroupement par champ personnalisé.

Le bloc prévoit notamment :
- une vue en cartes ;
- une vue en liste ;
- des sélecteurs de regroupement, recherche, tri et présentation ;
- un menu d'actions sur les cours ;
- une barre de progression lorsque la donnée est disponible ;
- un état vide lorsque l'utilisateur n'a aucun cours à afficher.

Les préférences sont stockées côté utilisateur. Le thème doit les respecter et ne pas créer un deuxième système de filtres, de favoris ou de mémorisation de présentation concurrent. Les données de progression sont préparées par le bloc : le thème ne doit pas les recalculer à partir des activités visibles.

### 54.4 Accès récents et échéances

Deux autres blocs peuvent compléter le parcours apprenant :
- `block_recentlyaccessedcourses` : accès rapide aux cours récemment consultés ;
- `block_timeline` : échéances et activités à venir ou en retard.

Dans Moodle 5.3, le bloc Timeline monte directement un composant React `@moodle/lms/block_timeline/Timeline`. Il transmet notamment des préférences de filtre (tout, en retard, 7 jours, 30 jours, 3 mois, 6 mois), de tri (date ou cours) et de limite d'éléments. Le thème ne doit pas supposer que ce bloc repose sur un template Mustache classique ni remplacer son composant React par une liste construite indépendamment.

Ces blocs restent des composants distincts : l'accès récent répond à « où reprendre ? », la timeline à « que dois-je faire et pour quand ? », et l'aperçu des cours à « quels cours suivre ? ». Leur présentation peut être harmonisée, mais leur fonction et leurs données ne doivent pas être fusionnées.

### 54.5 Page de cours : source de vérité pédagogique

Le contrôleur `public/course/view.php` confirme plusieurs principes :
- l'accès au cours est contrôlé par `require_login($course)` et les capacités associées au contexte du cours ;
- la visibilité d'une section et son accessibilité sont évaluées à partir des informations de cours et des restrictions ;
- le format de cours est chargé dynamiquement avec `course_get_format()` ;
- le layout est `course`, puis le type de page dépend du format ;
- les capacités d'édition, de gestion d'activités, de visibilité et de déplacement sont déclarées selon les possibilités du format.

La page de cours n'est donc pas une simple liste de sections que le thème peut reconstruire à sa convenance. Le format de cours est un plugin qui possède ses propres règles de présentation et de comportement. Le thème doit prendre en charge les formats activés sur le site, y compris les formats tiers, sans coder en dur une structure unique « sections + activités ».

Le thème peut améliorer la hiérarchie visuelle des sections, l'identification des activités, les espacements, les états et les actions principales. Il doit conserver :
- les URL et ancres des sections et activités ;
- les états visibles/masqués et les restrictions de disponibilité ;
- les capacités et commandes d'édition ;
- les contrats DOM et JavaScript des formats de cours ;
- les informations de complétion fournies par Moodle.

### 54.6 Achèvement et progression

Le cœur Moodle fournit un objet output `core_course\\output\\completion_status`, qui délègue les détails à `core_completion\\cm_completion_details` puis à `activity_completion`. Cela confirme que l'état d'achèvement d'une activité est contextualisé et préparé par le cœur, et qu'il ne doit pas être inféré de sa seule présence dans la page.

Conséquences :
1. Distinguer achèvement d'activité, achèvement de cours et progression pédagogique affichée.
2. Ne pas assimiler une activité consultée à une activité achevée.
3. Conserver les conditions automatiques, manuelles, les critères de note, de consultation, de durée ou les conditions combinées selon le paramétrage.
4. Rendre l'état autrement que par une couleur ou une icône isolée ; fournir un libellé compréhensible.
5. Ne pas présenter une progression calculée par le thème si Moodle ne fournit pas cette information.

### 54.7 Calendrier et échéances

Le bloc calendrier mensuel utilise l'API `core_calendar`, construit les informations du calendrier en fonction du contexte de page et rend un template du composant calendrier. Il active également le module AMD `core_calendar/popover`. La présentation du calendrier et de ses fenêtres contextuelles dépend donc de composants Moodle et de leur JavaScript.

Le calendrier, la Timeline et les dates affichées dans les activités peuvent représenter des informations proches, mais ne sont pas interchangeables. Project N doit éviter les calculs de dates parallèles et conserver les fuseaux horaires, les événements de cours/groupe/utilisateur et les liens vers les activités sources.

### 54.8 Décisions de conception pour Project N

1. Préserver les pages natives et leurs règles de disponibilité : tableau de bord, Mes cours, page de cours et calendrier.
2. Construire une hiérarchie de navigation cohérente entre ces destinations, sans supprimer les pages ni les préférences Moodle.
3. Traiter l'aperçu des cours, les accès récents et la Timeline comme trois fonctions complémentaires.
4. Harmoniser visuellement cartes, listes, badges d'état et progression sans modifier les règles métier ni recalculer les données.
5. Préserver les préférences utilisateur de tri, regroupement, pagination et présentation.
6. Ne pas imposer un format de cours unique ; le format actif reste maître de la structure des sections.
7. Respecter les blocs configurables du tableau de bord personnel et le caractère verrouillé de la page Mes cours.
8. Prévoir des états vides utiles et différenciés : aucun cours, aucun cours correspondant au filtre, aucune échéance, aucun accès récent.
9. Sur smartphone, hiérarchiser les informations et actions sans masquer les dates, les restrictions, les consignes ni les états de complétion.

### 54.9 Accessibilité et responsive — contrôles à mener

- Vérifier les noms accessibles des contrôles de recherche, tri, regroupement et présentation.
- Vérifier la restitution des changements de filtre et de pagination dans le bloc React Timeline.
- Vérifier la sémantique des cartes de cours, liens, menus d'action et états de progression.
- Vérifier l'accès clavier aux blocs déplaçables et aux commandes d'édition du tableau de bord.
- Vérifier la hiérarchie des titres, les landmarks et l'absence de navigation redondante.
- Vérifier les états de cours masqué, à venir, passé, favori et sans progression.
- Vérifier le reflow à 320 CSS px, le zoom 200 %, les cibles tactiles et l'absence de défilement horizontal non maîtrisé.
- Tester séparément les composants React et les interactions asynchrones.

### 54.10 Fichiers Moodle 5.3 examinés

| Fichier | SHA |
|---|---|
| `public/my/index.php` | `51063d56943703855982bb3829bde71a86ce0a90` |
| `public/my/courses.php` | `7aa2dec2e679472eba9178af08763ae806e9993f` |
| `public/blocks/myoverview/block_myoverview.php` | `a801f302cb2462292683ee62650abd54c9b8d499` |
| `public/blocks/myoverview/classes/output/main.php` | `49638baa7c560ca41ddbdcda8419e8e7a94ba778` |
| `public/blocks/myoverview/classes/output/renderer.php` | `f902390ab7493310dc8057e3c908ae8226510673` |
| `public/blocks/myoverview/templates/main.mustache` | `a0950275614da11cab883f86f21c99556654fae8` |
| `public/blocks/myoverview/templates/courses-view.mustache` | `c63f4ab63963b0b55ac00b038378b785e67a3111` |
| `public/blocks/myoverview/templates/view-cards.mustache` | `b82bb74f12b4bf5cc21b32f08541dad4ab7a73b1` |
| `public/blocks/myoverview/templates/view-list.mustache` | `4e8a021364910975a07e5c86f35718ee48bbcf69` |
| `public/blocks/myoverview/templates/progress-bar.mustache` | `c3ec947ecc874164096d7432544f383b0cead6a3` |
| `public/blocks/myoverview/templates/zero-state.mustache` | `7d221d58f4e64102364a3bac2445dbcec64ac8be` |
| `public/blocks/timeline/block_timeline.php` | `510fb44cb4eadd269d3931a076fc49a62e3a5f46` |
| `public/blocks/timeline/lib.php` | `4ff8079019000c52da5133376e3fec6011ad3cab` |
| `public/blocks/recentlyaccessedcourses/classes/output/main.php` | `0534d17670e8d138e68711a9960e2326674031a8` |
| `public/course/view.php` | `5e37e611053ba670eb0c72a8bd2b20197cf80395` |
| `public/course/format/classes/base.php` | `18f4787123a6c110fd34838235e9b81ca6f9b2be` |
| `public/course/classes/output/completion_status.php` | `fbe9117d00f0b5d3fc76e49c24c77db046404f13` |
| `public/blocks/calendar_month/block_calendar_month.php` | `96350fd51195fa08a1ddd2ec6cd6aa5e47095fe0` |

### 54.11 Limites et suite de la cartographie apprenant

Cette tranche approfondit le tableau de bord, l'aperçu des cours, la page de cours, les formats, la complétion et les blocs d'accès/échéances. Elle ne couvre pas encore de façon exhaustive :
- les pages de profil et préférences apprenant ;
- les notes et le rapport utilisateur de notes ;
- les notifications, messages et préférences de communication ;
- les pages de consultation de chaque activité et leurs états particuliers ;
- les restrictions d'accès et leur restitution complète ;
- les vues calendrier jour/semaine/mois et leurs composants ;
- les différences de code Moodle 4.5 / 5.0 / 5.1 / 5.2 / 5.3.

Les sections antérieures 40 à 46 documentent déjà plusieurs activités (devoir, test, forum, page, fichier, H5P) et doivent être considérées comme un premier inventaire, pas comme une couverture exhaustive. Les sections 37 et 43 restent les principes de parcours et de séparation entre présentation et logique métier.

**Nature des preuves :** analyse statique des fichiers Moodle 5.3 listés ci-dessus. Aucune installation, aucun parcours réel, aucun test axe-core, clavier, lecteur d'écran ou appareil mobile n'a été exécuté dans cette tranche.


## 55. Espace apprenant Moodle natif — profil, préférences, notes, messagerie et calendrier

**Périmètre de cette tranche :** Moodle `MOODLE_503_STABLE`. Analyse statique des contrôleurs principaux de profil, préférences, notes, messagerie, notifications et calendrier. Cette tranche complète la section 54 ; elle ne prétend pas couvrir toutes les pages de chaque activité ni tous les plugins de communication.

### 55.1 Profil : page publique et modification du compte sont deux fonctions distinctes

Moodle distingue au moins trois destinations :
- `/user/profile.php?id=...` : consultation d'un profil public ;
- `/user/edit.php?id=...` : modification des informations du compte, sous réserve des règles du plugin d'authentification ;
- `/user/preferences.php?userid=...` : réglages personnels regroupés par catégories.

La page de profil public s'appuie sur une page personnelle de type `MY_PAGE_PUBLIC`, avec régions de blocs et possibilité de personnalisation selon les capacités. Elle peut afficher le profil d'un autre utilisateur. La visibilité dépend de `core_user::can_view_profile()`, de la configuration de connexion obligatoire et des règles Moodle. Le contrôleur prévoit un rendu limité qui ne divulgue pas le nom de l'utilisateur lorsque le profil n'est pas consultable.

La modification du profil ne se résume pas à un formulaire générique :
- le plugin d'authentification détermine si le profil est modifiable et peut fournir une URL externe ;
- l'utilisateur peut modifier son propre profil avec `moodle/user:editownprofile` ;
- la modification d'un autre compte requiert `moodle/user:editprofile` dans le contexte utilisateur ;
- les champs personnalisés, intérêts/tags, description avec éditeur et image de profil sont préparés par les API dédiées ;
- certains comptes distants ou comptes invités suivent des traitements spécifiques.

**Conséquence Project N :** conserver les champs, aides, erreurs, avertissements, règles de disponibilité et soumissions du formulaire Moodle. Ne pas fabriquer un formulaire de profil parallèle dans le thème. La page de consultation et la page de modification doivent rester clairement différenciées dans la navigation.

### 55.2 Préférences : une page agrégée à partir de la navigation des réglages

`/user/preferences.php` n'est pas un formulaire statique. Le contrôleur construit des groupes à partir des nœuds de `settingsnav`, puis rend un objet `preferences_groups`. Les catégories visibles dépendent donc des réglages réellement exposés à l'utilisateur et des plugins présents.

La page vérifie notamment :
- que l'utilisateur cible est réel ;
- que l'utilisateur est connecté et n'est pas invité ;
- les droits permettant de consulter ou modifier les préférences d'un autre utilisateur ;
- les entrées disponibles dans la navigation des réglages.

Le thème doit préserver la structure de groupes, les liens vers chaque réglage, les droits et l'extension par les plugins. Il ne doit pas figer une liste de préférences supposée exhaustive, ni masquer les réglages ajoutés par un plugin.

### 55.3 Notes : rapport utilisateur dans le contexte d'un cours

Le rapport utilisateur des notes est accessible via `/grade/report/user/index.php?id=...`. Il s'agit d'un rapport de notes de cours, pas d'une synthèse globale de toutes les formations.

Le contrôleur :
- charge le cours et impose la connexion à ce cours ;
- exige `gradereport/user:view` dans le contexte du cours ;
- distingue la consultation de ses propres notes, la consultation de toutes les notes et la consultation du rapport d'un autre utilisateur ;
- tient compte de `moodle/grade:view`, `moodle/grade:viewall` et du paramètre de cours `showgrades` ;
- mémorise certaines préférences de présentation du rapport ;
- déclenche si nécessaire le recalcul des notes finales avant leur affichage.

**Conséquence Project N :** le thème ne doit ni exposer un lien Notes lorsque les capacités ou le paramétrage du cours l'interdisent, ni interpréter lui-même les notes. Il doit conserver les distinctions entre note brute, note pondérée, note finale, élément masqué, catégorie et total, telles que préparées par le carnet de notes. La lisibilité mobile des tableaux et des informations de calcul constitue un point de contrôle spécifique ; une transformation en simples cartes ne doit pas faire perdre les relations hiérarchiques et les totaux.

### 55.4 Messagerie : interface applicative et règles de communication Moodle

La page `/message/index.php` :
- exige une connexion et exclut les invités ;
- respecte le réglage global `$CFG->messaging` ;
- valide que l'utilisateur cible est réel ;
- vérifie l'appartenance de l'utilisateur courant à une conversation ;
- vérifie la possibilité d'envoyer un message à un utilisateur cible ;
- délègue le rendu à `core_message\\helper::render_messaging_widget()`.

La page est rendue dans le layout `mydashboard`. Il ne s'agit pas d'une page HTML traditionnelle dont le thème maîtriserait seul le contenu : le widget de messagerie porte l'essentiel de l'interface et de ses interactions.

Le thème peut agir sur l'enveloppe, les marges, les largeurs, les titres et la cohérence de navigation. Il doit éviter de reconstruire les conversations, les contacts, les états de lecture ou les interactions asynchrones. Le rendu doit être vérifié avec les vues conversation, liste, absence de conversation et messagerie désactivée.

### 55.5 Préférences de notification : ne pas les confondre avec les préférences générales

`/message/notificationpreferences.php` utilise un renderer spécifique `core/message` et la méthode `render_user_notification_preferences()`. Les règles d'accès distinguent la modification de son propre profil de messagerie et la modification du profil d'un autre utilisateur, avec des capacités dédiées.

Ces réglages concernent les canaux et modalités de notification exposés par Moodle et ses composants. Leur contenu peut dépendre des plugins de message installés et de leurs fournisseurs de notification.

**Conséquence Project N :** ne pas fusionner cette page avec les préférences générales ni coder en dur les canaux disponibles. Préserver les états activé/désactivé, les modalités par canal, les libellés accessibles et les éventuelles descriptions d'aide. Tester les cas où un fournisseur est absent, désactivé ou indisponible.

### 55.6 Calendrier : vues et contexte

Le contrôleur `/calendar/view.php` distingue notamment les vues `day`, `month` et `upcoming`. Il accepte le contexte d'un cours ou d'une catégorie, et prépare l'objet `calendar_information` à partir du temps, du cours et de la catégorie.

Le calendrier peut donc représenter :
- les événements personnels ;
- les événements de cours ;
- les événements de groupe ;
- les événements de catégorie ou du site, selon le contexte et les droits.

Le contrôleur adapte la navigation selon le contexte : depuis un calendrier de cours, la navigation active peut rester celle du cours ; depuis une catégorie, le lien actif peut renvoyer à la catégorie. Le calendrier utilise le fuseau horaire et les fonctions de date Moodle ; les paramètres temporels et la navigation entre périodes ne doivent pas être recalculés dans le thème.

La vue `upcoming` ne doit pas être assimilée à la vue mensuelle, ni le calendrier complet au bloc calendrier mensuel de la section 54. Le thème doit conserver les trois vues et leurs changements de période, les filtres/contexte disponibles, les liens vers les événements et les formulaires d'ajout ou de modification lorsque les capacités le permettent.

### 55.7 Décisions de conception Project N

1. Traiter profil public, édition du profil, préférences générales et préférences de notification comme quatre fonctions distinctes.
2. S'appuyer sur les capacités et les API Moodle ; la visibilité d'un lien ne constitue jamais une autorisation.
3. Conserver les groupes de préférences dynamiques et les réglages ajoutés par les plugins.
4. Garder le rapport de notes dans son contexte de cours et préserver sa structure de données.
5. Respecter le widget de messagerie Moodle et ses interactions ; ne pas créer une messagerie parallèle.
6. Conserver les vues jour, mois et événements à venir du calendrier, ainsi que leur contexte cours/catégorie.
7. Prévoir une navigation cohérente vers le profil, les préférences, les messages, les notifications, les notes et le calendrier sans transformer ces pages en un écran unique surchargé.

### 55.8 Contrôles d'accessibilité et d'ergonomie à réaliser

- Profil : ordre des titres, alternatives de l'image, libellés des champs personnalisés, restitution des erreurs et des champs obligatoires.
- Préférences : navigation clavier entre groupes, association des libellés et contrôles, conservation du contexte après modification.
- Notes : lecture des tableaux complexes au lecteur d'écran, identification des en-têtes, totaux, notes masquées et informations de calcul ; reflow sans perte d'association.
- Messagerie : navigation clavier dans les conversations, annonce des nouveaux messages et changements d'état, gestion du focus lors du changement de conversation et des mises à jour asynchrones.
- Notifications : compréhension des choix de canal et de leurs états sans dépendre uniquement de la couleur ou d'une icône.
- Calendrier : accès clavier aux jours/événements, annonce de la date complète, distinction des événements par texte et non par couleur seule, navigation entre périodes et reflow mobile.
- Toutes les pages : focus visible, zoom 200 %, largeur 320 CSS px, contrastes, cibles tactiles et absence de piège clavier.

### 55.9 Fichiers Moodle 5.3 examinés

| Fichier | SHA |
|---|---|
| `public/user/profile.php` | `3f5fc6a142abf9138139e0f410d8badb5fda6f3b` |
| `public/user/edit.php` | `d3f6d80ea22527ad2776be7bc9e7310925c686be` |
| `public/user/preferences.php` | `9abd5706b3668609bf9b615e2ed3664e55df8c22` |
| `public/grade/report/user/index.php` | `9bae87674fce052f52d4e1a4a23adfbad58bda13` |
| `public/message/index.php` | `5d9262e81f15519b96954bf423e0b0466590650b` |
| `public/message/notificationpreferences.php` | `cc02b9c7c6ec408efa3e8776237c4b51d60f2fe5` |
| `public/calendar/view.php` | `b167199f2a67c46a32bae16999df3b3b4f9c1411` |
| `public/calendar/classes/local/event/data_access/event_vault.php` | `f7c513307a3dee93faffafe841779d4c6618e56d` |

### 55.10 Limites et suite

Cette tranche est une analyse statique des contrôleurs et classes indiqués sur Moodle 5.3. Elle ne valide ni le rendu effectif des composants, ni les droits dans une installation, ni l'accessibilité réelle.

Restent à cartographier côté apprenant :
- les pages de consultation des activités et leurs états (devoir, test, forum, leçon, atelier, feedback, sondage, SCORM, H5P, etc.), en distinguant les activités déjà inventoriées aux sections antérieures ;
- les restrictions d'accès : affichage des conditions, disponibilité, dates, groupes, notes, achèvement et messages d'indisponibilité ;
- le détail des notifications et événements déclenchés par les activités ;
- les différences de code entre Moodle 4.5, 5.0, 5.1, 5.2 et 5.3 ;
- les parcours runtime et contrôles d'accessibilité.

**Nature des preuves :** analyse statique Moodle 5.3. Aucun test d'installation, de droits runtime, de parcours, axe-core, clavier, lecteur d'écran ou appareil mobile n'a été exécuté dans cette tranche.


## 56. Espace apprenant Moodle natif — consultation des activités et disponibilité conditionnelle

**Périmètre :** Moodle `MOODLE_503_STABLE`. Cette tranche examine les points d'entrée de plusieurs activités et le moteur de disponibilité conditionnelle. Elle complète l'inventaire des sections 40 à 46 sans prétendre analyser l'intégralité des workflows internes de chaque module.

### 56.1 Un point d'entrée commun, des expériences pédagogiques différentes

Les contrôleurs d'activité partagent certains invariants : résolution du cours et du module, authentification dans le contexte du cours, vérification des capacités, initialisation de la page et délégation du rendu au module. En revanche, le contenu et le déroulement sont propres à chaque activité. Le thème ne doit donc pas chercher à imposer une page d'activité universelle.

| Activité | Entrée examinée | Contrat de rendu / comportement constaté |
|---|---|---|
| Devoir | `mod/assign/view.php` | Résolution du cours et du module, `mod/assign:view`, mise à jour de l'accès effectif, puis délégation à l'objet `assign` et à sa méthode `view()`. La page peut présenter les consignes, l'état de remise et les actions disponibles selon l'utilisateur et la période. |
| Test | `mod/quiz/view.php` | Utilise `quiz_settings`, `access_manager`, les tentatives et les objets output dédiés. Distingue notamment accès invité, utilisateur non inscrit, possibilité de tentative, prévisualisation et révision de ses tentatives. |
| Forum | `mod/forum/view.php` | Construit la liste des discussions à partir des services/vaults du module ; prend en charge tri, pagination, recherche, groupes et modes d'affichage. Peut marquer les discussions comme lues selon le suivi et les réglages. |
| Leçon | `mod/lesson/view.php` | Parcours séquentiel et conditionnel piloté par le module : pages, navigation, réponses, progression et page de fin. Le contrôleur gère aussi le temps et le traitement de fin. |
| Atelier | `mod/workshop/view.php` | Affiche le plan utilisateur et la phase courante du processus d'atelier ; la phase et les actions dépendent du workflow d'évaluation par les pairs. Le module active son JavaScript dédié. |
| Feedback | `mod/feedback/view.php` | Vérifie l'ouverture et la possibilité de soumettre ; distingue activité fermée/non ouverte et réponse déjà soumise. Utilise le renderer du module et les règles de complétion de feedback. |
| SCORM | `mod/scorm/view.php` | Présente l'introduction, les tentatives et leur état, puis le lancement du paquet si disponible. Peut dépendre de JavaScript, d'une fenêtre popup et du runtime SCORM. |
| H5P activité | `mod/h5pactivity/view.php` | Charge le paquet et les options de présentation via le manager, puis délègue la lecture à `core_h5p\\player`. Le suivi des tentatives et le rapport dépendent de l'activation du tracking et des droits. |

Les exemples confirment que « consulter une activité » peut signifier lire une ressource, réaliser une tentative, participer à une discussion, suivre un parcours, remettre un travail ou effectuer une évaluation par les pairs. Les actions visibles et les états ne sont pas transposables d'un module à l'autre.

### 56.2 États et actions : ne pas réduire une activité à son titre

Pour chaque activité, Project N doit préserver les informations réellement préparées par le module :
- consignes et description ;
- dates d'ouverture, de fermeture et échéances ;
- disponibilité et raisons d'indisponibilité ;
- état de participation ou de tentative ;
- état de remise, de correction ou de révision ;
- nombre de tentatives et possibilité de réessai ;
- note et feedback lorsque l'utilisateur est autorisé à les consulter ;
- progression et achèvement ;
- actions possibles à cet instant précis.

Une carte d'activité dans une page de cours peut fournir un résumé, mais ne remplace pas la page de l'activité. Le thème ne doit pas dupliquer dans la carte des états métier approximatifs qui divergeraient de la page source.

### 56.3 Disponibilité conditionnelle : moteur natif, arbre de conditions et restitution

Le cœur `core_availability` expose une architecture générique pour la disponibilité des sections et modules. La classe `core_availability\\info` porte notamment le cours, la visibilité, la configuration de disponibilité et les groupes concernés. La classe `core_availability\\tree` représente l'arbre logique des conditions.

La structure permet des combinaisons logiques (ET, OU et négations), avec des règles de restitution distinctes :
- une activité peut être totalement masquée si la condition échoue ;
- elle peut rester visible avec une information expliquant pourquoi elle n'est pas disponible ;
- dans les groupes de conditions, certaines causes d'échec peuvent être affichées ou masquées selon la configuration.

Les conditions peuvent porter sur plusieurs dimensions : dates, note obtenue, achèvement d'une autre activité, appartenance à un groupe ou à un groupement, profil utilisateur et extensions fournies par des plugins. La disponibilité réelle peut aussi dépendre de la visibilité du module, de l'inscription au cours et d'autres contrôles du module.

**Point de vigilance :** disponibilité conditionnelle et autorisation d'accès ne sont pas synonymes. L'affichage d'une raison d'indisponibilité ne doit jamais permettre d'accéder à une ressource qui reste protégée côté serveur. Inversement, une activité masquée ne doit pas être réintroduite par une navigation ou une recherche indépendante du thème.

### 56.4 Décisions de conception Project N

1. Conserver les renderers, templates, formulaires et scripts propres à chaque module ; privilégier une harmonisation de l'enveloppe plutôt qu'une réécriture du contenu.
2. Ne pas imposer un gabarit unique aux activités qui ont des workflows distincts.
3. Préserver les messages d'accès, de disponibilité, de fermeture et de droits fournis par le module.
4. Distinguer dans les vues de cours les états « masqué », « visible mais indisponible », « disponible », « commencé », « remis », « évalué » et « terminé » lorsque le module fournit ces états.
5. Ne pas afficher de raison de restriction si Moodle ou le module ne la communique pas à l'utilisateur.
6. Ne pas recalculer les conditions d'accès côté thème et ne jamais considérer une condition d'affichage comme un contrôle de sécurité.
7. Concevoir les pages d'activité avec leurs besoins spécifiques : tableaux et filtres pour le forum, navigation séquentielle pour la leçon, lecteur intégré pour H5P/SCORM, formulaires de réponse pour feedback, interfaces de tentative pour test et devoir.
8. Préserver les comportements JavaScript et les contrats DOM des modules ; vérifier particulièrement les activités utilisant un lecteur intégré, un popup ou des interactions asynchrones.

### 56.5 Contrôles d'accessibilité à mener par famille d'activité

- **Devoir / feedback :** libellés, aides, erreurs de formulaire, pièces jointes, dépôt de fichiers, confirmation de soumission et restitution des états.
- **Test :** navigation entre questions, temps restant, avertissements, validation, sauvegarde, changement de page et annonces des états de tentative.
- **Forum :** hiérarchie des discussions/réponses, pagination, filtres, suivi lu/non lu, menus d'action et notifications.
- **Leçon :** ordre de lecture, alternatives aux interactions, compréhension des choix, navigation avant/arrière et restitution de la progression.
- **Atelier :** identification de la phase, consignes d'évaluation, formulaires de notation et distinction des travaux à évaluer.
- **SCORM / H5P :** clavier dans le contenu embarqué, focus à l'entrée/sortie, alternatives accessibles, zoom, comportement sans JavaScript et compatibilité avec les lecteurs d'écran. L'accessibilité du contenu auteur ne peut pas être garantie par le seul thème.
- **Toutes les activités :** ne pas supprimer les messages d'état, maintenir le focus après une action, éviter les interactions dépendantes du survol, contrôler le reflow et les contrastes.

### 56.6 Fichiers Moodle 5.3 examinés

| Fichier | SHA |
|---|---|
| `public/mod/assign/view.php` | `830fec3a11c2128c3fb529f4415a87f9fc6ca868` |
| `public/mod/quiz/view.php` | `9e3d7701701eeb0676e04e4ca1948ef1ed43d9ad` |
| `public/mod/forum/view.php` | `8504bb2b08d05feab825caf8eee6183a8e259ec5` |
| `public/mod/lesson/view.php` | `51add9ad2e7ea8007162c669683e6e6d59b8c2f7` |
| `public/mod/workshop/view.php` | `074eacdf7dd5dd0774a07a5895a015e451a1f2a7` |
| `public/mod/feedback/view.php` | `3fb2adbbbf88b97959b98eb45b47847077628716` |
| `public/mod/scorm/view.php` | `2cb4563fe70b749dad2af31b1bcd0c7cd4360cff` |
| `public/mod/h5pactivity/view.php` | `2976f8cfd93c1bb8b20bf7de695852e896c2ac9f` |
| `public/availability/classes/info.php` | `8fa2ea0d7315fc507b358a1e557064618a37e4da` |
| `public/availability/classes/tree.php` | `1280ef24330750838a20d4de1fa1aba15852114f` |

### 56.7 Limites et suite de l'analyse

Cette tranche porte sur les contrôleurs d'entrée et deux classes du moteur de disponibilité. Elle ne constitue pas une lecture exhaustive des renderers, templates, formulaires, services AJAX et scripts de chaque activité.

Les prochaines analyses doivent approfondir :
- les contrats de rendu de chaque module (Output API, renderers, templates, formulaires, AMD/JavaScript) ;
- les conditions de disponibilité effectivement prises en charge par chaque type d'activité ;
- les différences de comportement entre générations Moodle 4.5, 5.0, 5.1, 5.2 et 5.3 ;
- les parcours de remise, tentative, participation et consultation de feedback ;
- les mécanismes de fichiers et d'éditeurs intégrés aux activités.

**Nature des preuves :** analyse statique Moodle 5.3 des fichiers listés. Aucun test runtime, test d'accès direct, test axe-core, clavier, lecteur d'écran ou test de contenu SCORM/H5P n'a été exécuté dans cette tranche.


## 57. Contrats de rendu des activités — Output API, Mustache et JavaScript (première tranche)

**Périmètre :** Moodle `MOODLE_503_STABLE`. Lecture ciblée de renderables, renderers et templates pour devoir, test et forum. Il s'agit d'une première tranche représentative, pas d'une cartographie exhaustive de tous les modules.

### 57.1 Devoir : un état de remise riche, construit côté module

La classe `mod_assign\\output\\assign_submission_status` est un objet de rendu qui transporte bien davantage qu'un simple statut. Ses propriétés couvrent notamment :
- dates d'ouverture, échéance, date limite et éventuelle extension ;
- remise individuelle ou en équipe, groupe de remise et membres qui doivent encore remettre ;
- activation, verrouillage, correction et statut de notation ;
- plugins de remise et informations de contexte ;
- possibilités effectives de modifier, remettre ou consulter.

Le renderer du module assure des rendus spécialisés, notamment l'arborescence des fichiers de remise, qui peut initialiser du JavaScript Moodle. Les templates d'action séparent également les actions de remise et les actions de correction.

**Implication :** une carte « devoir » dans Project N ne peut pas déduire un état à partir de la seule échéance. Le statut doit provenir des données du module, en tenant compte des remises de groupe, extensions, verrouillages, plugins et capacités. La vue de détail doit préserver les composants et formulaires natifs.

### 57.2 Test : plusieurs interfaces et contrats distincts

La classe `mod_quiz\\output\\view_page` rassemble les informations de la page d'accueil du test : tentatives précédentes, notes, feedback, possibilité de réessayer, tentative inachevée, messages d'empêchement et texte du bouton de démarrage. Elle n'est pas elle-même un objet templatable : elle transmet des données au renderer.

Le renderer du test traite plusieurs vues différentes : page d'accueil, tentative, résumé, relecture et navigation entre questions. Il s'appuie sur les objets du moteur de questions et les options d'affichage. Les tentatives peuvent être paginées et les options de relecture varient selon les droits et les paramètres du test.

Le template du minuteur utilise un rôle ARIA `timer`, des propriétés d'annonce et un bouton contrôlant sa visibilité. Ce composant ne doit pas être remplacé par un simple compte à rebours décoratif : sa relation avec la tentative, le temps restant et les annonces accessibles est fonctionnelle.

**Implication :** ne pas uniformiser la page d'accueil du test, l'écran de tentative et la relecture. Toute modification visuelle du minuteur, de la navigation des questions ou des formulaires doit être testée avec clavier et lecteur d'écran, notamment en situation chronométrée.

### 57.3 Forum : templates Mustache et interactions imbriquées

Le rendu des discussions du forum s'appuie sur des objets renderables, dont `mod_forum\\output\\forum_post`, qui exportent leurs données vers les templates Mustache. Les templates observés montrent plusieurs contrats :
- la liste des discussions expose un tableau avec légende, pagination, actions de création, notation et filtres selon les capacités ;
- la discussion imbriquée affiche les publications préparées par le module, les notifications et les contrôles de discussion ;
- le template `forum_discussion_nested_v2` initialise le module AMD `mod_forum/discussion_nested_v2` sur un conteneur identifié.

Le forum possède plusieurs présentations de discussions (liste, fil, imbriquée, versions alternatives) et des interactions telles que réponses, favoris, abonnement, verrouillage, épinglage, marquage comme lu et navigation entre discussions.

**Implication :** la surcharge CSS doit préserver les structures sémantiques, les identifiants et attributs attendus par JavaScript, ainsi que les contrôles produits par les capacités. Transformer systématiquement les tableaux en cartes ou réécrire les fils de discussion risquerait de casser pagination, repères de lecture et interactions.

### 57.4 Conséquences transversales pour l'architecture du thème

1. Le thème intervient prioritairement sur les variables visuelles, les composants Boost et les styles de l'enveloppe ; il ne remplace pas les règles métier des modules.
2. Un renderer PHP peut générer directement du HTML, déléguer à Mustache ou combiner les deux. Il faut identifier le contrat réel avant toute surcharge.
3. Les templates Mustache peuvent contenir des régions de surcharge, des partiels et des blocs JavaScript ; le nom du template ne suffit pas à connaître toutes ses dépendances.
4. Les classes CSS, identifiants, attributs `data-*`, rôles ARIA et sélecteurs utilisés par les scripts sont des contrats techniques à préserver.
5. Les actions et leur disponibilité sont préparées par les capacités et les données du module. Le thème ne doit pas reproduire ces décisions.
6. Les plugins d'activité et de remise peuvent étendre les états et composants ; toute présentation doit tolérer des extensions et des données absentes.
7. Les vues complexes doivent être vérifiées à plusieurs largeurs : un tableau responsive ne garantit pas à lui seul une bonne expérience mobile ni une lecture accessible.

### 57.5 Contrôles techniques et accessibilité à intégrer au futur prototype

- Comparer le DOM avant/après surcharge et repérer les sélecteurs JavaScript supprimés ou déplacés.
- Vérifier que les régions Mustache et partiels restent présents, notamment lorsqu'ils sont injectés par le module.
- Tester les formulaires avec erreurs, confirmation, dépôt de fichiers et états verrouillés.
- Tester les tentatives de test, le minuteur, la navigation entre questions et la relecture.
- Tester les discussions longues, les réponses imbriquées, la pagination et les actions de forum.
- Contrôler les noms accessibles, l'ordre de tabulation, le focus après action et les annonces dynamiques.
- Tester l'affichage à 320 CSS px, le zoom 200 %, le clavier seul et un lecteur d'écran.
- Ne pas conclure à la conformité sur la seule lecture des attributs ARIA : le comportement doit être testé en runtime.

### 57.6 Fichiers Moodle 5.3 examinés

| Fichier | SHA |
|---|---|
| `public/mod/assign/classes/output/assign_submission_status.php` | `7be979424df357c17978984daaa6cbc91be83ad7` |
| `public/mod/assign/classes/output/renderer.php` | `a2f9593546ca9c2e4e8df82c8bab640e61816ec0` |
| `public/mod/assign/templates/submission_actionmenu.mustache` | `da65dbc345d72dee8ad52d8d1b9b74b12b9ceca2` |
| `public/mod/quiz/classes/output/view_page.php` | `a1e9ed55b39a20c7a8bb55b96f702fb158c8d9b8` |
| `public/mod/quiz/classes/output/renderer.php` | `f29f14edb845bd716e4095ed045b6507d1c86f9d` |
| `public/mod/quiz/templates/timer.mustache` | `eecf99b5fa32ea09b196ec0d35c62faa6f7c1c65` |
| `public/mod/forum/classes/output/forum_post.php` | `52c51bc8d8e0e44b691a5467281d76900c7f0ef4` |
| `public/mod/forum/templates/discussion_list.mustache` | `50146a73bcf6b28629ab80d1eaa3adc1b9c2618c` |
| `public/mod/forum/templates/forum_discussion_nested_v2.mustache` | `82a39370acb8e382bd6c09c457e5c97beff2e8e3` |

### 57.7 Limites et suite

Cette tranche n'analyse pas encore les formulaires internes de dépôt et de tentative, les plugins de remise, le moteur de questions, les services AJAX, ni l'ensemble des templates et scripts du forum. Les activités leçon, atelier, feedback, SCORM et H5P restent à traiter sur le même niveau de profondeur.

La suite doit étendre l'analyse des contrats de rendu à ces modules, puis couvrir les mécanismes transversaux de fichiers et d'éditeurs utilisés dans les activités. Les écarts entre Moodle 4.5 et 5.3 devront être établis par comparaison des fichiers correspondants, sans extrapolation à partir de la seule branche 5.3.

**Nature des preuves :** analyse statique des neuf fichiers listés sur Moodle 5.3. Aucun test runtime, test de surcharge de thème, test axe-core, clavier ou lecteur d'écran n'a été exécuté.


## 58. Contrats de rendu des activités — Leçon, Atelier, Feedback, SCORM et H5P

**Périmètre :** Moodle `MOODLE_503_STABLE` (Moodle 5.3), lecture statique ciblée des contrôleurs, renderers, classes de sortie, formulaires, templates Mustache et scripts identifiés ci-dessous. Cette tranche complète la première analyse des activités, sans constituer une revue exhaustive de chaque branche fonctionnelle.

### 58.1 Leçon (`mod_lesson`) : parcours à embranchements et formulaires dépendants de la page

La page d'entrée `mod/lesson/view.php` traite plusieurs états avant et pendant le parcours : activité indisponible, protection par mot de passe, dépendances entre leçons, session chronométrée interrompue, reprise ou poursuite. Le renderer historique `mod/lesson/renderer.php` fournit notamment l'en-tête, les messages d'inaccessibilité et le formulaire de connexion.

Les pages sont typées et implémentées dans `mod/lesson/pagetypes` : branche, cluster, fin de branche/cluster, essai, appariement, choix multiple, numérique, réponse courte et vrai/faux. Les réponses et transitions ne sont donc pas une simple succession de blocs éditoriaux. La correction d'une question de type essai possède son propre formulaire et un éditeur de commentaires ; la vue sans réponses utilise un formulaire avec navigation vers la page suivante. Le script `timer.js` actualise le compte à rebours.

**Implication pour le thème :** préserver les formulaires et leurs champs cachés de navigation, les retours de correction, les messages de dépendance et les états de reprise. Ne pas transformer les pages de questions en composants génériques qui feraient disparaître le type de question, le feedback ou la transition. Le compte à rebours doit rester perceptible et intelligible ; son comportement réel doit être vérifié au clavier et avec lecteur d'écran.

### 58.2 Atelier (`mod_workshop`) : workflow par phases, dépôt et évaluation par les pairs

L'atelier suit un workflow de phases (configuration, remise, évaluation, notation/fermeture) qui détermine les actions et les vues accessibles. `view.php` construit le plan utilisateur et la phase courante, délègue l'affichage au renderer, puis initialise `mod_workshop/workshopview`. Le renderer expose notamment une barre d'action via `mod_workshop/action_bar` et des rendus spécifiques de messages et de remises.

Le formulaire de remise peut combiner un éditeur de texte et un gestionnaire de fichiers. Ces deux modes sont configurables indépendamment et peuvent être désactivés, facultatifs ou obligatoires. La page d'évaluation charge la remise, contrôle l'accès, prépare les éditeurs et gestionnaires de fichiers des commentaires, puis enregistre l'évaluation selon la stratégie configurée. L'identité de l'auteur peut être masquée selon les capacités.

**Implication pour le thème :** une seule carte d'activité ne suffit pas à représenter les phases. Les actions visibles doivent rester celles calculées par le module et le plan de l'utilisateur. Préserver la distinction auteur/évaluateur, l'anonymat éventuel, les formulaires d'évaluation, les pièces jointes et les contrôles de phase. Ne pas supposer que tous les utilisateurs déposent ou évaluent au même moment.

### 58.3 Feedback (`mod_feedback`) : formulaire structuré, progression multi-page et anonymat

`view.php` compose l'interface à partir du contexte du feedback, de la barre d'action et de son état : aperçu, consignes après envoi, périodes d'ouverture/fermeture, restrictions d'accès, cours associés et réponses. Le résumé est un objet rendu par le template `mod_feedback/summary`. Le renderer dédié gère notamment les barres d'action et les titres.

Le formulaire `mod_feedback\complete_form` conserve des paramètres de progression tels que page courante, dernière page et positions des éléments. Il gère également le mode anonyme et des modes distincts (consultation, édition, modèle). Le JavaScript AMD d'édition initialise les composants et utilise le moteur de templates Moodle pour rendre certains éléments enrichis.

**Implication pour le thème :** préserver l'ordre et la progression des éléments, les contrôles de navigation multi-page, les indications d'anonymat, les messages de confirmation et les différences entre réponse et édition de modèle. Le thème ne doit jamais exposer une identité ou des données de réponse que le module masque selon son mode et ses permissions. Tester les formulaires longs, les erreurs et la reprise de navigation sur petit écran.

### 58.4 SCORM (`mod_scorm`) : lecteur externe, API de suivi et fenêtres de lancement

`view.php` présente l'introduction, l'état des tentatives et les possibilités de lancement ou de reprise ; elle tient compte notamment des paramètres de navigation linéaire du cours. Le lancement est distinct, dans `player.php`, qui prépare le lecteur et l'API de communication avec le paquet. Les scripts `view.js`, `player.js`, `module.js` et `request.js` participent à l'ouverture, au suivi et à la fermeture du lecteur, aux tentatives et aux échanges avec le contenu SCORM. Le renderer expose des éléments de rapport et des barres d'action, dont certaines sont produites par Mustache.

**Implication pour le thème :** ne pas traiter le lecteur SCORM comme un simple iframe décoratif ni déplacer ses contrôles hors du contexte attendu. Préserver les scripts, identifiants, fenêtres/popup éventuelles, mécanismes de sortie et transmission des données de suivi. Le contenu du paquet peut imposer ses propres dimensions et styles : le thème doit éviter les règles CSS globales qui débordent dans le lecteur ou en perturbent le redimensionnement. Les tests doivent inclure lancement, reprise, sortie normale, fermeture de fenêtre et remontée des données de progression/score.

### 58.5 H5P (`mod_h5pactivity`) : contenu interactif, tentatives et résultats xAPI

`view.php` prépare le contexte de l'activité et les messages d'état avant de rendre le contenu H5P. Les classes de sortie `attempt` et `result` sont des objets `renderable` et `templatable` : elles exportent les données d'une tentative ou d'un résultat vers les templates. Les templates identifiés comprennent les tentatives, les résultats, les listes de tentatives et les vues de révision/rapport. Le module dispose également de classes locales d'exécution et d'un sous-système xAPI ; le contenu interactif et la collecte de résultats ne se réduisent donc pas à la présentation du rapport.

**Implication pour le thème :** distinguer l'affichage du contenu H5P, l'état de tentative et le rapport de résultats. Préserver les dimensions et le contexte d'exécution du contenu, les contrôles internes H5P, les messages d'état et les liens de revue. Éviter de cibler globalement les éléments internes au contenu H5P sans vérifier les conséquences sur ses bibliothèques. Tester plusieurs types de contenus, les tentatives sans score, les résultats partiels et la consultation des rapports selon les capacités.

### 58.6 Conséquences transversales

1. Ces cinq activités ont des modèles d'interaction très différents : embranchements et questions pour Leçon, phases et évaluation par les pairs pour Atelier, questionnaire multi-page pour Feedback, lecteur/API de suivi pour SCORM, contenu interactif et résultats xAPI pour H5P.
2. Le thème doit d'abord travailler sur l'enveloppe de page, la hiérarchie visuelle, les espaces et les composants Boost. Toute surcharge du contenu interne doit être justifiée par un contrat identifié.
3. Les formulaires Moodle (éditeur, gestionnaire de fichiers, champs cachés, validation et boutons) doivent rester fonctionnels. Une apparence commune ne doit pas effacer les différences métier.
4. Les messages d'indisponibilité, d'erreur, de confirmation, de progression et de reprise sont des informations fonctionnelles, pas du bruit visuel.
5. Les extensions et réglages d'activité peuvent modifier les composants présents. Une surcharge doit tolérer des éléments facultatifs, des plugins additionnels et des contenus inconnus.
6. La présence d'un template Mustache ne signifie pas que toute la page passe par ce template : les contrôleurs et renderers peuvent produire directement du HTML et initialiser du JavaScript en complément.

### 58.7 Points de contrôle à prévoir au prototype

- Leçon : chaque type de question, branchement, dépendance, mot de passe, essai à corriger, session chronométrée et reprise.
- Atelier : chaque phase, dépôt texte/fichier, évaluation, anonymat, commentaires et actions autorisées.
- Feedback : formulaire anonyme et nominatif, multi-page, questions conditionnelles, validation, confirmation et consultation des réponses.
- SCORM : lancement, reprise, popup, sortie, redimensionnement et remontée du suivi.
- H5P : plusieurs types de contenus, contrôles internes, tentative, résultat, rapport et révision.
- Pour tous : 320 CSS px, zoom 200 %, navigation clavier, focus après action, erreurs de formulaire, lecteur d'écran, mode sombre et contraste.

### 58.8 Fichiers Moodle 5.3 examinés

| Fichier | SHA |
|---|---|
| `public/mod/lesson/view.php` | `51add9ad2e7ea8007162c669683e6e6d59b8c2f7` |
| `public/mod/lesson/renderer.php` | `02b01f8b60cff4d0d83104049f76032c925973f1` |
| `public/mod/lesson/essay_form.php` | `a1ab40c5cff785acc33407ef2ab6ab9085154314` |
| `public/mod/lesson/view_form.php` | `db30a3b922ca5944d45a22ee4fbd11ab78310a53` |
| `public/mod/lesson/timer.js` | `3f3c8f0d9ed16dd5377be044330c6bc7045c7e5b` |
| `public/mod/lesson/templates/edit_action_buttons.mustache` | `94124a2cce9806518ac3b3428cc44f8b70dfb9c6` |
| `public/mod/workshop/view.php` | `074eacdf7dd5dd0774a07a5895a015e451a1f2a7` |
| `public/mod/workshop/renderer.php` | `f702e26395a159d5da14695dd2aa762f85661a72` |
| `public/mod/workshop/submission_form.php` | `a2cbb845422d1cc9fa57ab537caca654ff3a284c` |
| `public/mod/workshop/assessment.php` | `cc5aa3184cb38de9d436d01721573e494759bc89` |
| `public/mod/workshop/amd/src/workshopview.js` | `d1d4c56f3921c78a5d331f35b6b8ede7213ed145` |
| `public/mod/workshop/templates/action_bar.mustache` | `fd47b2b821b6e932e3d5c61c7e435bd7e921e957` |
| `public/mod/feedback/view.php` | `3fb2adbbbf88b97959b98eb45b47847077628716` |
| `public/mod/feedback/classes/output/renderer.php` | `4fa2a42bf2e4cc0085a909b23a331539aa7d928a` |
| `public/mod/feedback/classes/complete_form.php` | `eae37ffd03ecd4ce2c786961407100978e920be8` |
| `public/mod/feedback/amd/src/edit.js` | `faacbdd65edd38f3f8a8f6d019e80911c39e45d3` |
| `public/mod/feedback/templates/summary.mustache` | `4c4f0f9970b2bd283759c668e208a73b5caf7066` |
| `public/mod/scorm/view.php` | `2cb4563fe70b749dad2af31b1bcd0c7cd4360cff` |
| `public/mod/scorm/player.php` | `080be1607bc8a0dc1f7eba128a371e7df9da4fee` |
| `public/mod/scorm/renderer.php` | `632bd1d00d573927c4a2609c5003ed1c476f2bc6` |
| `public/mod/scorm/view.js` | `6060daf4ee9836b6edfa44205053cdc5289a657c` |
| `public/mod/scorm/player.js` | `20dd6c08b0e84dca8941741aeeadb2c43ad582a3` |
| `public/mod/scorm/templates/player_exitbar.mustache` | `c73865949bf881d036566a1041f04e1daa4dc8e8` |
| `public/mod/scorm/classes/output/actionbar.php` | `cac0f8a672a9c412945ec71e5ad523fa9b2148ed` |
| `public/mod/h5pactivity/view.php` | `2976f8cfd93c1bb8b20bf7de695852e896c2ac9f` |
| `public/mod/h5pactivity/classes/output/attempt.php` | `2b1911fb163a0986fe41ed97133c49b17ca0ba1d` |
| `public/mod/h5pactivity/classes/output/result.php` | `ad722a3a5a5efb00521c5356b3b686cc1e85bdc7` |
| `public/mod/h5pactivity/templates/attempt.mustache` | `faa97b39a055dc3dd9fbe317230bde692c3af7be` |
| `public/mod/h5pactivity/templates/reviewattempts.mustache` | `c0cfa5c99e5fb74d585bcc21b70480b03d68d3f6` |

### 58.9 Limites et suite

Cette tranche est une analyse statique ciblée. Elle n'a pas exécuté les activités, leurs contenus embarqués, les formulaires ni les scripts en navigateur. Elle ne démontre donc ni le bon fonctionnement d'une surcharge de thème, ni la compatibilité runtime, ni l'accessibilité effective.

**Suite immédiate :** cartographier les mécanismes transversaux de fichiers et d'éditeurs utilisés dans les activités (File API, dépôts, zones de dépôt, gestionnaires de fichiers, éditeurs, pièces jointes, fichiers intégrés et contextes de fichiers), puis comparer les contrats et changements entre Moodle 4.5, 5.0, 5.1, 5.2 et 5.3.


## 59. Mécanismes transversaux — fichiers, dépôts, gestionnaires et éditeurs

**Périmètre :** Moodle `MOODLE_503_STABLE` (Moodle 5.3), analyse statique ciblée de la File API, des formulaires Moodle, du sélecteur de fichiers, des dépôts et de l'éditeur TinyMCE. Cette section établit les contrats à préserver dans les activités et les formulaires ; elle ne décrit pas toutes les implémentations de plugins de dépôt ou d'éditeur.

### 59.1 File API : identité d'un fichier et séparation des espaces

Le stockage Moodle ne se résume pas à un chemin physique. Les fichiers sont identifiés par leur contexte, composant, zone de fichiers (`filearea`), identifiant d'élément (`itemid`), chemin interne et nom. La classe `file_storage` gère le stockage et les objets `stored_file` représentent les fichiers stockés ; la documentation du code distingue explicitement l'accès aux fichiers propres à une zone de celui des fichiers d'autres composants, qui passe par les mécanismes de navigation et d'autorisation prévus.

Les zones de brouillon (`draft`) jouent un rôle intermédiaire lors de l'édition d'un formulaire. Le fichier n'est pas nécessairement déjà enregistré dans sa zone métier définitive : l'enregistrement du formulaire et les fonctions de préparation/transfert assurent la transition entre brouillon et zone cible. Les identifiants de contexte et d'élément ne sont donc pas interchangeables.

**Implication pour le thème :** ne jamais construire des URL de fichiers, déplacer des fichiers ou déduire leur visibilité à partir de leur seul nom ou chemin. Le thème présente les liens et contrôles produits par Moodle ; il ne remplace ni les fonctions de stockage, ni le contrôle d'accès, ni la gestion des brouillons.

### 59.2 Formulaires : éditeur, filemanager et filepicker sont trois contrats distincts

Les éléments QuickForm Moodle `editor`, `filemanager` et `filepicker` ont des classes et des templates propres :

- `editor` : champ de contenu avec format, éditeur préféré et options de fichiers intégrés ; il peut fonctionner avec Tiny ou une zone de texte selon la configuration.
- `filemanager` : gestion d'un ensemble de fichiers, avec options de contexte, nombre et taille maximale, types acceptés et contenu initial.
- `filepicker` : sélection d'un fichier pour un usage ponctuel, avec un contexte et des options de dépôts.

Leurs templates (`element-editor`, `element-filemanager`, `element-filepicker`) s'insèrent dans le template générique des éléments de formulaire. Le thème doit donc préserver le label, les aides, les erreurs, les indications obligatoires, les attributs et les identifiants qui relient l'élément à son comportement JavaScript.

### 59.3 Dépôts et sélecteur de fichiers

Le sélecteur est une interface commune aux dépôts Moodle : dépôt du serveur, fichiers récents, fichiers personnels, fichiers de cours et plugins externes selon les dépôts activés et les droits. Le script `repository/filepicker.js` gère la navigation et l'affichage des fichiers ; les requêtes passent notamment par `filepicker.php` et `repository_ajax.php`. Le contexte, l'élément de brouillon, l'environnement d'ouverture et l'identifiant du dépôt font partie des paramètres techniques.

Les dépôts sont des plugins : les fonctionnalités et les vues proposées varient selon les instances configurées (par exemple stockage local ou dépôts distants). Il ne faut pas supposer que la liste des dépôts, les actions ou les métadonnées sont fixes. Les opérations AJAX sont distinctes de la page qui héberge le formulaire.

**Implication pour le thème :** le sélecteur doit rester une interface Moodle fonctionnelle, avec ses vues, navigation, sélection, prévisualisation, dépôt et retour au formulaire. Une adaptation de style peut viser les panneaux, listes, boutons, états actifs et responsive, mais ne doit pas remplacer la logique de sélection ni supposer un fournisseur particulier. Tester plusieurs dépôts et plusieurs types de fichiers.

### 59.4 Dépôt et glisser-déposer

Le script `lib/form/dndupload.js` initialise une zone de dépôt et transmet notamment les options de contexte, identifiant de brouillon, types acceptés, nombre maximal de fichiers et taille maximale. Le glisser-déposer est un raccourci d'interaction, pas le seul moyen d'ajouter un fichier : l'interface doit conserver une action explicite de sélection et un fonctionnement utilisable au clavier et sur écran tactile.

Les scripts de gestionnaire de fichiers et de sélecteur ont leurs propres événements et états. Masquer visuellement la zone de dépôt, déplacer son bouton ou supprimer son conteneur peut interrompre l'initialisation ou les retours de progression.

### 59.5 Éditeur TinyMCE : configuration par contexte et intégration au thème

Dans Moodle 5.3, Tiny est implémenté par `editor_tiny\editor` et son gestionnaire de plugins. La configuration dépend notamment du contexte, des options du champ, des options du sélecteur de fichiers et des plugins activés. L'éditeur récupère également la feuille de style du thème via `$PAGE->theme->editor_css_url()`. Le JavaScript principal lit le mode de couleur Bootstrap depuis l'attribut `data-bs-theme` du document ; l'éditeur adapte ainsi son apparence au mode clair/sombre sans que cela soit une simple surcharge CSS globale.

Le module d'upload de Tiny utilise le sélecteur de fichiers et un mécanisme d'envoi asynchrone ; il gère les notifications de début/fin et les erreurs d'envoi. Les plugins Tiny (lien, média, image, H5P, équation, accessibilité, etc.) peuvent ajouter leurs propres commandes, fenêtres et interactions.

**Implication pour le thème :** conserver l'URL de CSS éditeur, les options du contexte et l'initialisation JavaScript. Ne pas imposer à Tiny une palette ou des règles qui contredisent le mode de couleur, les plugins ou les contenus intégrés. Les styles de contenu édité et les styles de l'interface de l'éditeur doivent être traités séparément. Le thème ne doit pas dépendre d'un seul plugin Tiny activé.

### 59.6 Atto et autres éditeurs : ne pas figer l'implémentation

Le formulaire Moodle choisit l'éditeur selon la configuration et les préférences disponibles ; le thème ne doit pas présumer que Tiny est toujours le seul éditeur actif. Les options de compatibilité, les zones de texte simples et les éditeurs alternatifs doivent continuer à fonctionner. La présente tranche approfondit Tiny car c'est l'éditeur moderne identifié dans Moodle 5.3, mais ne démontre pas la compatibilité visuelle de chaque éditeur alternatif.

### 59.7 Contrats de présentation à préserver

1. Les identifiants, attributs et conteneurs utilisés par les modules AMD/YUI sont des contrats fonctionnels ; ne pas les renommer ou les déplacer sans vérification.
2. Un fichier en brouillon n'est pas encore nécessairement un fichier métier enregistré ; ne pas afficher un lien définitif avant la finalisation du formulaire.
3. Les limites de taille, types acceptés, permissions et options de dépôt sont calculées côté Moodle et peuvent varier selon le contexte.
4. Les messages de progression, d'erreur, de remplacement, de suppression et de sélection font partie du parcours utilisateur.
5. Le sélecteur doit être utilisable au clavier, avec lecteur d'écran, au toucher et sans glisser-déposer.
6. Les éditeurs peuvent être initialisés dans des formulaires chargés dynamiquement ; le DOM peut être modifié après le chargement initial.
7. Les contenus intégrés (images, médias, H5P, liens) ont leurs propres règles d'affichage et ne doivent pas hériter aveuglément des styles d'interface.

### 59.8 Scénarios de vérification pour le prototype

- Éditeur Tiny avec texte seul, image déposée, image choisie dans un dépôt, lien, média, équation et plugin H5P si activé.
- Zone de texte simple ou éditeur alternatif selon la configuration.
- Filepicker ponctuel et filemanager multi-fichiers.
- Dépôts locaux et distants effectivement configurés, avec droits différents.
- Fichiers autorisés, refusés, trop volumineux, renommés, remplacés et supprimés.
- Brouillon abandonné, formulaire soumis, formulaire invalide et retour après erreur.
- Glisser-déposer, bouton de sélection, navigation clavier et usage tactile.
- Formulaire chargé dynamiquement, fenêtre modale, mode sombre et zoom.
- Vérifier qu'aucun lien de fichier ne contourne les contrôles d'accès Moodle.

### 59.9 Fichiers Moodle 5.3 examinés

| Fichier | SHA |
|---|---|
| `public/lib/form/editor.php` | `6b4b40b5357132f3c5640e24319730e86637f3e6` |
| `public/lib/form/filemanager.php` | `a7b31c7c0afc5d6dde1c7333365e2eb3bbaefaed` |
| `public/lib/form/filepicker.php` | `c86a29b55c352c6ab9b4e7c9c9b5e6e19ea34f44` |
| `public/lib/form/dndupload.js` | `edf2b8c398f06c9a2f15115e4aa7405fd95f1b18` |
| `public/lib/form/templates/element-editor.mustache` | `6f02ca05f26815521566d32a72298f7edae8f8cf` |
| `public/lib/form/templates/element-filemanager.mustache` | `425b7b8737937a054d182dedc840183da2aa30de` |
| `public/lib/form/templates/element-filepicker.mustache` | `7c4e99b06c0ab7d73c8feef193b6dd5a1933e5d4` |
| `public/repository/filepicker.js` | `3e6879533d595e6d754cb8f602fd2f687f026193` |
| `public/repository/filepicker.php` | `fcdb2ab9182b03a4e784691f34dcf0934ef88d98` |
| `public/repository/draftfiles_ajax.php` | `65c4fea95634b18880349f1cb3c4e079762e8216` |
| `public/repository/draftfiles_manager.php` | `99019a7df2f067ca9288ec32936a69d83f76909` |
| `public/repository/repository_ajax.php` | `37c40264b5166b53d2ac356317bf0791e6f10760` |
| `public/repository/lib.php` | `60ec0d301d871a4699032024a10a04ee59b19a6c` |
| `public/lib/editor/tiny/classes/editor.php` | `7e06fb0381722ee6d2a782e5dbfc6500d78a54c3` |
| `public/lib/editor/tiny/classes/manager.php` | `29d29e25cc48e9a448f3997f7214144a126aa4c1` |
| `public/lib/editor/tiny/amd/src/editor.js` | `3f59087a3cf28d97461c179e1df930d3f0abe92b` |
| `public/lib/editor/tiny/amd/src/uploader.js` | `4f11d80bb3ee40101f9d4f48c407027413a674db` |
| `public/lib/editor/tiny/plugins/h5p/amd/src/plugin.js` | `ea055cf60e628fd8ce25ec800657b451356289c7` |
| `public/lib/filelib.php` | `679e4bcb4231e71c6362ca465b5906176409eac3` |
| `public/lib/filestorage/file_storage.php` | `5e7b06e64ad319a2c6c9d224959d38074689f9e2` |
| `public/lib/filestorage/stored_file.php` | `9702fbcae3ac4f27ef9c9408ffbc29e7cdde40cb` |

### 59.10 Limites et suite

Cette tranche repose sur l'analyse statique de Moodle 5.3. Elle ne teste pas les dépôts réellement configurés, les éditeurs en navigateur, les droits sur des fichiers réels, ni les interactions clavier et lecteur d'écran. Elle ne démontre pas non plus la compatibilité des éventuelles surcharges de thème.

**Suite :** comparer ces contrats et ceux des activités entre Moodle 4.5, 5.0, 5.1, 5.2 et 5.3, en distinguant les changements de version du socle Moodle des adaptations IOMAD. Ensuite, poursuivre la cartographie des autres mécanismes transversaux du parcours apprenant avant de passer à l'espace formateur.


## 60. Comparaison interversions — activités, formulaires et éditeur (Moodle 4.5 à 5.3)

**Périmètre :** comparaison statique des fichiers sources de Moodle `MOODLE_405_STABLE`, `MOODLE_500_STABLE`, `MOODLE_501_STABLE`, `MOODLE_502_STABLE` et `MOODLE_503_STABLE`. Les chemins des sources sont préfixés par `public/` dans les branches 5.1 à 5.3 examinées. Les empreintes SHA permettent d'identifier les fichiers identiques ou modifiés ; elles ne remplacent pas une comparaison sémantique exhaustive des diffs.

### 60.1 Constat général

La comparaison ne montre pas de réécriture globale des parcours d'activités entre 4.5 et 5.3. Les contrats fonctionnels structurants restent reconnaissables : Leçon conserve son moteur de pages et de tentatives, Atelier son workflow par phases, SCORM son lecteur et son suivi, H5P son contenu et ses tentatives. En revanche, plusieurs fichiers évoluent ponctuellement, et Feedback présente des changements successifs de son contrôleur de vue.

Il faut donc éviter deux conclusions abusives :
- un fichier modifié ne signifie pas nécessairement une rupture d'interface ou de contrat ;
- un fichier inchangé ne prouve pas que toutes ses dépendances, templates, styles ou scripts sont inchangés.

### 60.2 Éléments relativement stables

- `mod/workshop/view.php` conserve la même empreinte dans les branches 4.5 à 5.3 examinées : le contrôleur reste stable sur cette période, sans préjuger de l'évolution des classes, templates ou scripts qu'il appelle.
- `mod/h5pactivity/view.php` conserve la même empreinte entre 4.5 et 5.3 dans les versions comparées.
- `mod/lesson/view.php` conserve l'empreinte observée en 4.5, 5.0, 5.1 et 5.2 ; la branche 5.3 présente une empreinte différente. Il faudra examiner le diff exact avant d'attribuer un impact visuel ou fonctionnel à cette évolution.
- Les éléments principaux du workflow Atelier et des tentatives H5P peuvent donc être considérés comme des contrats à préserver, mais pas comme des composants dont le HTML serait garanti identique.

### 60.3 Évolutions identifiées dans les contrôleurs d'activités

**Feedback.** `mod/feedback/view.php` possède une empreinte différente en 4.5, 5.0, 5.1, 5.2 et 5.3. C'est le signal le plus régulier de cette sélection. La page mérite une comparaison sémantique ciblée des changements : états d'affichage, contrôles d'accès, barre d'action, cours associés, résumé et transitions de réponse. La différence d'empreinte ne permet pas à elle seule de conclure à un changement de DOM.

**SCORM.** `mod/scorm/player.php` est identique entre 4.5 et 5.0, puis change en 5.1, 5.2 et 5.3. Les passages examinés confirment que la logique de tentative, de mode, de disponibilité, de table des matières et de suivi reste présente. Les lignes de suivi évoluent dans le fichier, mais il faut comparer les diffs complets avant de déterminer les conséquences exactes pour le lecteur, les fenêtres ou l'API de communication.

**Leçon.** Le changement d'empreinte de `view.php` en 5.3 doit être vérifié au niveau des pages typées, des formulaires de réponse et du rendu des tentatives. Il ne justifie pas de modifier préventivement les composants de présentation.

### 60.4 Évolutions de la File API de formulaire

Les fichiers `lib/form/editor.php` et `lib/form/filemanager.php` changent d'empreinte au cours de la période :
- `editor.php` évolue entre 4.5 et 5.0, puis à nouveau entre 5.1 et 5.2 ; les versions 5.2 et 5.3 ont la même empreinte pour ce fichier.
- `filemanager.php` évolue entre 4.5 et 5.0, reste identique en 5.1, puis évolue en 5.2 ; les versions 5.2 et 5.3 ont la même empreinte.

La structure générale reste celle de contrôles QuickForm Moodle templatisés et alimentés par des identifiants de brouillon. Les changements appellent une vérification des options exportées vers Mustache, des attributs HTML, des limites de taille et des retours de dépôt avant toute surcharge CSS ou JavaScript. Il ne faut pas coder des sélecteurs spécifiques à une seule version sur la base d'une observation 5.3.

### 60.5 Éditeur Tiny : adaptation au mode de couleur du thème

Le fichier `lib/editor/tiny/classes/editor.php` garde la même empreinte de 4.5 à 5.3 dans les branches comparées ; il continue notamment à transmettre la feuille de style du thème via `editor_css_url()`.

En revanche, le JavaScript `lib/editor/tiny/amd/src/editor.js` évolue entre les branches 4.5, 5.0, 5.1, 5.2 et 5.3. Une évolution importante est visible en 5.3 : le script lit `data-bs-theme` sur le document, choisit le skin Tiny clair ou sombre et propage le mode de couleur aux conteneurs et documents internes de l'éditeur. Ce point crée une exigence concrète pour Project N : le mode sombre du thème doit être cohérent avec l'éditeur, et non se limiter à recolorer la page Moodle.

**Décision de conception :** privilégier les mécanismes de thème prévus par Moodle et Bootstrap, dont `data-bs-theme` et `editor_css_url()`. Ne pas piloter Tiny par des sélecteurs CSS fragiles ciblant son DOM interne. Vérifier aussi que les dialogues et contenus édités restent lisibles en mode sombre.

### 60.6 Tableau des empreintes de référence

Les empreintes ci-dessous sont relevées directement dans les branches du fork Moodle examiné. Elles identifient les fichiers, mais ne constituent pas une preuve de compatibilité d'exécution.

| Fichier | Moodle 4.5 | Moodle 5.0 | Moodle 5.1 | Moodle 5.2 | Moodle 5.3 |
|---|---|---|---|---|---|
| `mod/lesson/view.php` | `16f8e4d3efaf` | `16f8e4d3efaf` | `16f8e4d3efaf` | `16f8e4d3efaf` | `51add9ad2e7e` |
| `mod/workshop/view.php` | `074eacdf7dd5` | `074eacdf7dd5` | `074eacdf7dd5` | `074eacdf7dd5` | `074eacdf7dd5` |
| `mod/feedback/view.php` | `f4d972d50951` | `5d7e4efe0b1a` | `5d7e4efe0b1a` | `1e2e96031126` | `3fb2adbbbf88` |
| `mod/scorm/player.php` | `28b5fe66056b` | `28b5fe66056b` | `01d1699990db` | `a4fef6572c0f` | `080be1607bc8` |
| `mod/h5pactivity/view.php` | `2976f8cfd93c` | `2976f8cfd93c` | `2976f8cfd93c` | `2976f8cfd93c` | `2976f8cfd93c` |
| `lib/form/editor.php` | `8106a9994d6b` | `5b85e8e0c43d` | `5b85e8e0c43d` | `6b4b40b53571` | `6b4b40b53571` |
| `lib/form/filemanager.php` | `c8422d94aa0b` | `dfe37f273841` | `dfe37f273841` | `a7b31c7c0afc` | `a7b31c7c0afc` |
| `lib/editor/tiny/classes/editor.php` | `7e06fb038172` | `7e06fb038172` | `7e06fb038172` | `7e06fb038172` | `7e06fb038172` |
| `lib/editor/tiny/amd/src/editor.js` | `a16666f6c639` | `1eadfe0b0008` | `a16666f6c639` | `a3ca5e26d17a` | `3f59087a3cf2` |

### 60.7 Conséquences pour Project N

1. La base d'interface doit rester compatible avec les contrats Moodle, et non dépendre d'un HTML supposé identique entre toutes les versions.
2. Les surcharges doivent être minimales, ciblées et versionnées seulement lorsqu'une différence vérifiée l'exige.
3. Tiny doit être traité comme un composant intégré au système de thèmes : mode de couleur, CSS éditeur et dialogues doivent être vérifiés ensemble.
4. Les formulaires d'éditeur et de fichiers doivent être testés sur au moins 4.5, 5.0 et 5.3, puis les changements intermédiaires 5.1/5.2 doivent être couverts par des contrôles ciblés.
5. La stabilité de certains contrôleurs ne dispense pas d'inspecter leurs renderers, templates, AMD/ESM et feuilles de style.

### 60.8 Limites de cette comparaison

Cette comparaison repose sur les fichiers listés et leurs empreintes, complétée par une lecture ciblée des points de code. Elle n'est pas un diff complet de chaque module ni une comparaison exhaustive de tous les templates et scripts dépendants. Aucun test runtime, test navigateur, test clavier, lecteur d'écran ou test d'accessibilité n'a été exécuté. Les empreintes sont celles du dépôt forké `ericfalcon/moodle` et doivent être revalidées si les branches sont mises à jour.

**Suite :** approfondir les diffs sémantiques des fichiers ayant changé (en priorité Feedback, SCORM, Leçon 5.3 et File API), puis étendre la comparaison aux autres mécanismes du parcours apprenant. Ne passer à l'espace formateur qu'après avoir terminé cette cartographie transversale.


## 61. Comparaison sémantique Moodle 4.5–5.3 — activité Feedback

**Périmètre et méthode.** Comparaison des fichiers réels des branches `MOODLE_405_STABLE` et `MOODLE_503_STABLE` du fork `ericfalcon/moodle`. Pour Moodle 5.3, les chemins sont préfixés par `public/`. Analyse statique des contrôleurs, du moteur de complétion, du formulaire et des objets de rendu ; aucun test runtime ni test d’accessibilité n’a été exécuté.

### Contrôleur `mod/feedback/view.php`

- 4.5 et 5.0 : la page affiche la description dans `feedback_description`, puis la barre d’action principale à l’intérieur de cette enveloppe.
- 5.2 et 5.3 : la description et cette enveloppe disparaissent de cette page ; la barre d’action est rendue directement après l’en-tête.
- 5.3 : lorsque l’activité est fermée ou que la réponse a déjà été envoyée, le bouton de retour au cours est conditionné à la désactivation de la navigation linéaire du cours (`core_courseformat\\local\\linearnavigationsettings`). En 5.0–5.2, ce bouton est rendu sans ce test.
- Le contrôleur conserve le contrôle d’accès au cours, le contrôle de correspondance des Feedback placés sur la page d’accueil, les capacités `mod/feedback:edititems`, `mod/feedback:viewreports` et `mod/feedback:mapcourse`, ainsi que la construction du résumé et l’appel à l’événement de consultation.

### Moteur de complétion et formulaire

- `classes/completion.php` conserve la même structure et le même nombre de lignes entre 4.5 et 5.3 ; le diff textuel ciblé identifie une modification d’appel à `has_capability()` : le quatrième argument `false` est désormais explicite pour le contrôle de `mod/feedback:complete`. Il s’agit d’une précision dans l’appel d’autorisation, pas d’une nouvelle règle métier démontrée.
- `classes/complete_form.php` conserve sa structure ; le diff ciblé remplace la classe utilitaire `sr-only` par `visually-hidden` pour le libellé d’accessibilité des éléments obligatoires. Le thème ne doit donc pas supposer que l’ancienne classe est toujours présente.
- Le formulaire reste un `moodleform` multi-page. Il porte les identifiants de module et de cours, la page courante, les positions d’items et les commandes précédent/suivant/enregistrer. Les éléments sont produits par les types d’items Feedback ; les dépendances conditionnelles et la reprise des réponses temporaires relèvent de `mod_feedback_completion`.

### Barre d’action et résumé

- `classes/output/standard_action_bar.php` et `classes/output/summary.php` ont le même SHA Git entre 4.5 et 5.3 dans le fork examiné : leur contenu est identique sur ces deux branches.
- La barre d’action expose selon les capacités : modification des questions, prévisualisation pour les personnes pouvant modifier ou consulter les rapports, et accès au formulaire ou reprise à la page mémorisée.
- Le résumé exporte notamment le nombre de réponses terminées pour le groupe sélectionné et le nombre de questions. Le template Mustache utilise les classes `feedback_info` et `feedback_info_value`.

### Conséquences pour Project N

1. Ne pas remplacer le moteur de complétion ni reconstruire les états « pas encore ouvert », « fermé », « déjà répondu », « réponse en cours » et « réponse terminée » à partir de données visuelles.
2. Préserver les formulaires Moodle, leurs éléments typés, leurs boutons de navigation, leurs champs cachés et leurs classes fonctionnelles ; la mise en page mobile doit s’adapter sans modifier leur sémantique.
3. Ne pas cibler exclusivement `.sr-only` pour les contenus masqués accessibles ; prendre en compte les classes utilitaires Bootstrap/Moodle de la version cible.
4. Ne pas imposer un bouton « retour au cours » : en 5.3, Moodle le supprime dans certains cas lorsque la navigation linéaire est active.
5. Garder la barre d’action et le résumé sous le contrôle des objets Output API et du template Moodle. Le thème peut les styliser, mais ne doit pas reconstruire les liens en contournant les capacités.

**Limites.** Cette comparaison porte sur les fichiers ciblés et les branches indiquées. Les états précis de disponibilité dépendent également des méthodes de `mod_feedback_structure`, des paramètres de l’activité, des groupes et des capacités effectives. Les différences relevées ne constituent pas une validation runtime ou une preuve de compatibilité visuelle.

**Empreintes des fichiers comparés.**
- 4.5 `mod/feedback/view.php` : `f4d972d50951e5200b1f43c377c2a4448f954db8`
- 5.3 `public/mod/feedback/view.php` : `3fb2adbbbf88b97959b98eb45b47847077628716`
- 4.5 `mod/feedback/classes/completion.php` : `860d822594f125d684e58f0f50bf5ae36021ae77`
- 5.3 `public/mod/feedback/classes/completion.php` : `ccc72ddc3d921a06f37c7234eb882d9564b9e6f4`
- 4.5 `mod/feedback/classes/complete_form.php` : `36be27a70f9e2c16eb4ebd47e32f053ac2a9fe7f`
- 5.3 `public/mod/feedback/classes/complete_form.php` : `eae37ffd03ecd4ce2c786961407100978e920be8`
- Barre d’action et résumé : SHA identiques entre les deux branches (voir fichiers correspondants).


## 62. Comparaison sémantique Moodle 4.5–5.3 — activité SCORM

**Périmètre et méthode.** Comparaison des contrôleurs `mod/scorm/view.php` et `player.php`, ainsi que de `locallib.php`, entre `MOODLE_405_STABLE` et `MOODLE_503_STABLE` du fork `ericfalcon/moodle`. Analyse statique des différences réelles ; aucune exécution de paquet SCORM ni validation runtime n’a été effectuée.

### Page d’accès et lancement (`view.php`)

- Les deux versions acceptent l’identifiant du module (`id`) ou celui de l’instance (`a`), vérifient les enregistrements associés, imposent la connexion au cours et préparent les données nécessaires au lecteur.
- Le lancement automatique lié à `skipview` reste conditionné par la configuration, la capacité `mod/scorm:skipview`, l’absence de capacité de consultation des rapports et l’existence éventuelle de traces antérieures.
- Changement significatif en 5.3 : lorsque la navigation linéaire du cours est activée, l’URL de retour du lecteur popup est la page de l’activité SCORM elle-même, avec `preventskip=1`. Cela évite de relancer automatiquement le lecteur à sa fermeture et permet de poursuivre vers l’activité suivante.
- Sans navigation linéaire, Moodle conserve les règles antérieures liées au format de cours, notamment le format `singleactivity` et le retour à la section.

### Lecteur et sortie (`player.php`)

- Le lecteur conserve la validation du SCO, de l’organisation, du mode et du numéro de tentative, puis vérifie la connexion, la visibilité et la disponibilité de l’activité.
- En 5.3, Moodle désactive explicitement le pied de page de navigation de la page du lecteur (`set_show_navigation_footer(false)`). Le lecteur SCORM garde ainsi son propre dispositif de navigation et de sortie.
- La logique de sortie est remaniée : elle prend en compte la navigation linéaire, le format du cours et l’option d’affichage des sections (`coursedisplay`). En navigation linéaire, la sortie revient à la page de l’activité avec `preventskip=1`. Sinon, Moodle distingue notamment le cours en affichage multipage (retour à la section avec paramètre `sr`) et l’affichage sur une seule page (retour à l’ancre de section).
- Le mécanisme de keepalive en cas de perte réseau évolue également : l’URL de redirection est explicitée et le nom de chaîne varie selon l’activation du service web mobile.

### Bibliothèque SCORM (`locallib.php`)

- Le fichier conserve le même nombre de lignes entre les deux branches et les différences textuelles ciblées identifient ici le remplacement de la classe utilitaire `sr-only` par `visually-hidden` pour le texte signalant l’ouverture dans une nouvelle fenêtre.
- Les fonctions de lancement, de récupération de la table des matières, de gestion des tentatives et de suivi ne doivent pas être remplacées ou recalculées par le thème. Le suivi repose sur le runtime SCORM et les données de tentatives/SCO.

### Conséquences pour Project N

1. Traiter le lecteur SCORM comme une interface applicative autonome, et non comme une simple page de cours à réorganiser. Préserver ses dimensions, son mode popup/embedded, ses scripts, ses fenêtres et ses contrats de communication.
2. Ne pas intercepter ou réécrire les sorties du lecteur : elles dépendent de la navigation linéaire, du format de cours, du mode d’affichage et des règles `skipview`.
3. Ne pas ajouter un second pied de page de navigation au lecteur ; Moodle le désactive explicitement en 5.3.
4. Conserver les états et informations de tentative, SCO, organisation et suivi produits par Moodle. Le thème peut améliorer l’enveloppe de la page d’accès, mais ne doit pas simuler l’achèvement SCORM.
5. Prendre en compte les classes utilitaires d’accessibilité qui évoluent (`sr-only` vers `visually-hidden`) et vérifier le rendu sur chaque version cible.

**Limites.** L’analyse ne couvre pas l’exécution de contenus SCORM 1.2/2004, les comportements des API JavaScript du paquet, les fenêtres popup selon les navigateurs, ni les tests de reprise après perte réseau. Ces aspects nécessitent des paquets de test et des essais runtime sur les versions réellement supportées.

**Empreintes des fichiers comparés.**
- 4.5 `mod/scorm/view.php` : `61e655cb7771dcc5e5e214295a5355fbc58e76cb`
- 5.3 `public/mod/scorm/view.php` : `2cb4563fe70b749dad2af31b1bcd0c7cd4360cff`
- 4.5 `mod/scorm/player.php` : `28b5fe66056b1d4a2b5e65485eb2f3c1c2c6f3f4`
- 5.3 `public/mod/scorm/player.php` : `080be1607bc8a0dc1f7eba128a371e7df9da4fee`
- 4.5 `mod/scorm/locallib.php` : `1c1149e1ceee53e2c0a8e406913978ceb55460d8`
- 5.3 `public/mod/scorm/locallib.php` : `d2208ace2f244823692a8b67cc0f256bf7c784c1`
