# Project N — P1 : fondations du design system

**Statut :** première couche de tokens et primitives SCSS créée ; non compilée ni testée dans Moodle.  
**Branche :** `feature/p1-foundation`.

## 1. Intention

Construire un langage visuel cohérent, mobile-first et accessible, en prenant Chamilo comme référence ergonomique — hiérarchie claire, espaces de travail lisibles, cartes fonctionnelles et administration organisée par domaines — sans figer prématurément l'identité de marque. Moodle reste le socle fonctionnel : ses activités, formulaires, permissions, navigations contextuelles et mécanismes d'administration sont conservés. Cette tranche ne cherche pas à redessiner globalement Boost : elle fournit des tokens et des classes explicitement destinés aux composants Project N.

## 2. Décisions de conception

- **Référence Chamilo :** reprendre les qualités de présentation et de repérage, pas son modèle fonctionnel. Les écrans Project N doivent rendre les tâches évidentes sans remplacer les pages, API ou mécanismes Moodle.
- **Principe de composition :** Project N porte les espaces, les tableaux de bord et les raccourcis ; Moodle conserve les contenus pédagogiques, les opérations métier, les contrôles d'accès et les navigations contextuelles.

- **Palette de marque :** non définie à ce stade. Les couleurs neutres des surfaces et du texte servent de base provisoire ; la couleur d'accent doit être décidée après validation de la direction graphique.
- **Typographie :** héritage des polices et mécanismes natifs de Boost. Aucun chargement de police externe ou Google Fonts. Cette décision évite une dépendance réseau et préserve la politique de confidentialité. Le changement de police par défaut de Boost en Moodle 5.3 doit rester pris en charge.
- **Mode sombre :** les tokens s'adaptent à `data-bs-theme="dark"`, mécanisme exposé par Moodle 5.3. Le thème ne crée pas un second sélecteur de mode. Sur les versions antérieures, le comportement natif Boost reste prioritaire.
- **Composants :** les primitives `.projectn-card`, `.projectn-surface` et `.projectn-text-muted` ne sont appliquées qu'aux futurs composants Project N. Aucun style global ne transforme les composants natifs Moodle.
- **Focus :** indicateur explicite et contrasté, sans suppression des styles natifs.
- **Mouvement :** respect de `prefers-reduced-motion`.

## 3. Tokens disponibles

| Famille | Tokens |
|---|---|
| Surfaces | `--projectn-color-canvas`, `--projectn-color-surface`, `--projectn-color-surface-raised` |
| Texte | `--projectn-color-text`, `--projectn-color-text-muted` |
| Interaction | `--projectn-color-focus`, `--projectn-color-link`, `--projectn-color-action` |
| Formes | `--projectn-radius-sm/md/lg` |
| Espacements | `--projectn-space-1/2/3/4/6/8`, `--projectn-content-gutter` |
| Élévation | `--projectn-shadow-card` |

Les variables Bootstrap sont utilisées avec repli CSS lorsqu'elles existent ; le thème ne dépend pas de la présence des mêmes variables entre Bootstrap 4.6 et Bootstrap 5.

## 4. Points de vigilance interversions

- Moodle 4.5 utilise Bootstrap 4.6.2 ; Moodle 5.3 utilise Bootstrap 5.3.8. Ne pas introduire de classes utilitaires ou d'attributs JavaScript qui supposent une seule génération Bootstrap.
- Le thème hérite de la typographie Boost ; ne pas imposer une police différente avant comparaison sur les versions cibles.
- Le mode sombre via `data-bs-theme` est pris en charge dans cette première couche à partir du mécanisme Moodle 5.3. Ne pas supposer qu'il existe sous cette forme en 4.5–5.2.
- La structure `public/` introduite à partir de Moodle 5.1 ne modifie pas les chemins internes relatifs du thème, mais doit être prise en compte dans le packaging et les environnements de test.

## 5. Accessibilité : validations requises

Les tokens ne constituent pas une validation de conformité. Avant de les utiliser dans des écrans :

1. calculer et vérifier les contrastes de chaque paire texte/fond réellement utilisée ;
2. vérifier les couleurs d'accent et les états hover/active/disabled ;
3. vérifier le focus clavier sur fond clair et sombre ;
4. tester le reflow à 320 CSS px et le zoom à 200 % ;
5. tester les préférences de mouvement réduit ;
6. lancer axe-core puis compléter par des tests clavier et lecteur d'écran.

Les couleurs de bordure ne sont pas destinées à porter seules une information. Les états ne doivent jamais être identifiés uniquement par leur couleur.

## 6. Limites de cette tranche

- aucune identité de marque définitive ;
- aucune police spécifique ;
- aucun changement global des boutons, formulaires ou composants Boost ;
- aucun sélecteur de mode clair/sombre personnalisé ;
- aucune déclaration de compatibilité runtime.

## 7. Suite

La prochaine tranche doit valider le composant global déjà codé sur un environnement Moodle, puis construire progressivement la navigation d'espace et les composants de tableau de bord inspirés de la clarté de Chamilo. L'architecture hybride de `docs/P0-NAVIGATION.md` reste la règle : navigation globale et espaces Project N, navigation contextuelle et pédagogique native Moodle/Iomad. La navigation 5.3 basée sur React ne doit pas être copiée ni manipulée directement.
