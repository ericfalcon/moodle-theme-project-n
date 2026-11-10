# Project N — Feuille de route

Cette feuille de route organise le cadrage, la conception, le développement, la validation et la publication de `theme_projectn`. Elle intègre dès le départ trois exigences structurantes : **Boost comme thème parent, conception mobile-first et accessibilité à chaque étape**.

Une phase n'est considérée comme terminée que lorsque ses livrables et critères d'acceptation sont vérifiés et documentés. Les statuts ne doivent pas être avancés sans preuve.

## État d'avancement

| Phase | Objet | Périmètre cible | État |
|---|---|---|---|
| P0 | Cadrage, spécifications et décisions | MVP | En cours |
| P1 | Socle Boost, design system et premiers composants d'interface | MVP | En cours — navigation globale codée ; tableau de bord apprenant/enseignant intégré par surcharge ciblée de `main_content()` ; tests ajoutés mais non exécutés ; compilation et installation non vérifiées |
| P2 | Espace apprenant | MVP | En cours — première séparation des cours apprenant/enseignant ; progression, prochaine activité et états avancés à réaliser |
| P3 | Espace formateur | MVP progressif | À réaliser |
| P4 | Espace administrateur | Version 1.x, après cadrage de la valeur ajoutée | À réaliser |
| P5 | Adaptation Iomad | Fondations et cloisonnement dans le MVP | À réaliser |
| P6 | Stabilisation et validation | MVP / version 1.0 | À réaliser |
| P7 | Documentation et publication | Version 1.0 | À réaliser |

Aucune phase n'est déclarée terminée à ce stade.

## Décisions structurantes

| Sujet | Décision / orientation | Statut |
|---|---|---|
| Thème parent | Boost ; décision de principe retenue, détails d'héritage à documenter en P0 | Retenu |
| Approche ergonomique | Mobile-first pour les trois espaces, puis adaptation tablette/desktop | Retenu |
| Navigation | Approche hybride : navigation globale Project N et navigation contextuelle native Moodle | Retenu ; détails à prototyper dans [docs/P0-NAVIGATION.md](docs/P0-NAVIGATION.md) |
| Accessibilité | WCAG 2.2 AA, RGAA et EN 301 549 applicables ; validation continue | Retenu |
| CI | GitHub Actions : contrôles PHP, PHPUnit Moodle, validation plugin, compilation SCSS et axe-core sur navigateur ; compléter progressivement | En cours |
| Licence | GPL v3 ou ultérieure envisagée, à confirmer | À confirmer |
| Compatibilité | Moodle 4.5–5.3 ; Iomad 4.5–5.2 ; compatibilité réelle à établir par génération et tests exécutés | À vérifier |
| Points d'extension | Inventaire des API, composants, renderers, layouts et templates nécessaires | À documenter |
| Tests utilisateurs | Tâches types et profils apprenant, formateur, administrateur ; inclure autant que possible des personnes en situation de handicap | À définir |

---

## P0 — Cadrage, spécifications et décisions

Le dossier de travail est suivi dans [docs/P0-CADRAGE.md](docs/P0-CADRAGE.md). Il rassemble la première analyse Boost, les parcours prioritaires, les exigences mobile-first, la stratégie de validation et les décisions restant à instruire. La décision d'architecture de navigation est détaillée dans [docs/P0-NAVIGATION.md](docs/P0-NAVIGATION.md). Ces documents ne valent pas validation de compatibilité : les prototypes et tests sur environnements réels restent à réaliser. Le retour d'expérience du thème Épure est documenté dans [docs/P0-REX-EPURE-PROJECTN.md](docs/P0-REX-EPURE-PROJECTN.md) : l'inventaire a été complété sur les renderers de cours, tableaux de bord et la configuration, mais reste une analyse statique. La cartographie technique [docs/P0-CARTOGRAPHIE-NAVIGATION.md](docs/P0-CARTOGRAPHIE-NAVIGATION.md) a été enrichie jusqu'à la section 69. La synthèse fonctionnelle du point 66.22 (comptes utilisateurs et cohortes) est consignée dans [docs/P0-SYNTHESE-66.22-COMPTES-COHORTES.md](docs/P0-SYNTHESE-66.22-COMPTES-COHORTES.md) ; elle clôt la synthèse de la section 66 sans valoir validation runtime, accessibilité ou mobile. Les sections 65 à 68 couvrent l'espace formateur, les comptes/cohortes, les catégories/cours et les méthodes d'inscription ; leurs comparaisons ciblées Moodle/Iomad 4.5 à 5.2 sont désormais consignées, avec les rôles Iomad et les personnalisations du fork Iomad 5.0. La synthèse ergonomique du point 67.13 (gestion des catégories et cours, navigation mobile-first, capacités et actions) est consignée dans la cartographie ; elle ne vaut pas validation runtime, accessibilité ou responsive. La section 69 fixe la méthode de comparaison interversions. La section 70 documente le modèle Moodle des rôles, capacités, contextes, attributions et dérogations, ainsi que les comparaisons ciblées Moodle/Iomad ; la matrice exhaustive et les tests runtime restent à réaliser. Le registre vivant des versions et écarts est maintenu dans [docs/P0-MATRICE-COMPATIBILITE.md](docs/P0-MATRICE-COMPATIBILITE.md). Les corrections documentaires de cohérence (métrique des capacités, numérotation, statut des constats non revalidés et version courante d'Épure) sont consignées dans la matrice, la cartographie et le REX Épure. Les sections consacrées aux activités, aux contrats techniques et aux fichiers/éditeurs sont également documentées. Cette cartographie reste une analyse statique ; elle ne clôture pas P0. À la demande du porteur du projet, une construction P1 limitée a néanmoins commencé en parallèle sur `feature/p1-foundation` : socle du thème et premiers tokens du design system. Les choix dépendant encore de P0 restent explicitement provisoires et aucun parcours fonctionnel ne sera déclaré validé sans tests.

**Objectif :** transformer la vision en spécifications vérifiables et sécuriser les décisions structurantes.

### Priorité de la cartographie restante
- **Point 70 — rôles et permissions :** modèle Moodle, déclarations de capacités, rôles Iomad, workflow de gestion et restrictions entreprise documentés en analyse statique. Les corrections de la matrice distinguent désormais les comptages de clés de capacités et d'occurrences de lignes, et signalent comme non revalidés les constats sur l'état exact des forks, les personnalisations 5.0, le chemin sans entreprise courante, l'articulation Iomad de `CAP_PROHIBIT` et la tâche de réinitialisation des rôles. Les tests runtime restent à réaliser.
- **Point 71 — rapports d'administration et Report Builder :** architecture des rapports système et personnalisés, parcours d'édition, audiences, permissions, planifications et exports documentés ; comparaison Moodle 4.5–5.3 établie par SHA. Les sources Iomad et les rapports autonomes sont cartographiés. L'isolation des données par entreprise dans les sources Report Builder Iomad est un point de vérification runtime prioritaire, non une vulnérabilité démontrée.

La cartographie technique doit désormais privilégier **Moodle standard**. Les travaux déjà réalisés sur Iomad constituent une base suffisante pour suspendre l'approfondissement de son administration, sauf lorsqu'un écart Iomad conditionne directement un parcours Moodle ou une décision d'architecture.

Ordre de priorité pour la suite de P0 :
1. consolider la cartographie des parcours apprenant et formateur Moodle, en vérifiant les éventuels angles morts et les différences entre versions ; l'espace formateur a fait l'objet d'une première synthèse en section 65 ;
2. poursuivre l'analyse de l'administration Moodle standard : comptes utilisateurs/cohortes (section 66), catégories/cours (section 67), méthodes d'inscription (sections 68.1–68.18), rôles/capacités/contextes (section 70) et rapports d'administration/Report Builder (section 71) sont documentés. Reste notamment l'analyse des paramètres de plateforme et des autres domaines transversaux. Chaque tranche doit être comparée aux cinq générations Moodle 4.5 à 5.3 et consignée dans le registre de compatibilité ;
3. approfondir les mécanismes transversaux qui conditionnent les parcours : banque de questions, formulaires, notifications, navigation contextuelle et contrats techniques (capabilities, Output API, renderers, templates, AMD/JavaScript, événements et attributs DOM) ;
4. approfondir les écarts Iomad qui conditionnent directement les parcours Moodle : le contrôle d'accès aux cours, les rôles/contextes d'entreprise et les personnalisations du fork 5.0 sont identifiés ; les plugins et synchronisations Iomad restent à examiner par domaine, toujours à génération Moodle équivalente ;
5. maintenir distincts les constats issus de l'analyse statique et les validations qui nécessitent une installation, des scénarios runtime ou des tests d'accessibilité.

Cette priorité ne réduit pas le périmètre Iomad du projet : elle remet simplement Moodle natif au centre de l'analyse, conformément à l'objectif d'un thème Moodle compatible Iomad.

Livrables :
- cartographie des parcours pertinents de Chamilo et Moodle : principes ergonomiques retenus, éléments écartés ;
- parcours détaillés des trois espaces, avec priorités et tâches réalisables sur smartphone ;
- architecture de l'information et stratégie de navigation mobile, tablette et desktop ;
- inventaire des composants Boost/Moodle réutilisables et des points d'extension sur la matrice cible ;
- analyse de l'héritage Boost : layouts, templates, composants, contraintes et risques de surcharge ;
- architecture du thème et limites éventuelles d'un plugin compagnon facultatif ;
- matrice de compatibilité Moodle/Iomad et disponibilité réelle des environnements de test ;
- stratégie CI : lint, tests PHPUnit/Behat pertinents, contrôles de qualité et axe-core selon faisabilité ;
- protocole de tests utilisateurs avec tâches types et indicateurs : réussite, erreurs, demandes d'aide, temps et difficultés ;
- critères d'acceptation fonctionnels, techniques, sécurité, responsive et accessibilité ;
- maquettes mobile-first des trois espaces, puis déclinaisons tablette et desktop ;
- design system initial et règles de contenu ;
- estimation des jalons et définition précise du MVP.

Critères d'acceptation :
- les trois espaces sont décrits et leurs priorités mobiles explicites ;
- chaque parcours MVP dispose d'une maquette smartphone et d'une déclinaison grand écran ;
- le choix Boost et les surcharges envisagées sont justifiés techniquement ;
- les parcours restent fondés sur les mécanismes natifs Moodle ;
- le contexte Iomad et les risques de cloisonnement sont pris en compte dès la conception ;
- les exigences d'accessibilité sont intégrées aux spécifications et maquettes ;
- les versions cibles et environnements réellement testables sont distingués des simples objectifs.

## P1 — Socle Boost et design system responsive

**Avancement au 10 novembre 2026 :** le squelette du plugin et une première couche de tokens/primitives SCSS sont enregistrés sur `feature/p1-foundation`. Voir [docs/P1-SOCLE-TECHNIQUE.md](docs/P1-SOCLE-TECHNIQUE.md) et [docs/P1-DESIGN-SYSTEM.md](docs/P1-DESIGN-SYSTEM.md). La compilation SCSS, l'installation Moodle et les tests multi-versions restent à effectuer. La palette de marque et la navigation ne sont pas figées. La première navigation globale est maintenant codée sur `feature/p1-foundation` : hook de sortie, callback, fournisseur de données, template Mustache, chaînes EN/FR et styles responsive. Voir [docs/P1-NAVIGATION-GLOBALE.md](docs/P1-NAVIGATION-GLOBALE.md). Elle propose provisoirement Accueil et Formations aux utilisateurs connectés non invités. Le tableau de bord personnel distingue maintenant les formations apprenant et enseignant dans deux sections, à partir des rôles et capacités effectifs au niveau des cours. Son insertion passe par une surcharge ciblée du renderer Boost `main_content()`, sans copie du layout. Voir [docs/P1-ESPACE-APPRENANT.md](docs/P1-ESPACE-APPRENANT.md). Le code et les tests PHPUnit sont présents mais non exécutés ; l'installation, le rendu effectif et les tests multi-versions restent à faire. Le premier workflow GitHub Actions contrôle la syntaxe PHP sur plusieurs versions ; le workflow de tests installe désormais les deux plateformes depuis les forks GitHub, exécute PHPUnit sur la matrice de versions et axe-core sur Moodle 5.3/Iomad 5.2 ; les premiers runs restent à vérifier, est décrite dans [docs/P1-CI-TESTS.md](docs/P1-CI-TESTS.md).

**Objectif :** créer une base installable, maintenable et cohérente, conçue mobile-first.

Livrables :
- squelette installable `theme_projectn`, héritant de Boost ;
- `version.php`, langues et chaînes (anglais puis français) ;
- design tokens : couleurs, typographie, espacements, rayons, ombres et états ;
- composants Project N réutilisables ;
- layouts et navigation responsive ;
- comportements tactiles et clavier ;
- réglages de personnalisation limités aux besoins identifiés ;
- stratégie de surcharge sélective des templates Boost ;
- première configuration de CI si retenue en P0.

Critères d'acceptation :
- installation, activation, désactivation et mise à niveau vérifiées sur les premières versions de référence ;
- navigation utilisable sur smartphone, tablette et desktop ;
- aucune fonction dépendant d'un survol ;
- cibles tactiles conformes aux objectifs de conception ;
- pas de défilement horizontal global à 320 CSS px ;
- focus visible, sémantique et contrastes contrôlés ;
- contrôles automatisés et manuels documentés.

## P2 — Espace apprenant

**Objectif :** rendre le suivi des formations et les prochaines actions immédiatement compréhensibles.

Périmètre :
- tableau de bord centré sur les formations et actions à réaliser ;
- cartes et listes de cours adaptées aux petits écrans ;
- progression et prochaines activités ;
- échéances et notifications ;
- page de cours et navigation entre sections ;
- accès aux ressources et activités ;
- états vides, erreurs et messages d'aide ;
- présentation des contenus et activités sur smartphone.

Critères d'acceptation :
- informations conformes aux droits et inscriptions Moodle ;
- liens vers les prochaines actions exacts ;
- parcours testés sur smartphone réel, tablette et desktop ;
- tâches types testées auprès d'apprenants ; résultats documentés sans prétendre à une représentativité statistique avec un petit échantillon ;
- utilisabilité au clavier et avec lecteur d'écran ;
- états dynamiques perceptibles autrement que par la couleur seule ;
- aucune régression d'accessibilité connue non traitée.

