<form method="POST">
<div class="panel">
    <div class="panel-heading">
        {l s='CONFIGURATION' mod='cednewegg'}
    </div>
    <div class="panel-body">
      
        <label for="pricesetting">{l s='Newegg Price Setting(Product Price)' mod='cednewegg'}</label>
        <input type="text" name="pricesetting" id="pricesetting"  class="form-control" value="{$CEDNEWEGGPRICE_STR}"/>

         <label for="inventorysetting">{l s='Newegg Inventory Setting(Set Inventory on Basis of Threshold)' mod='cednewegg'}</label>
        <input type="text" name="inventorysetting" id="inventorysetting"  class="form-control" value="{$CEDNEWEGGINV_STR}"/>

        <label for="ordercronsetting">{l s='Newegg Order Cron' mod='cednewegg'}</label>
        <input type="text" name="ordercronsetting" id="ordercronsetting"  class="form-control" value="{$CEDNEWEGGORDERCRON_STR}"/>
        
        <label for="inventorycronsetting">{l s='Newegg Inventory Cron' mod='cednewegg'}</label>
        <input type="text" name="inventorycronsetting" id="inventorycronsetting"  class="form-control" value="{$CEDNEWEGGINVCRON_STR}"/>
      
    </div>
    <div class="panel-footer">
    <button type="submit" name="saveneweggconfig" class="btn btn-default pull-right">
        <i class = "process-icon-save"></i>
        {l s='Save' mod='cednewegg'}
    </button>
    </div>
</div>
</form> 