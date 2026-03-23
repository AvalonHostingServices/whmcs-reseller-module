<style>
    .cpanel-package-details {
        margin-bottom: 12px;
    }
    
    .cpanel-usage-stats {
        padding: 17px 15px;
    }
    
    .cpanel-usage-stats .limit-near {
        margin: 15px 0 5px;
        font-size: 0.8em;
    }
    
    .cpanel-feature-row {
        margin-top: 10px;
        margin-bottom: 10px;
    }
    
    .cpanel-feature-row img {
        display: block;
        margin: 0 auto 5px auto;
    }
    #cPanelWordPress .panel-body .row:not(:last-of-type):not(.no-margin) {
        padding-bottom: 20px;
        margin-bottom: 20px;
        border-bottom: 1px solid #ddd;
    }
    .cpanel-feature-row {
        margin-top: 10px;
        margin-bottom: 10px;
    }
    
    .shortcut-icon {
        display: block;
        margin-bottom: 2px;
    }



</style>

<div class="tab-content margin-bottom">
    <div class="tab-pane fade show active" role="tabpanel" id="tabOverview">
        <div class="row">
            <!-- Package/Domain -->
            <div class="col-md-6">
                <div class="panel panel-default card mb-3" id="productPackagePanel">
                    <div class="panel-heading card-header">
                        <h3 class="panel-title card-title m-0">Package/Domain</h3>
                    </div>
                    <div class="panel-body card-body text-center">
                        <div class="product-package-details">
                            <em>{$product->name|default:'Product'}</em>
                            <h4 style="margin:0;">{$product->name|default:'Service'}</h4>
                            <a href="http://{$service->domain}" target="_blank">{$service->domain}</a>
                        </div>
                        <p>
                            <a href="http://{$service->domain}" class="btn btn-default btn-sm" target="_blank">
                                Visit Website
                            </a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Empty right column (if needed for something later) -->
            <div class="col-md-6">
                <!-- Usage Statistics -->
                <div class="panel card panel-default mb-3" id="cPanelUsagePanel">
                    <div class="panel-heading card-header">
                        <h3 class="panel-title card-title m-0">Usage Statistics</h3>
                    </div>
                    <div class="panel-body card-body text-center cpanel-usage-stats">
                        <div class="row">
                            <!-- Disk Usage -->
                            <div class="col-md-6 text-center" id="diskUsage">
                                <strong>Disk Usage</strong>
                                <br><br>
                                <input type="text"
                                       value="{$usage.disk_percent}"
                                       class="usage-dial"
                                       data-fgcolor="#444"
                                       data-angleoffset="-125"
                                       data-anglearc="250"
                                       data-min="0"
                                       data-max="100"
                                       data-readonly="true"
                                       data-width="100"
                                       data-height="80" style="width: 54px; height: 33px; position: absolute; vertical-align: middle; margin-top: 33px; margin-left: -77px; border: 0px; background: none; font: bold 20px Arial; text-align: center; color: rgb(68, 68, 68); padding: 0px; appearance: none;"/>
                            
                                <br>
                                {$usage.disk_used} M / {$usage.disk_limit}
                            </div>
                            
                            <!-- Bandwidth Usage -->
                            <div class="col-md-6 text-center" id="bandwidthUsage">
                                <strong>Bandwidth Usage</strong>
                                <br><br>
                                <input type="text"
                                       value="{$usage.bw_percent}"
                                       class="usage-dial"
                                       data-fgcolor="#d9534f"
                                       data-angleoffset="-125"
                                       data-anglearc="250"
                                       data-min="0"
                                       data-max="100"
                                       data-readonly="true"
                                       data-width="100"
                                       data-height="80" style="width: 54px; height: 33px; position: absolute; vertical-align: middle; margin-top: 33px; margin-left: -77px; border: 0px; background: none; font: bold 20px Arial; text-align: center; color: rgb(217, 83, 79); padding: 0px; appearance: none;"/>
                            
                                <br>
                                {$usage.bw_used} M / {$usage.bw_limit}
                            </div>

                        </div>
                
                        <div class="text-info limit-near">
                            Last Updated {$usage.last_updated}
                        </div>
                
                        <script src="{$BASE_PATH_JS}/jquery.knob.js"></script>
                        <script type="text/javascript">
                            jQuery(function($) {
                                $(".usage-dial").knob({
                                    'format': function (value) {
                                        return value + '%';  // shows percentage inside knob
                                    }
                                });
                            });
                        </script>

                    </div>
                </div>

            </div>
        </div>

        <!-- Quick Shortcuts -->
        <div class="panel card panel-default mb-3" id="productQuickShortcutsPanel">
            <div class="panel-heading card-header">
                <h3 class="panel-title card-title m-0">Quick Shortcuts</h3>
            </div>
            <div class="panel-body card-body text-center">
                <div class="row cpanel-feature-row">
                    {foreach $shortcuts as $shortcut}
                        <div class="col-md-3 col-sm-4 col-xs-6 col-6">
                            <a href="clientarea.php?action=productdetails&id={$service->id}&dosinglesignon=1&app={$shortcut.app}" target="_blank" rel="noopener noreferrer" class="d-block mb-3">
                                <img src="{$shortcut.icon}" width="48" height="48" alt="{$shortcut.label}" class="shortcut-icon">
                                {$shortcut.label}
                            </a>
                        </div>
                    {/foreach}
                </div>
            </div>
        </div>

        <!-- Billing Overview -->
        <div class="panel card panel-default mb-3" id="productBillingPanel">
            <div class="panel-heading card-header">
                <h3 class="panel-title card-title m-0">Billing Overview</h3>
            </div>
            <div class="panel-body card-body">
                <div class="row">
                    <div class="col-md-5">
                        <div class="row" id="recurringAmount">
                            <div class="col-xs-6 col-6 text-right">Recurring Amount</div>
                            <div class="col-xs-6 col-6">
                                ${$service->amount|number_format:2}
                            </div>
                        </div>
                        <div class="row" id="billingCycle">
                            <div class="col-xs-6 col-6 text-right">Billing Cycle</div>
                            <div class="col-xs-6 col-6">
                                {$service->billingcycle|capitalize}
                            </div>
                        </div>
                        <div class="row" id="paymentMethod">
                            <div class="col-xs-6 col-6 text-right">Payment Method</div>
                            <div class="col-xs-6 col-6">
                                {$service->paymentmethod|default:'N/A'}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="row" id="registrationDate">
                            <div class="col-xs-6 col-6 col-xl-5 text-right">Registration Date</div>
                            <div class="col-xs-6 col-6 col-xl-7">
                                {$service->regdate|date_format:"%A, %B %e, %Y"}
                            </div>
                        </div>
                        <div class="row" id="nextDueDate">
                            <div class="col-xs-6 col-6 col-xl-5 text-right">Next Due Date</div>
                            <div class="col-xs-6 col-6 col-xl-7">
                                {$service->nextduedate|date_format:"%A, %B %e, %Y"}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
