<div class="row">
    <div class="col-sm-8 col-sm-offset-1">
        <div class="form-wrapper">
            <div class="form-group row">
                <input type="hidden" name="profileId">
            </div>
            <div class="form-group row">
                <label class="control-label col-lg-4 required">
                    {l s='Profile Name' mod='cednewegg'}
                </label>
                <div class="col-lg-8">
                    <input type="text" name="profileTitle" {if isset($profile_name) } value="{$profile_name}"{/if} class="" id="profile-title">
                </div>
            </div>

            <div class="form-group row">
                <label class="control-label col-lg-4">
                    {l s='Status' mod='cednewegg'}
                </label>
                <div class="col-lg-8">
            <span class="switch prestashop-switch fixed-width-lg">
                
				
                <input type="radio" name="profileStatus" id="active_on" value="1"  checked='checked'>
                <label for="active_on">{l s='Enable' mod='cednewegg'}</label>
                <input type="radio" name="profileStatus" id="active_off" value="0"  {if isset($profile_status) && $profile_status == '0' }
                    checked="checked"{/if}>
				<label for="active_off">{l s='Disable' mod='cednewegg'}</label>
                
				<a class="slide-button btn"></a>
		    </span>
                </div>
            </div>

            

        </div>
    </div>
    <div class="col-sm-1"></div>
</div>

