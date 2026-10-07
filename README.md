# Ever Block

![Ever Block logo](logo.png)

Ever Block est un module gratuit pour PrestaShop qui permet d'ajouter des blocs HTML, du contenu dynamique et des shortcodes dans les hooks d'affichage de la boutique.

Le module est concu pour les boutiques PrestaShop 8 et 9. PrestaShop 1.7 n'est plus une cible de compatibilite.

Version documentee : 9.0.6.

[Faire un don pour soutenir le developpement des modules gratuits Team Ever](https://www.paypal.com/donate?hosted_button_id=3CM3XREMKTMSE)

## Compatibilite

- PrestaShop 8.0+ et PrestaShop 9.x.
- PHP 8.1 ou superieur.
- Multiboutique.
- Back office moderne base sur les routes, formulaires et grilles Symfony de PrestaShop 8/9.
- QCD Page Builder lorsque le module est installe.
- QCD ACF pour les champs personnalises et la bibliotheque SVG.

Le module declare ces prerequis directement dans `everblock.php` et `composer.json`. Si vous travaillez depuis les sources, utilisez un PHP 8.1+ pour Composer, les commandes Symfony et les controles de syntaxe.

## Installation

1. Copiez le dossier dans `modules/everblock`.
2. Si vous partez du depot source, lancez `composer install --no-dev` depuis le dossier du module avec PHP 8.1+.
3. Installez le module depuis le back office PrestaShop.
4. Videz le cache PrestaShop apres installation ou mise a jour.

Le module cree ses tables SQL a l'installation et les maintient via les scripts du dossier `upgrade/`.

## Back Office

Le menu est installe dans `Improve > Ever Block` avec les entrees suivantes :

- `Configuration` : reglages globaux, integrations, outils et crons.
- `HTML Blocks` : blocs affiches dans les hooks.
- `Hooks` : hooks d'affichage utilisables par les blocs.
- `Shortcodes` : shortcodes personnalises.
- `Shortcode documentation` : reference generee depuis le code.
- `FAQ` : questions/reponses, tags et associations produits.
- `Pages` : pages de contenu gerees par le module.

La page de configuration est organisee en onglets : reglages, Meta/Instagram, WordPress, Google, traductions, migration d'URL, outils, fichiers, flags, pages, horaires de jours feries et taches cron.

## Blocs HTML

Un bloc Ever Block contient du HTML multilingue et peut etre attache a n'importe quel hook d'affichage actif.

Principales options :

- contenu HTML et code personnalise par langue ;
- hook, position, statut actif/inactif ;
- ciblage par page d'accueil, categories, categories produit, fabricants, fournisseurs, categories CMS et groupes clients ;
- affichage par type d'appareil : tous, mobile, tablette ou desktop ;
- fenetre de publication avec dates de debut et de fin ;
- wrapper Bootstrap, classe CSS, couleur de fond et attributs `data-*` ;
- lazy loading lorsque le template le permet ;
- obfuscation des liens rendus ;
- rendu en modal avec delai d'apparition et duree du cookie.

Les hooks PrestaShop disponibles dependent du theme et des modules installes. Ever Block intercepte dynamiquement les hooks `display*` et ignore les hooks d'action pour l'affichage des blocs.

Documentation officielle utile :

- [Liste des hooks PrestaShop 8](https://devdocs.prestashop-project.org/8/modules/concepts/hooks/list-of-hooks/)
- [Liste des hooks PrestaShop 9](https://devdocs.prestashop-project.org/9/modules/concepts/hooks/list-of-hooks/)

## Shortcodes

La reference complete est disponible dans le back office via `Ever Block > Shortcode documentation`. Elle est generee par `src/Service/ShortcodeDocumentationProvider.php`, qui est la source de verite a maintenir quand un shortcode evolue.

Exemples courants :

| Famille | Exemples |
| --- | --- |
| Catalogue | `[product 1,2,3 carousel=true]`, `[category id="8" nb="8"]`, `[manufacturer id="3" nb="12"]`, `[best-sales nb=10]` |
| Merchandising | `[promo-products nb=10]`, `[random_product nb="10"]`, `[products_by_tag tag="summer|sale"]`, `[low_stock limit="8"]` |
| Produit courant | `[linkedproducts nb="8"]`, `[accessories nb="8"]`, `[crosselling nb=4]`, `[everaddtocart ref="ABC123"]` |
| Contenu | `[everblock 3]`, `[cms id="1"]`, `[evercms id="1"]`, `[everimg name="image.jpg"]`, `[video https://www.youtube.com/watch?v=...]` |
| Boutique | `[everstore 1]`, `[storelocator]`, `[evermap]`, `[googlereviews place_id="..."]` |
| Client et panier | `[entity_firstname]`, `[entity_company]`, `[evercart]`, `[cart_total]`, `[cart_quantity]`, `[newsletter_form]` |
| FAQ | `[everfaq tag="faq1"]`, `[everfaq_product id_product="42"]` |
| Formulaires | `[evercontactform_open]`, `[evercontact type="text" label="Votre nom"]`, `[evercontactform_close]` |
| Commande | `[everorderform_open]`, `[everorderform type="text" label="Information"]`, `[everorderform_close]` |
| Integrations | `[qcdacf my_field product 12]`, `[displayQcdSvg name="icon" inline=true]`, `[wordpress-posts]`, `[everinstagram]`, `[widget moduleName="mymodule" hookName="displayHome"]` |

Les shortcodes peuvent aussi afficher des variables Smarty simples via le contexte PrestaShop, par exemple les informations boutique, client, devise et URL.

### Ajouter la documentation des shortcodes d'un module tiers

Enregistrez votre module sur `actionEverBlockShortcodeDocumentation` dans son installation (ou dans un script de mise a jour pour un module deja installe) :

```php
$this->registerHook('actionEverBlockShortcodeDocumentation');
```

Le hook recoit `documentation` par reference, `module` (l'instance Ever Block) et `id_lang` (la langue courante). Il est execute avant la mise en cache de la documentation, une fois par langue et par requete. Ajoutez vos rubriques directement au tableau ; la valeur de retour du hook n'est pas utilisee.

```php
public function hookActionEverBlockShortcodeDocumentation(array $params): void
{
    $params['documentation'][] = [
        'title' => $this->l('My module'),
        'entries' => [
            [
                'code' => '[my_shortcode id="42"]',
                'description' => $this->l('Display content from my module.'),
                'parameters' => [
                    [
                        'name' => 'id',
                        'description' => $this->l('Content identifier.'),
                        'required' => true,
                    ],
                ],
            ],
        ],
    ];
}
```

Chaque rubrique contient `title` et `entries`. Chaque entree contient `code`, `description` et `parameters` (tableau vide si aucun parametre). Les libelles doivent etre traduits par le module contributeur dans la langue courante. Ce hook enrichit uniquement la documentation ; le rendu du shortcode reste gere par votre module.

## FAQ et pages front office

Ever Block fournit deux contenus front office natifs :

- Pages : liste sur `/guide` par defaut, detail sur `/guide/{id}-{rewrite}`.
- FAQ : liste sur `/faq` par defaut, filtre par tag sur `/faq/tag/{tag}`.

Les bases d'URL (`guide` et `faq`) et la pagination se configurent dans `Configuration > Pages`.

Les FAQ sont regroupees par tag et peuvent etre liees a des produits via la table `everblock_faq_product`. Quand un produit possede des FAQ associees, le module peut ajouter automatiquement un onglet produit via `displayProductExtraContent`.

Les helpers disponibles sur l'alias legacy `EverblockFaq` permettent de piloter ces associations depuis un import, un cron ou un module tiers :

- `EverblockFaq::linkToProduct($faqId, $productId, $shopId = null, $position = null)`
- `EverblockFaq::unlinkProductFaqs($productId, $shopId = null, array $faqIds = null)`
- `EverblockFaq::getFaqIdsByProduct($productId, $shopId = null)`
- `EverblockFaq::getProductsByFaq($faqId, $shopId = null)`
- `EverblockFaq::getByIds(array $faqIds, $langId, $shopId = null)`

## Produit, checkout et commande

Le module ajoute plusieurs outils autour de la fiche produit et du tunnel de commande :

- onglets produit globaux ou specifiques au produit ;
- FAQ produit ;
- modales produit avec contenu multilingue et fichiers associes ;
- flags produit, dont flag "sold out" et flags bases sur des caracteristiques ;
- etape supplementaire de checkout configurable ;
- contenu supplementaire sur confirmation de commande, detail commande, facture PDF, bon de livraison PDF et emails.

## Integrations

### QCD Page Builder

Lorsque QCD Page Builder est installe, Ever Block enregistre ses hooks d'integration et expose des cibles editables pour les blocs, shortcodes, FAQ, pages et contenus produit.

### QCD ACF

Les shortcodes `[qcdacf ...]` et `[displayQcdSvg ...]` permettent d'afficher des champs ACF et des icones SVG fournis par QCD ACF.

### Google

La configuration Google couvre :

- les avis Google Places via `[googlereviews ...]` ;
- Google Maps pour `[evermap]` ;
- le store locator via `[storelocator]` ;
- l'icone SVG de marqueur de carte ;
- les horaires specifiques de jours feries par magasin.

### Instagram et WordPress

Le module peut :

- rafraichir un token Instagram et telecharger les medias utilises par `[everinstagram]` ;
- recuperer les derniers articles d'un site WordPress REST pour `[wordpress-posts]`.

## Cache

Ever Block utilise un cache module centralise dans `Everblock\Tools\Service\EverblockCache`.

Le cache est invalide automatiquement lors de la mise a jour des blocs, FAQ, pages, relations FAQ/produit et contenus produit. Pour le vider manuellement :

- back office : `Ever Block > Configuration > Outils > Empty Everblock cache` ;
- console : `php bin/console everblock:tools:execute clearcache`.

Apres une mise a jour de code, une installation, une restauration ou une migration, videz aussi le cache PrestaShop.

## Taches cron

La page `Configuration > Taches crons` genere des URLs signees avec un token rotatif. Les actions HTTP exposees volontairement sont limitees a :

- `saveblocks`
- `droplogs`
- `refreshtokens`
- `fetchwordpressposts`

Les actions de restauration, migration ou securisation restent disponibles en console ou via les boutons du back office, pas via le controleur front cron.

## Commandes console

Toutes les commandes doivent etre lancees depuis la racine PrestaShop avec PHP 8.1+.

| Commande | Usage |
| --- | --- |
| `php bin/console everblock:tools:execute --list` | Liste les actions de maintenance disponibles. |
| `php bin/console everblock:tools:execute clearcache` | Vide uniquement le cache Ever Block. |
| `php bin/console everblock:tools:execute checkdatabase` | Verifie et repare le schema SQL du module. |
| `php bin/console everblock:tools:execute saveblocks [idshop]` | Sauvegarde les tables et assets du module. |
| `php bin/console everblock:tools:execute restoreblocks` | Restaure les tables et assets depuis une sauvegarde. |
| `php bin/console everblock:tools:execute duplicateblockslang [idshop] [fromlang] [tolang]` | Duplique le contenu des blocs d'une langue vers une autre. |
| `php bin/console everblock:tools:execute fetchwordpressposts` | Recupere les articles WordPress configures. |
| `php bin/console everblock:tools:execute fetchinstagramimages` | Telecharge les medias Instagram configures. |
| `php bin/console everblock:tools:execute refreshtokens` | Rafraichit le token Instagram. |
| `php bin/console everblock:tools:execute removeinlinecsstags [idshop]` | Supprime les attributs `style` des descriptions produit. |
| `php bin/console everblock:tools:execute removehn [idshop]` | Remplace les titres `h1` a `h6` dans les descriptions par des paragraphes classes. |
| `php bin/console everblock:tools:execute droplogs` | Vide les logs natifs PrestaShop. |
| `php bin/console everblock:tools:execute dropunusedlangs` | Supprime les traductions orphelines. |
| `php bin/console everblock:tools:execute securewithapache` | Ajoute des protections Apache dans les dossiers module. |
| `php bin/console everblock:tools:execute saveproducts [idshop]` | Re-sauvegarde les produits de la boutique. |
| `php bin/console everblock:tools:execute generateproducts [idshop]` | Genere des produits de demonstration. |
| `php bin/console everblock:tools:export blocks [idshop] [lang]` | Exporte les blocs vers `output/everblock.xlsx`. |
| `php bin/console everblock:tools:import` | Importe `input/everblock.xlsx`, puis supprime le fichier et vide le cache module. |
| `php bin/console everblock:tools:import_tab` | Importe `input/everblock_tabs.xlsx`. |
| `php bin/console everblock:tools:search-replace <search> <replace> [idshop]` | Remplace une chaine dans la base pour une migration d'URL. |

## Fichiers et securite

- Les assets publics sont dans `views/css`, `views/js` et `views/img`.
- Le dossier `views/img` doit contenir uniquement des medias. Les fichiers executables y sont supprimes par `EverblockTools::cleanObsoleteFiles()`, sauf les `index.php` de protection.
- Les uploads de formulaire et de modale passent par `EverblockUploadGuard`.
- Les URLs cron utilisent un token dedie et regenerable.
- Les actions de configuration verifient les permissions natives PrestaShop selon l'operation executee.

## Developpement

Le depot contient des workflows GitHub pour :

- lint PHP 8.1 ;
- lint Smarty quand Smarty est disponible ;
- PHPStan avec `phpstan.neon` ;
- PHP CS Fixer ;
- validation et packaging de release.

Commandes utiles en local :

```bash
composer install
php -l everblock.php
php bin/console everblock:tools:execute --list
php phpstan.phar analyse --no-progress --configuration=phpstan.neon --memory-limit=1G
```

Sur Windows, si le PHP du `PATH` est inferieur a 8.1, lancez ces commandes dans un conteneur Docker PHP 8.1+ ou dans l'environnement PrestaShop cible.

## Licence

Ever Block est distribue sous licence AFL 3.0. Voir `LICENSE.md`.
