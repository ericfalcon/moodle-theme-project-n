# P0 — Retour d’expérience Épure pour Project N

**Statut :** analyse statique documentaire et code, à compléter pendant la cartographie P0.  
**Dépôt de référence :** [ericfalcon/moodle-theme-iomad](https://github.com/ericfalcon/moodle-theme-iomad), branche `main`, thème `theme_epure`.  
**Dépôt cible :** [ericfalcon/moodle-theme-project-n](https://github.com/ericfalcon/moodle-theme-project-n), branche `main`.

> Ce document distingue les mécanismes effectivement observés dans Épure, les décisions déjà prises pour Project N et les points qui exigent encore une vérification. Il ne vaut ni portage de code ni preuve de compatibilité runtime.

## 1. Synthèse décisionnelle

Épure constitue un retour d’expérience concret pour construire un thème unique qui fonctionne sur Moodle standard et Iomad. Project N peut reprendre certains principes d’architecture, mais ne doit pas copier l’ensemble du code : les objectifs d’interface sont différents et certaines intégrations Iomad d’Épure peuvent dépendre d’API internes ou de structures propres à une génération.

| Sujet | Observation dans Épure | Décision pour Project N | Contrôle nécessaire |
|---|---|---|---|
| Socle | Thème enfant de Boost : `$THEME->parents = ['boost']` dans `theme/epure/config.php`. | **Reprendre le principe**, déjà retenu dans le cadrage Project N. | Vérifier les points d’extension sur chaque génération cible et limiter les copies de templates. |
| Fonctionnement Moodle/Iomad | Épure se déclare comme thème Iomad avec `$THEME->isiomadtheme = true`; Moodle standard ignore ce paramètre selon le commentaire du code. | **Adapter** : la compatibilité Moodle standard doit rester le cas de base ; activer les comportements Iomad seulement si les capacités/API requises existent. | Installer et tester sur Moodle seul et Iomad, y compris pages de connexion et contextes sans entreprise courante. |
| Apparence par entreprise | `theme_epure\\company_style` centralise les paramètres propres à l’entreprise et leur repli vers les paramètres du site/Iomad. Les données de thème d’entreprise sont sérialisées dans des réglages `companystyle_<id>`. | **Réutiliser le principe de centralisation et de repli**, pas nécessairement le stockage ou le schéma. | Définir le modèle de configuration tenant de Project N ; vérifier les API de stockage et les règles de portée. |
| Isolation visuelle multi-tenant | Le code Épure décrit une apparence calculée pour l’entreprise courante et des styles générés pour la page. | **Reprendre le principe** : aucune valeur d’apparence d’une entreprise ne doit fuiter vers une autre via un cache global ou une variable statique mal indexée. | Tests alternant deux entreprises dans une même session/cache, utilisateurs multi-entreprises et utilisateur sans entreprise courante. |
| Repli de marque | Épure dérive une palette accessible à partir d’une couleur de marque, avec couleurs de texte/contraste et variantes claires/sombres. | **Adapter** au design system Project N ; ne pas réutiliser automatiquement toutes les options de personnalisation. | Contrastes calculés et vérifiés, modes clair/sombre, couleurs invalides ou absentes. |
| Personnalisation tenant | Épure permet de remplacer certains choix du site par des réglages d’entreprise, avec listes de valeurs autorisées et valeurs par défaut. | **Reprendre sélectivement** le modèle « valeur entreprise → valeur site → valeur par défaut ». | Décider quels réglages sont réellement nécessaires à Project N et qui peut les modifier. |
| Fichiers déposés par entreprise | `company_style::allowed_file()` limite les extensions par zone ; `theme_epure_pluginfile()` sert les fichiers avec contrôles de zone et de contexte système. | **Réutiliser le principe de validation côté serveur**, pas copier la fonction sans revue. | Revoir permissions de gestion, MIME, SVG, cacheabilité, noms de fichiers et règles Moodle de stockage. |
| Versionnement Moodle | `theme/epure/version.php` déclare `requires = 2024100700` et `supported = [405, 503]` dans le snapshot consulté. | **S’inspirer de la déclaration explicite**, mais annoncer une compatibilité seulement après tests effectifs. | Confirmer les versions exactes et la signification attendue des métadonnées sur les versions Moodle ciblées. |
| Dépendances | Le snapshot Épure déclare Boost et `tool_customlang` dans `$plugin->dependencies`. | **Ne pas reprendre les dépendances par défaut** : chaque dépendance Project N doit être justifiée par une fonctionnalité indispensable. | Vérifier l’installation sans plugin compagnon et la compatibilité sur toutes les versions retenues. |
| Rendu et surcharge | Épure utilise des callbacks SCSS, un renderer factory Moodle et des callbacks de profil. | **Réutiliser le principe des points d’extension Moodle** avant de surcharger des templates complets. | Cartographier hooks, renderers, callbacks, templates et contrats DOM/JS par génération. |
| Navigation globale | Épure apporte ses propres adaptations d’interface, mais la cartographie Project N retient déjà une navigation hybride. | **Conserver la décision Project N** : navigation globale Project N, navigation contextuelle et pédagogique native Moodle/Iomad. | Prototyper la composition et tester les composants de navigation des versions 4.5–5.3. |
| Accessibilité | Épure possède des fonctions de palette et de préférences d’affichage ; la présence de tests automatisés dans le dépôt ne prouve pas à elle seule qu’un parcours donné est conforme. | **Reprendre l’approche de validation continue**, avec axe-core complété par tests manuels. | Consigner les versions, parcours, résultats et anomalies des tests réellement exécutés. |

## 2. Éléments de code observés

### 2.1 Configuration et héritage

Le fichier `theme/epure/config.php` :
- charge `lib.php` ;
- déclare Boost comme parent ;
- configure les callbacks SCSS et le renderer factory Moodle ;
- déclare l’utilisation de l’index de cours et la configuration du titre d’activité ;
- définit `$THEME->isiomadtheme = true` pour permettre l’intégration des champs d’apparence Iomad.

**Leçon pour Project N :** garder la configuration du thème simple et explicite. Une déclaration de compatibilité Iomad ne remplace ni la détection des fonctions réellement disponibles ni les contrôles de droits côté serveur.

### 2.2 Centralisation de l’apparence entreprise

Le fichier `theme/epure/classes/company_style.php` regroupe :
- les options de style disponibles ;
- les zones de fichiers acceptées et les extensions autorisées ;
- les valeurs d’apparence d’entreprise ;
- les règles de repli vers les réglages du site et les paramètres de marque Iomad ;
- la palette et les valeurs de présentation calculées.

**Leçon pour Project N :** éviter que la logique tenant soit dispersée dans les templates et callbacks. Prévoir une frontière explicite entre :
1. la découverte du contexte entreprise par les mécanismes Iomad ;
2. la résolution des réglages effectifs ;
3. la production de valeurs de présentation ;
4. leur rendu par les composants.

Cette séparation doit rester sans effet sur Moodle standard.

### 2.3 Styles et compilation SCSS

Dans `theme/epure/lib.php`, les callbacks assemblent les styles de Boost et ceux du thème, calculent des variables depuis les paramètres et renvoient les styles additionnels. Le code traite aussi la couleur d’accent, les rayons, les polices et les réglages de largeur.

**Leçon pour Project N :** privilégier des design tokens et variables CSS/SCSS cohérents plutôt qu’une multitude de règles conditionnelles dans les templates. Les décisions de largeur et de structure doivent cependant être revérifiées contre les layouts et tiroirs des versions Moodle cibles : une valeur fonctionnelle dans une génération peut perturber les calculs d’une autre.

### 2.4 Fichiers et sécurité

Le callback `theme_epure_pluginfile()` distingue les fichiers d’apparence entreprise, la feuille de style de l’application mobile et les fichiers de réglage globaux du thème. La classe entreprise documente une validation de l’extension autorisée côté serveur.

**Leçon pour Project N :** un sélecteur de fichiers limité dans le formulaire ne suffit pas. La validation doit aussi être faite au moment de la conservation et de la diffusion. Les règles de cacheabilité doivent être évaluées au regard de la nature publique ou privée du fichier et du contexte multi-tenant.

### 2.5 Déclaration des versions

Le fichier `theme/epure/version.php` du snapshot consulté indique la version de thème `0.56.3`, une exigence Moodle 4.5 et une plage supportée déclarée jusqu’à Moodle 5.3. Ce sont des métadonnées du dépôt à un instant donné, pas une preuve que tous les parcours ont été exécutés sur toutes ces versions.

**Leçon pour Project N :** séparer explicitement :
- version déclarée comme compatible ;
- analyse statique effectuée ;
- installation vérifiée ;
- tests fonctionnels exécutés ;
- tests d’accessibilité exécutés ;
- compatibilité validée et publiée.

## 3. Précautions spécifiques à Iomad

Le code et les analyses P0 Project N montrent que l’architecture Iomad diffère entre les générations, notamment dans les namespaces, chemins et noms de tables à partir de la génération 5.1.

En conséquence :

1. Ne pas recopier dans Project N une requête SQL ou un appel de classe Iomad d’Épure sans vérifier sa disponibilité et son statut d’API dans chaque version cible.
2. Ne pas supposer que la détection Iomad, la présence d’un contexte entreprise et l’existence d’une entreprise courante sont équivalentes.
3. Ne pas coder les menus d’administration Iomad en dur : certains sont déclarés dynamiquement par les composants.
4. Ne pas reconstruire les capacités ni les règles d’isolation. Le thème organise et présente l’interface ; Moodle/Iomad reste responsable des autorisations effectives.
5. Ne pas attribuer à Iomad un changement qui relève en fait du passage de Moodle 4.5/5.0 à 5.1/5.2. Les comparaisons doivent rester entre branches de même génération.
6. Pour chaque usage d’une API Iomad, consigner le fichier source, la génération, le niveau de stabilité connu et la stratégie de repli Moodle standard.

## 4. Décisions de conception pour Project N

### À reprendre

- Boost comme parent technique.
- Une couche centralisée pour résoudre le contexte et les préférences d’entreprise.
- Une résolution explicite des valeurs effectives avec repli vers les réglages du site.
- Une palette accessible calculée, avec contrôles de contraste.
- Des validations côté serveur pour les fichiers et réglages tenant.
- Une stratégie de surcharge minimale fondée d’abord sur les API et points d’extension Moodle.
- Des tests séparant Moodle standard, Iomad, les générations et les contextes d’entreprise.

### À adapter

- Les paramètres d’apparence par entreprise : ne conserver que ceux qui servent la vision mobile-first et le design system de Project N.
- La gestion des polices, logos et couleurs : choisir un périmètre plus réduit si cela diminue la complexité et les risques d’accessibilité.
- Le mécanisme de détection et l’adaptateur Iomad : tenir compte des évolutions de namespaces et d’API entre 4.5/5.0 et 5.1/5.2.
- La déclaration de compatibilité : la lier aux preuves de test et à une matrice maintenue.

### À ne pas reprendre sans justification

- Toute dépendance Épure qui ne serait pas indispensable à Project N.
- Toute surcharge de template qui dupliquerait un composant natif complet.
- Tout accès SQL direct aux tables Iomad.
- Toute hypothèse de cache global d’une valeur tenant.
- Toute logique d’autorisation basée uniquement sur le nom d’un rôle.
- Toute option de personnalisation simplement parce qu’elle existe dans Épure.

## 5. Vérifications encore nécessaires

| Contrôle | Méthode | Statut actuel |
|---|---|---|
| Inventaire exhaustif des classes Épure de détection et d’adaptation Iomad | Recherche dans le dépôt et lecture de chaque classe appelée | À poursuivre |
| Appels aux API/classes Iomad et éventuels SQL directs dans Épure | Recherche ciblée, puis vérification des versions correspondantes | À poursuivre |
| Points d’extension Boost/Moodle effectivement utilisés | Cartographie par version et comparaison des fichiers concernés | Partiellement documenté dans les documents P0 existants |
| Dépendances et tests annoncés par Épure | Lecture complète des workflows, configurations et suites de tests | À confirmer dans cette analyse |
| Fonctionnement réel sur Moodle standard et Iomad | Installations et scénarios runtime | Non établi par cette analyse statique |
| Accessibilité réelle | axe-core + clavier + lecteur d’écran + reflow/zoom + mobile | Non établie par cette analyse statique |
| Isolation entre entreprises | Tests avec au moins deux entreprises, utilisateurs mono/multi-entreprises et changement de contexte | Non établi par cette analyse statique |

## 6. Relation avec les documents P0

Ce document complète, sans remplacer :
- [P0-CADRAGE.md](P0-CADRAGE.md) — vision et contraintes ;
- [P0-NAVIGATION.md](P0-NAVIGATION.md) — navigation hybride ;
- [P0-CARTOGRAPHIE-NAVIGATION.md](P0-CARTOGRAPHIE-NAVIGATION.md) — composants et contrats de navigation ;
- [P0-COMPARAISON-VERSIONS-MOODLE-IOMAD.md](P0-COMPARAISON-VERSIONS-MOODLE-IOMAD.md) — différences par génération ;
- [P0-IOMAD-ADMINISTRATION.md](P0-IOMAD-ADMINISTRATION.md) — administration Iomad ;
- [P0-ROLES-MOODLE-IOMAD.md](P0-ROLES-MOODLE-IOMAD.md) — rôles et capacités.

**Critère de clôture :** le retour d’expérience Épure ne sera considéré comme intégré au P0 qu’après achèvement de l’inventaire Iomad et des points d’extension, puis inscription des décisions retenues dans l’architecture et la matrice de tests. Aucun développement de l’interface ne doit commencer avant la clôture de la cartographie P0.


## 7. Résultat de l’inventaire complémentaire des classes Iomad d’Épure

L’inspection des classes spécialisées affine la conclusion : Épure ne se contente pas d’un test de présence d’Iomad. Il possède une couche de compatibilité dédiée et des adaptations de rendu pour des blocs Iomad.

### 7.1 Adaptateur `theme_epure\\iomad`

Le fichier `theme/epure/classes/iomad.php` documente explicitement la transition Iomad 5.1 :
- les tables historiques deviennent notamment `local_iomad_companies`, `local_iomad_company_users`, `local_iomad_company_departments` et `local_iomad_company_courses` ;
- l’ancienne classe `iomad` est remplacée par `local_iomad\\iomad` ;
- le contexte entreprise passe à `local_iomad\\custom_context\\context_company`.

L’adaptateur centralise les noms de tables, détecte l’installation, choisit la classe API, fournit le contexte entreprise et expose quelques opérations de domaine. **Il contient aussi des accès SQL directs** aux tables Iomad, y compris pour déterminer la société liée à un cours ou les appartenances d’un utilisateur.

**Décision Project N :** reprendre l’idée d’un adaptateur versionné et localisé, mais ne pas reproduire automatiquement les requêtes SQL d’Épure. Priorité aux API publiques/supportées disponibles dans la version cible ; lorsqu’une requête directe est inévitable, elle doit être isolée, documentée, comparée entre les branches correspondantes et couverte par des tests. L’adaptateur ne doit pas devenir une copie du modèle de données Iomad.

### 7.2 Hooks et changement de thème par entreprise

Le fichier `theme/epure/classes/hook_callbacks.php` utilise notamment le hook `after_config` pour :
- déterminer l’entreprise associée au cours consulté par un utilisateur ;
- ajuster le contexte de session pour certains parcours Iomad ;
- suivre le thème de l’entreprise ;
- contribuer à la présentation des couleurs et aux champs d’apparence du formulaire entreprise.

Le code touche donc à la session et à la sélection du thème avant le rendu ordinaire de certaines pages. C’est un mécanisme puissant, mais plus sensible qu’un simple rendu conditionnel.

**Décision Project N :** ne pas reprendre le changement de thème en session ni la logique de sélection d’entreprise sans besoin fonctionnel démontré. Project N doit d’abord utiliser le contexte que Moodle/Iomad a établi ; toute intervention sur la session ou le thème doit avoir une justification documentée et des scénarios de non-régression dédiés.

### 7.3 Rendus des blocs Iomad

Les classes `block_iomad_company_admin_renderer` et `block_iomad_mycourses_renderer` spécialisent le rendu de blocs Iomad. Le commentaire du renderer d’administration indique que les données (actions, capacités, onglets et sélecteur d’entreprise) restent produites par Iomad, tandis qu’Épure adapte leur présentation.

C’est un pattern pertinent pour Project N : **conserver la construction des données et les permissions dans le composant métier**, et ne transformer que leur présentation. Il faut cependant vérifier les contrats du renderer parent dans chaque version Iomad cible avant de déclarer une compatibilité.

### 7.4 Vocabulaire par entreprise

La classe `theme_epure\\vocabulary\\company` conserve des termes personnalisés par entreprise dans les réglages du thème et met en cache les réécritures par entreprise et langue. Cela montre un autre usage tenant de la configuration et du cache.

**Décision Project N :** ne pas introduire de changement de vocabulaire tenant par défaut. Si ce besoin est retenu ultérieurement, les clés de cache doivent inclure toutes les dimensions pertinentes (au minimum entreprise et langue), avec invalidation lors des modifications et tests entre entreprises.

### 7.5 Statut de l’analyse

Cette lecture confirme l’existence d’une couche d’adaptation utile, mais ne valide pas à elle seule toutes ses branches conditionnelles. Les fichiers ont été lus dans la branche `main` d’Épure ; il reste à comparer précisément les appels aux classes Iomad et les contrats de rendu dans les couples de versions Moodle/Iomad correspondants. Les résultats ne constituent pas des tests runtime.
