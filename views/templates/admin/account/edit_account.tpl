<div class="bootstrap" id="error-message" style="display: none;">
    <div class="alert alert-danger" id="error-text">
        <button type="button" class="close" onclick="closeMessage()">×</button>
        <span id="default-error-message-text">Error</span>
    </div>
</div>

<form method="post" id="newegg-account-form">
<div class="panel">
        <div class="panel-heading">
            <i class="icon-tags"></i>
            {l s=' New Account' mod='cednewegg'}
            
        </div>
        <div class="panel-body">
            <div class="productTabs">
                <ul class="tab nav nav-tabs">
                    <li class="tab-row active">
                        <a class="tab-page" href="#accountInfo" data-toggle="tab">
                            <i class="icon-file-text"></i> {l s='Account Information' mod='cednewegg'}
                        </a>
                    </li>
                    <li class="tab-row">
                        <a class="tab-page" href="#rootCategory" data-toggle="tab">
                            <i class="icon-wrench"></i> {l s='Root Category' mod='cednewegg'}
                        </a>
                    </li>
                </ul>
            </div>
            <div class="tab-content">
                <div class="panel tab-pane fade in active row" id="accountInfo">
                                {include file="./account_info.tpl"} 
                </div>
                <div class="panel tab-pane" id="rootCategory">
                                {include file="./account_root_category.tpl"}
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <button type="submit"  value="1" id="test_form_submit_btn"
                name="submitNeweggAccountSave" class="btn btn-default pull-right">
                <i class="process-icon-save"></i> {l s='Save' mod='cednewegg'}
            </button>
            <a class="btn btn-default" id="back-newegg-profile-controller">
                <i class="process-icon-cancel"></i> {l s='Cancel' mod='cednewegg'}
            </a>
        </div>
    </div>
</form>