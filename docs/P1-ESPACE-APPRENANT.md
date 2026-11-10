# Project N — P1 : formations apprenant et enseignant

**Statut :** fournisseur et template séparant les deux catégories préparés sur `feature/p1-foundation`. Le composant n'est pas encore raccordé à la zone de contenu principale du tableau de bord. Tests ajoutés/à compléter, non exécutés.  
**Version de développement :** `2026111005`.

## 1. Principe de classement

Le classement est effectué **pour chaque cours**, et non à partir d'un rôle global de l'utilisateur. Un même compte peut donc apparaître dans les deux sections :
- **Mes formations en tant qu'apprenant** ;
- **Mes formations en tant qu'enseignant**.

La récupération initiale passe par `enrol_get_my_courses()` et ne retient que les inscriptions actives. Le thème ne crée aucun mécanisme d'inscription ni de contrôle d'accès.

## 2. Détection du rôle d'enseignement

Pour chaque cours récupéré :
1. le fournisseur examine les rôles affectés dans le contexte du cours et ses contextes parents via `get_user_roles()` ;
2. les archétypes Moodle `editingteacher` et `teacher` classent le cours dans la section enseignante ;
3. pour les rôles personnalisés sans archétype, les capacités effectives `moodle/course:update` ou `moodle/grade:viewall` servent de solution de repli ;
4. les autres cours inscrits sont classés dans la section apprenant.

Les rôles standards de gestion (`manager`) ne sont pas assimilés à un rôle enseignant sur la seule base de leurs capacités étendues. Un rôle personnalisé qui cumule des fonctions d'enseignement et de gestion devra être validé avec des configurations réelles.

## 3. Limites et vigilance

- Le modèle Moodle ne fournit pas de notion universelle « formateur » indépendante des rôles et capacités. Les rôles personnalisés des sites doivent donc être testés.
- Un rôle d'enseignement sans archétype et sans les deux capacités de repli peut être classé côté apprenant : il faudra ajuster la règle si les tests Iomad ou les rôles métier le révèlent.
- Le classement ne donne aucun droit supplémentaire. Les pages de cours restent soumises aux contrôles natifs Moodle/Iomad.
- Six cours maximum sont affichés dans chaque section.
- Progression, prochaine activité, échéances et cours terminés ne sont pas encore présentés.

## 4. Intégration visuelle

Le hook `before_standard_top_of_body_html_generation` ne doit servir qu'à la navigation globale : il intervient avant le layout Boost. Le tableau de bord apprenant/enseignant n'y est donc pas injecté. Le point d'insertion dans la zone de contenu reste à valider avant raccordement.

## 5. Vérifications restantes

- [ ] Vérifier les rôles standard apprenant, enseignant non éditeur et enseignant éditeur.
- [ ] Vérifier un compte présent dans les deux sections sur des cours différents.
- [ ] Vérifier les rôles personnalisés et les rôles Iomad.
- [ ] Vérifier les rôles hérités de catégories.
- [ ] Exécuter PHPUnit et PHP lint.
- [ ] Tester Moodle 4.5 à 5.3 et Iomad 4.5 à 5.2.
- [ ] Vérifier rendu responsive, clavier, focus, contrastes et absence de doublons.

Aucune compatibilité runtime n'est revendiquée avant ces essais.
