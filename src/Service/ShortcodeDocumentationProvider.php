<?php

/**
 * 2019-2025 Team Ever
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Team Ever <https://www.team-ever.com/>
 *  @copyright 2019-2025 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace Everblock\Tools\Service;

use Context;
use Module;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ShortcodeDocumentationProvider
{
    /**
     * @var array<int, array<int, array<string, mixed>>>
     */
    protected static $cache = [];

    /**
     * Build the translated documentation describing every available shortcode.
     *
     * @param Module $module
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getDocumentation(Module $module): array
    {
        $context = Context::getContext();
        $idLang = (int) $context->language->id;

        if (isset(static::$cache[$idLang])) {
            return static::$cache[$idLang];
        }

        $translator = $context->getTranslator();
        $domain = 'Modules.Everblock.Shortcodes';

        $docs = [
            [
                'title' => $translator->trans('Catalog & merchandising', [], $domain),
                'entries' => [
                    [
                        'code' => '[product id="1,2,3" carousel=true]',
                        'description' => $translator->trans('Display one or more products by their IDs with an optional carousel layout.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'id',
                                'description' => $translator->trans('Comma-separated list of product IDs to render.', [], $domain),
                                'required' => true,
                            ],
                            [
                                'name' => 'carousel',
                                'description' => $translator->trans('Enable the product carousel (true/false).', [], $domain),
                                'required' => false,
                            ],
                        ],
                    ],
                    [
                        'code' => '[product_image id="1" image="2"]',
                        'description' => $translator->trans('Output a specific image from a product gallery.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'id',
                                'description' => $translator->trans('Product ID providing the media.', [], $domain),
                                'required' => true,
                            ],
                            [
                                'name' => 'image',
                                'description' => $translator->trans('Image position to display. Defaults to the first picture.', [], $domain),
                                'required' => false,
                            ],
                        ],
                    ],
                    [
                        'code' => '[productfeature id="2" nb="12" carousel=true]',
                        'description' => $translator->trans('List products matching a given feature.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'id',
                                'description' => $translator->trans('Feature ID used to filter the catalog.', [], $domain),
                                'required' => true,
                            ],
                            [
                                'name' => 'nb / limit',
                                'description' => $translator->trans('Number of products to display (defaults to 10).', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'carousel',
                                'description' => $translator->trans('Render the selection inside a carousel.', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'orderby / orderway',
                                'description' => $translator->trans('Control the sorting field and direction.', [], $domain),
                                'required' => false,
                            ],
                        ],
                    ],
                    [
                        'code' => '[productfeaturevalue id="5" nb="8"]',
                        'description' => $translator->trans('Display products attached to a specific feature value.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'id',
                                'description' => $translator->trans('Feature value ID to target.', [], $domain),
                                'required' => true,
                            ],
                            [
                                'name' => 'nb / limit / carousel / orderby / orderway',
                                'description' => $translator->trans('Fine-tune quantity, carousel layout and ordering.', [], $domain),
                                'required' => false,
                            ],
                        ],
                    ],
                    [
                        'code' => '[category id="8" nb="8"]',
                        'description' => $translator->trans('Show products coming from a specific category.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'id',
                                'description' => $translator->trans('Category ID to pull products from.', [], $domain),
                                'required' => true,
                            ],
                            [
                                'name' => 'nb / limit / carousel / orderby / orderway',
                                'description' => $translator->trans('Adjust product count, carousel usage and sorting.', [], $domain),
                                'required' => false,
                            ],
                        ],
                    ],
                    [
                        'code' => '[manufacturer id="3" nb="12"]',
                        'description' => $translator->trans('Display products associated with a manufacturer or brand.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'id',
                                'description' => $translator->trans('Manufacturer ID to filter by.', [], $domain),
                                'required' => true,
                            ],
                            [
                                'name' => 'nb / limit / carousel / orderby / orderway',
                                'description' => $translator->trans('Control the number of items and ordering.', [], $domain),
                                'required' => false,
                            ],
                        ],
                    ],
                    [
                        'code' => '[brands nb="8" carousel=true]',
                        'description' => $translator->trans('List manufacturer logos and links.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'nb',
                                'description' => $translator->trans('How many brands to display.', [], $domain),
                                'required' => true,
                            ],
                            [
                                'name' => 'carousel',
                                'description' => $translator->trans('Enable the carousel layout.', [], $domain),
                                'required' => false,
                            ],
                        ],
                    ],
                    [
                        'code' => '[subcategories id="2" nb="8"]',
                        'description' => $translator->trans('Reveal the child categories of a category.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'id',
                                'description' => $translator->trans('Parent category ID.', [], $domain),
                                'required' => true,
                            ],
                            [
                                'name' => 'nb',
                                'description' => $translator->trans('Maximum number of subcategories to show.', [], $domain),
                                'required' => true,
                            ],
                        ],
                    ],
                    [
                        'code' => '[last-products nb="4" carousel=true]',
                        'description' => $translator->trans('Display the most recently created products.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'nb / limit',
                                'description' => $translator->trans('Number of products to retrieve.', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'carousel',
                                'description' => $translator->trans('Switch to a carousel view.', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'orderby / orderway',
                                'description' => $translator->trans('Override the default sorting strategy.', [], $domain),
                                'required' => false,
                            ],
                        ],
                    ],
                    [
                        'code' => '[recently_viewed nb="4" carousel=true]',
                        'description' => $translator->trans('Suggest the products recently seen by the visitor.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'nb',
                                'description' => $translator->trans('Maximum number of items.', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'carousel',
                                'description' => $translator->trans('Display items in a carousel.', [], $domain),
                                'required' => false,
                            ],
                        ],
                    ],
                    [
                        'code' => '[promo-products nb="10" carousel=true]',
                        'description' => $translator->trans('Highlight products currently on promotion.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'nb / limit',
                                'description' => $translator->trans('Number of discounted products to show.', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'carousel / orderby / orderway',
                                'description' => $translator->trans('Adjust layout and ordering.', [], $domain),
                                'required' => false,
                            ],
                        ],
                    ],
                    [
                        'code' => '[best-sales nb="10" carousel=true]',
                        'description' => $translator->trans('Expose the top selling products in the catalogue.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'nb / limit',
                                'description' => $translator->trans('Quantity of best sellers to return.', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'days',
                                'description' => $translator->trans('Restrict sales data to the last X days.', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'carousel / orderby / orderway',
                                'description' => $translator->trans('Switch layout and ordering.', [], $domain),
                                'required' => false,
                            ],
                        ],
                    ],
                    [
                        'code' => '[categorybestsales id="8" nb="10"]',
                        'description' => $translator->trans('Show best selling products limited to a category.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'id',
                                'description' => $translator->trans('Category ID that constrains the statistics.', [], $domain),
                                'required' => true,
                            ],
                            [
                                'name' => 'nb / limit / days / carousel / orderby / orderway',
                                'description' => $translator->trans('Fine-tune the dataset and rendering.', [], $domain),
                                'required' => false,
                            ],
                        ],
                    ],
                    [
                        'code' => '[brandbestsales id="3" nb="10"]',
                        'description' => $translator->trans('Top sellers restricted to a manufacturer.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'id',
                                'description' => $translator->trans('Manufacturer ID that filters the report.', [], $domain),
                                'required' => true,
                            ],
                            [
                                'name' => 'nb / limit / days / carousel / orderby / orderway',
                                'description' => $translator->trans('Optional layout and dataset controls.', [], $domain),
                                'required' => false,
                            ],
                        ],
                    ],
                    [
                        'code' => '[featurebestsales id="2" nb="10"]',
                        'description' => $translator->trans('Best sellers using a feature as the selector.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'id',
                                'description' => $translator->trans('Feature ID referenced by the report.', [], $domain),
                                'required' => true,
                            ],
                            [
                                'name' => 'nb / limit / days / carousel / orderby / orderway',
                                'description' => $translator->trans('Optional filters for volume and ordering.', [], $domain),
                                'required' => false,
                            ],
                        ],
                    ],
                    [
                        'code' => '[featurevaluebestsales id="5" nb="10"]',
                        'description' => $translator->trans('Best selling products tied to a feature value.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'id',
                                'description' => $translator->trans('Feature value ID analysed by the shortcode.', [], $domain),
                                'required' => true,
                            ],
                            [
                                'name' => 'nb / limit / days / carousel / orderby / orderway',
                                'description' => $translator->trans('Optional configuration knobs.', [], $domain),
                                'required' => false,
                            ],
                        ],
                    ],
                    [
                        'code' => '[products_by_tag tag="summer|sale" match="all" limit="8"]',
                        'description' => $translator->trans('Pull products associated with one or several native PrestaShop tags.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'tag / tag_id',
                                'description' => $translator->trans('Pipe-separated tag names or IDs (at least one is required).', [], $domain),
                                'required' => true,
                            ],
                            [
                                'name' => 'match',
                                'description' => $translator->trans('Decide whether all tags must match (all) or any tag is enough (any).', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'limit / offset',
                                'description' => $translator->trans('Control pagination of the result set.', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'order / way',
                                'description' => $translator->trans('Sort field and direction (price, date_add, position, etc.).', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'cols',
                                'description' => $translator->trans('Number of columns used in the grid template.', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'visibility',
                                'description' => $translator->trans('Filter on visibility states (both, catalog, search, none).', [], $domain),
                                'required' => false,
                            ],
                        ],
                    ],
                    [
                        'code' => '[low_stock limit="8" threshold="3" order="qty" way="asc"]',
                        'description' => $translator->trans('Surface products that are about to run out of stock.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'limit / offset',
                                'description' => $translator->trans('Number of items to display and optional offset.', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'threshold',
                                'description' => $translator->trans('Stock level threshold triggering the alert (defaults to module configuration).', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'match',
                                'description' => $translator->trans('Comparison operator against the threshold (lt, lte, eq, gt, gte).', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'order / way',
                                'description' => $translator->trans('Sort by quantity, creation date, name, price, sales or randomly.', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'days',
                                'description' => $translator->trans('Restrict results to products updated within the last X days.', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'id_category / id_manufacturer',
                                'description' => $translator->trans('Filter on specific categories or manufacturers (pipe-separated).', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'visibility',
                                'description' => $translator->trans('Accepted visibility values (both,catalog,search,none).', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'available_only',
                                'description' => $translator->trans('Limit to available combinations (1) or include disabled ones (0).', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'cols',
                                'description' => $translator->trans('Columns used by the template.', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'by',
                                'description' => $translator->trans('Aggregate stock by product or by combination.', [], $domain),
                                'required' => false,
                            ],
                        ],
                    ],
                    [
                        'code' => '[random_product nb="10" carousel=true]',
                        'description' => $translator->trans('Pick random products every time the block loads.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'nb / limit',
                                'description' => $translator->trans('Number of random items returned.', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'carousel',
                                'description' => $translator->trans('Whether the output should slide as a carousel.', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'orderby / orderway',
                                'description' => $translator->trans('Force a deterministic ordering instead of randomness.', [], $domain),
                                'required' => false,
                            ],
                        ],
                    ],
                    [
                        'code' => '[linkedproducts nb="8" orderby="date_add" orderway="DESC"]',
                        'description' => $translator->trans('Suggest accessories linked to the current product.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'nb / limit',
                                'description' => $translator->trans('Limit the number of related products.', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'orderby / orderway',
                                'description' => $translator->trans('Define the sorting field (position, price, date_add, etc.) and direction.', [], $domain),
                                'required' => false,
                            ],
                        ],
                    ],
                    [
                        'code' => '[accessories nb="8" orderby="date_add" orderway="DESC"]',
                        'description' => $translator->trans('Display accessories belonging to the current product.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'nb / limit',
                                'description' => $translator->trans('How many accessories to fetch (one of the two parameters is required).', [], $domain),
                                'required' => true,
                            ],
                            [
                                'name' => 'orderby / orderway',
                                'description' => $translator->trans('Adjust the sorting behaviour.', [], $domain),
                                'required' => false,
                            ],
                        ],
                    ],
                    [
                        'code' => '[crosselling nb="4" orderby="id_product" orderway="asc"]',
                        'description' => $translator->trans('Mix cross-selling accessories with top sellers when the cart is empty.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'nb / limit',
                                'description' => $translator->trans('Number of products displayed to the customer.', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'orderby / orderway',
                                'description' => $translator->trans('Select the ordering strategy.', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'carousel',
                                'description' => $translator->trans('Enable the carousel layout.', [], $domain),
                                'required' => false,
                            ],
                        ],
                    ],
                    [
                        'code' => '[everaddtocart ref="1234" text="Add me to cart"]',
                        'description' => $translator->trans('Generate an Add to cart button pointing to a product reference.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'ref',
                                'description' => $translator->trans('Product reference to add to the cart.', [], $domain),
                                'required' => true,
                            ],
                            [
                                'name' => 'text',
                                'description' => $translator->trans('Custom label displayed on the button.', [], $domain),
                                'required' => false,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => $translator->trans('Content & layout', [], $domain),
                'entries' => [
                    [
                        'code' => '[everblock 3]',
                        'description' => $translator->trans('Inject the content of another Ever Block by its ID.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'ID',
                                'description' => $translator->trans('Numeric identifier of the block to embed.', [], $domain),
                                'required' => true,
                            ],
                        ],
                    ],
                    [
                        'code' => '[cms id="1"]',
                        'description' => $translator->trans('Render the content of a CMS page.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'id',
                                'description' => $translator->trans('CMS page ID to display.', [], $domain),
                                'required' => true,
                            ],
                        ],
                    ],
                    [
                        'code' => '[everstore id="1,4"]',
                        'description' => $translator->trans('Display one or several stores from the store locator.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'id',
                                'description' => $translator->trans('Comma-separated store IDs to include.', [], $domain),
                                'required' => true,
                            ],
                        ],
                    ],
                    [
                        'code' => '[storelocator]',
                        'description' => $translator->trans('Insert the interactive store locator (requires a Google Maps API key).', [], $domain),
                        'parameters' => [],
                    ],
                    [
                        'code' => '[evermap]',
                        'description' => $translator->trans('Display a Google map centered on the shop address.', [], $domain),
                        'parameters' => [],
                    ],
                    [
                        'code' => '[video url="https://..."]',
                        'description' => $translator->trans('Embed a responsive iframe pointing to a video URL (YouTube, Vimeo, etc.).', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'url',
                                'description' => $translator->trans('Video sharing URL to embed.', [], $domain),
                                'required' => true,
                            ],
                        ],
                    ],
                    [
                        'code' => '[everimg name="image.jpg" class="img-fluid" carousel=true]',
                        'description' => $translator->trans('Render one or multiple images stored in the CMS folder.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'name',
                                'description' => $translator->trans('File name(s) to display (comma-separated for galleries).', [], $domain),
                                'required' => true,
                            ],
                            [
                                'name' => 'class',
                                'description' => $translator->trans('Extra CSS classes added to the image wrapper.', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'carousel',
                                'description' => $translator->trans('Convert the gallery into a carousel.', [], $domain),
                                'required' => false,
                            ],
                        ],
                    ],
                    [
                        'code' => '[displayQcdSvg name="icon" class="myclass" inline=true]',
                        'description' => $translator->trans('Load an SVG from the QCD ACF icon library.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'name',
                                'description' => $translator->trans('Icon identifier inside the QCD collection.', [], $domain),
                                'required' => true,
                            ],
                            [
                                'name' => 'class',
                                'description' => $translator->trans('Optional CSS classes added to the SVG.', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'inline',
                                'description' => $translator->trans('Force inline rendering instead of an <img> tag.', [], $domain),
                                'required' => false,
                            ],
                        ],
                    ],
                    [
                        'code' => '[qcdacf field="my_field" objectType="product" objectId="12"]',
                        'description' => $translator->trans('Print values provided by the QCD ACF module.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'field',
                                'description' => $translator->trans('ACF field key to display.', [], $domain),
                                'required' => true,
                            ],
                            [
                                'name' => 'objectType',
                                'description' => $translator->trans('Entity type (product, category, supplier, etc.).', [], $domain),
                                'required' => true,
                            ],
                            [
                                'name' => 'objectId',
                                'description' => $translator->trans('Identifier of the entity providing the field value.', [], $domain),
                                'required' => true,
                            ],
                        ],
                    ],
                    [
                        'code' => '[widget moduleName="mymodule" hookName="displayHome"]',
                        'description' => $translator->trans('Render any module that exposes a widget on the given hook.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'moduleName',
                                'description' => $translator->trans('Technical name of the module to call.', [], $domain),
                                'required' => true,
                            ],
                            [
                                'name' => 'hookName',
                                'description' => $translator->trans('Hook identifier passed to renderWidget.', [], $domain),
                                'required' => true,
                            ],
                        ],
                    ],
                    [
                        'code' => '[prettyblocks name="myzone"]',
                        'description' => $translator->trans('Insert the output of a PrettyBlocks zone.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'name',
                                'description' => $translator->trans('Zone name configured inside PrettyBlocks.', [], $domain),
                                'required' => true,
                            ],
                        ],
                    ],
                    [
                        'code' => '[wordpress-posts]',
                        'description' => $translator->trans('Display the latest posts retrieved from a connected WordPress site.', [], $domain),
                        'parameters' => [],
                    ],
                    [
                        'code' => '[googlereviews place_id="YOUR_PLACE_ID" limit="6" min_rating="4"]',
                        'description' => $translator->trans('Showcase reviews from your Google Business profile.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'key / api_key',
                                'description' => $translator->trans('Override the Google Places API key configured in the module.', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'place_id / id',
                                'description' => $translator->trans('Google Place identifier of your business.', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'limit',
                                'description' => $translator->trans('Maximum number of reviews to display.', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'min_rating',
                                'description' => $translator->trans('Ignore reviews below this rating.', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'sort',
                                'description' => $translator->trans('Sort order (most_recent, rating, most_useful).', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'show_rating / show_avatar / show_cta',
                                'description' => $translator->trans('Toggle UI elements on or off (true/false).', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'cta_label / cta_url',
                                'description' => $translator->trans('Customise the call-to-action button.', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'columns / title / intro / class',
                                'description' => $translator->trans('Layout columns, heading, introduction text and extra classes.', [], $domain),
                                'required' => false,
                            ],
                        ],
                    ],
                    [
                        'code' => '[everinstagram]',
                        'description' => $translator->trans('Display the latest Instagram media fetched by the module.', [], $domain),
                        'parameters' => [],
                    ],
                    [
                        'code' => '[llorem]',
                        'description' => $translator->trans('Insert placeholder lorem ipsum text according to module settings.', [], $domain),
                        'parameters' => [],
                    ],
                    [
                        'code' => '{hook h=\'displayHome\'}',
                        'description' => $translator->trans('Execute another PrestaShop hook and inject its widgets.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'h',
                                'description' => $translator->trans('Hook name to execute.', [], $domain),
                                'required' => true,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => $translator->trans('Cart & customer data', [], $domain),
                'entries' => [
                    [
                        'code' => '[evercart]',
                        'description' => $translator->trans('Display the dropdown cart widget.', [], $domain),
                        'parameters' => [],
                    ],
                    [
                        'code' => '[cart_total]',
                        'description' => $translator->trans('Show the current cart total formatted with the active currency.', [], $domain),
                        'parameters' => [],
                    ],
                    [
                        'code' => '[cart_quantity]',
                        'description' => $translator->trans('Print the total quantity of items in the cart.', [], $domain),
                        'parameters' => [],
                    ],
                    [
                        'code' => '[shop_logo]',
                        'description' => $translator->trans('Inject the shop logo as an <img> element.', [], $domain),
                        'parameters' => [],
                    ],
                    [
                        'code' => '[newsletter_form]',
                        'description' => $translator->trans('Render the PrestaShop newsletter subscription form.', [], $domain),
                        'parameters' => [],
                    ],
                    [
                        'code' => '[entity_lastname]',
                        'description' => $translator->trans('Authenticated customer last name.', [], $domain),
                        'parameters' => [],
                    ],
                    [
                        'code' => '[entity_firstname]',
                        'description' => $translator->trans('Authenticated customer first name.', [], $domain),
                        'parameters' => [],
                    ],
                    [
                        'code' => '[entity_gender]',
                        'description' => $translator->trans('Authenticated customer gender.', [], $domain),
                        'parameters' => [],
                    ],
                    [
                        'code' => '[nativecontact]',
                        'description' => $translator->trans('Embed the native PrestaShop contact form.', [], $domain),
                        'parameters' => [],
                    ],
                    [
                        'code' => '[everfaq tag="faq1"]',
                        'description' => $translator->trans('Display FAQs matching a given tag.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'tag',
                                'description' => $translator->trans('Tag used to group FAQ entries.', [], $domain),
                                'required' => true,
                            ],
                        ],
                    ],
                    [
                        'code' => '[alert type="success"]Content[/alert]',
                        'description' => $translator->trans('Bootstrap alert helper with optional contextual style.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'type',
                                'description' => $translator->trans('Alert flavour (primary, secondary, success, danger, warning, info, light, dark).', [], $domain),
                                'required' => false,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => $translator->trans('Forms & automation', [], $domain),
                'entries' => [
                    [
                        'code' => '[evercontactform_open] ... [evercontactform_close]',
                        'description' => $translator->trans('Wrap a custom contact form handled by Ever Block.', [], $domain),
                        'parameters' => [],
                    ],
                    [
                        'code' => '[evercontact type="text" label="Your name" required="true"]',
                        'description' => $translator->trans('Add a field to the contact form builder.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'type',
                                'description' => $translator->trans('Supported values include text, number, textarea, select, radio, checkbox, multiselect, file, hidden, sento, submit.', [], $domain),
                                'required' => true,
                            ],
                            [
                                'name' => 'label',
                                'description' => $translator->trans('Field label displayed to the user. For type="sento", provide comma-separated email recipients.', [], $domain),
                                'required' => true,
                            ],
                            [
                                'name' => 'values',
                                'description' => $translator->trans('Comma-separated options for select, radio, checkbox and multiselect fields.', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'required',
                                'description' => $translator->trans('Mark the field as mandatory (true/false).', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'class / value',
                                'description' => $translator->trans('Extra CSS classes or a predefined value depending on the field type.', [], $domain),
                                'required' => false,
                            ],
                        ],
                    ],
                    [
                        'code' => '[everorderform_open] ... [everorderform_close]',
                        'description' => $translator->trans('Create additional checkout steps with custom fields.', [], $domain),
                        'parameters' => [],
                    ],
                    [
                        'code' => '[everorderform type="text" label="Your name" required="true"]',
                        'description' => $translator->trans('Field definition used inside the order form wrapper.', [], $domain),
                        'parameters' => [
                            [
                                'name' => 'type',
                                'description' => $translator->trans('Supported values include text, number, textarea, select, radio, checkbox, multiselect, file, hidden and submit.', [], $domain),
                                'required' => true,
                            ],
                            [
                                'name' => 'label',
                                'description' => $translator->trans('Customer-facing label of the field.', [], $domain),
                                'required' => true,
                            ],
                            [
                                'name' => 'values',
                                'description' => $translator->trans('Comma-separated options for select, radio, checkbox and multiselect fields.', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'required',
                                'description' => $translator->trans('Mark the field as mandatory (true/false).', [], $domain),
                                'required' => false,
                            ],
                            [
                                'name' => 'class / value',
                                'description' => $translator->trans('Extra CSS classes or a predefined value depending on the field type.', [], $domain),
                                'required' => false,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        static::$cache[$idLang] = $docs;

        return $docs;
    }
}
