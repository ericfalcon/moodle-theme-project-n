# Project N — Architecture des rôles Moodle et Iomad

> Analyse statique du code Moodle 5.2 et Iomad 5.2 (`MOODLE_502_STABLE` / `IOMAD_502_STABLE`). Objectif : définir les règles de conception du thème unique Project N pour les deux plateformes. Les rôles et capacités réellement présents dans une installation peuvent varier après personnalisation ou ajout de plugins.

## 1. Principe fondamental : ne pas confondre rôle, affectation et capacité

Dans Moodle, un rôle est un ensemble de permissions (capacités). Ce rôle est attribué à un utilisateur dans un contexte précis. Les permissions effectives résultent notamment :
- des rôles attribués dans le contexte courant et ses contextes parents ;
- des permissions définies pour chaque capacité ;
- des éventuelles dérogations locales ;
- des interdictions explicites (`CAP_PROHIBIT`) qui prévalent sur les autorisations.

Le rôle seul ne suffit donc pas à déterminer l'interface à afficher. Un même utilisateur peut cumuler plusieurs rôles dans des contextes différents et avoir des droits différents selon la page, le cours ou l'activité consultés.

**Règle Project N :** afficher une action parce que la capacité correspondante est accordée dans le contexte concerné, et non parce que le nom du rôle de l'utilisateur correspond à une catégorie présumée.

## 2. Rôles standards Moodle

Le code d'installation Moodle 5.2 crée les rôles standards suivants *(tableau non vérifié contre `install.php`)* :

| Rôle (shortname) | Fonction générale | Contexte d'affectation habituel |
|---|---|---|
| `manager` | Administration déléguée de la plateforme ou d'une catégorie selon les capacités | Système / catégories |
| `coursecreator` | Création de cours | Système / catégories |
| `editingteacher` | Enseignement avec édition du cours | Cours |
| `teacher` | Enseignement sans édition du cours | Cours |
| `student` | Participation comme apprenant | Cours |
| `guest` | Accès invité limité | Cours |
| `user` | Utilisateur authentifié, rôle par défaut dans les contextes prévus | Selon contexte |
| `frontpage` | Rôle spécifique à la page d'accueil | Page d'accueil |

### Administrateur du site : cas particulier

L'administrateur Moodle n'est pas simplement un rôle supplémentaire nommé « administrateur ». Les identifiants des administrateurs sont enregistrés dans le réglage `siteadmins`. La fonction `is_siteadmin()` identifie ce statut particulier.

Le rôle `manager` est bien un rôle Moodle standard, distinct de l'administrateur du site. Il permet une délégation importante de l'administration, mais ses droits effectifs restent gouvernés par ses capacités et leur contexte. Il ne faut pas confondre :
- administrateur du site ;
- gestionnaire Moodle ;
- gestionnaire d'entreprise Iomad ;
- gestionnaire de département Iomad.

## 3. Spécificités Iomad : sept rôles supplémentaires

Le fichier `local/iomad/db/install.php` crée sept rôles Iomad. Leur portée d'affectation est explicitement configurée :

| Rôle (shortname) | Nom fonctionnel | Contexte d'affectation | Usage |
|---|---|---|---|
| `companymanager` | Company Manager | Entreprise | Gestion d'une entreprise |
| `companydepartmentmanager` | Company Department Manager | Entreprise | Gestion d'un périmètre départemental |
| `companycourseeditor` | Company Course Editor | Cours | Rôle pédagogique avec édition |
| `companycoursenoneditor` | Company Course Non Editor | Cours | Rôle pédagogique sans édition |
| `companyreporter` | Company Report Only | Entreprise | Consultation des rapports de l'entreprise |
| `clientadministrator` | Client Administrator | Système | Administration multi-entreprises Iomad |
| `clientreporter` | Client Report Only | Système | Consultation des rapports multi-entreprises |

Ces rôles sont créés par l'API Moodle `create_role()`, puis leurs contextes d'affectation sont définis par `set_role_contextlevels()`. Ils restent des rôles Moodle au sens du système de permissions, mais leurs archétypes et capacités sont fournis ou exploités par Iomad.

### Attention au rôle Client Administrator

`clientadministrator` n'est pas l'administrateur du site Moodle. Il s'agit d'un rôle Iomad de portée système permettant des opérations multi-entreprises. Le code de contrôle des capacités Iomad conserve un traitement particulier pour les véritables administrateurs du site via `is_siteadmin()`.

L'interface Project N doit donc distinguer explicitement :
- administrateur du site ;
- client administrator Iomad ;
- manager Moodle ;
- company manager Iomad ;
- company department manager Iomad.

## 4. Fonctions de rattachement Iomad : elles ne sont pas toutes des rôles

La table `local_iomad_company_users` décrit l'association d'un utilisateur à une entreprise et à un département. Elle contient notamment les champs `companyid`, `userid`, `departmentid`, `managertype`, `educator` et `suspended`.

La méthode `company::get_managertypes()` expose les valeurs suivantes :

| Valeur `managertype` | Fonction |
|---:|---|
| 0 | Utilisateur |
| 1 | Gestionnaire d'entreprise |
| 2 | Gestionnaire de département |
| 3 | Formateur (*educator*) |
| 4 | Responsable des rapports de l'entreprise (*company reporter*) |

**Point de vigilance :** `educator` est un champ distinct de `managertype`. Le formateur ne doit pas être traité comme un rôle d'entreprise autonome équivalent à `companymanager`. Selon la configuration Iomad, les formateurs ou les gestionnaires peuvent recevoir des rôles pédagogiques dans les cours.

Le commentaire du schéma XML sur les valeurs de `managertype` mentionne seulement 0, 1 et 2 ; la méthode `get_managertypes()` et les usages du code prennent également en charge les valeurs 3 et 4. Le commentaire du schéma est donc incomplet par rapport au code examiné.

## 5. Attribution des rôles pédagogiques Iomad dans les cours

Iomad sépare la gestion d'une entreprise de l'intervention pédagogique dans un cours.

Dans `company::add_course()`, pour les cours non licenciés, le code récupère les rôles `companycourseeditor` et `companycoursenoneditor`. L'affectation dépend de la configuration `local_iomad/autoenrol_managers` *(règles à vérifier jusqu'au bout)* :

- Si l'auto-inscription des gestionnaires est activée, les rattachements de gestionnaires (`managertype != 0`) sont utilisés pour affecter les rôles pédagogiques. Un gestionnaire de département reçoit le rôle non éditeur ; un gestionnaire d'entreprise peut recevoir le rôle éditeur sur un cours qu'il crée ; sur un cours qui n'est pas créé par un gestionnaire, le rôle non éditeur est affecté.
- Si cette option est désactivée, les associations marquées `educator != 0` sont utilisées pour attribuer les rôles pédagogiques. Le choix éditeur / non éditeur dépend notamment du fait que le cours soit créé par un gestionnaire.

Ces règles doivent être analysées plus largement pour les cours licenciés, les cours délégués, les cours partagés et les inscriptions manuelles. Elles montrent déjà que l'interface ne peut pas déduire le rôle pédagogique du seul intitulé de fonction dans l'entreprise.

**Conséquence :** le rôle d'entreprise n'est pas automatiquement un rôle de cours. Iomad effectue des affectations distinctes au contexte du cours. Le thème doit lire les capacités effectives du cours pour déterminer les outils pédagogiques à afficher.

## 6. Contexte supplémentaire Iomad : CONTEXT_COMPANY

Iomad ajoute un contexte personnalisé `context_company`, identifié par `CONTEXT_COMPANY = 13`. Ce contexte :
- est un enfant direct du contexte système ;
- représente une entreprise Iomad ;
- expose les capacités applicables à ce niveau ;
- permet d'affecter les rôles de gestion d'entreprise.

Il ne faut pas le confondre avec un contexte de catégorie de cours ou de cours. Les cours conservent leur propre hiérarchie de contextes Moodle. Les permissions du rôle d'entreprise ne remplacent donc pas les rôles pédagogiques attribués dans les cours.

**Conséquence Project N :** le thème doit reconnaître ce contexte additionnel lorsqu'il fonctionne sous Iomad, sans supposer qu'il existe sous Moodle seul.

## 7. Couche de contrôle des capacités Iomad

Iomad fournit `local_iomad\\iomad::has_capability()`, qui complète le contrôle Moodle :

1. La fonction appelle d'abord `has_capability()` de Moodle. Si Moodle refuse, Iomad refuse également.
2. Un administrateur du site bénéficie du traitement particulier prévu par `is_siteadmin()`.
3. Pour un utilisateur non administrateur, Iomad détermine l'entreprise courante ou vérifie l'appartenance à l'entreprise demandée.
4. Iomad examine les rôles présents dans les données d'accès et les permissions effectives.
5. Iomad consulte également `local_iomad_company_role_restrictions` : une restriction enregistrée pour l'entreprise, le rôle et la capacité entraîne un refus.

La couche Iomad peut donc retirer, pour une entreprise donnée, une capacité que le rôle aurait autrement accordée. Le thème ne doit pas reconstruire ce calcul.

**Règle d'intégration :** pour les opérations propres à l'administration Iomad, respecter les contrôles de capacité et les API Iomad du composant concerné. Ne pas remplacer ces contrôles par une simple vérification Moodle ni par une comparaison de shortnames de rôles.

## 8. Capacités Iomad : première cartographie

Le fichier `blocks/iomad_company_admin/db/access.php` déclare 86 capacités pour le bloc d'administration, dont 84 capacités au contexte entreprise et deux capacités d'instance de bloc *(répartition 84/2 non vérifiée)*. Dans les déclarations d'archétypes de ce fichier, on relève notamment :

| Archétype | Nombre de capacités où il est explicitement autorisé |
|---|---:|
| `companymanager` | 41 |
| `companydepartmentmanager` | 20 |
| `clientadministrator` | 83 |
| `clientreporter` | 1 |
| `user` | 1 |

Ces nombres sont un comptage des déclarations explicites dans ce seul fichier, pas un inventaire des droits effectifs. Ils n'incluent ni les capacités d'autres plugins Iomad, ni les capacités Moodle, ni les dérogations, restrictions ou personnalisations de l'installation.

Quelques différences structurantes ressortent déjà :
- le gestionnaire d'entreprise dispose de droits de gestion des utilisateurs, des départements, de certaines fonctions de cours et de licences ;
- le gestionnaire de département dispose d'un sous-ensemble de ces droits ;
- le client administrator reçoit la plupart des capacités de l'administration Iomad ;
- le client reporter n'est autorisé ici que pour la consultation multi-entreprises correspondante ;
- certaines capacités de gestion des cours, de suppression, de configuration ou de gestion globale sont réservées au client administrator dans les archétypes par défaut.

La liste complète des capacités doit être rapprochée des menus et des contrôles dans chaque page métier avant de produire une matrice de droits définitive.

## 9. Impacts sur la conception du thème Project N

### 9.1 Ne pas créer une interface figée par rôle

Éviter une logique telle que :
- « si shortname = manager, afficher l'administration » ;
- « si shortname = editingteacher, afficher les outils formateur » ;
- « si managertype = 1, afficher tous les outils de gestion ».

Ces raccourcis échouent dès qu'un rôle est personnalisé, qu'un utilisateur cumule plusieurs rôles, qu'une capacité est retirée ou qu'un utilisateur change d'entreprise.

### 9.2 Organiser l'interface par espaces, puis par capacités

Le thème peut organiser les interfaces autour de grands espaces fonctionnels :
- plateforme ;
- entreprise ;
- département ;
- cours ;
- apprentissage ;
- enseignement ;
- rapports.

La visibilité des entrées et des actions dans ces espaces doit ensuite dépendre des capacités effectives dans le contexte concerné.

### 9.3 Détection Moodle / Iomad

Le thème doit rester installable et fonctionnel sur Moodle seul :
- ne jamais charger directement une classe Iomad sans vérifier sa disponibilité ;
- ne jamais supposer que `CONTEXT_COMPANY` existe ;
- isoler les adaptations Iomad dans des points d'extension conditionnels ;
- conserver un parcours Moodle natif lorsqu'Iomad est absent ;
- éviter de maintenir deux copies divergentes des templates communs.

### 9.4 Matrice de tests à prévoir

| Plateforme | Profil | Contexte à tester |
|---|---|---|
| Moodle | Administrateur du site | Système |
| Moodle | Manager | Système et catégorie |
| Moodle | Course creator | Catégorie / création de cours |
| Moodle | Enseignant éditeur | Cours |
| Moodle | Enseignant non éditeur | Cours |
| Moodle | Étudiant | Cours |
| Iomad | Administrateur du site | Système et entreprise |
| Iomad | Client Administrator | Multi-entreprises |
| Iomad | Client Report Only | Rapports multi-entreprises |
| Iomad | Company Manager | Entreprise et cours |
| Iomad | Department Manager | Entreprise, département et cours |
| Iomad | Company Report Only | Rapports d'entreprise |
| Iomad | Educator | Entreprise et cours |
| Iomad | Utilisateur / apprenant | Entreprise et cours |

Ajouter des cas de cumul de rôles, d'utilisateur rattaché à plusieurs entreprises, de changement d'entreprise, de suspension, de restriction de capacité et de rôle personnalisé.

## 10. Sources examinées

- Moodle 5.2 : `public/lib/db/install.php` — création des rôles standards et enregistrement de `siteadmins`.
- Moodle 5.2 : `public/lib/db/access.php` — capacités et archétypes standards.
- Moodle 5.2 : `public/lib/accesslib.php` — API et résolution des capacités.
- Moodle 5.2 : `public/admin/roles/classes/define_role_table_advanced.php` — définition des rôles, niveaux de contexte et permissions.
- Iomad 5.2 : `public/local/iomad/db/install.php` — création des sept rôles spécifiques.
- Iomad 5.2 : `public/local/iomad/db/install.xml` — association utilisateur / entreprise / département.
- Iomad 5.2 : `public/local/iomad/classes/company.php` — types de rattachement et attribution des rôles pédagogiques.
- Iomad 5.2 : `public/local/iomad/classes/iomad.php` — résolution des permissions et restrictions par entreprise.
- Iomad 5.2 : `public/local/iomad/classes/custom_context/context_company.php` — contexte entreprise.
- Iomad 5.2 : `public/local/iomad/db/access.php` et `public/blocks/iomad_company_admin/db/access.php` — capacités spécifiques.

Analyse statique uniquement. Aucun test de droits en exécution, aucune simulation de cumul de rôles et aucun test de cloisonnement entre entreprises n'a été effectué dans cette tranche.
