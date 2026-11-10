# Project N — CI et stratégie de tests

**Statut :** stratégie définie ; premier contrôle CI PHP activé. Les tests PHPUnit Moodle et les audits axe-core sur navigateur seront ajoutés dès que l'environnement Moodle de test et les parcours concernés seront disponibles.

## 1. Principes

- La CI est exécutée sur chaque push des branches de développement et sur chaque pull request.
- Une anomalie bloquante de syntaxe, de test ou d'accessibilité doit faire échouer le pipeline.
- Les contrôles sont réalisés sur le thème installé dans Moodle, et pas uniquement sur des fichiers isolés.
- Les versions Moodle et Iomad sont testées séparément : Moodle 4.5 à 5.3 ; Iomad 4.5 à 5.2. Iomad 5.3 sera ajouté lorsqu'une version exploitable sera disponible.
- Les rôles Iomad, notamment `companycourseeditor` et `companycoursenoeditor`, font partie des scénarios de non-régression.

## 2. Dépôts sources et environnements réels

Les environnements de test doivent être construits à partir des forks du compte GitHub du projet, et non depuis des archives téléchargées ou des dépôts amont substitués :

| Environnement | Dépôt utilisé | Branches vérifiées dans le fork |
|---|---|---|
| Moodle | [ericfalcon/moodle](https://github.com/ericfalcon/moodle) | `MOODLE_405_STABLE`, `MOODLE_500_STABLE`, `MOODLE_501_STABLE`, `MOODLE_502_STABLE`, `MOODLE_503_STABLE` |
| Iomad | [ericfalcon/iomad](https://github.com/ericfalcon/iomad) | `IOMAD_405_STABLE`, `IOMAD_500_STABLE`, `IOMAD_501_STABLE`, `IOMAD_502_STABLE` |

Le fork Iomad est la source de référence pour les tests Iomad. **Ne pas le remplacer par le dépôt amont et ne pas réinitialiser ses branches** : la branche Iomad 5.0 contient des modifications personnelles qui doivent être conservées. Les workflows doivent faire un checkout explicite de la branche de version demandée et enregistrer le SHA exact utilisé dans les logs/artefacts.

Matrice cible :
- Moodle : 4.5, 5.0, 5.1, 5.2 et 5.3.
- Iomad : 4.5, 5.0, 5.1 et 5.2, chacune sur la branche correspondante du fork.
- Iomad 5.3 : ne pas créer de job tant qu'une branche/version exploitable n'existe pas dans le fork.

Chaque environnement doit être une **installation réelle et indépendante** : base de données dédiée, installation CLI, thème Project N installé et activé, données de test créées par les API Moodle/Iomad. Le job Iomad doit démarrer le code Iomad lui-même ; un Moodle standard auquel on aurait ajouté quelques plugins Iomad ne constitue pas un test Iomad valide.

Pour maîtriser la durée des pull requests, la stratégie pourra distinguer :
- une matrice courte de validation sur chaque pull request (version Moodle de référence et version Iomad de référence) ;
- la matrice complète des versions sur exécution planifiée et déclenchement manuel ;
- une campagne complète axe-core sur les deux plateformes, avec possibilité d'un contrôle ciblé sur pull request.

Le choix des versions de référence et la fréquence des campagnes complètes devront être explicités dans le workflow, sans masquer les versions non testées.

## 3. Contrôles CI

| Niveau | Contrôle | Déclenchement | Critère |
|---|---|---|---|
| Activé | Syntaxe PHP (`php -l`) | Push, pull request | Aucune erreur |
| À intégrer | Moodle Coding Style / PHPCS | Push, pull request | Aucune violation non justifiée |
| À intégrer | Validation du plugin et des templates | Push, pull request | Validation réussie |
| À intégrer | PHPUnit dans Moodle | Push, pull request | Tous les tests passent |
| À intégrer | Compilation SCSS / vérification des assets | Push, pull request | Compilation réussie |
| À intégrer | axe-core via Playwright sur Moodle | Push, pull request | Aucun défaut impactant bloquant |
| À organiser | Tests Behat et parcours Iomad | Pull request et campagnes ciblées | Parcours critiques réussis |

Le premier workflow est `.github/workflows/php-quality.yml`. Il vérifie la syntaxe sur PHP 8.1, 8.2 et 8.3. Ce contrôle ne remplace ni PHPUnit ni les tests d'intégration Moodle.

## 4. Tests PHPUnit à couvrir

- Navigation globale : visibilité selon l'état de connexion, page courante et exclusion des layouts incompatibles.
- Tableau de bord : état vide, cours apprenant, cours enseignant, compte présent dans les deux sections.
- Rôles Moodle : étudiant, enseignant, enseignant éditeur.
- Rôles Iomad : `companycourseeditor` et `companycoursenoeditor`, vérifiés chacun dans un cours distinct et dans un même compte multi-cours.
- Rôles hérités de catégories et rôles personnalisés sans archétype.
- Achèvement : suivi désactivé, progression disponible, activité suivante visible/non terminée, activité terminée ou non accessible.
- Contrôle des limites de cartes et des liens natifs Moodle.

Les tests doivent utiliser les générateurs et API Moodle. Ils ne doivent pas simuler les capacités en contournant le système de rôles.

## 5. Audit automatisé axe-core

L'audit axe-core sera exécuté avec Playwright sur **deux instances distinctes démarrées pour la CI : une instance Moodle et une instance Iomad**, toutes deux construites depuis les forks indiqués ci-dessus. Le thème Project N doit être installé et activé dans chacune. Chaque instance doit être initialisée avec des comptes, rôles et cours de test reproductibles.

### Pages et états à auditer

- Connexion et récupération de compte lorsque ces pages sont personnalisées.
- Tableau de bord apprenant vide et avec plusieurs formations.
- Tableau de bord enseignant Moodle.
- Tableau de bord enseignant Iomad, avec `companycourseeditor` et `companycoursenoeditor`.
- Page de cours, navigation globale, index du cours et menus contextuels.
- Formulaires et fenêtres modales réellement utilisés dans les parcours couverts.
- États clair et sombre si le thème expose ces deux modes.

### Viewports

- 320 × 568 CSS px : reflow mobile étroit.
- 390 × 844 CSS px : mobile courant.
- 768 × 1024 CSS px : tablette.
- 1280 × 800 CSS px : bureau.

Le zoom à 200 % et les parcours clavier restent également à contrôler. Le viewport ne simule pas à lui seul le zoom navigateur.

### Règles et seuils

- Utiliser axe-core avec les tags WCAG 2.1 niveau A et AA, WCAG 2.2 A/AA lorsque les règles sont pertinentes, et les bonnes pratiques axe.
- Faire échouer la CI sur les violations `critical` et `serious`.
- Traiter les violations `moderate` et `minor` dans le rapport ; leur exclusion du seuil bloquant doit rester explicite et temporaire.
- Toute exception doit être documentée avec le sélecteur concerné, la justification, le risque résiduel et une échéance de correction.
- Ne pas neutraliser globalement une règle axe pour faire passer le pipeline.

### Limites

axe-core ne détecte pas tous les problèmes d'accessibilité. Les tests automatisés doivent être complétés par des contrôles manuels : navigation clavier, ordre et visibilité du focus, lecteurs d'écran, intitulés et annonces dynamiques, contraste dans les états interactifs, zoom et reflow.

## 6. Conditions d'activation du gate axe

Les jobs axe deviennent obligatoires dès que le pipeline peut démarrer de façon reproductible les deux instances (Moodle et Iomad), installer Project N, créer les jeux de données et se connecter aux pages testées. Avant cela, le statut « axe non exécuté » doit rester visible : une absence de résultat ne vaut jamais réussite.

Les rapports axe (résumé JSON/HTML et captures en cas d'échec) doivent être conservés comme artefacts de CI pour faciliter la correction.
