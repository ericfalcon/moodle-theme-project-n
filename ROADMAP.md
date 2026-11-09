# Project N — Feuille de route

Cette feuille de route organise le cadrage, la conception, le développement, la validation et la publication de `theme_projectn`. Elle intègre dès le départ trois exigences structurantes : **Boost comme thème parent, conception mobile-first et accessibilité à chaque étape**.

Une phase n'est considérée comme terminée que lorsque ses livrables et critères d'acceptation sont vérifiés et documentés. Les statuts ne doivent pas être avancés sans preuve.

## État d'avancement

| Phase | Objet | Périmètre cible | État |
|---|---|---|---|
| P0 | Cadrage, spécifications et décisions | MVP | En cours |
| P1 | Socle Boost et design system responsive | MVP | À réaliser |
| P2 | Espace apprenant | MVP | À réaliser |
| P3 | Espace formateur | MVP progressif | À réaliser |
| P4 | Espace administrateur | Version 1.x, après cadrage de la valeur ajoutée | À réaliser |
| P5 | Adaptation Iomad | Fondations et cloisonnement dans le MVP | À réaliser |
| P6 | Stabilisation et validation | MVP / version 1.0 | À réaliser |
| P7 | Documentation et publication | Version 1.0 | À réaliser |

Aucune phase n'est déclarée terminée à ce stade.

## Jalons

| Jalon | Contenu | Critère de passage |
|---|---|---|
| J0 | Cadrage (P0) | Décisions structurantes, parcours, maquettes et matrice de tests documentés |
| J1 | Socle installable (P1) | Thème Boost installable et responsive, contrôles initiaux réussis |
| J2 | MVP fonctionnel (P2, P3 essentiel, fondations P5) | Parcours apprenant et formateur utilisables sur mobile et desktop ; tests Iomad et isolation validés |
| J3 | Version 1.0 publiable (P6, P7) | Matrice testée, accessibilité évaluée, documentation et paquet publiable prêts |
| J4 | Version 1.x | Espace administrateur enrichi et fonctions formateur avancées selon retours utilisateurs |

Les durées seront estimées à la fin de P0, une fois le périmètre MVP et les environnements disponibles confirmés.

## Décisions structurantes

| Sujet | Décision / orientation | Statut |
|---|---|---|
| Thème parent | Boost ; décision de principe retenue, détails d'héritage à documenter en P0 | Retenu |
| Approche ergonomique | Mobile-first pour les trois espaces, puis adaptation tablette/desktop | Retenu |
| Navigation | Approche hybride : navigation globale Project N et navigation contextuelle native Moodle | Retenu ; détails à prototyper dans [docs/P0-NAVIGATION.md](docs/P0-NAVIGATION.md) |
| Accessibilité | WCAG 2.2 AA, RGAA et EN 301 549 applicables ; validation continue | Retenu |
| CI | CI envisageable et à définir en P0 ; elle n'est pas exclue | À définir |
| Licence | GPL v3 ou ultérieure envisagée, à confirmer | À confirmer |
| Compatibilité | Moodle 4.5–5.3 ; Iomad 4.5–5.2 ; compatibilité réelle à établir par génération et tests exécutés | À vérifier |
| Points d'extension | Inventaire des API, composants, renderers, layouts et templates nécessaires | À documenter |
| Tests utilisateurs | Tâches types et profils apprenant, formateur, administrateur ; inclure autant que possible des personnes en situation de handicap | À définir |

---

## P0 — Cadrage, spécifications et décisions

Le dossier de travail est suivi dans [docs/P0-CADRAGE.md](docs/P0-CADRAGE.md). Il rassemble la première analyse Boost, les parcours prioritaires, les exigences mobile-first, la stratégie de validation et les décisions restant à instruire. La décision d'architecture de navigation est détaillée dans [docs/P0-NAVIGATION.md](docs/P0-NAVIGATION.md). Ces documents ne valent pas validation de compatibilité : les prototypes et tests sur environnements réels restent à réaliser. Le retour d'expérience du thème Épure est documenté dans [docs/P0-REX-EPURE-PROJECTN.md](docs/P0-REX-EPURE-PROJECTN.md) : l'inventaire a été complété sur les renderers de cours, tableaux de bord et la configuration, mais reste une analyse statique. La cartographie technique [docs/P0-CARTOGRAPHIE-NAVIGATION.md](docs/P0-CARTOGRAPHIE-NAVIGATION.md) a été enrichie jusqu'à la section 69. La synthèse fonctionnelle du point 66.22 (comptes utilisateurs et cohortes) est consignée dans [docs/P0-SYNTHESE-66.22-COMPTES-COHORTES.md](docs/P0-SYNTHESE-66.22-COMPTES-COHORTES.md) ; elle clôt la synthèse de la section 66 sans valoir validation runtime, accessibilité ou mobile. Les sections 65 à 68 couvrent l'espace formateur, les comptes/cohortes, les catégories/cours et les méthodes d'inscription ; leurs comparaisons ciblées Moodle/Iomad 4.5 à 5.2 sont désormais consignées, avec les rôles Iomad et les personnalisations du fork Iomad 5.0. La synthèse ergonomique du point 67.13 (gestion des catégories et cours, navigation mobile-first, capacités et actions) est consignée dans la cartographie ; elle ne vaut pas validation runtime, accessibilité ou responsive. La section 69 fixe la méthode de comparaison interversions. La section 70 documente le modèle Moodle des rôles, capacités, contextes, attributions et dérogations, ainsi que les comparaisons ciblées Moodle/Iomad ; la matrice exhaustive et les tests runtime restent à réaliser. Le registre vivant des versions et écarts est maintenu dans [docs/P0-MATRICE-COMPATIBILITE.md](docs/P0-MATRICE-COMPATIBILITE.md). Les sections consacrées aux activités, aux contrats techniques et aux fichiers/éditeurs sont également documentées. Cette cartographie reste une analyse statique ; elle ne clôture pas P0 et ne déclenche pas le développement.

**Objectif :** transformer la vision en spécifications vérifiables et sécuriser les décisions structurantes.

### Priorité de la cartographie restante
- **Point 70 — rôles et permissions :** contrôleurs natifs et premières comparaisons documentés ; analyse des déclarations de capacités Iomad 5.2 et des extensions d'archétypes ajoutée. Restent à comparer les déclarations sur Iomad 4.5–5.1, les capacités Moodle core et les permissions effectives en runtime. L'écart Iomad companycourseeditor sur moodle/role:switchroles est également relevé. L'analyse du calcul effectif, des rôles cumulés, des caches et des restrictions entreprise est documentée (sections 70.13 à 70.15). Les sept rôles Iomad et leurs contextes autorisés sont comparés sur les branches 4.5 à 5.2 (section 70.16). La section 70.17 décrit le workflow d'affectation, les rattachements départementaux et les restrictions de capacités, avec stabilité SHA confirmée entre Iomad 5.1 et 5.2 pour les composants étudiés ; les générations 4.5/5.0 et les scénarios runtime restent à vérifier. L'analyse du calcul natif, des caches, de l'origine des attributions et du contrôle complémentaire Iomad par entreprise est ajoutée ; les comparaisons interversions et tests runtime restent ouverts.

La cartographie technique doit désormais privilégier **Moodle standard**. Les travaux déjà réalisés sur Iomad constituent une base suffisante pour suspendre l'approfondissement de son administration, sauf lorsqu'un écart Iomad conditionne directement un parcours Moodle ou une décision d'architecture.

Ordre de priorité pour la suite de P0 :
1. consolider la cartographie des parcours apprenant et formateur Moodle, en vérifiant les éventuels angles morts et les différences entre versions ; l'espace formateur a fait l'objet d'une première synthèse en section 65 ;
2. poursuivre l'analyse de l'administration Moodle standard : les comptes utilisateurs/cohortes (section 66) et la gestion des catégories/cours (section 67) sont documentés ; les méthodes d'inscription sont documentées fonctionnellement et ergonomiquement en sections 68.1–68.18 ; restent notamment les rôles/capacités/contextes, les rapports d'administration et les paramètres de plateforme. Chaque tranche doit désormais être comparée aux cinq générations Moodle 4.5 à 5.3 et consignée dans le registre de compatibilité ;
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
- aucune dépendance à un survol pour accéder à une fonction ;
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

## P3 — Espace formateur

**Objectif :** simplifier la création et le pilotage d'un cours sans remplacer les outils Moodle.

Périmètre MVP :
- création guidée d'un cours ;
- organisation des sections ;
- ajout d'activités et ressources par intention pédagogique : transmettre, pratiquer, évaluer, collaborer ;
- accès aux paramètres natifs avancés à tout moment ;
- prévisualisation du cours côté apprenant ;
- parcours mobile pour les opérations simples et fréquentes.

Version 1.x :
- réorganisation avancée et réutilisation enrichie des contenus ;
- accès consolidé aux participants, évaluations et corrections ;
- amélioration des opérations complexes sur smartphone.

Les fonctions non intégrées au MVP restent accessibles par les pages natives Moodle lorsque les droits le permettent.

Critères d'acceptation :
- aucune fonction guidée ne crée un format de cours propriétaire ;
- les réglages natifs restent accessibles ;
- capacités et contextes Moodle respectés ;
- parcours testés avec plusieurs profils de formateurs ;
- ajout d'activités et organisation testés sur smartphone et desktop ;
- dialogues, sélecteurs, formulaires et interactions accessibles ;
- aucune opération n'est déclarée mobile-compatible sans test réel.

## P4 — Espace administrateur

**Objectif :** rendre l'administration plus repérable sans réécrire le cœur de Moodle.

Périmètre version 1.x :
- centre de gestion organisé par domaines ;
- raccourcis vers les actions fréquentes ;
- recherche et regroupements thématiques des réglages ;
- accès aux fonctions de gestion des utilisateurs, cours, plugins, rapports et maintenance ;
- renvoi vers les pages natives pour les formulaires, validations et contrôles de droits ;
- consultation mobile et accès aux opérations courantes.

Le lancement de cette phase dépendra d'une démonstration de valeur ajoutée par rapport à la recherche et à la navigation d'administration natives.

Critères d'acceptation :
- raccourcis conditionnés par les capacités effectives ;
- aucune donnée ni action exposée à un rôle non autorisé ;
- pages natives toujours accessibles ;
- parcours testés avec plusieurs profils de droits ;
- interface adaptée au smartphone, sans rendre les opérations complexes impraticables.

## P5 — Adaptation Iomad

**Objectif :** intégrer les spécificités multi-entreprises dès le MVP, sans dégrader Moodle standard.

Travaux anticipés :
- environnements Iomad de test identifiés dès P0 ;
- détection et points d'intégration conçus dès P1 ;
- fonctionnement Moodle standard garanti en l'absence d'Iomad.

Périmètre MVP :
- détection fiable de l'environnement Iomad ;
- prise en compte du contexte entreprise dans les écrans du MVP ;
- adaptation minimale des parcours et menus nécessaires aux rôles impliqués dans ces parcours ;
- accès aux fonctions de gestionnaire d'entreprise nécessaires au MVP ;
- tests dédiés du cloisonnement des données entre entreprises.

Version 1.x :
- enrichissement de l'espace gestionnaire d'entreprise et de ses raccourcis ;
- couverture élargie des fonctions spécifiques Iomad.

Critères d'acceptation :
- tests sur Iomad 4.5 LTS, 5.0, 5.1 et 5.2 dès qu'un environnement correspondant est disponible ; distinguer la branche expérimentale Iomad 4.5 de la branche LTS ;
- absence de fuite de données entre entreprises, vérifiée par des tests dédiés ;
- rôles, capacités et contextes contrôlés ;
- aucune dépendance Iomad dans le chemin d'exécution Moodle standard ;
- parcours Iomad du MVP validés sur smartphone et desktop ;
- écarts de compatibilité documentés.

## P6 — Stabilisation et validation

**Objectif :** consolider la qualité avant publication.

Périmètre :
- tests d'installation, mise à niveau et désinstallation sur la matrice retenue ;
- tests de régression Moodle et Iomad ;
- tests avec les activités natives et les plugins courants du périmètre ;
- revue de sécurité et des contrôles d'accès ;
- tests responsive sur appareils réels et navigateurs ciblés ;
- mesure des performances, notamment sur réseau mobile ;
- CI et tests automatisés si retenus en P0 ;
- axe-core et tests manuels d'accessibilité ;
- tests clavier, lecteurs d'écran, zoom, contraste, reflow et interactions tactiles ;
- tests utilisateurs de validation et correction des anomalies.

Critères d'acceptation :
- aucun défaut bloquant connu non traité ;
- compatibilités annoncées appuyées par des tests réels ;
- parcours principaux validés sur smartphone, tablette et desktop ;
- résultats axe-core analysés et anomalies pertinentes corrigées ;
- tests manuels documentés ;
- limites connues et écarts résiduels publiés.

## P7 — Documentation et publication

**Objectif :** rendre le thème installable, compréhensible et maintenable.

Livrables :
- vérification de l'unicité du nom de composant ;
- licence GPL compatible confirmée et en-têtes ajoutés ;
- `version.php` renseigné selon les versions effectivement supportées ;
- documentation d'installation, mise à jour et désinstallation ;
- guide administrateur ;
- guide formateur ;
- guide apprenant si nécessaire ;
- documentation Iomad et réglages ;
- déclaration d'accessibilité et limites connues ;
- documentation des tests et de la CI ;
- changelog et procédure de contribution ;
- paquet et première version stable publiables dans le répertoire des plugins Moodle.

Critères d'acceptation :
- documentation conforme à la version publiée ;
- installation vérifiée sur environnement vierge ;
- versions supportées et non supportées clairement indiquées ;
- procédure de signalement des anomalies disponible.

---

## Architecture et garde-fous

- Livrable principal : thème Moodle autonome `theme_projectn`, enfant de Boost.
- Un éventuel `local_projectn` ne sera créé que si une logique métier le justifie ; il ne sera jamais une dépendance obligatoire.
- Aucun format de cours propriétaire.
- API, composants, capacités, contextes et formulaires natifs privilégiés.
- Surcharges de templates sélectives, justifiées et documentées.
- Adaptations Iomad isolées et conditionnelles.
- Aucune logique d'autorisation parallèle aux capacités Moodle.
- Aucun code ou asset Chamilo/Épure copié sans vérification de licence et décision explicite.
- Le thème web mobile ne remplace pas l'application mobile Moodle.

## Qualité : validation à chaque étape

Chaque fonctionnalité est examinée selon cinq axes indissociables :

1. **Fonctionnel** — le parcours répond au besoin et respecte les comportements Moodle attendus.
2. **Technique** — le code suit les API et conventions Moodle, reste maintenable et compatible avec la matrice.
3. **Sécurité** — capacités, contextes, validations et données sont correctement protégés.
4. **Responsive et performance** — fonctionnement et confort d'usage sur smartphone, tablette et desktop, avec attention aux réseaux mobiles.
5. **Accessibilité** — WCAG 2.2 AA, RGAA et EN 301 549 applicables, vérifiés dès la conception.

La validation accessibilité combine axe-core, tests clavier, focus, contrastes, zoom à 200 %, reflow à 320 CSS px, lecteurs d'écran (NVDA et TalkBack), noms accessibles, annonces d'état et interactions tactiles. Un résultat axe-core sans anomalie ne suffit pas à déclarer un parcours accessible.

## Mesure par l'usage

Les tâches types sont rejouées aux jalons majeurs et comparées, lorsque cela est pertinent, à Moodle standard. Mesures : taux de réussite, temps de réalisation, erreurs, demandes d'aide et difficultés observées. Les tests couvrent apprenants, formateurs et administrateurs ; des utilisateurs en situation de handicap sont associés autant que possible. Les petits échantillons servent à détecter des problèmes d'usage, pas à revendiquer une validation statistique.

## Hors périmètre

- réécriture du moteur Moodle ;
- remplacement généralisé des formulaires et mécanismes natifs ;
- création d'un format de cours propriétaire ;
- dépendance obligatoire à un plugin compagnon ;
- modification de l'application mobile Moodle ;
- reproduction de l'interface ou reprise de code/assets Chamilo.

## Règle de mise à jour

La feuille de route est mise à jour à chaque évolution significative. Chaque tranche de cartographie validée doit être intégrée au dépôt avant de poursuivre l'analyse ; le numéro de section et son périmètre sont reportés ici lorsque cela modifie l'état ou l'ordre des travaux. Une phase ne passe à « Terminée » qu'après vérification de tous ses livrables et critères d'acceptation. Les éléments partiellement réalisés sont signalés comme tels, avec les preuves ou tests associés. Toute décision structurante est datée et justifiée.
