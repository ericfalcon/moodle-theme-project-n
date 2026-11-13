# Project N — Continuité intersessions

**Document de reprise obligatoire.** À consulter au début de chaque nouvelle conversation ou reprise de travail sur le dépôt.

## 1. Règle de continuité

Le travail ne doit pas dépendre de la mémoire de la conversation. Ce dépôt est la source de vérité opérationnelle : décisions, contraintes, état des branches, commits, tests, anomalies connues et prochaines actions doivent être consignés ici ou dans le document métier concerné.

À chaque étape significative :
1. relire l'état GitHub réel (branche, commit, PR, workflows et jobs) ;
2. relire les documents P0/P1/P2 pertinents avant toute recherche externe ;
3. consigner les décisions, les changements effectués, les résultats vérifiés et les points non vérifiés ;
4. indiquer le prochain travail concret et les dépendances ;
5. ne jamais présenter une hypothèse, une analyse statique ou un workflow encore en cours comme une validation réussie.

## 2. Documentation à consulter avant toute recherche Web

Commencer par le répertoire [docs](https://github.com/ericfalcon/moodle-theme-project-n/tree/main/docs), puis consulter selon le sujet :
- [P0-CADRAGE.md](P0-CADRAGE.md) — objectifs et décisions de conception ;
- [P0-MATRICE-COMPATIBILITE.md](P0-MATRICE-COMPATIBILITE.md) — versions et compatibilité ;
- [P0-COMPARAISON-VERSIONS-MOODLE-IOMAD.md](P0-COMPARAISON-VERSIONS-MOODLE-IOMAD.md) — écarts entre versions ;
- [P0-REX-EPURE-PROJECTN.md](P0-REX-EPURE-PROJECTN.md) — retour d'expérience Épure ;
- [P1-CI-TESTS.md](P1-CI-TESTS.md) — stratégie de tests et dépôts sources ;
- les documents P2/P3 correspondant à la fonctionnalité en cours.

Ne pas refaire une recherche déjà documentée sans vérifier d'abord si le dépôt ou les sources ont évolué.

## 3. Dépôts sources de référence

Les forks de l'utilisateur sont les sources de référence pour les comparaisons et les tests :
- [ericfalcon/moodle](https://github.com/ericfalcon/moodle) — fork Moodle à jour ;
- [ericfalcon/iomad](https://github.com/ericfalcon/iomad) — fork Iomad à jour, contenant notamment des modifications personnelles à préserver ;
- [ericfalcon/moodle-theme_epure](https://github.com/ericfalcon/moodle-theme_epure) — référence fonctionnelle et accessibilité ; consulter notamment `amd/src/aria_repair.js` ;
- [ericfalcon/moodle-theme-iomad](https://github.com/ericfalcon/moodle-theme-iomad) — ancien thème/fork de référence à examiner si pertinent ;
- [ericfalcon/moodle-theme-project-n](https://github.com/ericfalcon/moodle-theme-project-n) — dépôt cible.

### 3.1 Forks d'autres thèmes Moodle : corpus d'analyse obligatoire

L'utilisateur indique disposer sur son compte GitHub de forks de plusieurs thèmes Moodle qui avaient provoqué des problèmes d'affichage avec Épure. Ces dépôts doivent être retrouvés et examinés : ils constituent un corpus de régression visuelle et de compatibilité, pas une simple source d'inspiration.

**Inventaire à compléter par interrogation effective de GitHub :**
| Dépôt du fork | Thème amont | Version/branche Moodle ou Iomad | Problème d'affichage observé avec Épure | Leçon / risque pour Project N | Statut |
|---|---|---|---|---|---|
| À identifier dans les dépôts GitHub de l'utilisateur | À établir | À établir | À documenter depuis le code, les commits ou les échanges | À analyser | Non commencé |

Règles d'analyse :
- ne pas deviner les noms des forks ni les causes des problèmes ;
- rechercher les dépôts de l'utilisateur, y compris les forks dont le nom diffère de celui du thème amont ;
- examiner README, branches, commits spécifiques et fichiers de configuration, SCSS, templates Mustache, renderers et JavaScript concernés ;
- comparer les mécanismes de surcharge avec Épure et Boost, puis vérifier les écarts Moodle/Iomad ;
- distinguer clairement le problème constaté, sa cause démontrée et le risque potentiel ;
- reporter les résultats dans ce document et dans le document technique concerné ;
- transformer chaque problème confirmé en scénario de non-régression (capture/DOM, test navigateur, axe-core si pertinent).

Ne pas clore cet inventaire tant que les forks pertinents n'ont pas été identifiés ou que les limites d'accès GitHub ne sont pas explicitement consignées.

## 4. Principes techniques non négociables

- Thème Moodle-first, compatible Iomad ; Boost comme parent.
- Cibles : Moodle 4.5 à 5.3 ; Iomad 4.5 à 5.2 tant qu'une version Iomad 5.3 exploitable n'est pas disponible.
- Approche mobile-first ; prévoir la barre d'outils inférieure mobile demandée.
- Accessibilité intégrée dès la conception : clavier, lecteurs d'écran, noms accessibles, axe-core et tests réels.
- Préférer les points d'extension Moodle aux copies intégrales de templates.
- Ne pas perdre les personnalisations du fork Iomad.
- Toute analyse importante et toute décision doivent être tracées dans GitHub.
- Ne pas fusionner dans `main` sans autorisation explicite de l'utilisateur.

## 5. Discipline de validation et CI

- Toujours consulter les exécutions GitHub Actions réelles, leurs jobs, étapes et logs ; les badges et messages de commit ne suffisent pas.
- Après un échec, diagnostiquer la cause, corriger, committer et relancer les contrôles.
- Suivre le nouveau run jusqu'à son état terminal et inspecter les artefacts axe-core lorsqu'ils existent.
- Vérifier que la CI porte sur le SHA exact du dernier commit à valider.
- Une installation ou un test sur une seule version ne valide pas toute la matrice.
- Ne jamais déclarer un menu ou une interaction corrigés sans test navigateur qui reproduit l'action.
- Ne jamais annoncer « tous les tests passent » tant que tous les jobs requis sont terminés avec succès.

## 6. État de reprise — 13 novembre 2026

- Branche de travail connue : `feature/p2-learner-workspace`.
- PR connue : [#4](https://github.com/ericfalcon/moodle-theme-project-n/pull/4).
- Dernier SHA connu dans le contexte de reprise : `da284fd4e1728a4258badb35ce081a0954e3b924`. À revalider auprès de GitHub avant toute action.
- Correctif du menu utilisateur : suppression de `templates/core/user_menu.mustache` pour laisser Moodle/Boost fournir le template natif adapté à Bootstrap 4 ou 5. Commit connu : `84d864bd7eddf69fee4c64db2011b25752dc4f85`.
- Documentation du correctif : commit connu `da284fd4e1728a4258badb35ce081a0954e3b924`.
- Runs à vérifier avant de poursuivre : plateforme/accessibilité `43422808668`, qualité `43422808652`. Leur état terminal et leur SHA doivent être vérifiés en direct ; ne pas supposer qu'ils sont encore en cours ou réussis.
- Module `amd/src/aria_repair.js` inspiré d'Épure intégré au hook ; son résultat axe-core doit être vérifié dans les rapports de CI. Ne pas confondre intégration et validation.
- Le menu utilisateur n'est considéré comme corrigé qu'après succès des tests navigateur sur les environnements cibles.

## 7. Prochaine séquence de travail

1. Vérifier l'état réel de la branche, de la PR et des runs mentionnés ci-dessus.
2. Finaliser les corrections éventuelles révélées par les tests navigateur et axe-core ; relancer et suivre la CI complète.
3. Rechercher et inventorier les forks de thèmes Moodle de l'utilisateur mentionnés en section 3.1.
4. Exploiter ces forks pour construire une matrice de risques d'affichage et de compatibilité (Boost, SCSS, Mustache, renderers, JS, navigation, menus, blocs, formats de cours).
5. Reprendre P2.2 : affichage responsive des cours, sections et activités ; couvrir formats de cours, restrictions, achèvement et interactions mobiles par des tests.
6. Mettre à jour ce document à chaque changement d'étape ou de priorité.

## 8. Format de mise à jour du journal

Pour chaque reprise importante, ajouter une entrée datée :
- **Branche / SHA :**
- **Travail réalisé :**
- **Fichiers et documents modifiés :**
- **Tests lancés :**
- **Résultats observés (avec liens de runs) :**
- **Anomalies restantes :**
- **Prochaine action :**
