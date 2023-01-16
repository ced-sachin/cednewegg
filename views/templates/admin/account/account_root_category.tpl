 <div class="row">
    <div class="col-sm-8 col-sm-offset-1">
        <div class="form-wrapper">
            <div class="form-group row">
                <label class="control-label col-lg-4 required">
                    {l s='Root Category' mod='cednewegg'}
                </label>
                <div class="col-lg-8">
                <select id="root_cat" name="root_cat[]" size="10" class=" required-entry _required select multiselect admin__control-multiselect" multiple="multiple" aria-required="true">
                        <option value="MI" id="" {if isset($root_cat)&& in_array("MI",$root_cat)}
                                    selected="selected"
                                {/if}>Musical Instruments</option>
                        <option value="CE" id="" {if isset($root_cat)&& in_array("CE",$root_cat)}
                                    selected="selected"
                                {/if}>Consumer Electronics</option>
                        <option value="OT" id=""{if isset($root_cat)&& in_array("OT",$root_cat)}
                                    selected="selected"
                                {/if}>Other</option>
                        <option value="BE" id=""{if isset($root_cat)&& in_array("BE",$root_cat)}
                                    selected="selected"
                                {/if}>Beauty</option>
                        <option value="SP" id=""{if isset($root_cat)&& in_array("SP",$root_cat)}
                                    selected="selected"
                                {/if}>Sports Goods</option>
                        <option value="PS" id=""{if isset($root_cat)&& in_array("PS",$root_cat)}
                                    selected="selected"
                                {/if}>Pet Supplies</option>
                        <option value="WG" id=""{if isset($root_cat)&& in_array("WG",$root_cat)}
                                    selected="selected"
                                {/if}>Food &amp; Gifts</option>
                        <option value="HE" id="" {if isset($root_cat)&& in_array("HE",$root_cat)}
                                    selected="selected"
                                {/if}>Health &amp; Personal care</option>
                        <option value="CH" id="" {if isset($root_cat)&& in_array("CH",$root_cat)}
                                    selected="selected"
                                {/if} >Computer Hardware</option>
                        <option value="AU" id="" {if isset($root_cat)&& in_array("AU",$root_cat)}
                                    selected="selected"
                                {/if}>Auto &amp; Hardware</option>
                        <option value="IN" id="" {if isset($root_cat)&& in_array("IN",$root_cat)}
                                    selected="selected"
                                {/if}>Industrial Supplies</option>
                        <option value="TY" id="" {if isset($root_cat)&& in_array("TY",$root_cat)}
                                    selected="selected"
                                {/if}>Toys Games &amp; Hobbies</option>
                        <option value="JW" id="" {if isset($root_cat)&& in_array("JW",$root_cat)}
                                    selected="selected"
                                {/if}>Jewelry</option>
                        <option value="AR" id="" {if isset($root_cat)&& in_array("AR",$root_cat)}
                                    selected="selected"
                                {/if}>Arts &amp; Crafts</option>
                        <option value="OD" id="" {if isset($root_cat)&& in_array("OD",$root_cat)}
                                    selected="selected"
                                {/if}>Outdoor &amp; Garden</option>
                        <option value="AC" id="" {if isset($root_cat)&& in_array("AC",$root_cat)}
                                    selected="selected"
                                {/if}>Accessories</option>
                        <option value="AL" id="" {if isset($root_cat)&& in_array("AL",$root_cat)}
                                    selected="selected"
                                {/if}>Appliance</option>
                        <option value="SW" id="" {if isset($root_cat)&& in_array("SW",$root_cat)}
                                    selected="selected"
                                {/if}>Software</option>
                        <option value="LU" id="" {if isset($root_cat)&& in_array("LU",$root_cat)}
                                    selected="selected"
                                {/if}>Bags &amp; Luggage</option>
                        <option value="DV" id="" {if isset($root_cat)&& in_array("DV",$root_cat)}
                                    selected="selected"
                                {/if}>DVD &amp; Videos</option>
                        <option value="BA" id="" {if isset($root_cat)&& in_array("BA",$root_cat)}
                                    selected="selected"
                                {/if}>Baby</option>
                        <option value="CP" id="" {if isset($root_cat)&& in_array("CP",$root_cat)}
                                    selected="selected"
                                {/if}>Camera &amp; Photo</option>
                        <option value="UC" id="" {if isset($root_cat)&& in_array("UC",$root_cat)}
                                    selected="selected"
                                {/if}>Unlocked Cell Phones</option>
                        <option value="CA" id="" {if isset($root_cat)&& in_array("CA",$root_cat)}
                                    selected="selected"
                                {/if}>Cell Phone Accessories</option>
                        <option value="BK" id="" {if isset($root_cat)&& in_array("BK",$root_cat)}
                                    selected="selected"
                                {/if}>Books, Media &amp; Entertainment</option>
                        <option value="VC" id="" {if isset($root_cat)&& in_array("VC",$root_cat)}
                                    selected="selected"
                                {/if}>Video Game Consoles</option>
                        <option value="HI" id="" {if isset($root_cat)&& in_array("HI",$root_cat)}
                                    selected="selected"
                                {/if}>Home Improvement</option>
                        <option value="OS" id="" {if isset($root_cat)&& in_array("OS",$root_cat)}
                                    selected="selected"
                                {/if}>Office Equipment &amp; Supplies</option>
                        <option value="HO" id="" {if isset($root_cat)&& in_array("HO",$root_cat)}
                                    selected="selected"
                                {/if}>Home &amp; Living</option>
                        <option value="BT" id="" {if isset($root_cat)&& in_array("BT",$root_cat)}
                                    selected="selected"
                                {/if}>Marine &amp; Aviation</option>
                        <option value="WA" id="" {if isset($root_cat)&& in_array("WA",$root_cat)}
                                    selected="selected"
                                {/if}>Watches</option>
                        <option value="AP" id="" {if isset($root_cat)&& in_array("AP",$root_cat)}
                                    selected="selected"
                                {/if}>Apparel</option>
                        <option value="MT" id="" {if isset($root_cat)&& in_array("MT",$root_cat)}
                                    selected="selected"
                                {/if}>Motorcycles &amp; Powersports</option>
                </select>
                </div>
            </div> 
            <div class="form-group row">
                <label class="control-label col-lg-4 required">
                    {l s='Warehouse Location' mod='cednewegg'}
                </label>
                <div class="col-lg-8">
                    <input type="text" name="warehouseLocation" {if isset($warehouseLocation)}
                        value="{$warehouseLocation|escape:'htmlall':'UTF-8'}" {else} value=""
                            {/if} class="" id="warehouse-location">
                </div>
            </div>
        </div>
    </div>
</div> 