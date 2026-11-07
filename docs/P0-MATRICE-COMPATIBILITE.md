# Project N — Matrice de compatibilité Moodle / Iomad

> Registre vivant des points communs et différences entre les versions cibles. À renseigner au fil de chaque tranche de cartographie, avant de considérer un mécanisme comme spécifié pour Project N.

## 1. Périmètre cible

| Plateforme | Version | Branche/tag exact | Commit examiné | Statut |
|---|---:|---|---|---|
| Moodle | 4.5 | À relever | À relever | À comparer |
| Moodle | 5.0 | `MOODLE_500_STABLE` | À relever au prochain contrôle | Référence initiale des sections 65 à 68 |
| Moodle | 5.1 | À relever | À relever | À comparer |
| Moodle | 5.2 | À relever | À relever | À comparer |
| Moodle | 5.3 | À relever | À relever | À comparer |
| Iomad | 4.5 | Branche correspondante à Moodle 4.5 | À relever | À comparer |
| Iomad | 5.0 | Branche correspondante, si publiée | À relever | À vérifier |
| Iomad | 5.1 | Branche correspondante, si publiée | À relever | À vérifier |
| Iomad | 5.2 | Branche correspondante à Moodle 5.2 | À relever | À comparer |
| Iomad | 5.3 | Non disponible selon l'état de référence du projet au 7 novembre 2026 | — | Non testable |

Les versions Iomad et Moodle sont comparées par génération correspondante. Ne jamais déduire la compatibilité d'une branche Iomad avec une autre génération Moodle.

## 2. Légende de preuve

- **Commun confirmé** : même mécanisme vérifié sur toutes les versions concernées.
- **Différence confirmée** : changement localisé et vérifié dans le code.
- **Ajout / retrait** : mécanisme introduit, supprimé ou déprécié.
- **Conditionnel** : dépend d'une capacité, d'un plugin, d'une configuration ou d'un contexte.
- **À tester** : le code statique ne suffit pas ; test runtime, mobile, clavier ou lecteur d'écran nécessaire.
- **Non vérifié** : branche non examinée, source inaccessible ou constat incomplet.

Un champ vide n'est pas une preuve d'identité entre versions. Il signifie « non renseigné ».

## 3. Registre des domaines

| Domaine | Moodle 4.5 | Moodle 5.0 | Moodle 5.1 | Moodle 5.2 | Moodle 5.3 | Écart / impact thème | Preuve / statut |
|---|---|---|---|---|---|---|---|
| Navigation globale et secondaire | Non vérifié | Cartographie initiale antérieure | Non vérifié | Non vérifié | Non vérifié | Vérifier layouts, navbar, tiroirs, sélecteurs DOM et JS | À comparer |
| Layouts Boost et templates | Non vérifié | Référence initiale | Non vérifié | Non vérifié | Non vérifié | Compatibilité des surcharges et templates | À comparer |
| Tableau de bord apprenant | Non vérifié | Cartographie initiale antérieure | Non vérifié | Non vérifié | Non vérifié | Widgets, blocs, cours et responsive | À comparer |
| Page de cours et formats | Non vérifié | Cartographie initiale antérieure | Non vérifié | Non vérifié | Non vérifié | Output API, formats et édition | À comparer |
| Activités et ressources | Non vérifié | Cartographie initiale antérieure | Non vérifié | Non vérifié | Non vérifié | Contrats de rendu par activité | À comparer |
| Profil et préférences | Non vérifié | Cartographie initiale antérieure | Non vérifié | Non vérifié | Non vérifié | Formulaires, champs, notifications | À comparer |
| Disponibilité et achèvement | Non vérifié | Cartographie initiale antérieure | Non vérifié | Non vérifié | Non vérifié | États, indicateurs et conditions | À comparer |
| Fichiers et éditeurs | Non vérifié | Cartographie initiale antérieure | Non vérifié | Non vérifié | Non vérifié | File API, éditeurs, sélecteurs | À comparer |
| Espace formateur | Non vérifié | Sections 65 / code MOODLE_500_STABLE | Non vérifié | Non vérifié | Non vérifié | Gestion cours, participants, notes, corrections | À comparer |
| Comptes utilisateurs | Non vérifié | Section 66 / code MOODLE_500_STABLE | Non vérifié | Non vérifié | Non vérifié | Report Builder, filtres, actions groupées | À comparer |
| Cohortes | Non vérifié | Section 66 / code MOODLE_500_STABLE | Non vérifié | Non vérifié | Non vérifié | Portée système/catégorie, synchronisation | À comparer |
| Catégories et cours plateforme | Non vérifié | Section 67 / code MOODLE_500_STABLE | Non vérifié | Non vérifié | Non vérifié | Arborescence, actions, capacités | À comparer |
| Méthodes d'inscription | Non vérifié | Section 68 / code MOODLE_500_STABLE | Non vérifié | Non vérifié | Non vérifié | Plugins, instances, statuts, synchronisation | À comparer |
| Rôles et capacités | Non vérifié | Cartographie partielle antérieure | Non vérifié | Non vérifié | Non vérifié | Contextes, héritage, rôles Iomad | À compléter |
| Rapports d'administration | Non vérifié | Non vérifié | Non vérifié | Non vérifié | Non vérifié | Report Builder, exports, permissions | À analyser |
| Paramètres de plateforme | Non vérifié | Non vérifié | Non vérifié | Non vérifié | Non vérifié | Arborescence admin et capacités | À analyser |
| Iomad multi-entreprise | Sans objet | À comparer selon branche | À comparer selon branche | À comparer selon branche | Non disponible | Isolation, périmètre entreprise, rôles | À comparer |

Ce tableau est volontairement conservateur : « cartographie initiale antérieure » ne signifie pas comparaison interversions terminée.

## 4. Fiche obligatoire pour chaque différence

Pour chaque écart constaté, ajouter une entrée avec ce format :

### [ID] Domaine — intitulé court

- **Plateformes / versions :**
- **Branche, tag et commit :**
- **Fichiers et symboles concernés :**
- **Comportement commun confirmé :**
- **Différence constatée :**
- **Version d'apparition / disparition :**
- **Capacités et contextes concernés :**
- **Contrat technique concerné :** API, renderer, template Mustache, AMD/JavaScript, événement, attribut DOM…
- **Impact sur Project N :**
- **Stratégie :** code commun, branche conditionnelle, surcharge dédiée, adaptation CSS, absence de surcharge…
- **Tests à exécuter :**
- **Niveau de preuve :** code comparé / test exécuté / hypothèse / non vérifié.
- **Référence dans la cartographie :**

## 5. Règle de mise à jour continue

À chaque tranche de travail :
1. examiner les branches cibles pertinentes, et pas seulement la version de référence ;
2. noter les points communs et différences dans ce registre ;
3. enrichir la cartographie fonctionnelle avec les références de code exactes ;
4. mettre à jour la roadmap et le README si l'état ou les priorités changent ;
5. relire les fichiers depuis GitHub après commit ;
6. ne déclarer une compatibilité acquise qu'après les validations prévues.

Les sections 65 à 68 ont été rédigées à partir de Moodle 5.0. Elles ne constituent pas une preuve de compatibilité sur Moodle 4.5, 5.1, 5.2 ou 5.3. La comparaison doit être menée rétroactivement pour ces sections, puis systématiquement pour les suivantes.

## 6. Principe de conception multi-version

Le thème doit rester installable et fonctionnel sur Moodle 4.5, 5.0, 5.1, 5.2 et 5.3. Les adaptations doivent être minimisées et documentées :
- privilégier les API stables et les points d'extension publics ;
- éviter les copies de templates entiers lorsqu'une surcharge ciblée suffit ;
- isoler les adaptations de version dans des classes/fichiers dédiés lorsque nécessaire ;
- ne pas se baser uniquement sur le numéro de version si une détection de capacité ou de fonctionnalité est plus robuste ;
- ne jamais masquer une différence fonctionnelle par du CSS ;
- tester les parcours essentiels sur chaque génération cible.
