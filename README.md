# Project N — Thème Moodle

> Nom de travail du projet. Le nom définitif sera choisi après vérification de disponibilité et d'antériorité.

**Identifiant technique provisoire :** `theme_projectn`  
**Dépôt :** [ericfalcon/moodle-theme-project-n](https://github.com/ericfalcon/moodle-theme-project-n)

## Vision

Project N vise à proposer une expérience Moodle plus claire, moderne et cohérente, en s'inspirant de la simplicité d'usage et de la présentation de Chamilo, sans en reproduire les limites.

Moodle reste le moteur pédagogique : ses cours, activités, ressources, rôles, capacités, formulaires et mécanismes natifs sont conservés. Le projet repense prioritairement la navigation, la présentation et les parcours des trois principaux profils : apprenant, formateur et administrateur.

Le projet est conçu d'abord pour Moodle, avec une prise en compte d'Iomad dès l'architecture. Il est autonome et indépendant du thème Épure.

## Objectifs

- Simplifier l'accès aux fonctions utiles sans appauvrir les possibilités de Moodle.
- Donner à l'apprenant une vision lisible de ses formations, de sa progression et de ses prochaines actions.
- Guider le formateur dans la construction d'un cours tout en lui laissant un accès permanent aux réglages et fonctions avancés natifs.
- Regrouper les fonctions d'administration par thèmes et faciliter l'accès aux réglages.
- Garantir une expérience responsive et accessible.
- Préserver la compatibilité avec les mécanismes natifs Moodle et anticiper les particularités d'Iomad.

## Principes de conception

1. **Moodle natif en priorité** : pas de moteur pédagogique parallèle ni de format de cours propriétaire par défaut.
2. **Guidage sans enfermement** : les parcours simplifiés ne masquent pas durablement les fonctions avancées.
3. **Accessibilité dès la conception** : chaque fonctionnalité est spécifiée, développée et validée avec des critères d'accessibilité.
4. **Sécurité et capacités natives** : les contrôles d'accès reposent sur les rôles, capacités et contextes Moodle ; aucune permission parallèle.
5. **Iomad pris en compte explicitement** : les adaptations multi-entreprises ne doivent jamais compromettre l'isolation des données.
6. **Progressivité** : préférer les composants et API stables de Moodle aux surcharges massives de templates.
7. **Autonomie** : le thème doit fonctionner sans plugin compagnon obligatoire.
8. **Sobriété** : limiter les dépendances, options et personnalisations à celles qui apportent une réelle valeur.

## Les trois espaces

### Apprenant

- Tableau de bord centré sur les formations suivies.
- Accès lisible aux cours et aux parcours.
- Progression, prochaines activités et échéances.
- Navigation cohérente entre sections, ressources et activités.
- Notifications et états vides compréhensibles.

### Formateur

- Création et organisation guidées en s'appuyant sur les mécanismes natifs Moodle.
- Ajout d'activités et de ressources par intention pédagogique : transmettre, pratiquer, évaluer, collaborer.
- Organisation des sections et réutilisation des contenus.
- Accès direct aux participants, évaluations et corrections.
- Accès permanent aux paramètres avancés natifs.
- Prévisualisation du cours côté apprenant.

### Administrateur

- Centre de gestion organisé par grands domaines fonctionnels.
- Accès direct aux actions fréquentes.
- Recherche et repérage des paramètres.
- Présentation thématique et visuelle des réglages.
- Conservation des pages natives Moodle pour les formulaires, validations et contrôles de droits.

## Accessibilité et qualité

La cible de conception est **WCAG 2.2 niveau AA**, avec prise en compte du **RGAA** et de la norme **EN 301 549** selon le périmètre applicable.

L'accessibilité est un critère d'acceptation à chaque étape, et non une phase de correction finale. La validation combine notamment :

- analyse automatisée avec axe-core ;
- navigation complète au clavier et contrôle du focus ;
- vérification des contrastes, du zoom et du reflow ;
- tests avec lecteurs d'écran ;
- contrôle de la sémantique HTML et de l'usage d'ARIA ;
- tests des menus, dialogues, formulaires, notifications et états dynamiques.

Les résultats et écarts résiduels sont documentés. L'analyse automatisée ne remplace pas les tests manuels.

## Compatibilité visée

La matrice cible et le statut réel des tests sont suivis dans [ROADMAP.md](ROADMAP.md). Les versions annoncées comme compatibles devront avoir été effectivement testées.

- Moodle : cible de travail 4.5 à 5.3.
- Iomad : cible de travail 4.5 à 5.2, selon les versions disponibles et vérifiables.

## Architecture envisagée

- Thème autonome : `theme_projectn`.
- Plugin compagnon `local_projectn` uniquement si une fonction métier ne peut pas être réalisée proprement dans le thème ; il restera facultatif.
- Pas de format de cours propriétaire par défaut.
- Utilisation prioritaire des API, composants, capacités et mécanismes natifs Moodle.
- Adaptations Iomad conditionnelles, sans altérer le fonctionnement Moodle standard.

## État du projet

Le dépôt constitue le point de départ du cadrage. Les phases, livrables et critères de validation sont détaillés dans [ROADMAP.md](ROADMAP.md).

Aucune intégration continue (CI) n'est mise en place à ce stade. Les contrôles et tests sont exécutés localement jusqu'à décision contraire.

## Licence

À définir avant publication du code.