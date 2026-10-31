# Project N — Dossier de cadrage P0

> Document de travail — première analyse technique et ergonomique. Les hypothèses qui nécessitent un prototype ou un environnement de test sont explicitement signalées. Ce document ne vaut pas validation de compatibilité.

## 1. Objet et périmètre

Project N est un thème Moodle autonome, enfant de Boost, destiné à repenser l'expérience web de trois profils : apprenant, formateur et administrateur. La conception est mobile-first et l'accessibilité est une exigence transversale.

Le périmètre couvre l'interface web Moodle/Iomad utilisée dans un navigateur mobile, une tablette ou un ordinateur. Il ne couvre pas l'interface de l'application mobile Moodle, qui est distincte du thème web.

Cibles envisagées :
- Moodle 4.5 à 5.3 ;
- Iomad 4.5 LTS, 5.0 et 5.1 comme versions publiées documentées ; la branche expérimentale Iomad 4.5 doit être distinguée de la branche stable. Iomad 5.2 reste une cible conditionnelle à confirmer dans les notes de publication officielles avant de l'inscrire dans la matrice des versions installables.

Aucune version ne devra être déclarée compatible sur la seule base d'une inspection du code.

## 2. Décisions déjà retenues

| Sujet | Décision |
|---|---|
| Thème parent | Boost |
| Type de livrable | Thème autonome `theme_projectn` |
| Approche de conception | Mobile-first, responsive, tactile et accessible |
| Espaces | Apprenant, formateur, administrateur |
| Moteur pédagogique | Moodle natif : cours, sections, activités, ressources, rôles et capacités |
| Création de cours | Guidage ergonomique sans remplacer les mécanismes natifs |
| Iomad | Pris en compte dès l'architecture ; isolation multi-entreprises impérative |
| Plugin compagnon | Facultatif et justifié uniquement par un besoin métier impossible à traiter proprement dans le thème |
| CI | Envisageable ; périmètre à définir |
| Application Moodle mobile | Hors périmètre |

## 3. Première analyse technique de Boost

### 3.1 Héritage recommandé

Le thème doit déclarer Boost comme parent via la configuration Moodle (`$THEME->parents = ['boost'];`). Il doit conserver autant que possible les layouts, composants et comportements natifs.

La documentation Moodle indique qu'un thème enfant n'a pas à redéfinir les layouts qu'il ne souhaite pas modifier. Les layouts sont déclarés dans `config.php`, et un thème peut réutiliser ceux du parent. Les templates Mustache permettent de séparer la préparation du contexte PHP de la présentation.

Conséquence pour Project N :
- ne pas copier l'ensemble du répertoire de Boost ;
- ne créer un layout Project N que si la structure ou les régions doivent réellement changer ;
- ne surcharger un template que pour un besoin visuel ou fonctionnel démontré ;
- documenter chaque surcharge : besoin, page concernée, solution native écartée, versions testées et risque de maintenance ;
- privilégier les composants Moodle et les points d'extension documentés.

Références :
- [Moodle Developer — Theme plugins](https://moodledev.io/docs/4.5/apis/plugintypes/theme)
- [Moodle Developer — Layouts](https://moodledev.io/docs/4.5/apis/plugintypes/theme/layout)
- [MoodleDocs — Creating a theme based on Boost](https://docs.moodle.org/dev/Creating_a_theme_based_on_boost)

### 3.2 Risques de compatibilité identifiés

Les versions de Moodle ne doivent pas être considérées comme un continuum sans rupture :
Points vérifiés dans les fichiers `UPGRADING.md` des branches Moodle `MOODLE_405_STABLE`, `MOODLE_500_STABLE`, `MOODLE_501_STABLE`, `MOODLE_502_STABLE` et `MOODLE_503_STABLE` :
- Moodle 5.1 : les layouts peuvent être hérités du grand-parent et des parents, le thème enfant restant prioritaire (MDL-79319) ;
- Moodle 5.2 : les templates `flat_navigation.mustache` et `nav-drawer.mustache` de Boost sont supprimés (MDL-87425) ; la navigation latérale de Boost passe désormais par les partiels de `drawers.mustache` ;
- Moodle 5.3 : la police d'interface par défaut de Boost passe de la pile système à Noto Sans (MDL-88412), ce qui modifie le rendu des thèmes enfants qui s'appuyaient sur le repli système ;
- Moodle 5.3 : le bloc `drawerheadercontent` du tiroir `theme_boost/drawer` est supprimé et remplacé par `drawercontrols` (MDL-89050) ;
- Moodle 5.3 : les modes de couleur clair/sombre sont ajoutés (attribut `data-bs-theme` sur `html`) ; un thème enfant qui rend sa propre barre de navigation doit intégrer `theme_boost\colour_mode::render_menu()` pour proposer le sélecteur natif (MDL-68037) ;
- Moodle 5.3 : les variables `$dialogue-*` prennent les propriétés personnalisées du design system (MDL-89735), et le chargement direct des sous-modules Bootstrap est déprécié (MDL-88766). Pour les modules AMD, l'import public passe de `theme_boost/index` (jusqu'à 5.2) à `bootstrap` (à partir de 5.3) pour les composants Bootstrap concernés ;
- Moodle 5.0 à 5.2 : suppression des variables `$activity-icon-*-filter`, dépréciation de `bs4-compat` (SCSS et module JS), suppression des styles `mod_chat` et `mod_survey` du thème (MDL-83725, MDL-80519, MDL-84450, MDL-82457).

Non vérifié : la documentation React indique que Moodle 5.2 introduit un mécanisme de surcharge des composants React et que la résolution ne retombe pas automatiquement sur le thème parent. Cette affirmation n'a pas pu être confirmée (documentation moodledev.io inaccessible depuis la session) et doit être vérifiée avant d'être retenue.

Conséquences :
- ne pas fonder la navigation Project N sur une copie de `nav-drawer.mustache` ou `flat_navigation.mustache` ;
- intégrer les changements 5.3 ci-dessus à l'inventaire de P1 (design system, typographie, navigation, modes de couleur) ;
- inventorier les composants et templates sur chaque branche de version ciblée ;
- éviter les surcharges de composants React tant qu'un besoin fonctionnel précis ne les justifie ;
- si une surcharge React devient nécessaire, prévoir un test spécifique par version, après vérification du comportement de repli (voir ci-dessus) ;
- réaliser un prototype minimal sur Moodle 4.5 et une version 5.x récente avant de figer l'architecture.

Références :
- [Boost — UPGRADING.md](https://github.com/moodle/moodle/blob/main/public/theme/boost/UPGRADING.md)
- [Moodle Developer — Code restructure (depuis 5.1)](https://moodledev.io/docs/5.3/guides/restructure)
- [IOMAD — Releases](https://docs.iomad.org/wiki/Releases)
- [IOMAD 5.1 — changements techniques et prérequis](https://docs.iomad.org/wiki/IOMAD_5.1)
- [Moodle Developer 5.3 — Theming React components](https://moodledev.io/docs/5.3/guides/javascript/react/theming) (à vérifier)

### 3.3 Rupture d'arborescence à partir de Moodle 5.1

Moodle 5.1 introduit un répertoire `public/` qui devient la racine web. Les plugins tiers doivent être déplacés dans leur emplacement relatif sous ce répertoire. Ce changement concerne directement le déploiement du thème : `theme/projectn` sur Moodle 4.5 et 5.0, `public/theme/projectn` à partir de 5.1. Iomad 5.1 adopte également cette restructuration et annonce d'autres évolutions internes (schéma de données, autoload des classes, organisation des plugins). Le thème ne doit donc jamais coder en dur des chemins absolus du cœur ; les outils de packaging et d'installation doivent être testés sur les deux modèles.

Références :
- [MoodleDocs — Mise à jour à Moodle 5.1](https://docs.moodle.org/501/en/Upgrading_Moodle) ;
- [Moodle Developer — Code restructure](https://moodledev.io/docs/5.3/guides/restructure) ;
- [IOMAD 5.1](https://docs.iomad.org/wiki/IOMAD_5.1).

### 3.4 Architecture technique proposée

Structure indicative, à confirmer par le prototype :

```
theme/projectn/
├── config.php
├── version.php
├── lib.php
├── settings.php
├── lang/
│   ├── en/
│   └── fr/
├── classes/
│   ├── output/
│   └── ...
├── layout/
├── templates/
├── scss/
├── pix/
└── tests/
```

Principes :
- `config.php` : héritage Boost, layouts et régions réellement nécessaires ;
- `lib.php` : callbacks strictement nécessaires, sans logique métier lourde ;
- `classes/output` : classes de présentation et contextes de rendu, sans contourner les contrôles d'accès ;
- `templates` : composants de présentation Mustache ;
- `scss` : tokens et styles propres au thème, avec réutilisation maîtrisée des variables Boost ;
- `settings.php` : réglages utiles, limités et compréhensibles ;
- `tests` : tests PHP et tests de rendu pertinents.

Cette arborescence est une proposition, pas encore un engagement de créer tous ces répertoires. La structure finale dépendra du prototype et des conventions Moodle de la branche retenue.

### 3.5 Limites fonctionnelles d'un thème

Un thème peut modifier l'affichage, les layouts, les templates et certains comportements de présentation. Il ne doit pas devenir un plugin métier caché.

Relèvent du thème :
- navigation et présentation des espaces ;
- tableaux de bord et composants visuels reposant sur les données exposées par Moodle ;
- adaptations responsive ;
- présentation des actions autorisées ;
- regroupement et mise en valeur de liens vers les fonctions natives.

Ne doivent pas être recréés dans le thème :
- moteur de cours ou format de cours ;
- système de rôles et permissions ;
- moteur d'inscription, d'évaluation ou de suivi ;
- stockage métier parallèle ;
- traitements métier qui devraient relever d'un plugin Moodle.

Un éventuel `local_projectn` ne sera proposé qu'après démonstration d'une nécessité fonctionnelle et définition d'un contrat d'intégration. Le thème devra rester utilisable sans lui.

## 4. Principes d'expérience mobile

### 4.1 Formats de conception

Les formats suivants sont des repères de maquette, pas des breakpoints imposés :
- smartphone : 390 × 844 CSS px ;
- petit smartphone / contrôle de reflow : 320 CSS px de largeur ;
- tablette : 768 × 1024 CSS px ;
- ordinateur : 1440 × 900 CSS px.

Les breakpoints définitifs doivent s'appuyer sur le système responsive utilisé par la version de Bootstrap embarquée dans Moodle, puis être ajustés selon le contenu réel. Ne pas multiplier les breakpoints spécifiques sans justification.

### 4.2 Règles communes

- concevoir d'abord les tâches prioritaires sur smartphone ;
- ne jamais dépendre du survol ;
- viser des cibles tactiles d'au moins 44 × 44 CSS px, avec espacement suffisant ;
- conserver un ordre de lecture et de focus cohérent ;
- éviter le défilement horizontal de la page à 320 CSS px ;
- traiter explicitement les contenus intrinsèquement larges (tableaux, éditeurs, grilles) ;
- ne pas transformer systématiquement les tableaux en cartes si cela détruit les relations entre lignes et colonnes ;
- prévoir des états de chargement, vide, erreur et confirmation compréhensibles ;
- limiter les animations et respecter `prefers-reduced-motion` ;
- optimiser les images, polices et scripts pour les réseaux mobiles ;
- ne pas masquer sur smartphone une fonction uniquement parce qu'elle est complexe : proposer un parcours adapté ou expliquer la contrainte.

### 4.3 Navigation

Hypothèse à prototyper :
- navigation principale persistante et compacte sur smartphone ;
- accès aux destinations majeures sans menu à profondeur excessive ;
- navigation secondaire contextualisée à l'espace et à la page ;
- accès clairement identifiable au profil, aux notifications et à l'aide ;
- sur tablette et ordinateur, enrichissement progressif de la navigation sans changer les repères fondamentaux.

Il ne faut pas présumer que la navigation actuelle de Boost doit être conservée telle quelle. Le prototype devra comparer une navigation mobile dédiée et une adaptation du tiroir Boost, en évaluant accessibilité, cohérence et coût de maintenance.

## 5. Parcours fonctionnels prioritaires

### 5.1 Apprenant — MVP

| Tâche | Priorité mobile | Résultat attendu |
|---|---:|---|
| Retrouver ses formations | P0 | Liste claire, recherche/filtre si utile |
| Reprendre une formation | P0 | Accès direct au cours et à l'activité pertinente |
| Comprendre sa progression | P0 | Indicateur explicite, non fondé uniquement sur la couleur |
| Identifier la prochaine action | P0 | Activité ou échéance fiable, lien vérifié |
| Accéder à une ressource | P0 | Lecture et téléchargement utilisables sur smartphone |
| Retrouver une échéance/notification | P1 | Information hiérarchisée et actionnable |

Attention : les indicateurs de progression et les prochaines activités doivent utiliser des données Moodle réellement disponibles et respecter les règles d'achèvement, les inscriptions et les restrictions d'accès.

### 5.2 Formateur — MVP

| Tâche | Priorité mobile | Résultat attendu |
|---|---:|---|
| Retrouver ses cours | P0 | Liste distincte des cours enseignés |
| Accéder à l'édition d'un cours | P0 | Accès conforme aux capacités Moodle |
| Ajouter une activité/ressource | P0 | Choix guidé par intention pédagogique puis mécanisme natif |
| Organiser les sections | P0 | Opérations simples accessibles au tactile |
| Prévisualiser côté apprenant | P1 | Accès clair à la vue apprenant |
| Accéder aux réglages avancés | P0 | Aucun enfermement dans l'assistant |

Le guidage ne doit pas dupliquer les formulaires natifs. Il doit orienter vers eux, les préconfigurer lorsque l'API le permet et laisser la main au formateur.

### 5.3 Administrateur — cadrage et MVP limité

L'espace administrateur complet est candidat à la version 1.x. En P0, il faut néanmoins définir :
- les profils administrateurs et gestionnaires concernés ;
- les tâches administratives courantes à forte valeur mobile ;
- les fonctions qui doivent rester sur les pages natives ;
- les limites de la personnalisation de l'administration Moodle ;
- les différences entre administrateur Moodle et gestionnaire Iomad.

Ne pas créer un tableau de bord qui expose des actions sans vérifier les capacités et contextes de l'utilisateur.

## 6. Iomad : exigences dès la conception

- fonctionnement standard Moodle garanti en l'absence d'Iomad ;
- détection fondée sur des mécanismes fiables, jamais sur une simple chaîne de version ou un nom de rôle ;
- prise en compte du contexte entreprise pour les menus et raccourcis ;
- aucune requête ou agrégation de données inter-entreprises réalisée par le thème ;
- les écrans ne doivent afficher que des données que l'utilisateur peut consulter dans le contexte courant ;
- tests avec au moins deux entreprises, des utilisateurs distincts et des rôles différents ;
- vérification des accès directs aux URL, pas seulement de la visibilité des liens ;
- parcours du gestionnaire d'entreprise inclus dans le MVP Iomad, sur smartphone et desktop.

Le thème ne doit pas reproduire la logique de cloisonnement d'Iomad. Il doit respecter et tester les contrôles fournis par Iomad et Moodle. Il ne doit pas interroger directement les tables internes Iomad pour construire ses écrans : les évolutions Iomad 5.1 (notamment la modernisation du schéma et le changement de nom de la table des entreprises) renforcent la nécessité de s'appuyer sur les API, fonctions et capacités publiques disponibles. Toute intégration spécifique devra être isolée et testée sur chaque branche Iomad retenue.

## 7. Accessibilité et validation

Référentiel de conception : WCAG 2.2 AA, avec prise en compte du RGAA et de l'EN 301 549 dans leur champ d'application.

Pour chaque parcours :
- analyse axe-core ;
- navigation clavier complète, ordre et visibilité du focus ;
- lecteur d'écran : NVDA sur poste de travail et TalkBack sur Android au minimum ;
- zoom 200 % et reflow à 320 CSS px ;
- contrastes texte, composants et états ;
- noms accessibles, titres, landmarks et structure de titres ;
- annonces des mises à jour dynamiques ;
- formulaires : libellés, erreurs, aides et conservation des saisies ;
- menus, dialogues, accordéons, notifications et interactions tactiles ;
- vérification de la réduction des animations.

L'analyse automatisée ne prouve pas la conformité. Les tests manuels et les parcours utilisateurs restent obligatoires.

## 8. Matrice de tests à construire

La matrice doit distinguer les versions cibles des versions réellement testées.

| Axe | Couverture minimale à organiser en P0 |
|---|---|
| Moodle | 4.5 LTS et versions 5.x ciblées, dont 5.3 si environnement disponible |
| Iomad | 4.5 LTS, 4.5 expérimentale, 5.0 et 5.1 ; 5.2 uniquement après publication et disponibilité d'un environnement installable |
| Navigateurs desktop | Firefox, Chrome/Chromium, Edge ; Safari si environnement disponible |
| Navigateurs mobiles | Chrome Android et Safari iOS |
| Écrans | 320, 390, 768 et 1440 CSS px comme points de contrôle |
| Profils | Apprenant, formateur, administrateur, gestionnaire Iomad |
| Accessibilité | axe-core + clavier + NVDA + TalkBack + zoom/reflow |
| Fonctionnel | Tâches représentatives des trois espaces |
| Sécurité | Capacités, contextes, URL directes, séparation entreprise |
| Performance | Chargement sur réseau mobile simulé et appareil réel |

Il faudra sélectionner une matrice réaliste : toutes les combinaisons ne sont pas nécessairement à exécuter à chaque commit. La CI peut couvrir les tests rapides et reproductibles ; les tests multi-versions, appareils réels et lecteurs d'écran seront organisés à des jalons définis.

## 9. Protocole de tests utilisateurs

Échantillon exploratoire initial :
- 5 apprenants ;
- 5 formateurs, avec niveaux d'expérience Moodle variés ;
- 2 à 3 administrateurs ou gestionnaires ;
- participation recherchée de personnes utilisant des technologies d'assistance.

Ce volume sert à identifier des problèmes d'usage, pas à produire une preuve statistique. Pour chaque tâche, consigner :
- réussite autonome, réussite avec aide ou échec ;
- temps de réalisation ;
- erreurs et retours en arrière ;
- demandes d'aide ;
- compréhension des libellés ;
- difficultés tactiles ou d'accessibilité ;
- appareil et navigateur utilisés.

Comparer à Moodle standard uniquement sur des tâches identiques et dans des conditions comparables.

## 10. CI et stratégie de tests automatisés

Décision à prendre en P0 après identification des environnements :
- lint PHP et vérification des conventions ;
- PHPUnit pour les classes et comportements testables ;
- Behat pour les parcours Moodle qui apportent une vraie valeur ;
- tests d'installation/validation du plugin ;
- axe-core sur les parcours et pages représentatifs, sans considérer le score comme une preuve complète ;
- contrôles de compilation SCSS/JavaScript selon les outils réellement utilisés par les versions ciblées.

La CI ne doit pas simuler une matrice complète qu'elle ne peut pas réellement installer. Les environnements, versions, dépendances et limites des tests doivent être explicités.

## 11. Décisions restant à prendre en P0

| ID | Décision | Méthode |
|---|---|---|
| D-P0-01 | Navigation mobile : tiroir Boost adapté ou navigation Project N dédiée | Prototype comparatif et tests clavier/tactiles |
| D-P0-02 | Étendue des layouts spécifiques | Cartographie des pages et prototype |
| D-P0-03 | Composants Moodle réutilisés / personnalisés | Inventaire par parcours et versions |
| D-P0-04 | Périmètre exact du MVP formateur | Tests de tâches et estimation |
| D-P0-05 | Tâches mobiles prioritaires administrateur | Entretiens et analyse des rôles |
| D-P0-06 | Environnements de test Iomad | Vérification des versions disponibles |
| D-P0-07 | CI et outils | Prototype reproductible |
| D-P0-08 | Licence et stratégie de publication | Vérification juridique et règles Moodle plugins |
| D-P0-09 | Nom définitif | Recherche d'antériorité et répertoire Moodle |
| D-P0-10 | Charte visuelle et typographie | Maquettes et vérification RGPD/licences |

## 12. Critères de sortie de P0

P0 ne sera terminé que lorsque :
1. le prototype enfant de Boost aura été installé et testé sur au moins Moodle 4.5 et une version 5.x récente ;
2. les impacts des évolutions Boost/Moodle entre versions auront été documentés ;
3. les parcours MVP des trois espaces auront des scénarios fonctionnels et des maquettes smartphone ;
4. les déclinaisons tablette et desktop auront été définies ;
5. les décisions de navigation et les surcharges de templates seront justifiées ;
6. les besoins Iomad et le protocole de test du cloisonnement seront définis ;
7. les critères d'accessibilité et la stratégie de test seront intégrés aux spécifications ;
8. la matrice de compatibilité et les environnements réellement disponibles seront documentés ;
9. le périmètre du MVP, les jalons et les estimations seront validés ;
10. les décisions ouvertes auront un statut, un responsable ou une méthode de résolution.

## 13. Références techniques initiales

- [Moodle Developer — Theme plugins (4.5)](https://moodledev.io/docs/4.5/apis/plugintypes/theme)
- [Moodle Developer — Layouts (4.5)](https://moodledev.io/docs/4.5/apis/plugintypes/theme/layout)
- [MoodleDocs — Creating a theme based on Boost](https://docs.moodle.org/dev/Creating_a_theme_based_on_boost)
- [MoodleDocs — Moving your theme to use Boost as a parent](https://docs.moodle.org/dev/Moving_your_theme_to_use_boost_as_a_parent_theme)
- [Boost upgrade notes](https://github.com/moodle/moodle/blob/main/public/theme/boost/UPGRADING.md)
- [Moodle Developer 5.3 — Theming React components](https://moodledev.io/docs/5.3/guides/javascript/react/theming) (à vérifier)
