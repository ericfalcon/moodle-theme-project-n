# Project N — Architecture de navigation

> Document de décision P0 — approche hybride retenue le 31 octobre 2026. Les comportements visuels précis restent à prototyper et à tester sur les versions Moodle/Iomad ciblées.

## 1. Décision

Project N adopte une **navigation hybride** :

- **Project N** organise la navigation globale entre les grands espaces et les destinations les plus fréquentes.
- **Moodle** conserve la navigation contextuelle native : navigation secondaire de la page, navigation du cours, index des sections, actions de page, menus d'administration et navigation des activités.
- **Boost** reste le socle technique et fournit les composants et comportements réutilisables lorsque cela est pertinent.

La navigation Project N ne remplace ni les capacités Moodle, ni les contextes, ni les contrôles d'accès. Elle présente uniquement des destinations et actions déjà autorisées ; chaque page cible conserve ses contrôles serveur.

## 2. Pourquoi ce choix

| Option | Avantage | Limite |
|---|---|---|
| Reprendre le tiroir Boost comme navigation unique | Coût initial faible, continuité native | Hiérarchie et densité peu adaptées aux trois espaces et à une conception mobile-first |
| Remplacer toute la navigation par Project N | Cohérence visuelle et contrôle des parcours | Risque de réimplémenter des mécanismes Moodle, de perdre des extensions et d'accroître la maintenance |
| **Approche hybride retenue** | Repères globaux cohérents et contexte pédagogique natif | Nécessite de définir précisément les frontières entre les niveaux |

Le choix hybride évite de transformer le thème en système de navigation parallèle à Moodle.

## 3. Les quatre niveaux de navigation

### 3.1 Navigation globale Project N

Elle donne accès aux espaces et destinations principales, avec un contenu adapté au profil et aux capacités effectives :

- accueil / tableau de bord ;
- formations ou cours ;
- espace de travail correspondant au profil (par exemple activités à traiter pour un formateur) ;
- entrée « Plus » ou menu complet pour les destinations secondaires.

Les entrées exactes ne sont pas figées dans ce document : elles seront déterminées par la cartographie des tâches et les maquettes. Il ne faut pas afficher systématiquement trois espaces « apprenant / formateur / administrateur » à chaque utilisateur. Un utilisateur peut cumuler plusieurs rôles ; la navigation doit refléter ses capacités et contextes réels, sans supposer qu'un rôle unique décrit son profil.

### 3.2 Navigation d'espace

Elle organise les fonctions d'un espace sans dupliquer les pages Moodle :

- apprenant : formations suivies, à reprendre, échéances et ressources ;
- formateur : cours enseignés, conception, participants, évaluations et corrections ;
- administrateur : domaines de gestion, recherche de paramètres et raccourcis ;
- gestionnaire Iomad : fonctions disponibles dans le périmètre de son entreprise.

Les éléments non couverts par le MVP renvoient vers les pages natives correspondantes. Les raccourcis administratifs ne doivent pas masquer la navigation d'administration native.

### 3.3 Navigation contextuelle Moodle

Elle demeure la référence dans les pages Moodle :

- menu secondaire de la page (primary/secondary navigation et menus associés selon le contexte et la version) ;
- fil d'Ariane ;
- index du cours et navigation entre sections/activités ;
- navigation propre à l'activité ;
- menu d'actions et paramètres de la page ;
- navigation et recherche de l'administration ;
- blocs latéraux lorsqu'ils sont présents et utiles.

Project N peut harmoniser leur présentation, leur position ou leur adaptation responsive, mais ne doit pas supprimer une fonction native sans équivalent démontré et testé.

### 3.4 Navigation personnelle et transversale

Les accès au profil, aux préférences, à la langue, aux notifications, à la messagerie, à l'aide et au mode de couleur restent identifiables et disponibles depuis l'en-tête ou un menu personnel. Leur emplacement exact sera testé sur smartphone et grand écran.

## 4. Répartition des responsabilités techniques

| Élément Moodle/Boost | Décision Project N |
|---|---|
| Barre primaire Boost | Peut être présentée dans l'identité Project N ; ne pas recopier sa logique de génération |
| Menu secondaire | Conserver les données et mécanismes natifs ; adapter la présentation si nécessaire |
| Fil d'Ariane | Conserver ; ne pas le remplacer par un titre seul |
| Index / tiroir de cours | Conserver le mécanisme natif et son contexte ; adapter son déclenchement et sa présentation mobile |
| Tiroir des blocs | Conserver lorsqu'il est disponible et pertinent ; ne pas l'utiliser comme menu principal |
| Menu utilisateur | Conserver le menu et les actions natives, avec présentation adaptée |
| Navigation d'administration | Conserver la recherche, les catégories, les pages et les contrôles natifs ; ajouter des raccourcis Project N sans créer une administration parallèle |
| Navigation d'activité | Conserver les liens précédent/suivant et les commandes propres à l'activité |
| Sortie des plugins | Préserver les points d'extension documentés ; tester les plugins qui injectent des éléments dans la barre de navigation |

## 5. Comportement responsive à prototyper

### Smartphone

Hypothèse de prototype :

- en-tête compact : identité du site, accès personnel et notifications selon les sorties disponibles ;
- navigation globale persistante en bas d'écran ou solution équivalente, limitée à quelques destinations fréquentes ;
- entrée « Plus » pour les destinations secondaires ;
- navigation d'espace présentée dans la page, sous forme de liste, menu ou onglets accessibles selon le nombre d'entrées ;
- navigation de cours accessible à la demande, sans occuper en permanence une grande partie de l'écran ;
- tiroir de blocs non prioritaire et accessible seulement lorsqu'il apporte une fonction réelle.

La barre inférieure n'est pas encore un choix graphique définitif. Le prototype devra vérifier qu'elle ne masque pas les commandes, les messages, les champs, les boutons de validation ou le contenu en présence du clavier virtuel. Prévoir les zones de sécurité des appareils, un état actif explicite et une alternative accessible.

### Tablette

- conserver les mêmes destinations et libellés que sur smartphone ;
- utiliser l'espace supplémentaire pour rendre la navigation d'espace plus visible ;
- éviter un changement complet de modèle mental entre smartphone et desktop.

### Ordinateur

- navigation globale horizontale ou latérale selon le prototype ;
- navigation secondaire et contextuelle conservée à proximité du contenu concerné ;
- index du cours et blocs disponibles dans les tiroirs ou régions natives lorsque la page les fournit ;
- ne pas multiplier les barres fixes qui réduisent la hauteur utile.

## 6. Règles d'interaction et d'accessibilité

- Chaque zone de navigation possède un nom accessible et un rôle sémantique correct.
- Utiliser des liens pour naviguer et des boutons pour déclencher une action.
- Indiquer la page courante avec un état programmatique (par exemple `aria-current="page"`) et pas seulement une couleur.
- Préserver un ordre DOM, de lecture et de focus cohérent avec l'ordre visuel.
- Assurer le fonctionnement clavier, le focus visible, l'ouverture/fermeture et le retour du focus des menus et tiroirs.
- Les menus ne doivent pas dépendre du survol.
- Les icônes seules doivent avoir un nom accessible ; les libellés visibles sont préférables pour les destinations principales.
- Les changements de contexte ne doivent pas être annoncés de manière excessive ; les mises à jour dynamiques utiles doivent être perceptibles par les technologies d'assistance.
- La navigation doit rester utilisable à 200 % de zoom et à 320 CSS px.
- Tester les cibles tactiles, l'espacement, les zones de sécurité et l'interaction avec le clavier virtuel.
- Vérifier la navigation avec NVDA et TalkBack, en complément d'axe-core.

## 7. Sécurité et Iomad

- La visibilité d'un lien n'est jamais un contrôle d'accès.
- Les destinations et raccourcis sont calculés à partir des capacités, contextes et API Moodle/Iomad disponibles.
- Ne pas se fier à un nom de rôle, à un libellé ou à une chaîne de version pour décider d'un accès.
- Ne pas interroger directement les tables internes Iomad pour construire la navigation.
- Les gestionnaires Iomad ne voient que les destinations et données de leur périmètre entreprise.
- Tester les accès directs aux URL, les changements de contexte et les utilisateurs appartenant à au moins deux entreprises.
- Un utilisateur ayant plusieurs fonctions doit pouvoir accéder aux destinations autorisées sans basculer artificiellement entre des profils exclusifs.

## 8. Compatibilité Moodle 4.5 à 5.3

La navigation doit être conçue à partir des points d'extension et templates réellement présents dans chaque branche, sans copier les anciens templates de navigation Boost.

Constats vérifiés dans les sources Boost :

- Moodle 4.5 : `drawers.mustache` compose notamment le tiroir d'index du cours, le tiroir des blocs, la barre de navigation, le contenu principal et le menu secondaire ; `navbar.mustache` rend la navigation primaire, les sorties de plugins et le menu utilisateur.
- Moodle 5.2 : Boost supprime `flat_navigation.mustache` et `nav-drawer.mustache`. Project N ne doit donc pas fonder son architecture sur ces templates.
- Moodle 5.3 : `drawers.mustache` utilise le bloc `drawercontrols` à la place de `drawerheadercontent`, et le menu secondaire est rendu par le partiel `core/secondarymoremenu`. La barre de navigation inclut notamment le menu de mode de couleur.
- Les chemins du cœur changent avec la restructuration `public/` à partir de Moodle 5.1 ; les références de fichiers et procédures de développement doivent en tenir compte.

Conséquences :

1. Éviter de surcharger `nav-drawer.mustache` ou `flat_navigation.mustache`.
2. Ne pas copier intégralement `drawers.mustache` ou `navbar.mustache` tant qu'un prototype ne démontre pas que les points d'extension disponibles sont insuffisants.
3. Si une surcharge de layout ou de template est nécessaire, la limiter et documenter les versions concernées.
4. Tester les menus primaires/secondaires, les tiroirs, les sorties de plugins et le mode de couleur sur Moodle 4.5 et au moins une version 5.x récente, puis étendre à la matrice retenue.
5. Pour Iomad, valider les mêmes parcours avec les versions publiées réellement installables.

## 9. Parcours de validation du prototype

Le prototype devra permettre de comparer au minimum les scénarios suivants :

| Profil / contexte | Tâche | Points à observer |
|---|---|---|
| Apprenant | Retrouver une formation et reprendre une activité | Nombre d'étapes, compréhension, accès au cours |
| Apprenant | Naviguer d'une section à une activité puis revenir au cours | Cohérence entre navigation globale et index du cours |
| Formateur | Retrouver un cours, activer l'édition et ajouter une activité | Accès aux actions, maintien du contexte, accès aux réglages avancés |
| Formateur | Retrouver les travaux à corriger | Repérage des évaluations et retour au cours |
| Administrateur | Trouver un réglage natif | Complémentarité entre raccourcis Project N et recherche native |
| Gestionnaire Iomad | Accéder à une fonction de gestion de son entreprise | Cohérence du périmètre et absence de lien/donnée hors entreprise |
| Utilisateur multi-rôles | Passer d'une tâche apprenant à une tâche formateur | Pas de bascule artificielle de profil ni perte de contexte |

Pour chaque tâche, mesurer réussite autonome, temps, erreurs, retours arrière, demandes d'aide et compréhension des libellés. Tester au clavier, au tactile et avec lecteur d'écran.

## 10. Décisions confirmées et points restant à prototyper

**Décision confirmée :** architecture hybride (navigation globale Project N + navigation contextuelle native Moodle/Boost).

**À prototyper avant P0 terminé :**

- nombre et libellés des destinations globales ;
- comportement smartphone : barre inférieure, menu compact ou autre solution ;
- organisation de la navigation d'espace pour les profils et utilisateurs multi-rôles ;
- présentation du menu secondaire sur petit écran ;
- accès à l'index du cours et aux blocs ;
- position des notifications, de la messagerie, de l'aide et du menu personnel ;
- limites des surcharges de templates par version.

## 11. Sources techniques inspectées

- [Boost Moodle 4.5 — drawers.mustache](https://github.com/moodle/moodle/blob/MOODLE_405_STABLE/theme/boost/templates/drawers.mustache)
- [Boost Moodle 4.5 — navbar.mustache](https://github.com/moodle/moodle/blob/MOODLE_405_STABLE/theme/boost/templates/navbar.mustache)
- [Boost Moodle 5.3 — drawers.mustache](https://github.com/moodle/moodle/blob/MOODLE_503_STABLE/public/theme/boost/templates/drawers.mustache)
- [Boost Moodle 5.3 — navbar.mustache](https://github.com/moodle/moodle/blob/MOODLE_503_STABLE/public/theme/boost/templates/navbar.mustache)
- [Boost — UPGRADING.md](https://github.com/moodle/moodle/blob/MOODLE_503_STABLE/public/theme/boost/UPGRADING.md)

Ces sources sont des instantanés de branches de développement/stabilité. Les détails doivent être revérifiés sur les tags et environnements effectivement retenus pour les tests du projet.
