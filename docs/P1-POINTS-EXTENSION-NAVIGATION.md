# Project N — P1 : audit des points d'extension pour la navigation globale

**Statut :** analyse statique ciblée ; choix technique provisoire, non implémenté.  
**Date :** 10 novembre 2026  
**Branches de référence :** forks personnels `ericfalcon/moodle` et `ericfalcon/iomad`.

## 1. Question examinée

Comment afficher une navigation globale propre à Project N sans remplacer les navigations primaire/secondaire, le fil d'Ariane, l'index de cours ou les layouts Boost ?

L'analyse porte sur le renderer central, le hook de sortie situé au début du `body`, les vues primaire/secondaire et la configuration Boost. Elle ne constitue pas un audit exhaustif de tous les points d'extension Moodle.

## 2. Constats vérifiés

### 2.1 Boost fournit les layouts et le renderer hérités

Dans Moodle 4.5 et 5.3, `theme/boost/config.php` (sous `public/` à partir de 5.1) déclare notamment :

- les layouts Boost, dont `drawers.php` pour les pages standard, cours, catégories, administration et rapports ;
- `theme_overridden_renderer_factory` comme fabrique de renderers ;
- `usescourseindex = true` ;
- la prise en charge des tiroirs et des composants de navigation natifs.

Le `config.php` de Project N déclare Boost comme parent sans redéfinir les layouts. Cette stratégie laisse donc le parent fournir les layouts et les renderers tant que Project N n'a pas de besoin démontré de les remplacer.

**Conséquence :** ne pas créer un layout Project N complet uniquement pour afficher une barre de navigation. Une surcharge globale des layouts augmenterait fortement le coût de maintenance sur cinq générations Moodle.

### 2.2 Les vues primaire et secondaire sont des modèles du cœur

Les classes `core\navigation\views\primary` et `secondary` existent dans les branches Moodle 4.5 et 5.3 inspectées.

Elles construisent des vues distinctes à partir du contexte courant et des données de navigation Moodle. La vue secondaire sélectionne notamment les nœuds pertinents selon les contextes cours, module, catégorie et système. En Moodle 5.3, les templates de navigation primaire/secondaire s'intègrent à des composants React et fournissent leurs propres contrats de rendu et landmarks.

**Conséquence :** Project N ne doit pas modifier les classes du cœur, reconstruire ces arbres ni envelopper les composants de navigation 5.3 dans un landmark redondant. La navigation contextuelle reste la responsabilité de Moodle.

### 2.3 Un hook de sortie commun est présent

Le renderer central appelle `before_standard_top_of_body_html_generation` dans les quatre instantanés vérifiés :

| Dépôt / branche | Fichier | Constat |
|---|---|---|
| Moodle `MOODLE_405_STABLE` | `lib/classes/output/core_renderer.php` | Le hook est construit, les callbacks historiques sont traités, puis le hook est dispatché |
| Moodle `MOODLE_503_STABLE` | `public/lib/classes/output/core_renderer.php` | Même séquence générale |
| Iomad `IOMAD_405_STABLE` | `lib/classes/output/core_renderer.php` | Hook présent ; le renderer ajoute aussi les imports Iomad propres à cette branche |
| Iomad `IOMAD_502_STABLE` | `public/lib/classes/output/core_renderer.php` | Hook présent ; contexte Iomad adapté, séquence de sortie conservée |

La classe de hook `core\hook\output\before_standard_top_of_body_html_generation` est également présente dans les quatre générations inspectées. Elle expose le renderer et une méthode `add_html()` pour ajouter du HTML.

Le renderer appelle d'abord `$this->page->requires->get_top_of_body_code($this)`, puis ajoute le contenu de configuration `additionalhtmltopofbody` (hors layout embedded), puis traite et dispatche le hook. Le contenu du hook est ensuite renvoyé par `standard_top_of_body_html()`, avant l'ajout de l'avertissement de maintenance.

La callback historique `before_standard_top_of_body_html` est explicitement indiquée comme dépréciée depuis Moodle 4.4 dans la classe du hook. Elle ne doit donc pas être retenue comme nouvelle API.

**Conséquence :** un point d'injection au début du `body` est techniquement présent sur les branches Moodle/Iomad observées. Il pourrait accueillir un landmark de navigation globale, après le code de début de page (notamment les liens d'évitement) et avant le contenu du layout.

### 2.4 Limite importante du hook

Ce hook fournit un emplacement au début du `body`, pas un emplacement sémantique garanti à l'intérieur de l'en-tête Boost. Il ne permet donc pas, à lui seul, de garantir que la navigation Project N sera intégrée au même conteneur visuel que le logo, le menu utilisateur ou les notifications.

Il faut également confirmer le contrat exact d'enregistrement des callbacks du hook pour un composant de type thème, puis vérifier le chargement d'un template Mustache depuis le renderer fourni. Cette vérification n'est pas considérée comme acquise dans cette tranche.

## 3. Comparaison des options

| Option | Avantages | Risques / limites | Position |
|---|---|---|---|
| Surcharge de `drawers.php` ou des layouts Boost | Contrôle complet de la position | Copie de structure, divergences interversions, régressions de tiroirs et plugins | Écartée pour le premier prototype |
| Surcharge de `navbar.mustache` | Proximité de l'en-tête | Ce template est lié au rendu natif de la barre Boost ; surcharge sensible aux changements de contexte et de version | Non retenue à ce stade |
| Modification des vues primaire/secondaire | Réutilisation apparente des arbres natifs | Classes du cœur, contrats React/fallback et navigation contextuelle ; ne correspond pas au besoin de navigation globale indépendante | Écartée |
| Hook de sortie au début du `body` | Présent dans les instantanés Moodle/Iomad ; évite de copier les layouts | Position au début du body, pas dans le header ; enregistrement et rendu à valider | Candidat prioritaire à prototyper |
| Plugin compagnon local | Peut porter une logique métier et des services transversaux | Dépendance supplémentaire, alors que le thème doit rester autonome | À éviter sauf besoin démontré |

## 4. Orientation technique provisoire

Le premier prototype doit tester le hook de sortie au début du `body`, sans encore modifier les layouts Boost.

Principes :

1. Le callback ne doit produire qu'un conteneur de navigation globale Project N ; il ne reconstruit aucune navigation native.
2. Le rendu doit être confié à un template Mustache du thème, si le contrat d'enregistrement et le renderer le permettent.
3. La préparation des entrées doit être séparée du template et ne doit pas contenir de logique d'autorisation parallèle.
4. Les liens doivent provenir de destinations Moodle connues et les pages cibles restent responsables de leurs contrôles d'accès.
5. Les pages embedded, popup, login, maintenance, print et secure doivent être examinées individuellement avant d'afficher la navigation.
6. Le hook doit respecter l'ordre du début de page et ne pas passer avant les liens d'évitement.
7. Le prototype doit conserver la navigation primaire/secondaire Moodle, le fil d'Ariane, l'index du cours et les sorties de plugins.

Si le hook ne permet pas une position visuelle acceptable ou s'il est incompatible avec certains layouts, l'alternative sera une surcharge minimale et documentée, limitée aux layouts réellement nécessaires. Aucun layout ne sera copié avant d'avoir comparé les versions cibles.

## 5. Vérifications nécessaires avant implémentation

- [ ] Confirmer la convention d'enregistrement des callbacks de hooks dans un composant de type thème sur Moodle 4.5 et 5.3.
- [ ] Confirmer le chargement du callback et du template Mustache après installation, mise à niveau et purge des caches.
- [ ] Vérifier le rendu sur les layouts standard, course, admin, login, embedded, popup, maintenance, print et secure.
- [ ] Vérifier l'ordre avec les liens d'évitement, les annonces d'accessibilité, les messages et les sorties de plugins.
- [ ] Comparer le comportement Iomad 4.5, 5.0, 5.1 et 5.2, en particulier les pages de gestion d'entreprise.
- [ ] Vérifier l'absence de navigation globale sur les vues où elle serait inadaptée.
- [ ] Tester clavier, TalkBack/NVDA, axe-core, zoom 200 % et reflow à 320 CSS px.
- [ ] Tester le comportement mobile avec clavier virtuel et zones de sécurité des appareils.

## 6. Sources techniques consultées

Les chemins et branches sont ceux des forks personnels :

- [Moodle 4.5 — Boost config.php](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/theme/boost/config.php)
- [Moodle 4.5 — Boost lib.php](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/theme/boost/lib.php)
- [Moodle 4.5 — core_renderer.php](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/lib/classes/output/core_renderer.php)
- [Moodle 4.5 — hook de sortie](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/lib/classes/hook/output/before_standard_top_of_body_html_generation.php)
- [Moodle 4.5 — vue primaire](https://github.com/ericfalcon/moodle/blob/MOODLE_405_STABLE/lib/classes/navigation/views/primary.php)
- [Moodle 5.3 — core_renderer.php](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/lib/classes/output/core_renderer.php)
- [Moodle 5.3 — vue primaire](https://github.com/ericfalcon/moodle/blob/MOODLE_503_STABLE/public/lib/classes/navigation/views/primary.php)
- [Iomad 4.5 — core_renderer.php](https://github.com/ericfalcon/iomad/blob/IOMAD_405_STABLE/lib/classes/output/core_renderer.php)
- [Iomad 4.5 — hook de sortie](https://github.com/ericfalcon/iomad/blob/IOMAD_405_STABLE/lib/classes/hook/output/before_standard_top_of_body_html_generation.php)
- [Iomad 5.2 — core_renderer.php](https://github.com/ericfalcon/iomad/blob/IOMAD_502_STABLE/public/lib/classes/output/core_renderer.php)
- [Iomad 5.2 — hook de sortie](https://github.com/ericfalcon/iomad/blob/IOMAD_502_STABLE/public/lib/classes/hook/output/before_standard_top_of_body_html_generation.php)

**État de preuve :** comparaison statique des fichiers source. Aucun callback Project N n'a été enregistré, aucun template n'a été injecté et aucun test runtime ou accessibilité n'a été exécuté.
