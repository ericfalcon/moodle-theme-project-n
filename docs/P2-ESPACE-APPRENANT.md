# P2 — Espace apprenant

> Document de conception et de suivi de réalisation. Les éléments non testés ne sont pas réputés implémentés ni compatibles.

## 1. Objectif et périmètre

P2 transforme le tableau de bord existant en un espace apprenant cohérent et complète le parcours dans le cours. Le thème organise l'information et facilite les accès ; Moodle reste responsable des inscriptions, disponibilités, achèvements, échéances, notifications, capacités et contrôles d'accès.

Incréments livrables :
1. **P2.1 — Tableau de bord** : cartes de formation, progression, prochaine activité et actions à venir ; fiabilisation des états vides et des données.
2. **P2.2 — Page de cours** : hiérarchie du cours, accès aux sections et activités, navigation native conservée, présentation mobile.
3. **P2.3 — Échéances et notifications** : intégration des notifications et événements Moodle natifs, sans stockage ni système parallèle.
4. **P2.4 — États et aide** : chargement, absence de contenu, activité indisponible, erreur et aide contextuelle.
5. **P2.5 — Validation** : parcours complets, tests automatisés et manuels, accessibilité et responsive.

## 2. Principes fonctionnels

- Les cours affichés proviennent des API d'inscription Moodle ; aucun cours n'est déduit d'une simple URL ou d'un rôle global.
- Les cours où l'utilisateur intervient comme formateur restent distingués des formations suivies comme apprenant. Un compte peut apparaître dans les deux espaces.
- Les activités et ressources sont affichées selon leur disponibilité et leur visibilité effectives.
- La progression repose sur l'achèvement Moodle, uniquement lorsque le suivi est activé. Une absence de suivi ne doit jamais être présentée comme 0 %.
- La prochaine activité est la première activité visible et non achevée selon l'ordre du cours ; son URL est construite à partir des données de module fournies par Moodle.
- Les échéances proviennent des API calendrier/actions Moodle. Une action non réalisable n'est pas présentée comme un lien actif.
- Les notifications restent celles de Moodle (messagerie, notifications et préférences). Le thème améliore leur repérage sans remplacer les fournisseurs natifs.
- Les liens vers les pages natives conservent leurs contrôles d'accès serveur. Masquer un lien n'est jamais une mesure de sécurité.

## 3. Parcours prioritaires

### A. Reprendre une formation
1. Depuis le tableau de bord, repérer une formation suivie.
2. Comprendre son avancement, si le suivi est activé.
3. Identifier l'action suivante lorsqu'une activité visible reste à réaliser.
4. Ouvrir l'activité dans Moodle.
5. Revenir au cours et retrouver sa structure.

### B. Retrouver une activité dans un cours
1. Ouvrir un cours depuis le tableau de bord ou la liste native.
2. Identifier les sections et leur disponibilité.
3. Parcourir les ressources et activités dans l'ordre pédagogique du cours.
4. Ouvrir une activité et utiliser la navigation contextuelle Moodle.
5. Conserver les repères lorsque l'index du cours est replié sur smartphone.

### C. Traiter une échéance
1. Repérer une échéance sur le tableau de bord.
2. Identifier le cours, la date et l'action attendue.
3. Ouvrir l'action uniquement si l'API Moodle la déclare actionnable et fournit une URL valide.
4. Sans URL, afficher l'information sans faux bouton.

## 4. Architecture technique

- Le tableau de bord reste un fournisseur de contexte (classes/output/learner_dashboard.php) et un template Mustache.
- La page de cours reste rendue par Moodle et son format de cours. P2 privilégie les API Output et les points d'extension documentés de Boost/Moodle ; aucune copie complète du layout ou du moteur de format de cours.
- Navigation primaire/secondaire, fil d'Ariane, index du cours, liens précédent/suivant et contrôles d'activité natifs sont conservés.
- Toute surcharge Mustache est limitée à un composant précis, justifiée par un besoin démontré et comparée entre Moodle 4.5 à 5.3. Les chemins public/ et changements de templates sont pris en compte.
- Iomad n'ajoute aucune dépendance au chemin Moodle standard. Aucune requête directe aux tables Iomad.
- Les fonctions d'affichage et de navigation ne contournent jamais les capacités et contextes Moodle.

## 5. Mobile-first et accessibilité

- Conception d'abord à 390 CSS px, vérification à 320, 768 et 1280 CSS px.
- Aucun défilement horizontal global ; traitement dédié des contenus intrinsèquement larges.
- Cibles tactiles visées : au moins 44 × 44 CSS px pour les commandes principales.
- Titres de liens explicites ; libellés d'action compréhensibles hors contexte.
- Progression sémantique avec nom accessible et valeur numérique.
- Les états ne reposent jamais sur la couleur seule.
- Ordre DOM, visuel et focus cohérents ; focus visible ; clavier complet.
- Menus et tiroirs utilisables au clavier et au tactile, sans survol obligatoire.
- Vérifications axe-core, clavier, zoom 200 %, reflow 320 CSS px, NVDA et TalkBack. Zéro violation axe-core sur les états testés est requis, mais ne suffit pas à conclure à l'accessibilité.

## 6. Matrice minimale de scénarios

| Scénario | Données / profil | Résultat attendu |
|---|---|---|
| Aucun cours | Apprenant sans inscription active | État vide explicite, aucun faux lien |
| Cours sans achèvement | Suivi désactivé | Pas de progression artificielle |
| Cours avec achèvement | Activités suivies, certaines terminées | Pourcentage cohérent avec les activités visibles et suivies |
| Prochaine activité | Activité visible non terminée | URL exacte de l'activité |
| Activité masquée | Activité non visible | Ni affichage ni lien |
| Cours inaccessible | Inscription suspendue ou accès expiré | Contrôle serveur conservé, accès non garanti |
| Échéance actionnable | Événement avec URL valide | Lien exact vers l'action |
| Échéance informative | Événement sans URL actionnable | Information sans faux bouton |
| Multi-rôle | Apprenant dans un cours, formateur dans un autre | Deux espaces distincts |
| Iomad multi-entreprise | Utilisateur dans un contexte entreprise | Aucune donnée hors périmètre |

## 7. Validation requise

Chaque incrément fournit :
- tests PHPUnit non vides et assertions pertinentes ;
- contrôles de syntaxe et validation du plugin ;
- tests Moodle 4.5 et 5.3, Iomad 4.5 et 5.2 à chaque PR ; matrice complète planifiée pour les versions intermédiaires ;
- axe-core sur les pages et états réellement concernés, à toutes les largeurs prévues ;
- tests manuels clavier, tactile et lecteur d'écran ;
- inspection des logs et artefacts CI, correction des échecs avant fusion.

La validation du tableau de bord ne vaut pas validation de la page de cours ni des notifications. Les parcours non couverts restent explicitement « à valider ».

## 8. Suivi des incréments

| Incrément | État initial | Critère de clôture |
|---|---|---|
| P2.1 Tableau de bord | Première implémentation présente ; couverture partielle | Scénarios automatisés et testés sur les quatre environnements de référence |
| P2.2 Page de cours | À réaliser | Parcours sections/activités utilisable et accessible sur smartphone et desktop |
| P2.3 Échéances / notifications | Échéances calendrier présentes ; notifications non intégrées | API natives utilisées, préférences respectées, états et accès vérifiés |
| P2.4 États / aide | À compléter | États vides, erreurs, indisponibilités et aides testés |
| P2.5 Validation | Partielle | CI et tests manuels documentés, aucune anomalie bloquante non traitée |
