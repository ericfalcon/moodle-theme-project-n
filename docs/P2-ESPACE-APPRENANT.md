# P2 — Espace apprenant

> Document de conception et de suivi de réalisation. Les éléments non testés ne sont pas réputés implémentés ni compatibles.

## 1. Objectif et périmètre

P2 transforme le tableau de bord existant en un espace apprenant cohérent et complète le parcours dans le cours. Le thème organise l'information et facilite les accès ; Moodle reste responsable des inscriptions, disponibilités, achèvements, échéances, notifications, capacités et contrôles d'accès.

Incréments livrables :
1. **P2.1 — Tableau de bord** : cartes de formation, progression, prochaine activité et actions à venir ; fiabilisation des états vides et des données.
2. **P2.2 — Page de cours** : hiérarchie du cours, accès aux sections et activités, navigation native conservée, présentation mobile.
3. **P2.3 — Échéances et notifications** : intégration des notifications et événements Moodle natifs, sans stockage ni système parallèle.
4. **P2.4 — États et aide** : chargement, absence de contenu, activité indisponible, erreur et aide contextuelle.
5. **P2.5 — Validation** : parcours complets, tests automatisés et manuels, accessibilité et responsive.

## 2. Principes fonctionnels

- Les cours affichés proviennent des API d'inscription Moodle ; aucun cours n'est déduit d'une simple URL ou d'un rôle global.
- Les cours où l'utilisateur intervient comme formateur restent distingués des formations suivies comme apprenant. Un compte peut apparaître dans les deux espaces.
- Les activités et ressources sont affichées selon leur disponibilité et leur visibilité effectives.
- La progression repose sur l'achèvement Moodle, uniquement lorsque le suivi est activé. Une absence de suivi ne doit jamais être présentée comme 0 %.
- La prochaine activité est la première activité visible et non achevée selon l'ordre du cours ; son URL est construite à partir des données de module fournies par Moodle.
- Les échéances proviennent des API calendrier/actions Moodle. Une action non réalisable n'est pas présentée comme un lien actif.
- Les notifications restent celles de Moodle (messagerie, notifications et préférences). Le thème améliore leur repérage sans remplacer les fournisseurs natifs.
- Les liens vers les pages natives conservent leurs contrôles d'accès serveur. Masquer un lien n'est jamais une mesure de sécurité.

## 3. Parcours prioritaires

### A. Reprendre une formation
1. Depuis le tableau de bord, repérer une formation suivie.
2. Comprendre son avancement, si le suivi est activé.
3. Identifier l'action suivante lorsqu'une activité visible reste à réaliser.
4. Ouvrir l'activité dans Moodle.
5. Revenir au cours et retrouver sa structure.

### B. Retrouver une activité dans un cours
1. Ouvrir un cours depuis le tableau de bord ou la liste native.
2. Identifier les sections et leur disponibilité.
3. Parcourir les ressources et activités dans l'ordre pédagogique du cours.
4. Ouvrir une activité et utiliser la navigation contextuelle Moodle.
5. Conserver les repères lorsque l'index du cours est replié sur smartphone.

### C. Traiter une échéance
1. Repérer une échéance sur le tableau de bord.
2. Identifier le cours, la date et l'action attendue.
3. Ouvrir l'action uniquement si l'API Moodle la déclare actionnable et fournit une URL valide.
4. Sans URL, afficher l'information sans faux bouton.

## 4. Architecture technique

- Le tableau de bord reste un fournisseur de contexte (classes/output/learner_dashboard.php) et un template Mustache.
- La page de cours reste rendue par Moodle et son format de cours. P2 privilégie les API Output et les points d'extension documentés de Boost/Moodle ; aucune copie complète du layout ou du moteur de format de cours.
- Navigation primaire/secondaire, fil d'Ariane, index du cours, liens précédent/suivant et contrôles d'activité natifs sont conservés.
- Toute surcharge Mustache est limitée à un composant précis, justifiée par un besoin démontré et comparée entre Moodle 4.5 à 5.3. Les chemins public/ et changements de templates sont pris en compte.
- Iomad n'ajoute aucune dépendance au chemin Moodle standard. Aucune requête directe aux tables Iomad.
- Les fonctions d'affichage et de navigation ne contournent jamais les capacités et contextes Moodle.

## 5. Mobile-first et accessibilité

- Conception d'abord à 390 CSS px, vérification à 320, 768 et 1280 CSS px.
- Aucun défilement horizontal global ; traitement dédié des contenus intrinsèquement larges.
- Cibles tactiles visées : au moins 44 × 44 CSS px pour les commandes principales.
- Titres de liens explicites ; libellés d'action compréhensibles hors contexte.
- Progression sémantique avec nom accessible et valeur numérique.
- Les états ne reposent jamais sur la couleur seule.
- Ordre DOM, visuel et focus cohérents ; focus visible ; clavier complet.
- Menus et tiroirs utilisables au clavier et au tactile, sans survol obligatoire.
- Vérifications axe-core, clavier, zoom 200 %, reflow 320 CSS px, NVDA et TalkBack. Zéro violation axe-core sur les états testés est requis, mais ne suffit pas à conclure à l'accessibilité.

## 6. Matrice minimale de scénarios

| Scénario | Données / profil | Résultat attendu |
|---|---|---|
| Aucun cours | Apprenant sans inscription active | État vide explicite, aucun faux lien |
| Cours sans achèvement | Suivi désactivé | Pas de progression artificielle |
| Cours avec achèvement | Activités suivies, certaines terminées | Pourcentage cohérent avec les activités visibles et suivies |
| Prochaine activité | Activité visible non terminée | URL exacte de l'activité |
| Activité masquée | Activité non visible | Ni affichage ni lien |
| Cours inaccessible | Inscription suspendue ou accès expiré | Contrôle serveur conservé, accès non garanti |
| Échéance actionnable | Événement avec URL valide | Lien exact vers l'action |
| Échéance informative | Événement sans URL actionnable | Information sans faux bouton |
| Multi-rôle | Apprenant dans un cours, formateur dans un autre | Deux espaces distincts |
| Iomad multi-entreprise | Utilisateur dans un contexte entreprise | Aucune donnée hors périmètre |

## 7. Validation requise

Chaque incrément fournit :
- tests PHPUnit non vides et assertions pertinentes ;
- contrôles de syntaxe et validation du plugin ;
- tests Moodle 4.5 et 5.3, Iomad 4.5 et 5.2 à chaque PR ; matrice complète planifiée pour les versions intermédiaires ;
- axe-core sur les pages et états réellement concernés, à toutes les largeurs prévues ;
- tests manuels clavier, tactile et lecteur d'écran ;
- inspection des logs et artefacts CI, correction des échecs avant fusion.

La validation du tableau de bord ne vaut pas validation de la page de cours ni des notifications. Les parcours non couverts restent explicitement « à valider ».

### 7.1 Diagnostic du menu natif et fiabilité de l'audit navigateur — 13 novembre 2026

Le run GitHub Actions `43422808668` a exécuté PHPUnit avec succès sur Moodle 4.5, Moodle 5.3, Iomad 4.5 et Iomad 5.2. Les trois premiers audits navigateur ont ensuite échoué lors du clic sur `#user-menu-toggle` ; Iomad 5.2 a été annulé. Le DOM confirme que Moodle rend le template natif attendu (attribut `data-toggle` sur Bootstrap 4.6, `data-bs-toggle` sur Bootstrap 5), mais le menu ne reçoit pas l'état d'ouverture. La cause précise de l'absence d'initialisation Bootstrap reste à établir.

Le premier script d'audit ne sauvegardait son JSON qu'à la fin de tous les parcours : une exception de menu faisait perdre le rapport détaillé des audits précédents. Le commit `fb980051188b982eca7b286299f9fee65a565b10` corrige cette faiblesse et attend les modules AMD natifs `theme_boost/loader` et `core/usermenu` avant le clic. Le nouveau run `43424395033` doit confirmer si le problème provenait d'une course de chargement ou d'une initialisation réellement absente. **Ne pas déclarer le menu corrigé avant réussite du clic dans les quatre environnements.**

Les captures d'écran précédentes montrent aussi des violations `label-content-name-mismatch` détectées avant l'échec du menu. Elles doivent être examinées dans les rapports JSON partiels du nouveau run et attribuées précisément à un composant Project N ou Moodle/Iomad natif avant toute correction ou exclusion.


## 8. Suivi des incréments

| Incrément | État initial | Critère de clôture |
|---|---|---|
| P2.1 Tableau de bord | Première implémentation présente ; couverture partielle | Scénarios automatisés et testés sur les quatre environnements de référence |
| P2.2 Page de cours | À réaliser | Parcours sections/activités utilisable et accessible sur smartphone et desktop |
| P2.3 Échéances / notifications | Échéances calendrier présentes ; notifications non intégrées | API natives utilisées, préférences respectées, états et accès vérifiés |
| P2.4 États / aide | À compléter | États vides, erreurs, indisponibilités et aides testés |
| P2.5 Validation | Partielle | CI et tests manuels documentés, aucune anomalie bloquante non traitée |


## 9. Analyse technique P2.2 — page de cours

**État au 12 novembre 2026 : première couche CSS P2.2 ajoutée sur le DOM natif ; validation runtime encore en attente.** L'analyse statique des points d'entrée principaux est terminée. Le thème ne surcharge ni `course/view.php`, ni le renderer du format, ni les templates core. Les styles sont limités à `.path-course-view` et aux structures de sections/activités natives ; ils préservent l'ordre DOM et les contrôles Moodle. Les références ci-dessous sont les têtes de branches consultées le 12 novembre 2026.

### 9.1 Contrat de rendu natif

Les quatre branches de référence conservent le même enchaînement général :

1. `course/view.php` charge le cours, vérifie l'accès avec les mécanismes de la plateforme et initialise le contexte de page ;
2. le format réel du cours est obtenu avec `course_get_format($course)` ;
3. le renderer propre au format est préchargé avec `$format->get_renderer($PAGE)` ;
4. le contenu est rendu par le format actif et les classes Output de `core_courseformat`.

Le cœur du rendu des sections repose sur `core_courseformat\\output\\section_renderer`, les classes Output de `core_courseformat\\output\\local\\content` et les templates Mustache `core_courseformat/local/content/*`. Le renderer de section sait résoudre les templates par classe Output ; les formats peuvent fournir leurs propres classes et méthodes de rendu. Le thème ne doit donc ni reconstruire les sections, ni générer lui-même la liste des activités, ni remplacer le renderer du format.

### 9.2 Comparaison des sources consultées

| Source | Moodle 4.5 | Moodle 5.3 | Iomad 4.5 | Iomad 5.2 |
|---|---|---|---|---|
| `course/view.php` | Présent à la racine | Sous `public/` | Présent à la racine | Sous `public/` |
| `course/format/classes/output/section_renderer.php` | Présent | Présent sous `public/` | Présent | Présent sous `public/` |
| `course/format/classes/output/local/content/section.php` | Présent | Présent sous `public/` | Présent | Présent sous `public/` |
| Templates `course/format/templates/local/content/*` | Présents | Présents sous `public/` | Présents | Présents sous `public/` |

Les structures de rendu et les fichiers principaux existent sur les quatre branches. La comparaison du template `local/content/section.mustache` montre notamment une différence entre 4.5 et 5.3 : Moodle 5.3 ajoute l'attribut conditionnel `data-returnoptions` sur le conteneur de section. Les données d'exemple du template ajoutent également `headinglevel` dans l'en-tête. Ces évolutions confirment qu'une copie locale de template doit être évitée tant qu'un besoin visuel précis ne l'impose pas.

Les arbres Moodle 5.3 et Iomad 5.2 contiennent aussi des classes Output supplémentaires, notamment autour de l'activité chooser, des activités restreintes et de la vue d'ensemble. Le rendu du cours continue donc d'évoluer ; une surcharge structurelle large augmenterait le risque de divergence.

### 9.3 Écarts Iomad à préserver

Dans `course/view.php`, Iomad ajoute un contrôle spécifique avant la récupération du cours :
- Iomad 4.5 appelle `iomad::iomad_check_course(...)` ;
- Iomad 5.2 importe `local_iomad\\iomad` et appelle `iomad::iomad_check_course(...)`.

Ce contrôle intervient avant le chargement du cours et ne doit pas être remplacé ou contourné par le thème. Les contrôles natifs `require_login($course)`, capacités, disponibilité des sections et vérifications de visibilité doivent rester intacts. Aucun appel direct aux tables Iomad ne sera introduit dans Project N.

### 9.4 Décision d'architecture P2.2

La première tranche de développement sera **une amélioration CSS mobile-first du DOM natif**, sans surcharge de `course/view.php`, sans renderer de cours Project N et sans copie des templates core.

Ordre de réalisation :
1. relever les sélecteurs et structures DOM réellement produits par les formats natifs sur les quatre environnements ;
2. inventorier les formats installés et vérifier les comportements topics, weekly, single activity et tout format additionnel présent dans les forks ;
3. définir les règles responsive sans masquer les actions, restrictions, états d'achèvement, badges, menus ou contrôles d'édition ;
4. ajouter des tests navigateur avec un véritable compte apprenant inscrit à un cours, et non le seul compte administrateur ;
5. auditer `/course/view.php?id=...` avec activités visibles, activité masquée, section restreinte, suivi d'achèvement et index du cours, aux largeurs 320, 390, 768 et 1280 CSS px.

Une surcharge du template wrapper `core_courseformat/local/content/section` ne sera envisagée que si un besoin non réalisable par CSS est démontré. Elle devra être limitée à ce wrapper, comparée aux quatre versions de référence, et conserver les blocs Mustache de surcharge ainsi que les attributs et données requis par le JavaScript Moodle.

### 9.5 Références des forks au moment de l'analyse

- Moodle 4.5 — `ericfalcon/moodle@c0690bbc765fccacd4956452cbff3f6aacad77c5` (`MOODLE_405_STABLE`).
- Moodle 5.3 — `ericfalcon/moodle@236a9d702e62dc11f5aa66e4c8b5239cfc26c3d8` (`MOODLE_503_STABLE`).
- Iomad 4.5 — `ericfalcon/iomad@114ac90f25f74d79e4aae7bd72a749d8ab41a50e` (`IOMAD_405_STABLE`).
- Iomad 5.2 — `ericfalcon/iomad@88a9a3e9997916a049048db536048918cbdc1e56` (`IOMAD_502_STABLE`).

Cette analyse est statique. Elle ne démontre pas encore le rendu effectif dans un navigateur, la compatibilité de tous les formats tiers, ni l'accessibilité de la page de cours. Ces points restent des critères de validation P2.2.


## 10. Navigation mobile et menu utilisateur — 13 novembre 2026

Une première barre de navigation basse est ajoutée à la navigation globale existante. Elle propose quatre destinations natives : accueil, formations, calendrier et profil. Les liens sont calculés côté serveur avec `moodle_url`; l'entrée courante est signalée par `aria-current="page"`. Le menu ne crée aucun contrôle d'accès : les pages natives restent responsables des autorisations.

Sur les écrans de moins de 768 CSS px, la barre est fixe en bas, avec cibles tactiles d'au moins 44 CSS px, prise en compte de `safe-area-inset-bottom` et espace réservé dans le document. Elle revient dans le flux normal sur tablette et ordinateur. Elle se masque lorsque le focus est placé dans un champ de formulaire afin de ne pas recouvrir le clavier virtuel ou la saisie. Le comportement doit encore être confirmé sur appareils réels et avec lecteurs d'écran.

Le menu utilisateur natif Boost est conservé : Project N ne surcharge ni `core/user_menu` ni `navbar.mustache`. Les templates Moodle 4.5 et 5.3 utilisent des attributs Bootstrap différents (`data-toggle` / `data-bs-toggle`) et chargent tous deux le module AMD `core/usermenu`. Le test Playwright vérifie désormais, sur les pages auditées et à chaque largeur 320/390/768/1280, que le clic ouvre réellement `#user-action-menu`. En cas d'échec, le rapport fournit le HTML du déclencheur et du menu pour identifier la cause avant tout correctif spécifique.

**État :** barre basse implémentée dans le code ; tests PHPUnit et navigateur ajoutés. La fonctionnalité n'est pas déclarée validée tant que les tests de la tête courante ne sont pas terminés sur Moodle 4.5/5.3 et Iomad 4.5/5.2. Le défaut signalé sur le menu utilisateur reste à reproduire dans la CI avant de choisir un correctif.


## 11. Réutilisation des correctifs d'accessibilité d'Épure — 13 novembre 2026

Le dépôt de référence est `ericfalcon/moodle-theme_epure`, branche `main`, version 1.17.0 au moment de l'examen. Son pré-audit `docs/Pré-audit-d’accessibilité.md` recense les défauts observés sur les pages Moodle et Iomad, les critères WCAG/RGAA concernés, les correctifs apportés et les limites qui restent du ressort du cœur ou des contenus pédagogiques.

Le module `amd/src/aria_repair.js` d'Épure est porté dans Project N sous le nom `theme_projectn/aria_repair`, avec son fichier AMD compilé. Il traite les défauts de sémantique suivants, sans remplacer les composants ni leurs interactions natives :
- normalisation de `aria-hidden="1"` en valeur ARIA booléenne valide ;
- nettoyage des références inexistantes dans `aria-controls` ;
- nommage distinct des navigations `.moremenu` du site et de la page ;
- retrait du parcours clavier du bouton « Effacer mon choix » tant qu'il est masqué ;
- masquage aux technologies d'assistance du texte indicatif « Aucune sélection » dans une listebox d'autocomplétion vide ;
- rôle `separator` et valeur `aria-valuenow` pour la poignée de redimensionnement TinyMCE qui en est dépourvue.

Le renderer Project N charge le module via `full_header()`. Les chaînes « Menu du site » et « Menu de la page » sont disponibles en français et en anglais. L'audit Playwright contrôle les noms des navigations, les valeurs `aria-hidden` et les références `aria-controls` sur les pages et les quatre largeurs testées.

**Limite importante :** ce module ne répare pas le fonctionnement du menu utilisateur. Épure ne surcharge pas non plus le template natif `core/user_menu` ni le menu Boost pour en corriger l'ouverture ; il conserve l'initialisation native. Le défaut d'ouverture observé dans les tests Project N doit donc être diagnostiqué séparément, à partir des versions exactes de Bootstrap, des attributs de déclenchement et de l'initialisation AMD. Aucun correctif de menu ne doit être déduit du seul portage ARIA.

Références :
- Épure : `amd/src/aria_repair.js`, commit `89f89631de34abf862127f58af276ca90c75bf38` ;
- Épure : `docs/Pré-audit-d’accessibilité.md` ;
- Project N : `amd/src/aria_repair.js`, `amd/build/aria_repair.min.js`, `classes/output/core_renderer.php`.

Les résultats de la CI sur la tête courante restent requis avant de considérer ce portage comme validé sur les quatre plateformes.


## 12. Diagnostic du menu utilisateur et correction — 13 novembre 2026

Le run CI `43421421311` a terminé en échec sur les quatre environnements de référence. PHPUnit réussit sur les quatre ; l'étape navigateur échoue avant la production des rapports axe-core, car le menu utilisateur natif ne s'ouvre pas.

La cause est l'override local `templates/core/user_menu.mustache` : il imposait `data-bs-toggle` et l'API statique `Dropdown.getOrCreateInstance()` de Bootstrap 5 à tous les environnements. Moodle/Iomad 4.5 utilisent Bootstrap 4.6.2, dont le déclencheur est `data-toggle="dropdown"` et dont le module AMD ne fournit pas `getOrCreateInstance()`. L'initialisation personnalisée empêchait donc l'ouverture du menu. En outre, le template natif de chaque version porte déjà les attributs Bootstrap adaptés à sa génération.

**Correction appliquée :** suppression de l'override `templates/core/user_menu.mustache`. Project N revient au template natif Moodle/Boost de la version installée, qui conserve l'identifiant `user-menu-toggle`, les attributs Bootstrap corrects et l'initialisation `core/usermenu`. Le thème ne duplique plus l'initialisation du dropdown.

Commit : `84d864bd7eddf69fee4c64db2011b25752dc4f85` — `Restore native Moodle user menu across Bootstrap generations`.

**Validation attendue :** nouvelle exécution CI sur les quatre environnements ; vérifier l'ouverture et la fermeture du menu, l'état `aria-expanded`, les sous-menus, puis obtenir les rapports axe-core. Cette correction statique n'est pas encore considérée comme validée tant que ces jobs ne sont pas terminés.

## 7. Correctif du menu utilisateur — 14 novembre 2026

L'audit axe-core du commit `fed645c` a identifié `label-content-name-mismatch` sur `#user-menu-toggle`, sur les cinq parcours et les quatre largeurs. Le nom accessible générique « User menu » ne reprenait pas le texte visible du bouton (initiales ou nom du compte).

Correctifs appliqués sur `feature/p2-learner-workspace` :
- `amd/src/aria_repair.js` inclut désormais le texte visible de `.userbutton` dans le `aria-label` existant du déclencheur, sans remplacer le libellé traduit ;
- `amd/build/aria_repair.min.js` est synchronisé avec le module source ;
- la double initialisation du module depuis `core_renderer::full_header()` et le hook de sortie a été supprimée ; le hook reste le point d'entrée unique ;
- le test Playwright vérifie que le nom accessible contient le texte visible et attend la réparation avant l'assertion ;
- la version de développement est passée à `2026111400`.

**État de validation : non confirmé.** Le run antérieur `43424584187` exécutait bien PHPUnit sur les quatre plateformes, mais axe-core échouait sur le défaut du menu. Les runs déclenchés après le correctif (`43639565637`, `43639724706` et leurs contrôles qualité) se sont terminés avant la première étape de chaque job : aucun log d'exécution ou résultat PHPUnit/axe-core n'est disponible. Le correctif est donc présent dans la branche, mais il ne doit pas être déclaré validé tant qu'une exécution réelle de la matrice n'a pas terminé.

## 8. Formats de cours natifs — comparaison Moodle 4.5 / 5.3

Inspection des forks `ericfalcon/moodle`, branches `MOODLE_405_STABLE` et `MOODLE_503_STABLE`, le 14 novembre 2026.

### 8.1 Formats à sections

Le premier style P2.2 cible les structures natives `.course-section`, `.section-item` et `.activity-item`, sans remplacer les templates de format. Les listes `topics`, `weeks` et `ctopics` sont neutralisées visuellement uniquement pour éviter la double présentation des puces ; leur ordre DOM et les contrôles natifs sont conservés.

Les tests doivent vérifier au minimum :
- format thématique (`topics`) ;
- format hebdomadaire (`weeks`) ;
- sections masquées et activités masquées ;
- sections restreintes par disponibilité, pour un apprenant qui ne remplit pas les conditions puis pour un apprenant qui les remplit ;
- édition activée, afin de vérifier les menus d'action et le sélecteur d'activité.

Le CSS ne doit jamais rendre visibles des éléments que Moodle ne rend pas visibles au profil courant, ni neutraliser les états d'indisponibilité.

### 8.2 Format activité unique

Le format natif `singleactivity` ne doit pas être traité comme un format à sections ordinaire :
- sur Moodle 4.5, `format_singleactivity` expose son activité par sa logique interne `get_activity()` et ne possède pas de page de cours classique (`has_view_page() === false`) ;
- sur Moodle 5.3, la classe implémente en plus `core_courseformat\main_activity_interface` et expose `get_main_activity(): ?\cm_info`. Elle indique également `uses_sections() === false` et `get_sectionnum() === 0`.

Conséquence pour Project N : ne pas ajouter de renderer ou de template spécifique au thème pour ce format. Le format et Moodle doivent conserver la responsabilité de l'activité principale, de la redirection et de ses contrôles d'accès. Les styles `.path-course-view .course-content .course-section` ne doivent pas imposer de structure ni masquer le contenu de l'activité principale.

Test d'intégration à ajouter : créer un cours `singleactivity` configuré avec une activité principale de type Page, l'inscrire à un apprenant, puis vérifier que `/course/view.php?id=…` aboutit au parcours natif attendu sur Moodle 4.5 et 5.3. Contrôler aussi le comportement lorsque l'activité principale est absente ou inaccessible. Ne pas fabriquer artificiellement une page de cours si Moodle redirige vers l'activité.

### 8.3 Différence d'API à ne pas masquer

Le contrat `core_courseformat\main_activity_interface` est présent dans la classe du format activité unique Moodle 5.3 mais pas dans la version 4.5 examinée. Toute détection de l'activité principale dans le thème doit donc passer par les API de format disponibles dans la version installée, et non appeler directement cette interface sur Moodle 4.5.

### 8.4 État

- [x] Comparaison statique des implémentations `singleactivity` Moodle 4.5 et 5.3.
- [x] Décision de conserver le renderer et la navigation natifs.
- [ ] Ajouter un scénario d'installation et de parcours `singleactivity` à la CI.
- [ ] Ajouter les scénarios d'apprenant avec et sans disponibilité de section.
- [ ] Vérifier les formats supplémentaires éventuellement utilisés par les clients Iomad, sans les supposer présents dans Moodle standard.
