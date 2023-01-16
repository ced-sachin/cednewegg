<div class="panel">
    <div class="panel-heading">
        <i class="icon icon-user"></i> {l s='Profile Selector' mod='cednewegg'}
    </div>
    <div class="panel-body">
        <div class="form-group row">
            <div class="col-lg-2">
                <h4>
                    <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="Current Newegg Profile" data-original-title="">
                    {l s='Current Profile' mod='cednewegg'}
                    </span>
                </h4>

            </div>
            <div class="col-lg-10">
                <select name="profile_select" id="profile_select" onchange="changeNeweggProfile()" class="livesearch">
                    <option value=""> {l s='Select Newegg Profile' mod='cednewegg'}</option>
                    {if isset($allProfiles) && !empty($allProfiles)}
                        {foreach $allProfiles as $profile}
                         {assign var='profile_name' value="{$profile['profile_name']}{' | '}{$profile['account_code']}"} 
                            <option
                                    {if isset($idCurrentProfile) && !empty($idCurrentProfile) && $idCurrentProfile == $profile['id']}
                                        selected="selected"
                                    {/if}
                                    value="{$profile['id']|escape:'htmlall':'UTF-8'}">
                                {$profile_name|escape:'htmlall':'UTF-8'}
                            </option>
                        {/foreach}
                    {/if}
                </select>
            </div>
        </div>
        <input type="hidden" id="token_newegg" value='{$currentToken}' >
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.5.1/chosen.jquery.min.js"></script>
{* <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.5.1/chosen.min.css"> *}

<script>
    function changeNeweggProfile()
    {
        var url = '{$controllerUrl}';
        var tokenurl = url.split('&');
        token = tokenurl[tokenurl.length-1].split('=');
        token = token[0]+'={$token|escape:'htmlall'}';
        url = tokenurl[0]+'&'+token; 
        //alert(url);
        
        if($('#profile_select') && $('#profile_select').val())
            var x = location.hash;
        if(x){
            url = url.replace(x ,'');
            url = url + '&profile_select='+ $('#profile_select').val();
            url = url +x;
        } else {
            url = url + '&profile_select='+ $('#profile_select').val();
        }


        window.location = url;
    }
    $(document).ready(function () {
        var url = '{$controllerUrl}';

        var idCurrentProfile = '{$idCurrentProfile|escape:'htmlall'}';
        if (idCurrentProfile != 'all' && isNaN(idCurrentProfile)) {
            var ProfileId = $('#profile_select').val();

            var x = location.hash;
            if(x){
                url = url.replace(x ,'');
                url = url + '&profile_select='+ ProfileId;
                url = url +x;
            } else {
                url = url + '&profile_select='+ ProfileId;
            }
            window.location = url;
        }


    })
    window.onload = function () {
        $(".livesearch").chosen({
            'width' : "100%"
        });

    };
</script>
