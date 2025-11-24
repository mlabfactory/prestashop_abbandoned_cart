{extends file='page.tpl'}

{block name='page_title'}
    <h1>{l s='Cart Recovery Error' mod='pe_abandonedcart'}</h1>
{/block}

{block name='page_content'}
    <div class="alert alert-warning">
        <p>{l s='We\'re sorry, but we couldn\'t recover your cart.' mod='pe_abandonedcart'}</p>
        <p>{l s='Your cart may have expired or already been recovered.' mod='pe_abandonedcart'}</p>
    </div>
    
    <div class="card">
        <div class="card-body">
            <h3>{l s='What can you do now?' mod='pe_abandonedcart'}</h3>
            <ul>
                <li><a href="{$urls.pages.index}">{l s='Continue shopping' mod='pe_abandonedcart'}</a></li>
                <li><a href="{$urls.pages.my_account}">{l s='View your account' mod='pe_abandonedcart'}</a></li>
                <li><a href="{$urls.pages.contact}">{l s='Contact us for assistance' mod='pe_abandonedcart'}</a></li>
            </ul>
        </div>
    </div>
{/block}
