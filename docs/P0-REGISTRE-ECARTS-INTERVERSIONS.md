# Project N — Registre des différences interversions Moodle / Iomad

> Registre vivant des écarts réellement observés dans les sources. Ce document complète la cartographie fonctionnelle et la matrice de compatibilité ; il ne remplace ni les fiches détaillées de chaque domaine ni les tests runtime.

## 1. Règle documentaire obligatoire

Toute analyse réalisée pour Project N doit être enregistrée dans ce dépôt GitHub avant de passer à la tranche suivante. Une conclusion formulée dans une conversation, mais absente du dépôt, n'est pas considérée comme documentée.

Pour chaque tranche :
1. examiner les versions pertinentes des forks personnels et, lorsque nécessaire, les références amont ;
2. relever les branches, tags et SHA exacts des commits comparés ;
3. comparer les fichiers homologues, leurs dépendances et les contrats d'extension, pas seulement les noms de fichiers ;
4. documenter les invariants, différences, ajouts, retraits, dépréciations et conditions de disponibilité ;
5. indiquer l'impact concret pour le thème et la stratégie de compatibilité ;
6. distinguer analyse statique, hypothèse et test effectivement exécuté ;
7. mettre à jour la cartographie, la matrice et ce registre ;
8. relire les fichiers depuis GitHub après commit.

Une tranche n'est achevée que lorsque ses résultats sont enregistrés et relus sur la branche `main`. Aucun statut « compatible » ne peut être déduit d'une simple absence de différence repérée.

## 2. Périmètre de comparaison

| Plateforme | Générations à documenter | Règle |
|---|---|---|
| Moodle | 4.5, 5.0, 5.1, 5.2, 5.3 | Comparer chaque génération aux autres pour les domaines structurants du thème |
| Iomad | 4.5, 5.0, 5.1, 5.2 | Comparer chaque version à la génération Moodle correspondante |
| Iomad | 5.3 | Ne pas annoncer de compatibilité tant que la branche/version n'est pas disponible et inspectable |

Les numéros de version ne suffisent pas à identifier une source. Chaque fiche doit préciser le nom exact de branche ou tag et le SHA consulté. Le fork Iomad 5.0 contient des personnalisations personnelles : elles doivent être séparées des différences de l'amont et conservées lors de toute synchronisation. Le fork Moodle est traité comme aligné sur l'amont pour les branches déjà vérifiées, sous réserve de contrôler à nouveau les SHA au début d'une nouvelle campagne.

## 3. Fiche normalisée à renseigner

Pour chaque domaine ou parcours :

- **Identifiant / domaine :**
- **Profil concerné :** apprenant, formateur, administrateur, gestionnaire Iomad…
- **Versions Moodle examinées :** branche/tag et SHA pour chacune.
- **Versions Iomad examinées :** branche/tag et SHA pour chacune, en couples homologues.
- **Fichiers et symboles comparés :** chemins exacts, classes, méthodes, templates, AMD/JS, hooks.
- **Invariant vérifié :** mécanisme commun, avec preuves sur les versions listées.
- **Différences par version :** changement précis et version d'apparition/disparition.
- **Différences Moodle/Iomad :** à distinguer des différences entre versions d'une même plateforme.
- **Capacités et contextes :** changements de droits, périmètres ou conditions.
- **Contrats de rendu et d'extension :** Output API, renderer, Mustache, hooks, événements, DOM.
- **Impact Project N :** risque pour l'interface, le parcours, l'accessibilité et la maintenance.
- **Stratégie de compatibilité :** API commune, détection de capacité, adaptateur versionné, surcharge conditionnelle ou absence de surcharge.
- **Tests requis / exécutés :** runtime, Behat, PHPUnit, mobile, clavier, lecteur d'écran.
- **Niveau de preuve :** code comparé / test exécuté / hypothèse / non vérifié.
- **Références :** liens directs vers les fichiers et commits.

## 4. Écarts déjà consignés dans la cartographie

Cette liste est un index de constats déjà documentés. Elle ne signifie pas que tous les domaines ont été comparés exhaustivement entre les cinq générations Moodle.

| Domaine | Constat documenté | Portée / limite | Référence |
|---|---|---|---|
| Arborescence du code Moodle | La structure web est à la racine en 4.5 et sous `public/` dans la branche 5.3 inspectée | Ne pas extrapoler le point exact de transition sans comparer 5.0, 5.1 et 5.2 | [Cartographie, sections 2–3](P0-CARTOGRAPHIE-NAVIGATION.md) |
| Navigation Moodle 5.3 | Présence des composants `primarymoremenu`, `secondarymoremenu`, `navigation_action_bar`, avec rendu React et fallback serveur | Contrat à préserver ; vérifier séparément les versions intermédiaires | [Cartographie, sections 3 et 10](P0-CARTOGRAPHIE-NAVIGATION.md) |
| Anciennes vues de navigation | `flat_navigation` et certains templates historiques sont présents dans les versions antérieures inspectées et absents de certains arbres 5.2/5.3 | Ne pas dépendre de ces éléments sans matrice de présence par version | [Cartographie, sections 3–4](P0-CARTOGRAPHIE-NAVIGATION.md) |
| Navigation Iomad | Iomad 4.5 adapte encore `lib/navigationlib.php` ; Iomad 5.1/5.2 adapte les classes sous `public/lib/classes/navigation` | Comparaison ciblée ; ne vaut pas inventaire complet des écarts de navigation | [Cartographie, section 4](P0-CARTOGRAPHIE-NAVIGATION.md) |
| Affichage des cours | Le point d'entrée Iomad ajoute `iomad_check_course()` sur les couples 4.5, 5.0, 5.1 et 5.2 comparés | Contrôle d'accès, pas simple différence de présentation ; isolation complète à tester | [Cartographie, section 65.11](P0-CARTOGRAPHIE-NAVIGATION.md) |
| Édition du cours et participants | `course/edit.php` et `user/index.php` identiques sur les couples examinés | Comparaison limitée aux fichiers d'entrée listés | [Cartographie, section 65.11](P0-CARTOGRAPHIE-NAVIGATION.md) |
| Inscriptions | Contrôleurs standards comparés identiques entre Moodle et Iomad sur 4.5, 5.0, 5.1 et 5.2 | Plugins, synchronisations et règles entreprise restent à analyser | [Cartographie, section 68.12](P0-CARTOGRAPHIE-NAVIGATION.md) |
| Rôles Iomad | Rôles propres à Iomad, contexte entreprise et capacités spécifiques ; distinction entre rôle, `managertype` et `educator` | Analyse détaillée surtout établie sur Iomad 5.0 ; vérifier les évolutions des autres versions | [Cartographie, sections 66.11–66.12](P0-CARTOGRAPHIE-NAVIGATION.md) |
| Fork Iomad 5.0 | Six fichiers personnalisés dans `blocks/iomad_company_admin`, notamment désambiguïsation des cours et restriction du clonage | Personnalisations du fork, non comportement natif Iomad ; préserver et comparer avant portage | [Cartographie, section 67.10](P0-CARTOGRAPHIE-NAVIGATION.md) |

## 5. État de couverture — ne pas surinterpréter

| Domaine cartographié | Analyse fonctionnelle | Comparaison interversions Moodle 4.5–5.3 | Comparaison Moodle/Iomad | État |
|---|---|---|---|---|
| Espace formateur (section 65) | Première synthèse détaillée | Incomplète ; la section 65.11 compare certains points d'entrée Moodle/Iomad, pas les cinq versions Moodle entre elles | Ciblée sur certains contrôleurs 4.5–5.2 | À compléter |
| Comptes et cohortes (section 66) | Analyse détaillée, rôles Iomad identifiés | Incomplète ; référence initiale Moodle 5.0 pour plusieurs éléments | Rôles et capacités Iomad surtout étudiés en 5.0 | À compléter |
| Catégories et cours (section 67) | Analyse détaillée | Incomplète | Personnalisations du fork Iomad 5.0 documentées ; autres écarts ciblés | À compléter |
| Méthodes d'inscription (section 68) | Analyse détaillée | Incomplète | Contrôleurs comparés sur 4.5, 5.0, 5.1 et 5.2 | À compléter |
| Navigation et layouts | Comparaison ciblée 4.5 / 5.3 et Iomad 4.5 / 5.1 / 5.2 | Versions intermédiaires à vérifier selon chaque mécanisme | Écarts de structure repérés | À compléter |
| Activités, ressources, éditeurs | Plusieurs analyses ponctuelles déjà présentes dans la cartographie | Ne pas considérer la couverture complète sans fiche par activité et par version | À établir au cas par cas | À compléter |

**Règle de lecture :** une comparaison Moodle/Iomad sur quatre couples de versions n'est pas une comparaison interversions Moodle 4.5–5.3. Ces deux axes doivent rester distincts dans les tableaux et les conclusions.

## 6. Ordre de traitement des prochaines comparaisons

Conformément à la priorité Moodle standard du projet :

1. reprendre les sections 65 à 68 déjà rédigées sur Moodle 5.0 et établir, pour chaque domaine, les écarts Moodle 4.5, 5.1, 5.2 et 5.3 ;
2. poursuivre avec les rôles, capacités et contextes Moodle sur les cinq générations ;
3. analyser les rapports d'administration et paramètres de plateforme, avec comparaison interversions ;
4. approfondir les mécanismes transversaux qui conditionnent les parcours (banque de questions, formulaires, notifications, navigation contextuelle, Output API, renderers, templates, AMD/JavaScript, événements et attributs DOM) ;
5. examiner les écarts Iomad qui conditionnent directement ces parcours, toujours à génération Moodle équivalente ;
6. traiter séparément les personnalisations personnelles du fork Iomad 5.0 et leur éventuel portage.

La cartographie existante ne doit pas être remplacée par une nouvelle analyse générique : elle doit être complétée à partir des sections et sources déjà consignées.

## 7. Liens de référence

- [Cartographie technique](P0-CARTOGRAPHIE-NAVIGATION.md)
- [Matrice de compatibilité](P0-MATRICE-COMPATIBILITE.md)
- [Cadrage](P0-CADRAGE.md)
- [Feuille de route](../ROADMAP.md)

