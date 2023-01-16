<div class="row">
    <div class="col-sm-8 col-sm-offset-1">
        <div class="form-wrapper">
            <div class="form-group row">
                <input type="hidden" name="accountId">
            </div>
            <div class="form-group row">
                <label class="control-label col-lg-4 required">
                    {l s='Account Code' mod='cednewegg'}
                </label>
                <div class="col-lg-8">
                    <input type="text" name="accountCode" class="" {if isset($accountCode)}
                        value="{$accountCode|escape:'htmlall':'UTF-8'}" disabled {else} value=""
                            {/if} id="account-code" >
                </div>
            </div>
            <div class="form-group row">
                <label class="control-label col-lg-4 required">
                    {l s='Seller Id' mod='cednewegg'}
                </label>
                <div class="col-lg-8">
                    <input type="text" name="sellerId" class="" {if isset($sellerId)}
                        value="{$sellerId|escape:'htmlall':'UTF-8'}" {else} value=""
                            {/if} id="seller-id">
                </div>
            </div>
            <div class="form-group row">
                <label class="control-label col-lg-4 required">
                    {l s='Secret Key' mod='cednewegg'}
                </label>
                <div class="col-lg-8">
                    <input type="text" name="secretKey" {if isset($secretKey)}
                        value="{$secretKey|escape:'htmlall':'UTF-8'}" {else} value=""
                            {/if} class="" id="secret-key">
                </div>
            </div>
            <div class="form-group row">
                <label class="control-label col-lg-4 required">
                    {l s='Authorization Key' mod='cednewegg'}
                </label>
                <div class="col-lg-8">
                    <input type="text" name="authorizationKey" {if isset($authorizationKey)}
                        value="{$authorizationKey|escape:'htmlall':'UTF-8'}" {else} value=""
                            {/if} class="" id="authorization-key">
                </div>
            </div>
            <div class="form-group row">
                <label class="control-label col-lg-4">
                    {l s='Status' mod='cednewegg'}
                </label>
                <div class="col-lg-8">
            <span class="switch prestashop-switch fixed-width-lg">
            
                <input type="radio" name="accountStatus" id="active_on" value="1" checked="checked">
                <label for="active_on">{l s='Enable' mod='cednewegg'}</label>
				<input type="radio" name="accountStatus" id="active_off" value="0" {if isset($accountStatus) && $accountStatus == '0'}
                    checked="checked"{/if} >
				<label for="active_off">{l s='Disable' mod='cednewegg'}</label>
				<a class="slide-button btn"></a>
		    </span>
                </div>
            </div>
            <div class="form-group row">
                <label class="control-label col-lg-4 required">
                    {l s='Account Location' mod='cednewegg'}
                </label>
                <div class="col-lg-8">
                     <select name="accountLocation" id="account-location">
                            <option selected="selected" value="US">US</option>
                            <option value="CAN">CAN</option>
                      </select>
                </div>
            </div>       
        </div>
    </div>
    <div class="col-sm-1"></div>
</div>