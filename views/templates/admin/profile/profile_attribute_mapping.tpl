<div class="bootstrap" id="" style="">
 <div class="alert alert-info" id="">
        <span id="">Map all the Required newegg attributes with Prestashop attributes in order to prevent error at the time of product upload</span>
    </div>
</div>
<div class="panel row">
    <div class="panel-heading">
        <div class="row">
            <div class="col-sm-6 col-lg-6 col-md-6">
                {l s='Newegg Attribute' mod='cednewegg'}
            </div>
            <div class="col-sm-6 col-lg-6 col-md-6">
                {l s='Store Attributes' mod='cednewegg'}
            </div>
        </div>
    </div>
    <table class="table table-bordered">
            <thead>
                <tr>
                    <td>Newegg Attribute</td>
                    <td>Store Attributes</td>
                    <td> Default Value</td>
                </tr>
            </thead>
            <tbody id="attributes-newegg">
<tr class="dynamic-field" id="dynamic-field-default" style="display: none;">
        <td>                        
            <select id="" >
                <option value="">--select optional attribute--</option>
                <optgroup value="0" label="Optional Attributes">
                {if isset($optionalAttrs)}
                    {foreach $optionalAttrs as $key => $system_attribute}
                            <option value="{$system_attribute}">{$system_attribute|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}            
                {/if}       
                </optgroup>
            </select>
        </td>
        <td>
            <select id="" class="newegg-attr-select" onchange="selectDefault(this)">
                <option value="">--please select--</option>
                <option value="--Set Default Value--">--Set Default Value--</option>
                <optgroup value="0" label="System (Default)">
                    {foreach $storeDefaultAttributes as $key => $system_attribute}
                            <option value="{$system_attribute|escape:'htmlall':'UTF-8'}">{$system_attribute|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}                    
                </optgroup>
                <optgroup value="0" label="Attributes(Variants)">
                    {foreach $storeAttributes as $store_attribute}
                            <option value="{$store_attribute['id_attribute']|escape:'htmlall':'UTF-8'}">
                            {$store_attribute['name']|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </optgroup>
            </select>
        </td>
        
        <td><input type='hidden' ></td>
</tr>
            {if isset($profile_req_opt_attribute)}
                {$count = 0}
{foreach $profile_req_opt_attribute[0] as $k => $attr}
<tr>
        <td>
        <input type="text" readonly name="newegg_attributes[{$count}][name]" class="" value="{$attr['name']}">                        
        </td>
        <td>
            <select class="newegg-attr-select" name="newegg_attributes[{$count}][presta_attr_code]" onchange="selectDefault(this)">
                <option value="">--please select--</option>
                <option {if isset($attr['presta_attr_code'])&& $attr['presta_attr_code'] == '--Set Default Value--' }
                                    selected="selected"
                                {/if}
                                value="--Set Default Value--">--Set Default Value--</option>
                <optgroup value="0" label="System (Default)">
                    {foreach $storeDefaultAttributes as $key => $system_attribute}
                            {assign var='select_val' value="{'system-'}{$key}"}
                            <option value="system-{$key|escape:'htmlall':'UTF-8'}"
                            {if isset($attr['presta_attr_code'])&& $attr['presta_attr_code'] == $select_val }
                                    selected="selected"
                                {/if}
                            >{$system_attribute|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}                    
                </optgroup>
                <optgroup value="0" label="Attributes(Variants)">
                    {foreach $storeAttributes as $store_attribute}
                            {assign var='select_val' value="{'attribute-'}{$store_attribute['id_attribute']}"}
                            <option value="attribute-{$store_attribute['id_attribute']|escape:'htmlall':'UTF-8'}"
                            {if isset($attr['presta_attr_code'])&& $attr['presta_attr_code'] == $select_val }
                                    selected="selected"
                                {/if}
                            >{$store_attribute['name']|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </optgroup>
            </select>
        </td>
        <td><input style="" {if isset($attr['default']) && $attr['presta_attr_code'] == '--Set Default Value--' }
                            name="newegg_attributes[{$k}][default]" type="text" value='{$attr['default']}' {else} type='hidden'
                                {/if} value="" id="default_value_{$count}"></td>
</tr>
{$count=$count+1}
{/foreach}     
{/if}    

{$count=0}
{if isset($profile_req_opt_attribute)}
    {foreach $profile_req_opt_attribute[1] as $k => $attr}
    <tr class="dynamic-field-optional" id="dynamic-field-1">
        <td>                        
            <select name="newegg_opt_attributes[{$count}][name]" id="" >
                <option value="">--please select--</option>
                <optgroup value="0" label="Optional Attributes">
                {if isset($optionalAttrs)}
                    {foreach $optionalAttrs as $key => $system_attribute}
                            <option {if isset($attr['name'])&& $attr['name'] == $system_attribute }
                                    selected="selected"
                                {/if}  value="{$system_attribute}">{$system_attribute|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}            
                {/if}       
                </optgroup>
            </select>
        </td>
        <td>
            <select name="newegg_opt_attributes[{$count}][presta_attr_code]" id="" class="newegg-attr-select" onchange="selectDefault(this)">
                <option value="">--please select--</option>
                <option {if isset($attr['presta_attr_code'])&& $attr['presta_attr_code'] == '--Set Default Value--' }
                                    selected="selected"
                                {/if} value="--Set Default Value--">--Set Default Value--</option>
                <optgroup value="0" label="System (Default)">
                    {foreach $storeDefaultAttributes as $key => $system_attribute}
                            {assign var='select_val' value="{'system-'}{$key}"}
                            <option value="system-{$key|escape:'htmlall':'UTF-8'}" {if isset($attr['presta_attr_code'])&& $attr['presta_attr_code'] == select_val }
                                    selected="selected"
                                {/if}>{$system_attribute|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}                    
                </optgroup>
                <optgroup value="0" label="Attributes(Variants)">
                    {foreach $storeAttributes as $store_attribute}
                            {assign var='select_val' value="{'attribute-'}{$store_attribute['id_attribute']}"}
                            <option value="attribute-{$store_attribute['id_attribute']|escape:'htmlall':'UTF-8'}"
                            {if isset($attr['presta_attr_code'])&& $attr['presta_attr_code'] == $select_val }
                                    selected="selected"
                                {/if}>{$store_attribute['name']|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </optgroup>
            </select>
        </td>
        
        <td><input {if isset($attr['default']) && $attr['presta_attr_code'] == '--Set Default Value--' }
                                   type="text" name="newegg_opt_attributes[{$k}][default]" value='{$attr['default']}' {else} type='hidden'
                                {/if} value="" id="default_value_{$count}"></td>
        {$count= $count+1}
</tr>       
{/foreach}   
{/if}

            </tbody>
        </table>

      <button type="button" id="add-button" class="float-left"><i>Add Attribute</i>
      </button>
      <button type="button" id="remove-button" class="float-left"><i>Remove Attribute</i>
      </button>
</div>
 
