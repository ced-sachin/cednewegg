<div class="panel row">
    <div class="panel-body">
        <div class="form-group row">
                <label class="control-label col-lg-4 required">
                    {l s='Account' mod='cednewegg'}
                </label>
                <div class="col-lg-8">
                     <select name="accountSelect" id="account-select">
                     <option value='--Select Account--'>--Select Account--</option>
                        {if isset($accounts)}
                            {foreach $accounts as $account}
                                <option  {if isset($account_id)&& $account_id==$account['id']}
                                    selected="selected"
                                {/if}value="{$account['id']}">{$account['account_code']} | id:{$account['id']}</option>
                            {/foreach}
                        {/if}
                      </select>
                </div>
        </div>
        <div class="form-group row">
                <label class="control-label col-lg-4 required">
                    {l s='Profile Category' mod='cednewegg'}
                </label>
                <div class="col-lg-8">
                     <select name="profileCategory" id="profile-category">
                        {if isset($profileCat)}
                            {foreach $profileCat as $cat}
                            {assign var='category' value="{$cat['sub_cat_Id']}{':'}{$cat['sub_cat_name']}"} 
                              <option value= '{$category}'
                              {if isset($profile_category)&& $profile_category == $category }
                                    selected="selected"
                                {/if}>{$cat['sub_cat_name']}</option>
                            {/foreach}
                        {/if}             
                     </select>
                </div>
        </div>
        
    </div>
</div>