<form method="post" id="newegg-profile-form">
<div class="panel">
        <div class="panel-heading">
            <i class="icon-tags"></i>
            {l s=' New profile' mod='cednewegg'}
            
        </div>
        <div class="panel-body">
            <div class="productTabs">
                <ul class="tab nav nav-tabs">
                    <li class="tab-row active">
                        <a class="tab-page" href="#profileInfo" data-toggle="tab">
                            <i class="icon-file-text"></i> {l s='Profile Info' mod='cednewegg'}
                        </a>
                    </li>
                    <li class="tab-row">
                        <a class="tab-page" href="#profileCategory" data-toggle="tab">
                            <i class="icon-wrench"></i> {l s='Category Mapping' mod='cednewegg'}
                        </a>
                    </li>
                    <li class="tab-row">
                        <a class="tab-page" href="#profileAttributes" data-toggle="tab">
                            <i class="icon-wrench"></i> {l s='Attribute Mapping' mod='cednewegg'}
                        </a>
                    </li>
                </ul>
            </div>
            <div class="tab-content">
                <div class="panel tab-pane fade in active row" id="profileInfo">
                                {include file="./profile_info.tpl"}
                </div>
                <div class="panel tab-pane" id="profileCategory">
                                {include file="./profile_category_mapping.tpl"}
                </div>
                <div class="panel tab-pane" id="profileAttributes">
                                {include file="./profile_attribute_mapping.tpl"}
                </div>
            </div>
            <input type="hidden" id="token-newegg" value={$currentToken} >
        </div>
        <div class="panel-footer">
            <button type="submit"  value="1" id="test_form_submit_btn"
                name="submitNeweggProfileSave" class="btn btn-default pull-right">
                <i class="process-icon-save"></i> {l s='Save' mod='cednewegg'}
            </button>
            <a class="btn btn-default" id="back-newegg-profile-controller">
                <i class="process-icon-cancel"></i> {l s='Cancel' mod='cednewegg'}
            </a>
        </div>
    </div>

</form>