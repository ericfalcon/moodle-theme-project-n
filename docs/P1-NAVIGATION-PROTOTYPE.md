# Project N — P1 : stratégie du prototype de navigation

**Statut :** première navigation globale implémentée ; placement final et fonctionnement runtime restent à valider. Voir [P1-NAVIGATION-GLOBALE.md](P1-NAVIGATION-GLOBALE.md).  
**Branche :** `feature/p1-foundation`  
**Référence P0 :** [P0-NAVIGATION.md](P0-NAVIGATION.md) et [P0-CARTOGRAPHIE-NAVIGATION.md](P0-CARTOGRAPHIE-NAVIGATION.md).

## 1. Objectif de cette tranche

Passer de l'architecture hybride retenue en P0 à un prototype progressif, sans remplacer les mécanismes de navigation du cœur Moodle. Le prototype doit améliorer le repérage des destinations fréquentes, en priorité sur smartphone, tout en conservant les repères pédagogiques et contextuels natifs.

Cette tranche ne cherche pas à redessiner toute l'interface Moodle. Elle prépare un composant global limité, dont le contenu dépendra des destinations réellement disponibles pour l'utilisateur.

## 2. Modèle fonctionnel proposé

La navigation Project N comporte deux niveaux propres au thème :

1. **Navigation globale** : accès aux grands espaces du site et aux destinations transversales.
2. **Navigation d'espace** : raccourcis pertinents pour une tâche ou un contexte (apprendre, enseigner, administrer ou gérer une entreprise Iomad).

Les niveaux suivants restent ceux de Moodle : navigation primaire/secondaire, fil d'Ariane, index du cours, navigation d'activité, actions de page et administration. Ils ne sont ni dupliqués ni remplacés.

### 2.1 Destinations globales candidates

| Destination | Finalité | Règle d'exposition |
|---|---|---|
| Accueil | Revenir au tableau de bord ou à l'accueil pertinent | URL et destination définies selon la configuration Moodle ; ne pas supposer que tous les sites utilisent le tableau de bord |
| Formations | Retrouver les cours auxquels l'utilisateur a accès | Données et liens issus des mécanismes Moodle ; ne pas lister des cours sans vérifier leur visibilité |
| Activités | Accéder à une vue de travail utile selon le contexte | Ne pas afficher une entrée vide ; les tâches formateur seront précisées dans P3 |
| Plus | Accéder aux destinations secondaires et aux fonctions personnelles | Conserver les menus et liens natifs nécessaires |

Cette liste est un **candidat de prototype**, pas une taxonomie définitive. Le nombre d'entrées visibles doit rester faible sur smartphone. Les libellés seront évalués auprès d'utilisateurs.

### 2.2 Utilisateurs multi-rôles

Ne pas construire trois interfaces exclusives déterminées par un rôle unique. Un même compte peut être apprenant dans un cours, formateur dans un autre et gestionnaire dans un contexte Iomad.

Les entrées et raccourcis doivent être calculés à partir des capacités, contextes et API disponibles. Les noms de rôles, les chaînes de version et la présence visuelle d'un lien ne constituent jamais une autorisation. Le contrôle d'accès reste effectué par Moodle/Iomad sur la page cible et dans les actions serveur.

### 2.3 Distinction Moodle / Iomad

- Moodle standard doit fonctionner sans classe, table, constante ou plugin Iomad.
- Les raccourcis Iomad ne sont ajoutés que lorsque l'environnement et les capacités correspondantes sont effectivement disponibles.
- Le périmètre d'entreprise ne doit jamais être calculé uniquement dans le navigateur.
- Les liens d'administration Iomad ne doivent pas être présentés comme des fonctions universelles Moodle.

## 3. Comportement responsive à prototyper

### Smartphone

Prototype à comparer, sans décision graphique définitive :

- en-tête compact conservant l'identité du site et les accès personnels essentiels ;
- navigation globale de quatre destinations au maximum, avec une entrée de débordement si nécessaire ;
- navigation d'espace située près du contenu concerné, plutôt qu'une seconde barre fixe permanente ;
- accès explicite à l'index du cours via le mécanisme natif ;
- aucun élément fixe ne doit masquer les boutons de validation, les champs, les messages ou le contenu lorsque le clavier virtuel est ouvert.

La barre inférieure reste une hypothèse à comparer à un menu compact ou à une navigation horizontale. Le choix dépendra des tests sur appareils réels, de la hauteur utile, des zones de sécurité, du zoom et de la compatibilité avec TalkBack.

### Tablette

Conserver les mêmes destinations et libellés que sur smartphone. Utiliser l'espace disponible pour rendre la navigation d'espace plus visible, sans introduire un modèle de navigation différent.

### Ordinateur

La navigation globale peut être horizontale ou latérale selon le prototype. Les navigations secondaires et contextuelles doivent rester proches du contenu qu'elles organisent. Éviter l'empilement de plusieurs barres fixes.

## 4. Contrats d'accessibilité et d'interaction

- Utiliser des éléments `nav` nommés distinctement pour les régions de navigation réellement ajoutées.
- Ne pas englober un composant Moodle qui fournit déjà son propre landmark `nav` dans un second landmark redondant.
- Employer des liens pour les changements de page et des boutons pour les actions.
- Exposer l'élément courant avec `aria-current="page"` lorsque pertinent.
- Maintenir un ordre DOM, de lecture et de focus cohérent.
- Garantir l'utilisation au clavier, un focus visible, et une gestion correcte du focus à l'ouverture/fermeture des menus.
- Ne pas dépendre du survol.
- Donner un nom accessible aux commandes représentées uniquement par une icône.
- Vérifier les contrastes, les cibles tactiles, le reflow à 320 CSS px et le zoom à 200 %.
- Tester avec axe-core, clavier, NVDA et TalkBack. Un contrôle automatisé seul ne suffit pas.

## 5. Stratégie technique par compatibilité

Les analyses P0 montrent des évolutions importantes des contrats de navigation :

- Moodle 4.5 conserve plusieurs templates Boost historiques, dont certains sont dépréciés.
- Les générations récentes déplacent les fichiers web sous `public/` et font évoluer les templates primaire/secondaire.
- Moodle 5.3 s'appuie sur des composants de navigation qui fournissent leurs propres landmarks et fallback.
- Les forks Iomad 4.5, 5.1 et 5.2 ne suivent pas tous exactement la même organisation interne.

En conséquence :

1. Ne pas baser le prototype sur `flat_navigation.mustache` ou `nav-drawer.mustache`.
2. Ne pas copier intégralement `navbar.mustache` ou `drawers.mustache` pour obtenir une simple barre globale.
3. Avant toute surcharge, comparer les API de thème, les callbacks de rendu, les données de navigation et les templates disponibles sur chaque branche cible.
4. Préférer un point d'extension stable et documenté ; si aucun point d'extension ne permet le rendu requis, isoler la surcharge dans un template minimal et documenter précisément les versions concernées.
5. Ne pas supposer qu'une classe, un template ou un chemin présent en Moodle existe à l'identique dans Iomad.
6. Conserver les sorties de plugins et les menus natifs, y compris les possibilités d'extension des plugins tiers.

La première implémentation doit être ajoutée derrière une couche dédiée du thème, afin que la logique de construction des entrées ne soit pas mêlée au markup. Les entrées devront être testables indépendamment de leur présentation.

## 6. Scénarios d'acceptation du prototype

| Scénario | Résultat attendu |
|---|---|
| Apprenant inscrit à plusieurs cours | Retrouver les formations sans exposer de cours non accessibles |
| Formateur et apprenant dans des cours différents | Accéder aux deux ensembles de tâches sans basculer de profil |
| Utilisateur sans rôle d'édition | Aucune action d'édition présentée comme disponible ; l'accès direct reste contrôlé par Moodle |
| Gestionnaire Iomad | Voir uniquement les raccourcis permis dans son périmètre ; aucune donnée d'une autre entreprise |
| Page de cours sur smartphone | Accéder à l'index et à la navigation d'activité natifs sans perte de contexte |
| Page avec formulaire et clavier virtuel | Aucun menu fixe ne masque les champs ou actions |
| Navigation au clavier et lecteur d'écran | Régions identifiables, ordre cohérent, focus visible, état courant annoncé |

Mesurer la réussite autonome, le temps, les erreurs, les retours arrière et les demandes d'aide. Les tests doivent couvrir plusieurs tailles d'écran et les profils multi-rôles.

## 7. Étapes d'implémentation

1. **Audit des points d'extension** : s'appuyer sur la cartographie P0 et l'audit ciblé P1 déjà consignés ; ne rouvrir une inspection que si un test révèle un point non couvert.
2. **Contrat de données** : première structure réalisée dans `classes/output/global_navigation.php` (libellé, URL, état courant). Les conditions d'exposition sont contrôlées par le callback ; aucune autorisation n'est stockée dans les données.
3. **Premier rendu** : réalisé via le hook de sortie, `hook_callbacks.php` et `global_navigation.mustache`, sans surcharge des layouts Boost. Voir le document d'implémentation.
4. **Responsive et accessibilité** : tester le prototype à 320, 390, 768 et 1440 CSS px, au clavier et avec lecteur d'écran.
5. **Validation multi-version** : installer et comparer sur les environnements disponibles ; consigner explicitement les versions non testées.
6. **Décision de forme** : choisir entre barre inférieure, menu compact ou autre présentation après les tests, pas avant.

## 8. Limites et statut

Le premier composant est présent dans le dépôt, mais cela ne prouve pas encore son chargement, son rendu ni sa compatibilité runtime. Aucun template natif n'est surchargé. Les tests multi-versions, responsive et accessibilité restent à exécuter.

Les décisions encore ouvertes sont : destination d'accueil respectant les préférences utilisateur, nombre et ordre des destinations, emplacement smartphone et liste exacte des raccourcis par espace.
