# Project N — Feuille de route

Cette feuille de route décrit les étapes de conception, de développement et de validation du thème. Une phase n'est considérée comme terminée que lorsque ses livrables et critères d'acceptation sont vérifiés et documentés.

## État d'avancement

| Phase | Objet | État |
|---|---|---|
| P0 | Cadrage et spécifications | À réaliser |
| P1 | Socle du thème | À réaliser |
| P2 | Espace apprenant | À réaliser |
| P3 | Espace formateur | À réaliser |
| P4 | Espace administrateur | À réaliser |
| P5 | Adaptation Iomad | À réaliser |
| P6 | Stabilisation et validation | À réaliser |
| P7 | Documentation et publication | À réaliser |

Aucune phase n'est déclarée terminée à la création de cette feuille de route.

---

## P0 — Cadrage et spécifications

**Objectif :** transformer la vision du projet en spécifications vérifiables.

Livrables :
- cartographie des parcours et fonctions pertinentes de Chamilo et Moodle ;
- parcours détaillés apprenant, formateur et administrateur ;
- principes de navigation et architecture de l'information ;
- inventaire des composants Moodle réutilisables et des points d'extension ;
- architecture technique du thème et éventuel périmètre d'un compagnon facultatif ;
- matrice de compatibilité Moodle/Iomad et stratégie de tests ;
- critères d'acceptation fonctionnels, techniques, sécurité et accessibilité ;
- premières maquettes et règles de design system.

Critères d'acceptation :
- les trois espaces sont décrits sans ambiguïté ;
- les parcours restent réalisables avec les mécanismes natifs Moodle ;
- les limites entre thème et éventuel plugin compagnon sont explicites ;
- les risques de surcharge de templates et de rupture de compatibilité sont identifiés ;
- les exigences d'accessibilité sont intégrées aux spécifications.

## P1 — Socle du thème

**Objectif :** mettre en place une base technique et visuelle robuste.

Livrables :
- squelette installable `theme_projectn` ;
- déclaration des versions Moodle prises en charge ;
- identité visuelle, design tokens, typographie, espacements et couleurs ;
- composants de base cohérents et réutilisables ;
- réglages de personnalisation limités aux besoins identifiés ;
- gabarits et rendus responsives ;
- gestion des états de focus, survol, sélection, désactivation et erreur.

Critères d'acceptation :
- installation, activation, désactivation et mise à niveau vérifiées ;
- absence de régression sur les pages Moodle couvertes ;
- navigation clavier et focus visibles ;
- contrôles axe-core et vérifications manuelles documentés.

## P2 — Espace apprenant

**Objectif :** rendre le suivi des formations immédiatement compréhensible.

Périmètre :
- tableau de bord et accès aux formations ;
- cartes et listes de cours ;
- progression et prochaines activités ;
- échéances et notifications ;
- page de cours et navigation entre sections ;
- accès aux ressources et activités ;
- états vides, erreurs et messages d'aide.

Critères d'acceptation :
- les informations affichées respectent les droits et inscriptions Moodle ;
- les liens vers les prochaines actions sont exacts et utilisables ;
- les parcours sont utilisables sur mobile, au clavier et avec lecteur d'écran ;
- les états dynamiques sont perceptibles sans dépendre uniquement de la couleur.

## P3 — Espace formateur

**Objectif :** simplifier la création et le pilotage d'un cours sans remplacer les outils Moodle.

Périmètre :
- création guidée d'un cours et organisation des sections ;
- ajout d'activités et ressources par intention pédagogique : transmettre, pratiquer, évaluer, collaborer ;
- accès aux activités et ressources natives correspondantes ;
- réorganisation et réutilisation des contenus lorsque les API natives le permettent ;
- accès aux participants ;
- suivi des évaluations et corrections ;
- accès permanent aux réglages avancés natifs ;
- prévisualisation du cours côté apprenant.

Critères d'acceptation :
- aucune fonction guidée ne crée un format de cours propriétaire ;
- les réglages natifs restent accessibles ;
- les capacités Moodle sont respectées dans chaque action ;
- les parcours de création et de modification sont testés avec différents rôles ;
- l'accessibilité est vérifiée sur les dialogues, sélecteurs, formulaires et interactions.

## P4 — Espace administrateur

**Objectif :** rendre l'administration plus repérable sans réécrire le cœur de Moodle.

Périmètre :
- centre de gestion organisé par domaines ;
- raccourcis vers les actions fréquentes ;
- recherche et accès aux paramètres ;
- regroupements thématiques et présentation visuelle des réglages ;
- accès aux fonctions de gestion des utilisateurs, cours, plugins, rapports et maintenance ;
- renvoi vers les pages natives pour les formulaires, validations et contrôles de droits.

Critères d'acceptation :
- les raccourcis sont conditionnés par les capacités effectives ;
- aucune information ni action n'est exposée à un rôle non autorisé ;
- les pages natives restent accessibles ;
- les parcours administratifs sont testés avec plusieurs profils de droits.

## P5 — Adaptation Iomad

**Objectif :** intégrer les spécificités multi-entreprises sans dégrader le fonctionnement Moodle standard.

Périmètre :
- détection fiable de l'environnement Iomad ;
- adaptation des espaces et menus selon les rôles Iomad ;
- accès aux fonctions de gestionnaire d'entreprise ;
- prise en compte du contexte entreprise dans les écrans concernés ;
- vérification du cloisonnement des données entre entreprises ;
- maintien d'un fonctionnement normal lorsque Iomad est absent.

Critères d'acceptation :
- tests sur versions Iomad réellement disponibles ;
- absence de fuite de données entre entreprises ;
- vérification des rôles, capacités et contextes ;
- aucune dépendance Iomad dans le chemin d'exécution Moodle standard ;
- les écarts de compatibilité sont documentés.

## P6 — Stabilisation et validation

**Objectif :** consolider la qualité avant publication.

Périmètre :
- tests d'installation, mise à niveau et désinstallation ;
- tests de régression sur la matrice de versions retenue ;
- tests avec les activités et plugins courants du périmètre ;
- revue de sécurité et des contrôles d'accès ;
- contrôle responsive, performance et robustesse ;
- audit axe-core et tests manuels d'accessibilité ;
- correction et documentation des anomalies.

Critères d'acceptation :
- aucun défaut bloquant connu non traité ;
- résultats axe-core analysés et anomalies pertinentes corrigées ;
- tests clavier, lecteurs d'écran, contraste, zoom et reflow réalisés ;
- compatibilités annoncées appuyées par des tests réels ;
- limites connues et écarts résiduels publiés.

## P7 — Documentation et publication

**Objectif :** rendre le projet installable, compréhensible et maintenable.

Livrables :
- documentation d'installation et de mise à jour ;
- guide administrateur ;
- guide formateur ;
- guide apprenant si nécessaire ;
- documentation des réglages et de la compatibilité Iomad ;
- déclaration d'accessibilité et limites connues ;
- changelog et procédure de contribution ;
- choix et ajout de la licence ;
- première version stable publiée.

Critères d'acceptation :
- documentation cohérente avec la version publiée ;
- procédure d'installation vérifiée sur environnement vierge ;
- versions supportées et non supportées clairement indiquées ;
- procédure de signalement des anomalies disponible.

---

## Architecture et garde-fous

- Le livrable principal est un thème Moodle autonome : `theme_projectn`.
- Un éventuel `local_projectn` ne sera créé que si une logique métier le justifie ; il ne sera pas une dépendance obligatoire du thème.
- Aucun format de cours propriétaire n'est prévu.
- Les API, composants, capacités, contextes et formulaires natifs Moodle sont privilégiés.
- Les surcharges massives de templates sont évitées ; toute surcharge doit être justifiée et documentée.
- Les adaptations Iomad sont isolées et conditionnelles.
- Aucune logique d'autorisation parallèle aux capacités Moodle ne doit être introduite.
- Aucune intégration continue (CI) n'est mise en place pour le moment ; les contrôles sont exécutés localement.

## Qualité : validation à chaque étape

Chaque fonctionnalité doit être examinée selon quatre axes indissociables :

1. **Fonctionnel** — le parcours répond au besoin et respecte les comportements Moodle attendus.
2. **Technique** — le code suit les API et conventions Moodle, reste maintenable et compatible avec la matrice retenue.
3. **Sécurité** — les capacités, contextes, validations et données sont correctement protégés.
4. **Accessibilité** — les exigences WCAG 2.2 AA, RGAA et EN 301 549 applicables sont vérifiées dès la conception.

La validation accessibilité combine :
- axe-core exécuté localement sur les écrans et parcours concernés ;
- tests manuels au clavier ;
- vérification du focus, des contrastes, du zoom à 200 % et du reflow à 320 pixels CSS ;
- essais avec lecteurs d'écran (notamment NVDA et TalkBack) ;
- contrôle de la sémantique, des noms accessibles et des annonces de changements d'état.

Un résultat axe-core sans anomalie ne suffit pas à déclarer un parcours accessible.

## Hors périmètre à ce stade

- réécriture du moteur Moodle ;
- remplacement généralisé des formulaires et mécanismes natifs ;
- création d'un format de cours propriétaire ;
- dépendance obligatoire à un plugin compagnon ;
- intégration continue (CI), jusqu'à décision explicite contraire.

## Règle de mise à jour

La feuille de route est mise à jour à chaque évolution significative. Une phase ne passe à « Terminée » qu'après vérification de tous ses livrables et critères d'acceptation. Les éléments partiellement réalisés doivent être indiqués comme tels, avec les preuves ou tests associés.