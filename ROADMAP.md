# Project N — Feuille de route

Cette feuille de route décrit les étapes de conception, de développement, de validation et de publication du thème `theme_projectn`. Une phase n'est considérée comme terminée que lorsque ses livrables et critères d'acceptation sont vérifiés et documentés.

## État d'avancement

| Phase | Objet | Dans le MVP | État |
|---|---|---|---|
| P0 | Cadrage, spécifications et décisions | Oui | À réaliser |
| P1 | Socle du thème | Oui | À réaliser |
| P2 | Espace apprenant | Oui | À réaliser |
| P3 | Espace formateur | Partiel (création guidée et prévisualisation) | À réaliser |
| P4 | Espace administrateur | Non (version 1.x) | À réaliser |
| P5 | Adaptation Iomad | Oui (détection et cloisonnement) | À réaliser |
| P6 | Stabilisation et validation | Oui | À réaliser |
| P7 | Documentation et publication | Oui | À réaliser |

Aucune phase n'est déclarée terminée à la création de cette feuille de route.

## Jalons

| Jalon | Contenu | Critère de passage |
|---|---|---|
| J0 | Fin de P0 | Décisions D1 à D6 tranchées et documentées |
| J1 | Socle installable (P1) | Installation et mise à niveau vérifiées sur Moodle 4.5 et 5.3 |
| J2 | MVP fonctionnel (P2, P3 partiel, P5) | Parcours apprenant et création guidée testés avec plusieurs rôles, Iomad inclus |
| J3 | Version 1.0 publiable (P6, P7) | Matrice de tests complète, documentation, déclaration d'accessibilité |
| J4 | Version 1.x (P4 et suites) | Espace administrateur, après retour des utilisateurs du MVP |

Les durées ne sont pas fixées à ce stade. Elles seront estimées à la fin de P0, une fois le périmètre figé.

## Décisions à prendre

| Id | Sujet | Recommandation | Statut |
|---|---|---|---|
| D1 | Thème parent (Boost ou standalone) | Boost, pour limiter la surcharge et hériter des composants stables | À trancher |
| D2 | Intégration continue | Mettre en place une CI minimale (lint PHP, PHPUnit, Behat, axe-core) avant J1 : la matrice 4.5–5.3 × Iomad n'est pas soutenable à la main | À trancher |
| D3 | Licence | GPL v3 ou ultérieure, exigée par la compatibilité avec Moodle et le répertoire | À confirmer |
| D4 | Environnements Iomad de test | Identifier les versions Iomad 4.5 à 5.3 réellement disponibles et les installer en test | À trancher |
| D5 | Points d'extension retenus | Lister, par écran cible, les hooks, renderers et API de navigation utilisés, et justifier toute surcharge de template | À documenter en P0 |
| D6 | Protocole de tests utilisateurs | Définir quelques tâches types (trouver ses prochaines activités, créer une section, trouver un réglage) et un échantillon d'apprenants, de formateurs et d'administrateurs | À définir en P0 |

---

## P0 — Cadrage, spécifications et décisions

**Objectif :** transformer la vision en spécifications vérifiables et trancher les décisions structurantes.

Livrables :
- cartographie des parcours pertinents de Chamilo et de Moodle, avec ce qui est retenu (principes, hiérarchie de l'information) et ce qui ne l'est pas (rendu visuel, code) ;
- parcours détaillés apprenant, formateur et administrateur ;
- principes de navigation et architecture de l'information ;
- inventaire des composants et points d'extension Moodle 4.5 à 5.3 (D5) ;
- décisions D1 à D4 documentées ;
- matrice de compatibilité Moodle/Iomad et stratégie de tests ;
- protocole de tests utilisateurs (D6) ;
- critères d'acceptation fonctionnels, techniques, sécurité et accessibilité ;
- premières maquettes et règles de design system ;
- estimation de durée par jalon.

Critères d'acceptation :
- les trois espaces sont décrits sans ambiguïté ;
- les parcours restent réalisables avec les mécanismes natifs Moodle ;
- les limites entre thème, plugin compagnon et Iomad sont explicites ;
- les risques de surcharge de templates et de rupture de compatibilité sont identifiés ;
- aucune reproduction d'éléments visuels ou de code Chamilo sans vérification de licence ;
- les exigences d'accessibilité sont intégrées aux spécifications.

## P1 — Socle du thème

**Objectif :** mettre en place une base technique et visuelle robuste, installable sur le répertoire des plugins.

Livrables :
- squelette installable `theme_projectn` avec `version.php`, langue et chaînes en anglais puis en français ;
- déclaration des versions Moodle et Iomad prises en charge ;
- identité visuelle, design tokens, typographie, espacements et couleurs ;
- composants de base cohérents et réutilisables ;
- réglages de personnalisation limités aux besoins identifiés ;
- gabarits et rendus responsives ;
- états de focus, survol, sélection, désactivation et erreur.

Critères d'acceptation :
- installation, activation, désactivation et mise à niveau vérifiées sur Moodle 4.5 et 5.3 ;
- absence de régression sur les pages Moodle couvertes ;
- navigation clavier et focus visibles ;
- contrôles axe-core et vérifications manuelles documentés.

## P2 — Espace apprenant

**Objectif :** rendre le suivi des formations immédiatement compréhensible.

Périmètre :
- tableau de bord et accès aux formations ;
- cartes et listes de cours ;
- progression et prochaines activités ;
- échéances et notifications ;
- page de cours et navigation entre sections ;
- accès aux ressources et activités ;
- états vides, erreurs et messages d'aide.

Critères d'acceptation :
- les informations affichées respectent les droits et inscriptions Moodle ;
- les liens vers les prochaines actions sont exacts et utilisables ;
- les parcours sont utilisables sur mobile, au clavier et avec lecteur d'écran ;
- les états dynamiques sont perceptibles sans dépendre uniquement de la couleur ;
- les tâches types du protocole D6 sont réussies par au moins 5 apprenants, avec un taux d'achèvement et un temps mesurés et comparés à Moodle standard.

## P3 — Espace formateur

**Objectif :** simplifier la création et le pilotage d'un cours sans remplacer les outils Moodle.

Périmètre du MVP :
- création guidée d'un cours et organisation des sections ;
- ajout d'activités et ressources par intention pédagogique : transmettre, pratiquer, évaluer, collaborer ;
- prévisualisation du cours côté apprenant.

Hors MVP (version 1.x) :
- réorganisation avancée et réutilisation des contenus ;
- accès consolidé aux participants et aux corrections.

Ces fonctions restent accessibles via les pages natives dans le MVP.

Critères d'acceptation :
- aucune fonction guidée ne crée un format de cours propriétaire ;
- les réglages natifs restent accessibles à tout moment ;
- les capacités Moodle sont respectées dans chaque action ;
- les parcours de création et de modification sont testés avec au moins trois rôles ;
- l'accessibilité est vérifiée sur les dialogues, sélecteurs, formulaires et interactions.

## P4 — Espace administrateur

**Objectif :** rendre l'administration plus repérable sans réécrire le cœur de Moodle.

Périmètre (version 1.x) :
- centre de gestion organisé par domaines ;
- raccourcis vers les actions fréquentes ;
- regroupements thématiques des réglages ;
- renvoi vers les pages natives pour les formulaires, validations et contrôles de droits.

Justification à documenter avant de démarrer : ce que le centre de gestion apporte par rapport à la recherche d'administration native.

Critères d'acceptation :
- les raccourcis sont conditionnés par les capacités effectives ;
- aucune information ni action n'est exposée à un rôle non autorisé ;
- les pages natives restent accessibles ;
- les parcours administratifs sont testés avec plusieurs profils de droits.

## P5 — Adaptation Iomad

**Objectif :** intégrer les spécificités multi-entreprises sans dégrader Moodle standard.

Note : les environnements de test Iomad (D4) sont mis en place dès P0 et les points de détection sont conçus en P1, même si l'implémentation complète intervient ici.

Périmètre du MVP :
- détection fiable de l'environnement Iomad ;
- maintien d'un fonctionnement normal lorsque Iomad est absent ;
- prise en compte du contexte entreprise dans les écrans du MVP ;
- vérification du cloisonnement des données entre entreprises.

Hors MVP :
- adaptation des menus selon les rôles de gestionnaire d'entreprise.

Critères d'acceptation :
- tests sur chaque version Iomad de 4.5 à 5.3 réellement disponible ;
- absence de fuite de données entre entreprises, vérifiée par des tests dédiés ;
- vérification des rôles, capacités et contextes ;
- aucune dépendance Iomad dans le chemin d'exécution Moodle standard ;
- les écarts de compatibilité sont documentés.

## P6 — Stabilisation et validation

**Objectif :** consolider la qualité avant publication.

Périmètre :
- tests d'installation, mise à niveau et désinstallation sur chaque version de la matrice ;
- tests de régression sur la matrice complète Moodle 4.5–5.3 et Iomad disponibles ;
- tests avec les activités et plugins courants (au moins les activités natives et les plugins les plus utilisés) ;
- revue de sécurité et des contrôles d'accès ;
- contrôle responsive, performance et robustesse ;
- audit axe-core et tests manuels d'accessibilité ;
- correction et documentation des anomalies.

Critères d'acceptation :
- aucun défaut bloquant connu non traité ;
- résultats axe-core analysés et anomalies pertinentes corrigées ;
- tests clavier, lecteurs d'écran, contraste, zoom et reflow réalisés ;
- compatibilités annoncées appuyées par des tests réels ;
- limites connues et écarts résiduels publiés.

## P7 — Documentation et publication

**Objectif :** rendre le projet publiable dans le répertoire des plugins Moodle, installable et maintenable.

Prérequis de publication :
- nom de composant unique vérifié dans le répertoire ;
- licence GPL v3 ou ultérieure (D3), avec en-têtes de licence dans les fichiers ;
- `version.php` renseigné avec les versions Moodle minimales et maximales ;
- respect des standards de codage Moodle, vérifiés par la CI (D2) ;
- documentation d'installation et de mise à jour ;
- guide administrateur, guide formateur, et guide apprenant si nécessaire ;
- documentation des réglages et de la compatibilité Iomad ;
- déclaration d'accessibilité et limites connues ;
- changelog et procédure de contribution ;
- première version stable publiée.

Critères d'acceptation :
- documentation cohérente avec la version publiée ;
- procédure d'installation vérifiée sur environnement Moodle vierge, pour chaque version déclarée ;
- versions supportées et non supportées clairement indiquées ;
- procédure de signalement des anomalies disponible.

---

## Architecture et garde-fous

- Le livrable principal est un thème Moodle autonome : `theme_projectn`.
- Un éventuel `local_projectn` ne sera créé que si une logique métier le justifie ; il ne sera pas une dépendance obligatoire.
- Aucun format de cours propriétaire n'est prévu.
- Les API, composants, capacités, contextes et formulaires natifs sont privilégiés.
- Les surcharges massives de templates sont évitées ; toute surcharge est justifiée et documentée.
- Les adaptations Iomad sont isolées et conditionnelles.
- Aucune logique d'autorisation parallèle aux capacités Moodle.
- Aucun code ni asset issu de Chamilo ou d'Épure n'est copié sans vérification de licence et décision écrite.

## Qualité : validation à chaque étape

Chaque fonctionnalité est examinée selon quatre axes indissociables :

1. **Fonctionnel** — le parcours répond au besoin et respecte les comportements Moodle attendus.
2. **Technique** — le code suit les API et conventions Moodle, reste maintenable et compatible avec la matrice.
3. **Sécurité** — les capacités, contextes, validations et données sont correctement protégés.
4. **Accessibilité** — les exigences WCAG 2.2 AA, RGAA et EN 301 549 applicables sont vérifiées dès la conception.

La validation accessibilité combine axe-core, tests clavier, contrôle du focus, contrastes, zoom à 200 %, reflow à 320 px CSS, lecteurs d'écran (NVDA et TalkBack), et contrôle des noms accessibles et des annonces d'état.

Un résultat axe-core sans anomalie ne suffit pas à déclarer un parcours accessible.

## Mesure par l'usage

Une simplification est retenue si elle est vérifiée par des utilisateurs. Les tâches types (D6) sont rejouées à chaque jalon majeur (J2, J3) et comparées à Moodle standard sur le temps de réalisation et le taux de réussite.

## Hors périmètre à ce stade

- réécriture du moteur Moodle ;
- remplacement généralisé des formulaires et mécanismes natifs ;
- création d'un format de cours propriétaire ;
- dépendance obligatoire à un plugin compagnon ;
- application mobile Moodle ;
- reproduction de l'interface de Chamilo.

## Règle de mise à jour

La feuille de route est mise à jour à chaque évolution significative. Une phase ne passe à « Terminée » qu'après vérification de tous ses livrables et critères d'acceptation. Les éléments partiellement réalisés sont indiqués comme tels, avec les preuves ou tests associés. Une décision D est close par une mise à jour de cette section, avec sa date et sa justification.
