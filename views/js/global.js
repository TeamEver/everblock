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
 *  @copyright 2019-2024 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

$(document).ready(function() {
    $('#everblock_use_gpt').click(function(e) {
        e.preventDefault();
        var productId = $(this).data('id_product');
        var everAjaxUrl = $(this).data('link');
        var dataType = $(this).data('type');
        $('.gpt_group').slideUp();
        $('#loader').show();
        $.ajax({
            url: everAjaxUrl,
            method: 'POST',
            data: {
                id_product: productId,
                evergpt: true,
                objectType: dataType
            },
            success: function(response) {
                $('#loader').hide();
                location.reload();
            },
            error: function(error) {
                $('#loader').hide();
                $('.gpt_group').slideDown();

                console.error(error);
            }
        });
    });
});
