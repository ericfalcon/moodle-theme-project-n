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
