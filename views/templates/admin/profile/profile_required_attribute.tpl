{*<tr class="dynamic-field" id="dynamic-field-default" style="display: none;">
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
        
        <td><input type='hidden' id="default_value_{$count}"></td>
</tr>*}
{$count = 0}
{foreach $requiredAttributes as $attr}
<tr>
        <td>
        <input type="text" name="newegg_attributes[{$count}][name]" readonly class="" value="{$attr}">                        
        </td>
        <td>
            <select class="newegg-attr-select" name="newegg_attributes[{$count}][presta_attr_code]" onchange="selectDefault(this)">
                <option value="">--please select--</option>
                <option value="--Set Default Value--">--Set Default Value--</option>
                <optgroup value="0" label="System (Default)">
                    {foreach $storeDefaultAttributes as $key => $system_attribute}
                            <option value="{$system_attribute|escape:'htmlall':'UTF-8'}">{$system_attribute|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}                    
                </optgroup>
                <optgroup value="0" label="Attributes(Variants)">
                    {foreach $storeAttributes as $store_attribute}
                            <option value="{$store_attribute['id_attribute']|escape:'htmlall':'UTF-8'}">{$store_attribute['name']|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </optgroup>
            </select>
        </td>
        <td><input style="" type="hidden" name="" value="" id="default_value_{$count}"></td>
</tr>
{$count=$count+1}
{/foreach}
{if isset($requiredAttrs)}
    {foreach $requiredAttrs $req_attr}
    <tr>
        <td>           
        <input type="text" readonly name="newegg_attributes[{$count}][name]" class="req_attr" value="{$req_attr}">               
        </td>
        <td>
            <select name="newegg_attributes[{$count}][presta_attr_code]" class="newegg-attr-select" onchange="selectDefault(this)">
                <option value="">--please select--</option>
                <option value="--Set Default Value--">--Set Default Value--</option>
                <optgroup value="0" label="System (Default)">
                    {foreach $storeDefaultAttributes as $key => $system_attribute}
                            <option value="{$system_attribute|escape:'htmlall':'UTF-8'}">{$system_attribute|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}                    
                </optgroup>
                <optgroup value="0" label="Attributes(Variants)">
                    {foreach $storeAttributes as $store_attribute}
                            <option value="{$store_attribute['id_attribute']|escape:'htmlall':'UTF-8'}">{$store_attribute['name']|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </optgroup>
            </select>
        </td>
        <td><input style="" type="hidden" name="" value="" id="default_value_{$count}"></td>
</tr>   {$count = $count+1}                    
    {/foreach}
{/if}
{$count=0}
{if isset($optionalAttrs)}
    <tr class="dynamic-field" id="dynamic-field-1">
        <td>                        
            <select name="newegg_opt_attributes[{$count}][name]" id="" >
                <optgroup value="0" label="Optional Attributes">
                    {foreach $optionalAttrs as $key => $system_attribute}
                            <option value="{$system_attribute|escape:'htmlall':'UTF-8'}">{$system_attribute|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}                    
                </optgroup>
            </select>
        </td>
        <td>
            <select name="newegg_opt_attributes[{$count}][presta_attr_code]" id="" class="newegg-attr-select" onchange="selectDefault(this)">
                <option value="">--please select--</option>
                <option value="--Set Default Value--">--Set Default Value--</option>
                <optgroup value="0" label="System (Default)">
                    {foreach $storeDefaultAttributes as $key => $system_attribute}
                            <option value="{$system_attribute|escape:'htmlall':'UTF-8'}">{$system_attribute|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}                    
                </optgroup>
                <optgroup value="0" label="Attributes(Variants)">
                    {foreach $storeAttributes as $store_attribute}
                            <option value="{$store_attribute['id_attribute']|escape:'htmlall':'UTF-8'}">{$store_attribute['name']|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </optgroup>
            </select>
        </td>
        
        <td><input style="" type="hidden" name="" value="" id="default_value_{$count}"></td>
        {$count= $count+1}
</tr> 
         
{/if}

 

