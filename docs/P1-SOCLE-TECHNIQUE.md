# Project N — P1 : socle technique

**Statut :** socle et premier composant de navigation codés ; installation et tests runtime non encore effectués.  
**Branche de travail :** `feature/p1-foundation`  
**Base :** branche `main` du dépôt `ericfalcon/moodle-theme-project-n`.

## 1. Périmètre de cette première tranche

Cette tranche pose uniquement le squelette du plugin Moodle `theme_projectn`, avec les fichiers d'entrée à la racine du dépôt comme attendu pour un dépôt de plugin Moodle :

- métadonnées du plugin et version de développement ;
- héritage du thème parent Boost ;
- conservation des layouts, templates et composants natifs ;
- chaînes de langue anglaises et françaises ;
- fichier SCSS complémentaire ;
- première couche de tokens sémantiques et de primitives de composants, sans figer la palette de marque (voir [P1-DESIGN-SYSTEM.md](P1-DESIGN-SYSTEM.md)) ;
- premier composant de navigation globale, rendu par hook et template Mustache, avec tests PHPUnit du fournisseur de données ajoutés mais non exécutés (voir [P1-NAVIGATION-GLOBALE.md](P1-NAVIGATION-GLOBALE.md)).

Elle ne constitue pas encore un thème validé comme installable. Aucune compatibilité runtime n'est déclarée.

## 2. Choix techniques

### 2.1 Compatibilité minimale déclarée

Le plugin déclare Moodle 4.5 comme version minimale (`2024100700`). Cette déclaration permet de construire depuis la base technique commune ; elle ne prouve pas le fonctionnement sur Moodle 4.5 à 5.3 ni sur Iomad.

La compatibilité réelle devra être vérifiée sur chaque génération cible :
- Moodle 4.5, 5.0, 5.1, 5.2 et 5.3 ;
- Iomad 4.5, 5.0, 5.1 et 5.2, sur la génération Moodle correspondante.

### 2.2 Héritage Boost

Le thème déclare Boost comme parent et ne redéfinit aucun layout ni template à ce stade. Les styles Project N sont ajoutés par le callback SCSS complémentaire. Le code ne copie pas les sources SCSS de Boost.

Cette stratégie limite les divergences entre versions, notamment les changements de navigation et de composants observés dans Moodle 5.2 et 5.3.

### 2.3 Design tokens

Les premiers tokens concernent les rayons, les marges de contenu et l'indication du focus clavier. La palette de marque n'est pas figée : elle sera définie après prototypage et vérification des contrastes.

Le style de focus complète les indications natives et ne doit pas les supprimer. Les règles globales de largeur et de retour à la ligne devront être contrôlées sur les pages complexes (tableaux, éditeurs, activités et rapports) avant validation.

### 2.4 Accessibilité et mouvement

Le premier SCSS :
- conserve un indicateur de focus visible ;
- prend en compte `prefers-reduced-motion` ;
- vise à éviter le débordement horizontal causé par les chaînes longues.

Ces règles constituent des intentions de conception, pas une preuve WCAG/RGAA. Elles devront être vérifiées avec axe-core, clavier, zoom/reflow, lecteurs d'écran et appareils réels.

## 3. Points de contrôle avant la suite

1. Vérifier la syntaxe PHP et les conventions Moodle du plugin, puis exécuter les tests PHPUnit de `tests/output/global_navigation_test.php`.
2. Installer le thème sur Moodle 4.5 puis sur les générations 5.x.
3. Vérifier la compilation SCSS et le chargement effectif des styles.
4. Vérifier que les layouts, tiroirs et navigations Boost restent fonctionnels.
5. Vérifier l'installation et l'affichage sur Iomad, sans activer de logique tenant prématurée.
6. Contrôler les pages de connexion, tableau de bord, cours, activités, formulaires et administration.
7. Tester le rendu à 320, 390, 768 et 1440 CSS px.
8. Exécuter les contrôles automatisés et manuels d'accessibilité.

## 4. Hors périmètre de cette tranche

- navigation d'espace apprenant, formateur et administrateur ;
- surcharge de templates ;
- tableau de bord apprenant ;
- espace formateur ;
- détection et adaptation Iomad ;
- paramètres de personnalisation ;
- déclaration de compatibilité validée.

Ces éléments seront construits après validation du socle et des contrats techniques concernés.
