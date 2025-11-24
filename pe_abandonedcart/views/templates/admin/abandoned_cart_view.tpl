{extends file="helpers/view/view.tpl"}

{block name="override_tpl"}
<div class="panel">
    <div class="panel-heading">
        <i class="icon-shopping-cart"></i>
        {l s='Abandoned Cart Details' mod='pe_abandonedcart'}
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="panel">
                <div class="panel-heading">
                    <i class="icon-user"></i>
                    {l s='Customer Information' mod='pe_abandonedcart'}
                </div>
                <div class="panel-body">
                    <table class="table">
                        <tr>
                            <th>{l s='Customer ID' mod='pe_abandonedcart'}:</th>
                            <td>{$customer->id}</td>
                        </tr>
                        <tr>
                            <th>{l s='Name' mod='pe_abandonedcart'}:</th>
                            <td>{$customer->firstname} {$customer->lastname}</td>
                        </tr>
                        <tr>
                            <th>{l s='Email' mod='pe_abandonedcart'}:</th>
                            <td>{$abandonedCart->email}</td>
                        </tr>
                        <tr>
                            <th>{l s='Cart ID' mod='pe_abandonedcart'}:</th>
                            <td>{$cart->id}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="panel">
                <div class="panel-heading">
                    <i class="icon-calendar"></i>
                    {l s='Cart Status' mod='pe_abandonedcart'}
                </div>
                <div class="panel-body">
                    <table class="table">
                        <tr>
                            <th>{l s='Date Added' mod='pe_abandonedcart'}:</th>
                            <td>{$abandonedCart->date_add}</td>
                        </tr>
                        <tr>
                            <th>{l s='Last Update' mod='pe_abandonedcart'}:</th>
                            <td>{$abandonedCart->date_upd}</td>
                        </tr>
                        <tr>
                            <th>{l s='Email Sent' mod='pe_abandonedcart'}:</th>
                            <td>
                                {if $abandonedCart->email_sent}
                                    <span class="badge badge-success">{l s='Yes' mod='pe_abandonedcart'}</span>
                                    <br>{$abandonedCart->date_email_sent}
                                {else}
                                    <span class="badge badge-warning">{l s='No' mod='pe_abandonedcart'}</span>
                                {/if}
                            </td>
                        </tr>
                        <tr>
                            <th>{l s='Recovered' mod='pe_abandonedcart'}:</th>
                            <td>
                                {if $abandonedCart->recovered}
                                    <span class="badge badge-success">{l s='Yes' mod='pe_abandonedcart'}</span>
                                    <br>{$abandonedCart->date_recovered}
                                {else}
                                    <span class="badge badge-danger">{l s='No' mod='pe_abandonedcart'}</span>
                                {/if}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-shopping-cart"></i>
            {l s='Cart Products' mod='pe_abandonedcart'}
        </div>
        <div class="panel-body">
            {if isset($cartData.products) && $cartData.products}
                <table class="table">
                    <thead>
                        <tr>
                            <th>{l s='Product' mod='pe_abandonedcart'}</th>
                            <th>{l s='Quantity' mod='pe_abandonedcart'}</th>
                            <th>{l s='Price' mod='pe_abandonedcart'}</th>
                            <th>{l s='Total' mod='pe_abandonedcart'}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$cartData.products item=product}
                        <tr>
                            <td>{$product.name}</td>
                            <td>{$product.quantity}</td>
                            <td>{Tools::displayPrice($product.price_wt)}</td>
                            <td>{Tools::displayPrice($product.total_wt)}</td>
                        </tr>
                        {/foreach}
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right"><strong>{l s='Total' mod='pe_abandonedcart'}:</strong></td>
                            <td><strong>{Tools::displayPrice($cartData.total)}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            {else}
                <p class="alert alert-warning">{l s='No products found in cart.' mod='pe_abandonedcart'}</p>
            {/if}
        </div>
    </div>
    
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-link"></i>
            {l s='Recovery Link' mod='pe_abandonedcart'}
        </div>
        <div class="panel-body">
            <div class="form-group">
                <label>{l s='Recovery URL' mod='pe_abandonedcart'}:</label>
                <input type="text" class="form-control" value="{$recovery_url}" readonly onclick="this.select();">
                <p class="help-block">{l s='This is the unique recovery link for this cart. The customer can use this link to restore their cart.' mod='pe_abandonedcart'}</p>
            </div>
        </div>
    </div>
</div>
{/block}
