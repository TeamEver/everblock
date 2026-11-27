# Ever Block

![Ever Block logo](logo.png)

Ever Block est un module gratuit pour PrestaShop qui permet d'ajouter des blocs HTML, du contenu dynamique et des shortcodes dans les hooks d'affichage de la boutique.

Le module est concu pour les boutiques PrestaShop 8 et 9. PrestaShop 1.7 n'est plus une cible de compatibilite.

Version documentee : 9.0.8.

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

## Pages front office

Les pages de contenu sont listees sur `/guide` par defaut et affichees sur `/guide/{id}-{rewrite}`. La base d'URL et la pagination se configurent dans `Configuration > Pages`.

La FAQ a ete retiree en 9.0.8 au profit de QCD FAQ (`qcdfaq`) : la mise a jour supprime les tables `everblock_faq*`, l'onglet FAQ et les reglages `EVERBLOCK_FAQ_*`. Les shortcodes `[everfaq ...]` restes dans des contenus sont effaces au rendu ; l'outil MCP `audit` les signale.

Les jeux (roue de la fortune, carte a gratter, boites mystere, calendrier de l'Avent, machine a sous) et l'integration PrettyBlocks ont aussi ete retires en 9.0.8 : la mise a jour supprime la table `everblock_game_play` et le dossier `views/img/prettyblocks`.

## Produit, checkout et commande

Le module ajoute plusieurs outils autour de la fiche produit et du tunnel de commande :

- onglets produit globaux ou specifiques au produit ;
- modales produit avec contenu multilingue et fichiers associes ;
- flags produit, dont flag "sold out" et flags bases sur des caracteristiques ;
- etape supplementaire de checkout configurable ;
- donnees de l'etape de commande sur la confirmation de commande, la facture PDF et le bon de livraison PDF ;
- shortcodes rendus dans les e-mails.

## Integrations

### QCD Page Builder

Lorsque QCD Page Builder est installe, Ever Block enregistre ses hooks d'integration et expose des cibles editables pour les blocs, shortcodes, pages et contenus produit.

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

## Cache et performances

Ever Block utilise un cache module centralise dans `Everblock\Tools\Service\EverblockCache`, sur deux niveaux :

- memoire de la requete, toujours ;
- cache persistant de PrestaShop (Redis via QCD Redis, Memcached...) quand il est active, pour les seules donnees independantes du client : lignes de blocs par hook, HTML brut des blocs avant shortcodes, listes d'ID produits des shortcodes, meilleures ventes, avis Google, medias Instagram, flags produit. Duree de vie : une heure. Les produits presentes (prix du groupe client) et les objets restent en memoire de requete.

Toute modification faite dans le back-office, par MCP ou en console incremente une generation globale (`EVERBLOCK_CACHE_GENERATION`) qui invalide tout le cache persistant du module. Pour le vider manuellement :

- back office : `Ever Block > Configuration > Outils > Empty Everblock cache` ;
- console : `php bin/console everblock:tools:execute clearcache`.

Le moteur de shortcodes ne repasse sur la page entiere que si elle contient un shortcode reellement traite (natif, personnalise, champ client ou variable `$shop.` / `$customer.`...). Un module tiers branche sur `displayBeforeRenderingShortcodes` retablit la detection large. Les variables de page ne sont calculees qu'une fois par requete et seulement si elles sont citees.

Les flags produit des listings coutent une requete par page (produits qui ont des flags) puis une requete par produit concerne, en cache persistant. Les emplacements d'onglet et de flag vides ne sont plus enregistres.

Les controles d'environnement (index.php des dossiers, schema SQL, hooks) ne tournent plus qu'une fois par version du module ; `everblock:tools:execute checkdatabase` force la reparation.

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
| `php bin/console everblock:update [--check] [--reinstall] [--no-upgrade]` | Met a jour le module depuis la derniere release GitHub (TeamEver/everblock), puis lance `prestashop:module upgrade everblock`. `--check` : versions et notes seulement. |

## MCP

Le module declare `isMcpCompliant()`. Ses outils (`src/Mcp/EverblockMcpTools.php` pour les blocs, `src/Mcp/EverblockContentMcpTools.php` pour les contenus) portent les attributs de `ps_mcp_server` : ils sont publies par le serveur MCP officiel de PrestaShop comme par QCD MCP Server, qui applique en plus les droits back-office, les prevalidations et la validation des ecritures declares dans `meta` (`qcd_permission`, `qcd_precheck`, `qcd_approval`). Noms publies : `everblock-<outil>`.

| Outil | Role | Droit BO |
| --- | --- | --- |
| `blocks_list` | blocs par hook, statut (en ligne, programme, expire, inactif), recherche, extrait, langues renseignees | AdminEverBlock lecture |
| `block_get` | tous les reglages d'un bloc, contenu par langue (cles ISO), ciblage, habillage, modale | AdminEverBlock lecture |
| `block_save` | creer (inactif par defaut) ou modifier un bloc ; seuls les champs fournis changent | AdminEverBlock modification |
| `block_toggle` | activer ou desactiver | AdminEverBlock modification |
| `block_duplicate` | copier, eventuellement vers un autre hook ; copie inactive par defaut | AdminEverBlock creation |
| `block_delete` | supprimer definitivement | AdminEverBlock suppression |
| `block_preview` | rendu du hook du bloc dans un contexte (page, objet, langue, client) | AdminEverBlock lecture |
| `hooks_list` | hooks d'affichage, nombre de blocs, module greffe ou non | AdminEverBlock lecture |
| `shortcodes_doc` | reference des shortcodes et shortcodes personnalises | AdminEverBlock lecture |
| `audit` | blocs expires ou bientot expires, programmes, vides, traductions manquantes, hook absent ou non greffe, shortcodes inconnus ou retires, produits indisponibles appeles par `[product ...]` | AdminEverBlock lecture |
| `cache_clear` | vider le cache du module (sans validation) | AdminEverBlockConfiguration modification |
| `update_check` | version installee, derniere release GitHub, notes de version | AdminEverBlockConfiguration lecture |
| `cli_everblock_update` | commande `everblock:update` publiee par QCD MCP Server : installation de la derniere release (validation dans le BO) | configuration du module |
| `pages_list` | pages du guide : nom, titre, URL, statut, groupes, recherche | AdminEverBlockPage lecture |
| `page_get` | tous les champs d'une page par langue, URL | AdminEverBlockPage lecture |
| `page_save` | creer (inactive par defaut) ou modifier une page ; seuls les champs et langues fournis changent | AdminEverBlockPage modification |
| `page_delete` | supprimer une page | AdminEverBlockPage suppression |
| `shortcode_get` | detail d'un shortcode personnalise (ID ou code) | AdminEverBlockShortcode lecture |
| `shortcode_save` | creer ou modifier un shortcode personnalise ; code deja pris ou natif refuse | AdminEverBlockShortcode modification |
| `shortcode_delete` | supprimer un shortcode personnalise | AdminEverBlockShortcode suppression |
| `shortcode_render` | rendu d'un contenu avec ses shortcodes, sans enregistrer | AdminEverBlock lecture |
| `product_contents_get` | onglets, flags et modale d'un produit (ID ou reference) | Produits lecture |
| `product_tab_save` / `product_flag_save` | ecrire l'onglet ou le flag n° slot d'un produit, par langue | Produits modification |
| `product_modal_save` | contenu et libelle du bouton de la modale d'un produit | Produits modification |
| `content_search` | chercher un texte dans les blocs, shortcodes, pages, onglets, flags et modales | AdminEverBlock lecture |
| `content_replace` | remplacer un texte dans ces seuls contenus ; simulation par defaut, 1 000 champs au plus | AdminEverBlockConfiguration modification |

Les ecritures passent par les gestionnaires et entites du back-office (`SaveAdminItemHandler`, `DeleteAdminItemHandler`, onglets, flags, modale) : memes champs, meme conversion des images, meme vidage de cache, meme greffe du module sur le hook choisi. Le HTML des pages, shortcodes et contenus produit est controle comme dans le back-office (`Validate::isCleanHtml`) ; les fiches produit modifiees sont purgees du cache HTTP par les hooks de qcdvarnish. Volontairement absents : la commande `search-replace` (toute la base, remplacee par `content_replace`), les jetons et URL cron, l'import et l'export de fichiers, la restauration et la migration.

### Mise a jour depuis GitHub

Le bouton du back-office, la commande `everblock:update` et l'outil MCP `cli_everblock_update` suivent la meme procedure (`src/Service/ModuleUpdater.php`) :

1. archive du tag de la derniere release, extraite dans `var/everblock_update/` ;
2. controle de l'archive (module Ever Block, version egale au tag) ;
3. copie de sauvegarde du module dans `var/everblock_backups/` (les 3 dernieres sont gardees) ;
4. remplacement du dossier du module, retour a l'ancienne version si le remplacement echoue ;
5. reprise des fichiers generes par la boutique et absents de la release (CSS et JS personnalises, medias, imports et exports ; jamais de code) ;
6. scripts de mise a jour (`prestashop:module upgrade everblock`, lances dans un nouveau processus par la commande).

Toute modification locale du code du module, non publiee sur GitHub, est remplacee par la release : la restaurer depuis la sauvegarde si besoin.

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
