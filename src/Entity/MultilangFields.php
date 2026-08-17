<?php

declare(strict_types=1);

namespace Everblock\Tools\Entity;

/**
 * Resolution des champs multilang des entites Everblock.
 *
 * Ces entites ne sont pas des ObjectModel PrestaShop : leurs champs traduits
 * restent des tableaux indexes par id_lang, meme lorsqu'un id_lang est passe au
 * constructeur (celui-ci ne fait que restreindre les lignes chargees).
 */
trait MultilangFields
{
    /**
     * Resout une valeur multilang : langue demandee, puis langue par defaut de la
     * boutique, puis premiere traduction non vide disponible.
     *
     * Une traduction vide est traitee comme absente : en base les lignes de langue
     * sont creees pour toutes les langues, une chaine vide signifie donc « pas
     * encore traduit » et non « volontairement vide ».
     *
     * @param array<int, string> $values
     */
    protected function translated(array $values, ?int $idLang): string
    {
        if ($idLang !== null && isset($values[$idLang]) && $values[$idLang] !== '') {
            return (string) $values[$idLang];
        }

        $default = (int) \Configuration::get('PS_LANG_DEFAULT');
        if ($default > 0 && isset($values[$default]) && $values[$default] !== '') {
            return (string) $values[$default];
        }

        foreach ($values as $value) {
            if ((string) $value !== '') {
                return (string) $value;
            }
        }

        return '';
    }
}
