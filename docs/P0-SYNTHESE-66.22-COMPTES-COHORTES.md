# Project N — 66.22 Synthèse fonctionnelle et conséquences ergonomiques

> Synthèse de cadrage issue des sections 66.10 à 66.21 et des comparaisons de versions déjà consignées. Analyse statique des forks ; elle ne vaut pas validation runtime, test d'accessibilité ni recette mobile.

## 1. Conclusion générale

La gestion des comptes et des cohortes ne doit pas être conçue comme un écran CRUD unique. Moodle distingue au moins quatre objets et niveaux fonctionnels :

1. **Le compte utilisateur** : identité, authentification, profil et état global du compte.
2. **Les rattachements et inscriptions** : appartenance à un contexte, inscription à un cours, rôle attribué et éventuelles appartenances à des groupes.
3. **La cohorte** : regroupement transversal d'utilisateurs, généralement à l'échelle système ou d'une catégorie, avec ses propres membres et ses usages de synchronisation.
4. **Les actions d'administration** : opérations individuelles ou en lot, chacune soumise à des capacités, à un contexte et parfois à un traitement différé.

Project N doit rendre ces niveaux compréhensibles sans les fusionner. Une ligne « utilisateur actif » ou un unique statut ne suffit pas toujours à décrire la situation réelle d'un compte, en particulier dans Iomad où le compte Moodle, les rattachements à une ou plusieurs entreprises, les départements et les statuts associés sont distincts.

## 2. Ce que Project N doit améliorer

### 2.1 Une entrée « Utilisateurs » orientée tâches

L'écran d'entrée devrait permettre de répondre rapidement à quatre questions :

- Qui est la personne recherchée ?
- Dans quel périmètre intervient-elle ?
- Quel est son état réel (compte, rattachement, inscription) ?
- Quelle opération est autorisée et pertinente dans le contexte courant ?

La page peut proposer une recherche et des filtres immédiatement repérables, puis une liste compacte. Les filtres doivent être progressifs : recherche simple en premier, critères avancés accessibles sans encombrer l'écran mobile.

Filtres fonctionnels à étudier dans le prototype :
- nom, prénom, identifiant et adresse électronique ;
- état du compte et, en Iomad, état du rattachement entreprise ;
- cohorte, cours ou catégorie lorsque le contexte le permet ;
- rôle ou fonction, uniquement avec une définition explicite de son périmètre ;
- entreprise et département dans les vues Iomad autorisées.

Il ne faut pas afficher systématiquement tous les filtres : leur disponibilité dépend du contexte, des capacités et des fonctionnalités réellement présentes.

### 2.2 Une ligne utilisateur qui ne confond pas les états

La liste doit distinguer visuellement, lorsque l'information est disponible :
- le statut global du compte Moodle ;
- le statut du rattachement entreprise Iomad ;
- les inscriptions aux cours ;
- les rôles/fonctions et le périmètre auquel ils s'appliquent.

Les actions ne doivent pas être déduites d'un libellé de rôle. Elles sont déterminées par les capacités effectives dans le contexte pertinent. En Iomad, la portée entreprise/département doit également être prise en compte.

Sur smartphone, privilégier une carte ou une ligne repliable présentant d'abord l'identité et l'état principal, puis les informations secondaires et les actions. Éviter une table à nombreuses colonnes qui impose un défilement horizontal global. Les actions destructrices ou à effet étendu doivent rester explicites et confirmées.

### 2.3 Une fiche utilisateur organisée par domaines

La fiche peut regrouper les informations en sections ou onglets adaptés au mobile :
- identité et profil ;
- état du compte ;
- rattachements organisationnels (si Iomad) ;
- rôles et fonctions, avec leur contexte ;
- inscriptions et cours ;
- cohortes et groupes ;
- licences et accès spécifiques Iomad, sans les confondre avec les inscriptions ;
- historique ou informations de suivi lorsque Moodle les expose et que le droit le permet.

Cette organisation est une proposition de présentation, pas une invitation à réécrire les formulaires métier. Les formulaires natifs restent responsables de la validation, des contrôles et de l'enregistrement.

## 3. Actions individuelles et actions en lot

Les actions groupées constituent un parcours distinct de la fiche individuelle. La comparaison des branches a identifié `admin/user/user_bulk.php` comme identique sur Moodle 4.5, 5.0, 5.1, 5.2 et 5.3.

Project N devrait rendre le processus explicite :
1. sélectionner les utilisateurs ;
2. visualiser le nombre et le périmètre de la sélection ;
3. choisir une action parmi celles réellement autorisées ;
4. présenter les conséquences et les éventuels effets irréversibles ;
5. confirmer ;
6. afficher le résultat, les erreurs et les éléments ignorés.

Ne pas appliquer une mise à jour optimiste à la liste avant le retour du serveur. Pour les opérations asynchrones, afficher un état de traitement et distinguer succès, échec et traitement partiel.

Les opérations de suspension, réactivation et suppression ne doivent jamais être assimilées à une simple bascule de statut. Le compte Moodle et le rattachement Iomad peuvent avoir des états distincts. Une suppression doit notamment rendre visible le périmètre de l'opération et laisser le cœur effectuer ses contrôles.

## 4. Cohortes : un objet de gestion distinct

Les cohortes ne sont ni des groupes de cours ni des rôles. Elles regroupent des utilisateurs et peuvent alimenter des inscriptions ou d'autres traitements via des mécanismes natifs.

L'interface Project N doit donc séparer :
- la gestion de la cohorte (nom, contexte, description et paramètres disponibles) ;
- la gestion de ses membres ;
- les effets éventuels de la cohorte sur les inscriptions et synchronisations.

La liste des cohortes doit permettre de repérer leur contexte et leur volume de membres, si ces informations sont disponibles. La fiche d'une cohorte doit rendre compréhensible l'ajout/retrait de membres et les conséquences possibles sur les inscriptions liées. Un membre ne doit pas être présenté comme « inscrit à tous les cours » du seul fait de son appartenance à une cohorte.

Les mécanismes de synchronisation doivent rester ceux des plugins Moodle concernés. Project N ne doit pas réimplémenter leur logique ni présenter une synchronisation comme instantanée sans preuve. Les tâches planifiées, erreurs et délais éventuels devront être testés sur les installations cibles.

## 5. Règles particulières Iomad à préserver

Les sections précédentes montrent que l'administration Iomad ajoute une couche métier qui ne peut pas être ramenée à un simple filtre visuel :

- un compte Moodle peut être associé à plusieurs entreprises ;
- le statut du compte et celui de chaque rattachement entreprise sont distincts ;
- les départements définissent des sous-périmètres ;
- les rôles/fonctions Iomad (gestionnaire d'entreprise, gestionnaire de département, responsable des rapports, éditeur de cours, éducateur, etc.) ne se substituent pas aux capacités ;
- les inscriptions aux cours et les licences sont deux opérations différentes ;
- certaines opérations sont exécutées par tâches différées ;
- les contrôles d'appartenance et de capacité doivent être effectués côté serveur.

La branche Iomad 5.0 du fork contient des personnalisations locales à préserver. Aucune conclusion de cette synthèse n'autorise à écraser ou réinitialiser ces modifications.

La comparaison existante signale un écart potentiel entre capacités utilisées pour afficher les actions et contrôles serveur en Iomad 4.5/5.0. En 5.1/5.2, des contrôles dédiés sont déplacés dans des classes External API. Le thème ne doit pas reproduire ces contrôles : il doit consommer les pages et mécanismes métier et ne jamais considérer la visibilité d'un bouton comme une protection.

## 6. Conséquences techniques pour le thème

### À prendre en charge par Project N

- hiérarchie de l'information et navigation entre liste, fiche et cohorte ;
- présentation responsive des filtres, listes, sélections et résultats ;
- états visuels de chargement, succès, erreur et traitement partiel ;
- confirmations compréhensibles pour les actions sensibles ;
- liens vers les pages natives et contextualisation de l'espace courant ;
- adaptation des tableaux en cartes ou vues compactes sans perte d'information.

### À laisser à Moodle et aux plugins métier

- création, modification et suppression des comptes ;
- validation des champs et contrôles de sécurité ;
- calcul des capacités et contrôle des contextes ;
- traitement des actions groupées ;
- gestion des membres de cohortes et synchronisations ;
- inscriptions, rôles et affectations ;
- règles Iomad d'entreprise, de département, de licences et de tâches différées.

Le thème ne doit pas interroger directement les tables Moodle/Iomad pour reconstruire ces règles. Il ne doit pas non plus créer une API parallèle de gestion des comptes.

## 7. Différences de versions à prendre en compte

La comparaison déjà documentée identifie les évolutions suivantes pour Moodle standard :

| Élément | Écart constaté entre générations |
|---|---|
| `admin/user.php` | Moodle 4.5 diffère ; 5.0 = 5.1 = 5.2 ; 5.3 diffère |
| Rapport système utilisateurs Report Builder | 4.5 diffère ; 5.0 = 5.1 ; 5.2 diffère ; 5.3 diffère de 5.2 |
| `cohort/index.php` | 4.5 diffère ; 5.0 = 5.1 = 5.2 = 5.3 |
| `admin/user/user_bulk.php` | Identique sur les cinq générations |
| Entités Report Builder cohorte et membre de cohorte | Fichiers sélectionnés comparés entre Moodle et Iomad sur les couples 4.5 à 5.2 ; identités constatées pour ce périmètre |

Ces résultats sont des comparaisons de fichiers, pas une preuve d'identité de comportement. Le thème doit donc éviter de dépendre de la structure HTML interne de ces pages. Les composants de liste, filtres, actions et rapports peuvent évoluer indépendamment du contrôleur.

Pour Iomad, les conclusions spécifiques sur les comptes sont documentées dans les sections 66.15 à 66.21 et dans la comparaison des versions §9.6 à §9.9. Les cohortes nécessitent encore des vérifications ciblées sur la synchronisation, le cloisonnement et les permissions en exécution.

## 8. Priorités ergonomiques proposées

| Priorité | Besoin | Décision de conception |
|---|---|---|
| P1 | Retrouver rapidement une personne | Recherche simple visible, filtres avancés repliables |
| P1 | Comprendre l'état d'un compte | Distinguer compte, rattachement, inscription et licence |
| P1 | Agir sans erreur | Actions contextuelles, confirmation et résultat explicite |
| P1 | Gérer plusieurs utilisateurs | Sélection en lot avec résumé du périmètre et traitement traçable |
| P1 | Gérer les cohortes | Écran distinct, membres et effets de synchronisation explicités |
| P1 | Respecter les droits | Actions fondées sur les capacités et le contexte, contrôles serveur natifs |
| P2 | Usage mobile | Cartes compactes, filtres progressifs, actions tactiles et aucun tableau débordant la page |
| P2 | Réduire les erreurs de gestion | Distinguer opérations immédiates, différées, partielles et irréversibles |

Ces priorités devront être confrontées aux tâches réelles des administrateurs et gestionnaires lors des tests utilisateurs. Elles ne constituent pas encore une maquette validée.

## 9. Tests et points restant ouverts

| Vérification | État |
|---|---|
| Comparaison statique des pages utilisateurs et cohortes Moodle 4.5–5.3 | Réalisée sur les fichiers sélectionnés ; détails dans la matrice |
| Comparaison des entités Report Builder sélectionnées Moodle/Iomad 4.5–5.2 | Réalisée |
| Parcours de gestion des comptes Iomad 4.5–5.2 | Cartographiés statiquement, couverture partielle |
| Synchronisation des cohortes et effets des inscriptions | À tester |
| Permissions effectives et accès direct URL | À tester en runtime |
| Actions AJAX et traitements différés | À tester |
| Gestion multi-entreprises et multi-départements | À tester avec plusieurs comptes et entreprises |
| Accessibilité clavier, lecteurs d'écran, axe-core | Non testée |
| Responsive sur appareils réels | Non testé |
| Tests utilisateurs administrateurs | À organiser |

### Scénarios de recette à prévoir

1. Rechercher un utilisateur avec un compte actif puis suspendu.
2. Distinguer l'état d'un compte Moodle de l'état d'un rattachement Iomad.
3. Vérifier les actions disponibles avec des rôles et capacités différents.
4. Tenter un accès direct à une opération non autorisée.
5. Réaliser une action groupée avec succès partiel et erreurs.
6. Ajouter puis retirer un membre d'une cohorte et vérifier les effets réels sur les inscriptions synchronisées.
7. Vérifier les périmètres d'un gestionnaire d'entreprise et d'un gestionnaire de département.
8. Tester les états de chargement, d'erreur et de confirmation sur smartphone.
9. Réaliser les parcours au clavier et avec lecteur d'écran.

## 10. Décision de cadrage

**Décision proposée :** Project N fournit une couche de présentation et de guidage pour les comptes et cohortes, mais conserve les pages, formulaires, traitements et contrôles natifs Moodle/Iomad. L'amélioration porte sur la recherche, la lisibilité des états, la navigation, la présentation mobile et la compréhension des actions.

**Pas de réécriture du CRUD**, pas de gestion parallèle des permissions, pas de confusion entre compte, entreprise, département, cohorte, groupe, inscription et licence.

Le point 66.22 clôt la synthèse fonctionnelle de la section 66. Il ne clôt pas les vérifications runtime, accessibilité et mobile listées ci-dessus.
