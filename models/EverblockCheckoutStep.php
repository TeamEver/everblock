<?php
/**
 * 2019-2024 Team Ever
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
 *  @copyright 2019-2021 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use Symfony\Component\Translation\TranslatorInterface;

class EverblockCheckoutStep extends AbstractCheckoutStep
{
    protected $module;
    protected $everdata;

    public function __construct(
        Context $context,
        TranslatorInterface $translator,
        Everblock $module
    )
    {
        parent::__construct($context, $translator);
        $this->context = $context;
        $this->translator = $translator;
        $this->module = $module;
        $title = Configuration::get(
            'EVEROPTIONS_TITLE',
            (int) $this->context->language->id
        );
        if (!$title) {
            return;
        }
        $this->setTitle(
            htmlspecialchars_decode($title)
        );
    }


    /**
     * Récupération des données à persister
     *
     * @return array
     */
    public function getDataToPersist()
    {
        return [
            'everdata' => $this->everdata,
        ];
    }

    /**
     * Restoration des données persistées
     *
     * @param array $data
     * @return $this|AbstractCheckoutStep
     */
    public function restorePersistedData(array $data)
    {
        foreach ($data as $key => $value) {
            $this->everdata = $data['everdata'];
        }

        return $this;
    }

    /**
     * Traitement de la requête ( ie = Variables Posts du checkout )
     * @param array $requestParameters
     * @return $this
     */
    public function handleRequest(array $requestParameters = [])
    {
        if (isset($requestParameters['submitCustomStep'])) {
            foreach ($requestParameters as $key => $value) {
                $this->everdata[$key] = htmlspecialchars(pSQL($value, true), ENT_QUOTES);
            }
            $this->setComplete(true);
            $this->setNextStepAsCurrent();
        }

        return $this;
    }

    /**
     * Affichage de la step
     *
     * @param array $extraParams
     * @return string
     */
    public function render(array $extraParams = [])
    {
        $fields = Hook::exec(
            'displayEverblockExtraOrderStep',
            [
                'id_customer' => $this->context->customer,
                'id_cart' => $this->context->cart,
                'step_is_complete' => (int) $this->isComplete(),
                'step_is_reachable' => (int) $this->isReachable(),
                'step_is_current' => (int) $this->isCurrent(),
                'everdata' => $this->everdata,
            ]
        );
        if (!$fields || empty($fields)) {
            return;
        }
        $fields = $this->processFieldsWithEverdata($fields);
        $this->context->smarty->assign([
            'identifier' => 'everorderoptions',
            'position' => 3,
            'title' => $this->getTitle(),
            'step_is_complete' => (int) $this->isComplete(),
            'step_is_reachable' => (int) $this->isReachable(),
            'step_is_current' => (int) $this->isCurrent(),
            'fields' => $fields,
            'everdata' => $this->everdata,
        ]);
        return $this->module->display(
            _PS_MODULE_DIR_ . $this->module->name,
            'views/templates/hook/everCheckoutStep.tpl'
        );
    }

    /**
     * Réattribue les valeurs du formulaire
    */
    protected function processFieldsWithEverdata($fields)
    {
        return preg_replace_callback(
            '/\[everorderform(.*?)\]/i',
            function ($matches) {
                $attributesString = $matches[1];
                preg_match_all('/(\w+)\s*=\s*"([^"]*)"/', $attributesString, $attrMatches, PREG_SET_ORDER);
                $attributes = [];
                foreach ($attrMatches as $match) {
                    $attributes[$match[1]] = $match[2];
                }

                if (!isset($attributes['label'])) {
                    return $matches[0];
                }

                $labelKey = str_replace(' ', '_', $attributes['label']);

                if (isset($this->everdata[$labelKey])) {
                    // Convertissez l'attribut 'value' en chaîne si c'est un tableau
                    if (is_array($this->everdata[$labelKey])) {
                        $attributes['value'] = implode(',', $this->everdata[$labelKey]);
                    } else {
                        $attributes['value'] = $this->everdata[$labelKey];
                    }
                }

                $updatedShortcode = '[everorderform';
                foreach ($attributes as $key => $value) {
                    $updatedShortcode .= " $key=\"" . htmlspecialchars($value, ENT_QUOTES) . "\"";
                }
                $updatedShortcode .= ']';

                return $updatedShortcode;
            },
            $fields
        );
    }
}
