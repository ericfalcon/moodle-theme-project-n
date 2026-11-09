# Project N — P1 : première navigation globale implémentée

**Statut :** code présent sur `feature/p1-foundation` ; tests runtime non exécutés.  
**Version de développement :** `2026111002`.  
**Références P0 :** [architecture hybride](P0-NAVIGATION.md), [cartographie technique](P0-CARTOGRAPHIE-NAVIGATION.md), [matrice de compatibilité](P0-MATRICE-COMPATIBILITE.md).

## 1. Périmètre livré

La première version du composant affiche deux destinations globales aux utilisateurs connectés non invités :

- **Accueil** : racine du site Moodle ;
- **Formations** : page native `/my/courses.php`.

Ce périmètre réduit permet de valider la chaîne technique complète avant d'ajouter les espaces apprenant, formateur, administrateur ou gestionnaire Iomad. Il ne constitue pas la taxonomie finale de la navigation.

## 2. Architecture du code

| Fichier | Responsabilité |
|---|---|
| `db/hooks.php` | Enregistre le hook de sortie du cœur |
| `classes/hook_callbacks.php` | Filtre les contextes d'affichage et délègue le rendu |
| `classes/output/global_navigation.php` | Construit le contexte de données du template |
| `templates/global_navigation.mustache` | Produit le balisage sémantique |
| `lang/en/theme_projectn.php` | Libellés anglais |
| `lang/fr/theme_projectn.php` | Libellés français |
| `scss/projectn.scss` | Présentation responsive du composant |\n| `tests/output/global_navigation_test.php` | Tests PHPUnit du contrat de données |

La séparation données / callback / template évite de mélanger la logique de préparation et le HTML.

## 3. Règles de fonctionnement

- La navigation est affichée uniquement aux utilisateurs connectés qui ne sont pas invités.
- Elle est masquée sur les layouts spécialisés `login`, `popup`, `embedded`, `maintenance`, `print` et `secure`.
- L'état courant est exposé par `aria-current="page"` et renforcé autrement que par la couleur seule.
- Les liens utilisent les URL natives Moodle ; aucune liste de cours ou donnée utilisateur n'est reconstruite.
- Sur smartphone, les destinations utilisent une grille compacte avec des cibles d'au moins 44 CSS px ; sur écran plus large, elles sont présentées en ligne.\n- Le composant reste dans le flux normal du document : aucun positionnement fixe ou superposé susceptible de masquer le contenu.
- Moodle conserve les contrôles d'accès sur les pages cibles et reste responsable des navigations primaire/secondaire, du fil d'Ariane, de l'index de cours, des activités et de l'administration.
- Aucun code Iomad n'est appelé. Le composant doit donc rester utilisable sur Moodle standard.

## 4. Tests à exécuter

### Fonctionnel

- [ ] Connexion avec un compte apprenant : les deux liens apparaissent.
- [ ] Connexion avec un compte formateur : les deux liens apparaissent sans supposer un rôle unique.
- [ ] Compte invité et utilisateur non connecté : le composant n'apparaît pas.
- [ ] La page d'accueil marque « Accueil » comme courant.
- [ ] `/my/courses.php` marque « Formations » comme courant.
- [ ] Les layouts spécialisés ne reçoivent pas le composant.
- [ ] La navigation native, les sorties de plugins et les liens d'évitement restent présents.

### Responsive et accessibilité

- [ ] 320, 390, 768 et 1440 CSS px.
- [ ] Zoom 200 % et reflow sans défilement horizontal global.
- [ ] Parcours clavier, focus visible et ordre de tabulation.
- [ ] NVDA et TalkBack.
- [ ] Axe-core ; analyser les résultats et corriger les anomalies pertinentes.
- [ ] Vérifier que la barre ne masque aucun contenu ni contrôle.

### Compatibilité

- [ ] Moodle 4.5, 5.0, 5.1, 5.2 et 5.3.
- [ ] Iomad 4.5, 5.0, 5.1 et 5.2 sur leurs générations correspondantes.
- [ ] Vérifier le chargement du hook, l'autoload de la classe, le rendu Mustache et la compilation SCSS après purge des caches.

Aucune de ces validations runtime n'est déclarée comme effectuée par le seul dépôt du code.

## 5. Limites connues et suite

- La destination « Accueil » pointe actuellement vers la racine du site ; le respect des préférences individuelles de page d'accueil reste à traiter.
- Le nombre et l'ordre définitifs des destinations ne sont pas figés.
- Les entrées « Activités » et « Plus » ne sont pas ajoutées tant que leur contenu utile n'est pas défini par les parcours P0.
- Les raccourcis formateur et gestionnaire Iomad nécessiteront une construction conditionnée par les capacités et contextes, sans logique d'autorisation parallèle.
- Le placement visuel est provisoirement une grille compacte dans le flux du document sur smartphone et une ligne sur écran large ; il reste à confronter aux tests utilisateurs et à la référence ergonomique Chamilo.

**Prochaine tranche :** exécuter les tests PHPUnit et installer le thème sur un environnement Moodle de référence ; vérifier la chaîne hook → callback → fournisseur de données → Mustache, contrôler le rendu réel et corriger les écarts avant de construire la navigation d'espace.
