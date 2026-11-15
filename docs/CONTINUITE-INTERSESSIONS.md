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

**Vérification effectuée le 13 novembre 2026 :** l'action GitHub « lister les dépôts accessibles » a retourné 32 dépôts du compte `ericfalcon`. Parmi eux, les seuls dépôts explicitement identifiés comme thèmes sont :
- `ericfalcon/moodle-theme_epure` : thème Épure indépendant ;
- `ericfalcon/moodle-theme-iomad` : ancien dépôt contenant `theme/epure`, d'après son README ;
- `ericfalcon/moodle-theme-project-n` : dépôt cible.

Aucun autre fork de thème Moodle distinct n'apparaît dans cette liste accessible. En revanche, plusieurs dépôts de formats de cours existent (`moodle-format_tiles`, `moodle-format_flexsections`, `moodle-format_remuiformat`, `moodle-format_trail`) : ce ne sont pas des thèmes, mais leurs DOM/CSS peuvent avoir des effets sur la présentation des cours et ils méritent une analyse séparée dans P2.2. Cette vérification ne prouve pas l'absence d'autres dépôts privés ou non accessibles par la connexion GitHub active. Ne pas les confondre avec les forks de thèmes recherchés.

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

**Mise à jour après diagnostic CI :**
- Dernier commit de code connu : `fb980051188b982eca7b286299f9fee65a565b10` sur `feature/p2-learner-workspace`. Revalider le SHA sur GitHub avant toute action.
- Le run précédent `43422808668` (SHA `da284fd...`) a bien terminé avec des échecs navigateur : PHPUnit réussi sur les quatre plateformes ; l'étape « Run axe-core browser audit » a échoué sur Moodle 4.5, Moodle 5.3 et Iomad 4.5 ; Iomad 5.2 a été annulé. L'échec réel est l'ouverture du menu utilisateur natif, pas une erreur PHPUnit.
- Le run `43423643084` sur `ac292f8...` reproduit les échecs Moodle 4.5 et 5.3 ; Iomad 4.5 et Iomad 5.2 étaient encore en cours lors de la dernière consultation. Ne pas le considérer comme validé.
- Les captures `axe-Moodle-4.5` et `axe-Moodle-5.3` de `43422808668` contiennent des captures d'écran des violations relevées avant l'échec du menu, mais aucun rapport JSON complet n'a été conservé : le script s'arrêtait avant l'écriture du rapport.
- Correctif du script de test committé : `fb980051188b982eca7b286299f9fee65a565b10`. Il attend maintenant les modules AMD natifs `theme_boost/loader` et `core/usermenu` avant de cliquer, et conserve un rapport JSON partiel en cas d'exception. Ce changement améliore le diagnostic ; il ne constitue pas encore une correction du menu.
- Runs sur le SHA courant à suivre : qualité `43424395068`, plateforme/accessibilité `43424395033`.

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


## 9. Reprise du 15 novembre 2026

- **Branche / SHA de départ :** `feature/p2-learner-workspace` / `9907607070b13b4e2242e89886d1be07954fff98`.
- **PR :** [#4](https://github.com/ericfalcon/moodle-theme-project-n/pull/4), toujours en brouillon et non fusionnée au début de la reprise.
- **Runs constatés sur le SHA de départ :** `43640443069` (plateforme/accessibilité) et `43640443074` (qualité) sont signalés en échec par GitHub ; les API de jobs ne retournent toutefois aucune étape ni log exploitable. Les jobs ont été relancés pour obtenir une exécution diagnostiquable ; ces tentatives concernent le SHA antérieur.
- **Travail ajouté :** `tests/ci/learner-course-check.cjs` valide un parcours apprenant authentifié sur les cours `topics` et `weeks`, la visibilité des activités et l'absence de débordement horizontal aux quatre largeurs de référence.
- **Workflow :** une étape dédiée lance ce contrôle après le démarrage du serveur. Les variables écrites par `prepare-learner.php` dans `GITHUB_ENV` sont héritées par le processus Node ; elles ne sont pas réinjectées via les expressions `${{ env.* }}`.
- **Documentation :** `docs/P2-ESPACE-APPRENANT.md` section 12 et `ROADMAP.md` mis à jour.
- **Tests :** aucune nouvelle validation runtime n'est revendiquée à ce stade. La CI doit être lue sur le SHA final après les commits de cette reprise.
- **Limites restantes :** format `singleactivity`, sections à disponibilité conditionnelle, formats tiers, notifications natives et tests manuels clavier/lecteurs d'écran.
- **Prochaine action :** vérifier les nouveaux runs de la tête de branche, corriger chaque échec à partir des logs, puis compléter le scénario `singleactivity` et les restrictions de section.
