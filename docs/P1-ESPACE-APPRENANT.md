# Project N — P1 : premier espace apprenant

**Statut :** fournisseur de données, template et styles préparés sur `feature/p1-foundation` ; le composant n'est pas encore raccordé au contenu principal de la page ; tests ajoutés mais non exécutés.  
**Version de développement :** `2026111004`.

## 1. Objectif de cette tranche

Faire apparaître sur le tableau de bord personnel Moodle un premier espace apprenant plus lisible, inspiré de la hiérarchie visuelle de Chamilo, sans remplacer les mécanismes Moodle.

Le composant reste volontairement limité aux formations auxquelles l'utilisateur est inscrit. Il ne cherche pas à reconstituer un profil de rôle : un même compte peut être apprenant dans certains cours et formateur dans d'autres.

## 2. Fonctionnement livré

- Le fournisseur et le template sont prêts pour le tableau de bord personnel (`/my/` et `/my/index.php`).
- Le composant n'est volontairement pas injecté par le hook `before_standard_top_of_body_html_generation` : ce point d'extension intervient avant le layout Boost et placerait les cartes avant l'en-tête et le contenu principal.
- Le raccordement visuel attend un point d'extension qui insère le composant dans la zone de contenu, sans surcharge complète du layout Boost ni manipulation DOM fragile.
- Le fournisseur `classes/output/learner_dashboard.php` récupère les cours avec `enrol_get_my_courses()`, l'API Moodle d'inscription.
- Seuls les cours issus des inscriptions actives sont demandés.
- Six cartes au maximum sont affichées pour conserver une page d'accueil compacte.
- Chaque carte renvoie directement vers la page native `/course/view.php?id=…`.
- Un état vide est prévu pour les comptes sans inscription active.
- Les libellés sont disponibles en français et en anglais.
- Le template est sémantique et les cartes sont utilisables au clavier ; la grille passe d'une colonne sur smartphone à deux puis trois colonnes sur les écrans plus larges.

## 3. Limites assumées

Cette première tranche ne présente pas encore :
- le pourcentage de progression ;
- la prochaine activité ;
- les échéances ;
- les cours terminés ou archivés ;
- les images de cours.

Ces informations seront ajoutées seulement après vérification des API Moodle communes aux versions ciblées et des règles de disponibilité des données. Aucun calcul de progression ni lien d'activité n'est simulé.

Le composant ne remplace pas les blocs du tableau de bord Moodle ni la page native « Mes cours ». Son emplacement final et le risque de redondance avec les blocs activés devront être évalués avant son raccordement.

## 4. Fichiers

| Fichier | Responsabilité |
|---|---|
| `classes/output/learner_dashboard.php` | Récupération et préparation des données de cours |
| `templates/learner_dashboard.mustache` | Structure accessible du composant |
| `classes/hook_callbacks.php` | Affichage conditionnel sur le tableau de bord |
| `scss/projectn.scss` | Cartes et grille responsive |
| `lang/fr/theme_projectn.php` | Libellés français |
| `lang/en/theme_projectn.php` | Libellés anglais |
| `tests/output/learner_dashboard_test.php` | Tests du fournisseur de données |

## 5. Vérifications à exécuter

- [ ] `php -l` sur tous les fichiers PHP ajoutés ou modifiés.
- [ ] PHPUnit : état vide et cours inscrit.
- [ ] Identifier et valider un point d'insertion dans la zone principale du tableau de bord sans recopier le layout Boost.
- [ ] Installation et purge des caches sur Moodle 4.5.
- [ ] Vérifier la même récupération des inscriptions sur Moodle 5.0 à 5.3.
- [ ] Vérifier le fonctionnement sur Iomad 4.5 à 5.2, sans filtre d'entreprise ajouté au thème.
- [ ] Vérifier le rendu avec un compte apprenant, un formateur également inscrit à des cours et un compte sans inscription.
- [ ] Vérifier le rendu sur 320, 390, 768 et 1440 CSS px, puis à 200 % de zoom.
- [ ] Vérifier l'absence de doublon gênant avec les blocs natifs du tableau de bord.
- [ ] Contrôler les contrastes, le focus, l'ordre de tabulation et les noms accessibles avec axe-core et des tests manuels.

Aucune compatibilité runtime n'est revendiquée avant ces essais.

## 6. Suite de développement

La prochaine tranche technique sera le raccordement du composant dans la zone principale du tableau de bord, après validation du point d'extension Boost/Moodle. La progression et la prochaine action viendront ensuite, après validation des services et méthodes communs Moodle 4.5–5.3. Il faudra conserver les liens vers les activités natives et ne jamais exposer une activité que l'utilisateur ne peut pas consulter.
