# Project N — Thème Moodle

> Nom de travail du projet. Le nom définitif sera choisi après vérification de sa disponibilité dans le répertoire des plugins Moodle et de l'antériorité des marques.

**Composant Moodle :** `theme_projectn`  
**Dépôt :** [ericfalcon/moodle-theme-project-n](https://github.com/ericfalcon/moodle-theme-project-n)  
**Thème parent retenu :** Boost  
**Licence envisagée :** GNU GPL v3 ou ultérieure, à confirmer en P0.

## Vision

Project N vise à proposer une expérience Moodle plus claire, moderne et cohérente, inspirée de certains principes ergonomiques de Chamilo : cours lisibles, navigation peu profonde et interfaces orientées vers les actions. Il ne s'agit ni de reproduire son apparence ni de reprendre son modèle pédagogique.

Moodle reste le moteur pédagogique : cours, sections, activités, ressources, rôles, capacités, formulaires et mécanismes natifs sont conservés. Project N repense prioritairement la navigation, la présentation et les parcours des trois profils : apprenant, formateur et administrateur.

Le projet est **mobile-first** : les parcours et composants sont conçus d'abord pour les smartphones, puis adaptés aux tablettes et ordinateurs. Le responsive n'est pas une simple adaptation tardive du desktop.

Le thème cible Moodle en priorité et prend en compte Iomad dès l'architecture. Il est autonome et indépendant du thème Épure.

## Principes directeurs

1. **Mobile-first et responsive** : chaque fonctionnalité est pensée et validée sur smartphone, tablette et ordinateur.
2. **Boost comme socle technique** : le thème hérite de Boost et construit sa propre identité visuelle et son expérience utilisateur. Boost est une base technique, pas un modèle graphique à simplement recolorer.
3. **Moodle natif en priorité** : pas de moteur pédagogique parallèle ni de format de cours propriétaire.
4. **Guidage sans enfermement** : les parcours simplifiés n'empêchent jamais l'accès aux fonctions avancées natives.
5. **Accessibilité dès la conception** : elle fait partie des critères d'acceptation de chaque étape et fonctionnalité.
6. **Sécurité et capacités natives** : les contrôles d'accès reposent sur les rôles, capacités et contextes Moodle ; aucune permission parallèle.
7. **Iomad dès l'architecture** : les adaptations multi-entreprises et l'isolation des données sont conçues et testées sans compromettre Moodle standard.
8. **Progressivité et maintenabilité** : privilégier les API et composants stables, limiter les surcharges de templates et documenter celles qui sont nécessaires.
9. **Autonomie** : le thème fonctionne sans plugin compagnon obligatoire.
10. **Mesure par l'usage** : les choix ergonomiques sont confrontés à des tests utilisateurs.

## Les trois espaces

### Apprenant

- Tableau de bord centré sur les formations suivies et les actions à réaliser.
- Accès lisible aux cours et parcours.
- Progression, prochaines activités, échéances et notifications.
- Navigation cohérente entre sections, ressources et activités.
- Consultation confortable des contenus sur smartphone.

### Formateur

- Création et organisation guidées en s'appuyant sur les mécanismes natifs Moodle.
- Ajout d'activités et de ressources par intention pédagogique : transmettre, pratiquer, évaluer, collaborer.
- Organisation des sections et réutilisation des contenus lorsque les mécanismes natifs le permettent.
- Accès aux participants, évaluations et corrections.
- Accès permanent aux paramètres avancés natifs.
- Prévisualisation du cours côté apprenant.
- Distinction entre opérations adaptées au mobile et opérations complexes pouvant être plus confortables sur grand écran, sans rendre les fonctions inutilement inaccessibles sur smartphone.

### Administrateur

- Centre de gestion organisé par domaines fonctionnels.
- Accès direct aux actions fréquentes, selon les capacités effectives.
- Recherche et repérage des paramètres en complément de la recherche native.
- Présentation thématique des réglages.
- Conservation des pages natives Moodle pour les formulaires, validations et contrôles de droits.
- Accès mobile aux opérations courantes et présentation adaptée des opérations complexes.

## Mobile-first : exigences de conception

Les maquettes et tests couvrent dès P0 trois formats de référence : smartphone (390 × 844 CSS px), tablette (768 × 1024 CSS px) et ordinateur (1440 × 900 CSS px). Ces dimensions sont des repères de conception, pas des limites techniques.

- Navigation tactile, sans dépendance au survol.
- Cibles tactiles visées d'au moins 44 × 44 CSS px.
- Pas de défilement horizontal global à 320 CSS px ; les contenus intrinsèquement bidimensionnels (par exemple certains tableaux) disposent d'un traitement adapté.
- Cartes, tableaux, formulaires, menus, dialogues et notifications adaptés aux petits écrans.
- Claviers mobiles adaptés au type de champ et formulaires utilisables au tactile.
- Performance mobile : poids des ressources maîtrisé, images optimisées et JavaScript limité.
- Tests sur appareils réels en complément de l'émulation navigateur.

Le périmètre porte sur l'interface web Moodle/Iomad consultée dans un navigateur mobile. L'application mobile Moodle est hors périmètre : un thème Moodle ne contrôle pas son interface.

## Accessibilité et qualité

La cible est **WCAG 2.2 niveau AA**, avec prise en compte du **RGAA** et de la norme **EN 301 549** selon le périmètre applicable.

L'accessibilité est un critère d'acceptation à chaque étape, et non une phase de correction finale. La validation combine :

- analyse automatisée avec axe-core ;
- navigation complète au clavier et contrôle du focus ;
- vérification des contrastes, du zoom à 200 % et du reflow à 320 CSS px ;
- tests avec lecteurs d'écran (notamment NVDA et TalkBack) ;
- contrôle de la sémantique HTML, des noms accessibles et de l'usage justifié d'ARIA ;
- tests des menus, dialogues, formulaires, notifications et états dynamiques ;
- vérifications sur smartphone et avec interactions tactiles.

Un résultat axe-core sans anomalie ne suffit pas à déclarer un parcours accessible. Les résultats, anomalies et écarts résiduels sont documentés.

## Compatibilité visée

- **Moodle :** 4.5 à 5.3.
- **Iomad :** 4.5 à 5.2, selon les versions réellement disponibles et testables.

Une version n'est annoncée comme compatible que si elle a été effectivement testée. La matrice et le statut des tests sont suivis dans [ROADMAP.md](ROADMAP.md).

## Architecture envisagée

- Thème autonome `theme_projectn`, héritant de **Boost**.
- Réutilisation prioritaire des composants, API, layouts et mécanismes natifs Moodle.
- Surcharges de templates sélectives, justifiées et documentées.
- Plugin compagnon `local_projectn` uniquement si une fonction métier ne peut pas être réalisée proprement dans le thème ; il reste facultatif.
- Pas de format de cours propriétaire.
- Adaptations Iomad conditionnelles et isolées, sans effet sur le fonctionnement Moodle standard.
- Pas de logique d'autorisation parallèle aux capacités Moodle.

## Développement et validation

Une intégration continue (CI) est envisagée pour automatiser les contrôles reproductibles : qualité du code, tests automatisés et contrôles pertinents d'accessibilité. Son périmètre et son hébergement seront définis en P0. Elle complète les tests manuels et les validations sur environnements Moodle/Iomad réels ; elle ne les remplace pas.

## État du projet

La cartographie des rôles, contextes et capacités est documentée dans [docs/P0-ROLES-MOODLE-IOMAD.md](docs/P0-ROLES-MOODLE-IOMAD.md). L'analyse du tableau de bord et des mécanismes d'administration Iomad est disponible dans [docs/P0-IOMAD-ADMINISTRATION.md](docs/P0-IOMAD-ADMINISTRATION.md). La comparaison des générations Moodle/Iomad, des capacités et du thème Boost est documentée dans [docs/P0-COMPARAISON-VERSIONS-MOODLE-IOMAD.md](docs/P0-COMPARAISON-VERSIONS-MOODLE-IOMAD.md). Le retour d'expérience technique du thème Épure — mécanismes à reprendre, adaptations nécessaires et points à vérifier — est consigné dans [docs/P0-REX-EPURE-PROJECTN.md](docs/P0-REX-EPURE-PROJECTN.md).

Le projet est en **P0 — cadrage en cours**. La cartographie technique est alimentée au fil de l'analyse ; elle comprend notamment les parcours apprenant et formateur, avec une synthèse de l'espace formateur Moodle standard (section 65) et une première analyse de l'administration Moodle consacrée aux comptes utilisateurs et cohortes (section 66). La cartographie reste une analyse statique : les prototypes et validations sur environnements réels restent à réaliser. Le dossier de cadrage technique et ergonomique est disponible dans [docs/P0-CADRAGE.md](docs/P0-CADRAGE.md), et les jalons, livrables, décisions et critères d'acceptation sont détaillés dans [ROADMAP.md](ROADMAP.md).
