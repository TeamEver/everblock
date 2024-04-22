{*
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
*}
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <div class="justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-lg p-3 mb-5 bg-white rounded">
                <div class="card-body">
                    <h4 class="card-title text-center">{l s='Enter your password' mod='everblock'}</h4>
                    <form method="post">
                        <div class="form-group">
                            <label for="evermaintenancepassword">Mot de passe:</label>
                            <input type="password" id="evermaintenancepassword" name="evermaintenancepassword" class="form-control" placeholder="{l s='Password' mod='everblock'}">
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">{l s='Send' mod='everblock'}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>