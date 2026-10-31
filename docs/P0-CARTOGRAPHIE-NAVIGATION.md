# Project N — Cartographie technique de la navigation Moodle / Iomad

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

Les fichiers `theme/boost/layout/drawers.php` de Moodle 5.3 et Iomad 5.2 ont le même blob Git. Le template `theme/boost/templates/drawers.mustache` est également identique entre ces deux branches. La même identité avait été constatée pour le layout 4.5 Moodle/Iomad.

Cela indique que le shell Boost lui-même n'est pas le lieu principal des adaptations Iomad dans les branches comparées. Les différences se situent dans le cœur Iomad et les renderers.

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
