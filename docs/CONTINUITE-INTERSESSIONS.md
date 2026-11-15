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


## Reprise du 15 novembre 2026 — ajout des scénarios P2.2

La PR #4 reste ouverte en brouillon, non fusionnée, sur `feature/p2-learner-workspace`. La tête vérifiée avant les nouveaux changements était `262a5202d6a99d77bbb4d8ad30dc1a8cc7111aeb` ; PR toujours `mergeable:true` et `merged:false`.

### État CI constaté

Les exécutions associées à cette tête (`43745963514`, qualité ; `43745963487`, plateformes) sont terminées en échec. Les trois contrôles de syntaxe PHP 8.1/8.2/8.3 sont en échec ; les quatre jobs de référence Moodle 4.5, Moodle 5.3, Iomad 4.5 et Iomad 5.2 sont également en échec. La matrice complète planifiée/manuelle est ignorée, conformément au déclenchement. Les endpoints de journaux GitHub renvoient `BlobNotFound` et les étapes ne sont pas disponibles via l'API utilisée : la cause précise n'est donc pas établie. Le statut combiné de la tête ne retourne aucun check exploitable.

### Modifications depuis la tête vérifiée

- `tests/ci/prepare-learner.php` : ajout de deux sections soumises à disponibilité par date et d'un cours `singleactivity` avec activité Page principale ; réutilisation idempotente du modèle de ressource Page.
- `tests/ci/learner-course-check.cjs` : vérification des deux états de disponibilité, du parcours natif de redirection `singleactivity` et du reflow responsive.

Les commits de ce lot sont `107363e`, `d01c819`, `a554d1f` et `2de9d12` (préparation de la fixture, contrôle singleactivity, contrôle des restrictions et idempotence). La tête actuelle doit être relue sur GitHub avant tout nouveau changement ; aucune exécution CI postérieure à ces commits n'a encore été vérifiée ici.

### Prochaine étape obligatoire

1. Vérifier la tête exacte et les nouveaux workflows déclenchés par la PR.
2. Obtenir les journaux de syntaxe PHP et de chaque plateforme ; ne pas déduire la cause de l'échec à partir du seul statut.
3. Corriger les défauts révélés, puis vérifier les quatre jobs de référence et les rapports axe-core.
4. Si les journaux restent indisponibles, ajouter un diagnostic explicite au workflow sans désactiver les contrôles.
5. Ne fusionner dans `main` qu'après réussite réelle de tous les jobs requis et revue des parcours P2.2.


## État après ajout des scénarios P2.2 — 15 novembre 2026, 18:43 UTC

Tête de PR #4 vérifiée : `7ad63f21ab690410da3a1de49fedf3c373111530`. PR ouverte, brouillon, non fusionnée, base `main`, `mergeable:true`, `merged:false`.

Les commits de cette reprise couvrent : `107363e` (fixtures disponibilité + singleactivity), `d01c819` (parcours Playwright singleactivity), `a554d1f` (assertions disponibilité remplie/non remplie), `2de9d12` (idempotence du modèle Page), `df064d1`, `a96abfc`, `35d0924` (documentation), `f86aa10` et `7ad63f2` (diagnostics syntaxe PHP).

### CI du SHA courant

GitHub Actions a créé les runs : qualité `43748266551` (run 171), échec sur PHP 8.1, 8.2 et 8.3 ; plateformes `43748266547` (run 166), échec sur Moodle 4.5, Moodle 5.3, Iomad 4.5 et Iomad 5.2 ; matrice complète ignorée.

Les résumés d'étapes sont vides via l'API connectée. Le téléchargement des journaux renvoie `BlobNotFound` pour chaque job ; aucun artefact de diagnostic PHP n'est retourné. La cause des échecs n'est donc pas démontrée. Ne pas conclure à un défaut de syntaxe du code sans le journal correspondant.

**Décision :** ne pas fusionner. Les nouveaux scénarios sont codés, mais leur exécution réelle n'est pas prouvée. Prochaine action : récupérer les logs/artefacts directement dans l'interface GitHub Actions ou réexécuter les jobs lorsque le stockage des logs est disponible ; corriger la première étape en échec puis relancer la matrice.


## Reprise CI P2.2 — 15 novembre 2026 (soir)

La tête de PR #4 a été vérifiée : `170c5eaa17b666251f47e0efde73e3b34e76d76d`. Les workflows associés (runs `43748347207` et `43748347132`) échouaient sur les trois versions PHP et les quatre plateformes. Les API GitHub renvoient `BlobNotFound` pour les journaux de jobs, `steps` vides et aucun artefact de diagnostic ; la cause ne peut donc pas être attribuée avec certitude.

L'inspection du workflow a néanmoins révélé une régression certaine dans le contrôle de locale : l'expression grep avait deux antislashs (`^en_AU\\.utf8$`) au lieu d'un (`^en_AU\.utf8$`). Correction appliquée dans `.github/workflows/platform-test-reusable.yml`, commit `eca9a36a4ae3bcd5f0ff3e4fea549dc3e3465a4d`.

Les nouveaux runs sur ce commit (`43768859851` plateformes et `43768859847` qualité) échouent également ; leurs journaux sont encore inaccessibles. Les jobs ont été relancés via GitHub Actions ; vérifier leur nouvel état et récupérer les logs dès qu'ils sont disponibles. La correction de locale ne suffit donc pas à expliquer les échecs de tous les jobs. Ne pas fusionner avant diagnostic et CI verte.


## Correctif de compatibilité PHPUnit et conservation des diagnostics — 15 novembre 2026

Après inspection du workflow réutilisable `.github/workflows/platform-test-reusable.yml`, un risque de compatibilité certain a été corrigé : `--fail-on-empty-test-suite` était transmis sans condition, alors que la cible Moodle/Iomad 4.5 utilise PHPUnit 9. Cette option n'est pas disponible sur PHPUnit 9. Le workflow n'ajoute désormais cette option qu'à partir de PHPUnit 10 ; pour PHPUnit 9, il exécute d'abord `--list-tests` et vérifie qu'au moins un test Project N est découvert. La sortie de découverte et d'exécution est conservée dans des fichiers de diagnostic.

Un artefact `platform-diagnostics-*` est désormais téléversé avec `if: always()` pour conserver les sorties PHPUnit et le journal du serveur même si les tests échouent. Modifications : commits `a1deeb146e88f573041fe6026b2b6f21a5fd6cda` puis `20d1c31760582de4a94a1d081bb7015befd14285`.

Cette anomalie ne suffit pas à expliquer les échecs des trois jobs de syntaxe PHP ; ceux-ci restent à diagnostiquer. Les runs précédents échouent tous avant qu'on puisse obtenir leurs étapes ou journaux via le connecteur (`BlobNotFound`). La tête actuelle `20d1c31760582de4a94a1d081bb7015befd14285` doit encore être exécutée : aucun run PR n'était remonté immédiatement après le push. Aucun test n'est déclaré vert et aucune fusion n'est autorisée à ce stade.


## Confirmation du défaut d'exécution GitHub Actions — 15 novembre 2026, 22:26 UTC

Les workflows de la tête `3a5d6c1a0c51ef0db80fc9bc8311d57f175f839e` sont `43769167853` (plateformes) et `43769167847` (qualité). Pour les 7 jobs en échec, l'API GitHub renvoie `steps: []` ou aucun détail de job ; les logs renvoient `BlobNotFound` et les deux runs n'ont aucun artefact. Il n'y a donc aucune étape PHP, installation Moodle/Iomad, PHPUnit ou Playwright dont l'échec puisse être imputé au code : les jobs ne fournissent aucun indice d'exécution. Cela pointe vers un échec de démarrage/initialisation des jobs GitHub Actions, mais la cause infrastructure exacte n'est pas exposée.

Les jobs en échec ont été relancés. Sur la relance, les quatre jobs plateformes sont déjà retombés en échec avec `steps: []` ; les trois jobs qualité étaient encore `queued` lors de la dernière lecture. Vérifier leur état avant toute conclusion. Ne pas multiplier les modifications de code tant que GitHub ne fournit pas d'étapes ou de logs. La correction PHPUnit 9 reste une mesure de compatibilité préventive, pas une cause démontrée des échecs actuels.


## Vérification complémentaire CI — 15 novembre 2026, 22:27 UTC

- **Branche / SHA :** `feature/p2-learner-workspace` / `0d1a5c88b5176d342dbadd32a0b8abe1893719ac`.
- **PR #4 :** ouverte, draft, non fusionnée, `mergeable:true`.
- **Nouveaux runs sur le SHA courant :** qualité `43769234522` (run 179), plateformes `43769234551` (run 174). Les deux sont terminés en échec.
- **Jobs qualité :** PHP 8.1 `132981173692`, PHP 8.2 `132981173923`, PHP 8.3 `132981173943` — tous en échec.
- **Jobs plateformes :** Iomad 5.2 `132981173913`, Moodle 4.5 `132981174063`, Moodle 5.3 `132981174095`, Iomad 4.5 `132981174190` — tous en échec ; matrice complète ignorée.
- **Diagnostic :** pour les sept jobs en échec, l'API renvoie un tableau d'étapes vide et le téléchargement de logs renvoie `BlobNotFound` (404). Aucun artefact récupérable. Le problème se reproduit donc sur deux nouveaux runs déclenchés par le SHA final, y compris les jobs PHP de syntaxe indépendants des plateformes.
- **Inspection statique des workflows :** les deux workflows utilisent `runs-on: ubuntu-latest`, avec des étapes explicites ; aucune erreur YAML manifeste n'a été identifiée lors de cette lecture. Le workflow qualité expose également `workflow_dispatch`, mais aucun outil connecté ne permet ici de le déclencher. Le connecteur ne donne pas accès aux détails d'état des runners GitHub hébergés.
- **Conclusion prudente :** aucun élément ne permet d'attribuer ces échecs au code PHP, aux tests Moodle ou à axe-core, puisque aucune étape de job n'est exposée. L'hypothèse d'un problème d'exécution/initialisation GitHub Actions reste plausible mais non démontrée. Ne pas multiplier les modifications de code à l'aveugle.
- **Action suivante :** vérifier dans l'interface Actions GitHub les détails de ces runs (notamment les annotations système et la raison de fin des jobs), puis relancer depuis GitHub si nécessaire. Une fois les logs accessibles, corriger le premier défaut réel. Aucun test n'est validé par ces exécutions et aucune fusion ne doit être faite.


## Diagnostic approfondi des jobs GitHub Actions — 15 novembre 2026

L'inspection directe des réponses REST GitHub des runs `43769355760` (qualité) et `43769355921` (plateformes), sur le SHA `746da4e25f5ebb3d5e060e1047e86c7e0efc7cf4`, apporte un élément déterminant :

- Les sept jobs échoués ont tous `steps: []`.
- Les sept jobs ont `runner_id: 0`, `runner_name: ""`, `runner_group_id: 0` et `runner_group_name: ""`.
- Chaque job a été créé, démarré et terminé en environ deux secondes (22:27:05–22:27:08 UTC).
- Les jobs ne sont donc pas arrivés à une exécution sur un runner GitHub Actions identifié. Ce constat est beaucoup plus précis qu'une simple absence de logs : aucun runner n'a été attribué dans les données renvoyées par GitHub.
- Les deux workflows ont été relancés une nouvelle fois avec succès via l'API (requêtes de relance acceptées). Il faut contrôler les nouvelles tentatives avant toute conclusion définitive.
- La PR #4 est toujours en draft et non fusionnée. Aucun changement du code applicatif n'est justifié par ces échecs pré-runner.

**Conclusion opérationnelle :** le blocage observé se situe au niveau de l'attribution/initialisation des runners GitHub Actions, et non dans une étape PHP, PHPUnit, Moodle ou axe-core. La cause racine du refus d'attribution reste à déterminer côté GitHub. Si la nouvelle tentative reproduit `runner_id: 0`, escalader vers l'état du service GitHub Actions ou les limites/paramètres d'exécution du dépôt ; ne pas retoucher le thème pour traiter ce symptôme.
