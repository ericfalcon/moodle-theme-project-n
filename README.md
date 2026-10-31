# Project N — Thème Moodle

> Nom de travail. Le nom définitif sera choisi après vérification de disponibilité dans le répertoire des plugins Moodle et d'antériorité de marque.

**Identifiant technique provisoire :** `theme_projectn`
**Dépôt :** [ericfalcon/moodle-theme-project-n](https://github.com/ericfalcon/moodle-theme-project-n)
**Licence visée :** GNU GPL v3 ou ultérieure (à confirmer en P0, voir [ROADMAP.md](ROADMAP.md))

## Vision

Project N vise à proposer une expérience Moodle plus claire, moderne et cohérente. Le thème s'inspire de la simplicité d'usage de Chamilo (cours lisibles, navigation peu profonde, tableau de bord orienté actions), sans reproduire son interface ni ses limites.

Moodle reste le moteur pédagogique : cours, activités, ressources, rôles, capacités, formulaires et mécanismes natifs sont conservés. Le projet repense en priorité la navigation, la présentation et les parcours des trois profils : apprenant, formateur et administrateur.

Le thème est conçu pour **Moodle** et **Iomad**, avec une prise en compte d'Iomad dès l'architecture. Il est autonome et indépendant du thème Épure, et destiné à être publié dans le répertoire des plugins Moodle.

## Inspiration Chamilo : ce qu'on retient, ce qu'on évite

**Retenu comme principes :**
- une page d'accueil apprenant centrée sur « mes formations » et « ce que je dois faire maintenant » ;
- une page de cours lisible, avec peu de niveaux de navigation ;
- des actions principales visibles sans chercher dans les menus.

**Évité :**
- copier l'apparence ou les composants visuels de Chamilo (risque de confusion et de propriété intellectuelle) ;
- importer du code ou des assets Chamilo sans vérification de licence ;
- transposer le modèle de cours de Chamilo : on reste sur le modèle Moodle (sections, activités, ressources).

L'inspiration est donc sur les **parcours et la hiérarchie de l'information**, validée par des tests utilisateurs, et non sur le rendu graphique.

## Objectifs

- Simplifier l'accès aux fonctions utiles sans appauvrir les possibilités de Moodle.
- Donner à l'apprenant une vision lisible de ses formations, de sa progression et de ses prochaines actions.
- Guider le formateur dans la construction d'un cours tout en gardant un accès permanent aux réglages et fonctions avancés natifs.
- Regrouper les fonctions d'administration par domaines et faciliter le repérage des réglages.
- Garantir une expérience responsive et accessible.
- Préserver la compatibilité avec les mécanismes natifs Moodle et prendre en compte les spécificités d'Iomad.

## Principes de conception

1. **Moodle natif en priorité** : pas de moteur pédagogique parallèle ni de format de cours propriétaire.
2. **Guidage sans enfermement** : les parcours simplifiés ne masquent pas durablement les fonctions avancées.
3. **Accessibilité dès la conception** : chaque fonctionnalité est spécifiée, développée et validée avec des critères d'accessibilité.
4. **Sécurité et capacités natives** : les contrôles d'accès reposent sur les rôles, capacités et contextes Moodle ; aucune permission parallèle.
5. **Iomad pris en compte explicitement** : les adaptations multi-entreprises ne doivent jamais compromettre l'isolation des données.
6. **Progressivité** : préférer les composants et API stables de Moodle aux surcharges massives de templates.
7. **Autonomie** : le thème fonctionne sans plugin compagnon obligatoire.
8. **Sobriété** : limiter les dépendances, options et personnalisations à celles qui apportent une réelle valeur.
9. **Mesure par l'usage** : une simplification n'est retenue que si des utilisateurs réels la trouvent meilleure.

## Les trois espaces

### Apprenant
- Tableau de bord centré sur les formations suivies.
- Accès lisible aux cours et aux parcours.
- Progression, prochaines activités et échéances.
- Navigation cohérente entre sections, ressources et activités.
- Notifications et états vides compréhensibles.

### Formateur
- Création et organisation guidées, en s'appuyant sur les mécanismes natifs.
- Ajout d'activités et de ressources par intention pédagogique : transmettre, pratiquer, évaluer, collaborer.
- Organisation des sections et réutilisation des contenus.
- Accès direct aux participants, évaluations et corrections.
- Accès permanent aux paramètres avancés natifs.
- Prévisualisation du cours côté apprenant.

### Administrateur
- Centre de gestion organisé par grands domaines fonctionnels.
- Accès direct aux actions fréquentes, conditionné par les capacités réelles.
- Recherche et repérage des paramètres (en complément de la recherche native, pas en remplacement).
- Présentation thématique des réglages.
- Conservation des pages natives pour les formulaires, validations et contrôles de droits.

## Périmètre

- **Inclus** : interface web Moodle et Iomad, des versions 4.5 à 5.3.
- **Exclus** : application mobile Moodle (le thème n'a pas d'effet sur elle).

## Accessibilité et qualité

La cible est **WCAG 2.2 niveau AA**, avec prise en compte du **RGAA** et de la norme **EN 301 549** selon le périmètre applicable.

L'accessibilité est un critère d'acceptation à chaque étape, et non une phase de correction finale. La validation combine :
- analyse automatisée avec axe-core ;
- navigation complète au clavier et contrôle du focus ;
- vérification des contrastes, du zoom à 200 % et du reflow à 320 px ;
- tests avec lecteurs d'écran (NVDA, TalkBack) ;
- contrôle de la sémantique HTML et de l'usage d'ARIA ;
- tests des menus, dialogues, formulaires, notifications et états dynamiques.

Un résultat axe-core sans anomalie ne suffit pas à déclarer un parcours accessible. Les résultats et écarts résiduels sont documentés dans une déclaration d'accessibilité.

## Compatibilité visée

- **Moodle** : 4.5 à 5.3.
- **Iomad** : 4.5 à 5.3, selon les versions réellement disponibles et testables.

Une version n'est annoncée comme compatible que si elle a été effectivement testée. La matrice et le statut des tests sont suivis dans [ROADMAP.md](ROADMAP.md).

## Architecture envisagée

- Thème `theme_projectn`, avec choix du thème parent (Boost ou thème standalone) à documenter en P0.
- Plugin compagnon `local_projectn` uniquement si une fonction métier ne peut pas être réalisée proprement dans le thème ; il restera facultatif.
- Pas de format de cours propriétaire.
- Utilisation prioritaire des API, composants, capacités et mécanismes natifs.
- Adaptations Iomad conditionnelles, sans effet sur le fonctionnement Moodle standard.

## Publication

Le projet est destiné au répertoire des plugins Moodle. Les prérequis de publication (nom de composant unique, licence GPL compatible, documentation d'installation, versions déclarées dans `version.php`) sont suivis dans la roadmap, phase P7.

## État du projet

Le dépôt est en phase de cadrage. Aucune phase n'est terminée. Le plan de travail, les jalons et les décisions à prendre sont détaillés dans [ROADMAP.md](ROADMAP.md).
