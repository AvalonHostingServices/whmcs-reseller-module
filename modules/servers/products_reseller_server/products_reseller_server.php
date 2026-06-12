<?php
 
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}
 
use WHMCS\Database\Capsule;
use WHMCS\Module\Server\CustomAction;
use WHMCS\Module\Server\CustomActionCollection;
 
require_once __DIR__ . '/pr_server_classes.php';
 
function products_reseller_server_MetaData() {
    return array(
        'DisplayName' => 'Products Reseller for WHMCS',
        'APIVersion' => '1.0',
        'RequiresServer' => true,
        'ServiceSingleSignOnLabel' => "Login to cPanel",
        'AdminSingleSignOnLabel'   => "Login to cPanel",
    );
}
 
$hooks = Capsule::table('tblconfiguration')
    ->where('setting', 'ModuleHooks')
    ->value('value');
 
$hooksList = array_filter(explode(',', (string) $hooks));
 
if (!in_array('products_reseller_server', $hooksList)) {
    $hooksList[] = 'products_reseller_server';
    Capsule::table('tblconfiguration')->updateOrInsert(
        ['setting' => 'ModuleHooks'],
        ['value' => implode(',', $hooksList)]
    );
}
 
function products_reseller_server_TestConnection($params) {
    
    $server_id = Capsule::table('tblservers')->where('type', 'products_reseller_server')->where('active', 1)->value('id');
    $main_class = new ProductsReseller_Main();
 
    $all_products = $main_class->send_request_to_api([
        'action' => 'Get_Products',
        'server_id' => $server_id
    ]);
    
    return [
        'success' => ((is_array($all_products['data']) && $all_products['status'] == 'success') ? true : false),
        'error' => $all_products['message'],
    ];
}
 
function products_reseller_server_ConfigOptions($params) {
    
    $server_id = Capsule::table('tblservers')->where('type', 'products_reseller_server')->where('active', 1)->value('id');
    
    $main_class = new ProductsReseller_Main();
 
    $all_products = $main_class->send_request_to_api([
        'action' => 'Get_Products',
        'server_id' => $server_id
    ]);
 
    $products = ['0' => 'Select Product'];
 
    if (isset($all_products['data']) && is_array($all_products['data'])) {
        foreach ($all_products['data'] as $product) {
            $products[$product['product_id']] = $product['product_name'];
        }
    }
 
    return [
        "products" => [
            "FriendlyName" => "Assigned Products",
            "Type" => "dropdown",
            "Options" => $products,
            "Description" => "Choose Product",
            "Default" => "0",
        ],
        "show_server_name" => [
            "FriendlyName" => "Show Server Name",
            "Type" => "yesno",
            "Description" => "Enable to display the Server Name in Product Details Page",
            "Default" => "on",
        ],
    
        "show_host_name" => [
            "FriendlyName" => "Show Host Name",
            "Type" => "yesno",
            "Description" => "Enable to display the Host Name in Product Details Page",
            "Default" => "on",
        ],
    
        "show_domain" => [
            "FriendlyName" => "Show Domain",
            "Type" => "yesno",
            "Description" => "Enable to display the Domain in Product Details Page",
            "Default" => "on",
        ],
    
        "show_ip" => [
            "FriendlyName" => "Show IP Address",
            "Type" => "yesno",
            "Description" => "Enable to display the IP Address in Product Details Page",
            "Default" => "on",
        ],
    
        "show_username" => [
            "FriendlyName" => "Show Username",
            "Type" => "yesno",
            "Description" => "Enable to display the Username in Product Details Page",
            "Default" => "on",
        ],
    
        "show_password" => [
            "FriendlyName" => "Show Password",
            "Type" => "yesno",
            "Description" => "Enable to display the Password in Product Details Page",
            "Default" => "on",
        ],
        
    ];
}

function prs_getServiceCustomFields($serviceId)
{
    $service = Capsule::table('tblhosting')->where('id', $serviceId)->first();
    if (!$service) return [];
 
    $fields = Capsule::table('tblcustomfields')
        ->where('type', 'product')
        ->where('relid', $service->packageid)
        ->get();
 
    $result = [];
    foreach ($fields as $field) {
        $value = Capsule::table('tblcustomfieldsvalues')
            ->where('relid', $serviceId)
            ->where('fieldid', $field->id)
            ->value('value');
 
        $result[$field->fieldname] = $value ?? '';
    }
 
    return $result;
}

function prs_getServiceConfigOptions($serviceId)
{
    $rows = Capsule::table('tblhostingconfigoptions')
        ->where('relid', $serviceId)
        ->get();

    $result = [];
    foreach ($rows as $row) {
        $option = Capsule::table('tblproductconfigoptions')
            ->where('id', $row->configid)
            ->first();

        if (!$option) continue;

        $optionName = $option->optionname;
        $optiontype = (int)$option->optiontype;

        if ($optiontype === 4) {
            // Quantity type — the selected value is stored in qty column
            $result[$optionName] = [
                'type' => 'quantity',
                'qty'  => (int)($row->qty ?? 0),
            ];
        } else {
            $subOptionName = Capsule::table('tblproductconfigoptionssub')
                ->where('id', $row->optionid)
                ->value('optionname');

            $result[$optionName] = [
                'type'  => 'option',
                'value' => $subOptionName ?? '',
            ];
        }
    }

    return $result;
}
 
function products_reseller_server_CreateAccount($params) {
    
    $billingCycle = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('billingcycle');
    $qty      = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('qty');
    $domain   = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('domain');
    $username = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('username');
    $ip       = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('dedicatedip');
 
    $customFields  = prs_getServiceCustomFields($params['serviceid']);
    $configOptions = prs_getServiceConfigOptions($params['serviceid']);
    
    $main_class = new ProductsReseller_Main();
    
    return $main_class->create_account([
        'serviceid'        => $params['serviceid'],
        'billingcycle'     => $billingCycle,
        'qty'              => $qty,
        'domain'           => $domain,
        'username'         => $username,
        'password'         => $params['password'],
        'ip_address'       => $ip,
        'selected_product' => $params['configoption1'],
        'serverhostname' => $params['serverhostname'],
        'serverusername' => $params['serverusername'],
        'serverpassword' => $params['serverpassword'],
        'action'           => 'CreateAccount',
        'custom_fields'    => $customFields,
        'config_options'   => $configOptions,
    ]);
}
 
function products_reseller_server_SuspendAccount($params) {
    $domain   = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('domain');
    $username = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('username');
    $ip       = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('dedicatedip');
 
    $customFields  = prs_getServiceCustomFields($params['serviceid']);
    $configOptions = prs_getServiceConfigOptions($params['serviceid']);
 
    $main_class = new ProductsReseller_Main();
    
    return $main_class->suspend_account([
        'serviceid'      => $params['serviceid'],
        'serverhostname' => $params['serverhostname'],
        'serverusername' => $params['serverusername'],
        'serverpassword' => $params['serverpassword'],
        'suspendreason'  => $params['suspendreason'],
        'action'         => 'SuspendAccount',
        'domain'         => $domain,
        'username'       => $username,
        'password'       => $params['password'],
        'ip_address'     => $ip,
        'custom_fields'  => $customFields,
        'config_options' => $configOptions,
    ]);
}
 
function products_reseller_server_UnsuspendAccount($params) {
    $domain   = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('domain');
    $username = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('username');
    $ip       = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('dedicatedip');
 
    $customFields  = prs_getServiceCustomFields($params['serviceid']);
    $configOptions = prs_getServiceConfigOptions($params['serviceid']);
    
    $main_class = new ProductsReseller_Main();
    
    return $main_class->unsuspend_account([
        'serviceid'      => $params['serviceid'],
        'serverhostname' => $params['serverhostname'],
        'serverusername' => $params['serverusername'],
        'serverpassword' => $params['serverpassword'],
        'action'         => 'UnsuspendAccount',
        'domain'         => $domain,
        'username'       => $username,
        'password'       => $params['password'],
        'ip_address'     => $ip,
        'custom_fields'  => $customFields,
        'config_options' => $configOptions,
    ]);
}
 
function products_reseller_server_TerminateAccount($params) {
    $domain   = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('domain');
    $username = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('username');
    $ip       = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('dedicatedip');
 
    $customFields  = prs_getServiceCustomFields($params['serviceid']);
    $configOptions = prs_getServiceConfigOptions($params['serviceid']);
    
    $main_class = new ProductsReseller_Main();
    
    return $main_class->terminate_account([
        'serviceid'      => $params['serviceid'],
        'serverhostname' => $params['serverhostname'],
        'serverusername' => $params['serverusername'],
        'serverpassword' => $params['serverpassword'],
        'action'         => 'TerminateAccount',
        'domain'         => $domain,
        'username'       => $username,
        'password'       => $params['password'],
        'ip_address'     => $ip,
        'custom_fields'  => $customFields,
        'config_options' => $configOptions,
    ]);
}
 
function products_reseller_server_ChangePackage($params) {
    $domain   = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('domain');
    $username = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('username');
    $ip       = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('dedicatedip');
 
    $customFields  = prs_getServiceCustomFields($params['serviceid']);
    $configOptions = prs_getServiceConfigOptions($params['serviceid']);
    
    $main_class = new ProductsReseller_Main();
    
    return $main_class->change_package([
        'serviceid'      => $params['serviceid'],
        'serverhostname' => $params['serverhostname'],
        'serverusername' => $params['serverusername'],
        'serverpassword' => $params['serverpassword'],
        'action'         => 'ChangePackage',
        'domain'         => $domain,
        'username'       => $username,
        'password'       => $params['password'],
        'ip_address'     => $ip,
        'custom_fields'  => $customFields,
        'config_options' => $configOptions,
    ]);
}
 
 
function products_reseller_server_ChangePassword($params) {
    $domain   = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('domain');
    $username = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('username');
    $ip       = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('dedicatedip');
 
    $customFields  = prs_getServiceCustomFields($params['serviceid']);
    $configOptions = prs_getServiceConfigOptions($params['serviceid']);
    
    $main_class = new ProductsReseller_Main();
    
    return $main_class->change_password([
        'serviceid'      => $params['serviceid'],
        'serverhostname' => $params['serverhostname'],
        'serverusername' => $params['serverusername'],
        'serverpassword' => $params['serverpassword'],
        'password'       => $params['password'],
        'action'         => 'ChangePassword',
        'domain'         => $domain,
        'username'       => $username,
        'ip_address'     => $ip,
        'custom_fields'  => $customFields,
        'config_options' => $configOptions,
    ]);
}

function products_reseller_server_UsageUpdate($params) {
    $services = Capsule::table('tblhosting')
        ->where('server', $params['serverid'])
        ->get(['id']);

    if ($services->isEmpty()) {
        return;
    }

    $serviceIds = [];
    foreach ($services as $svc) {
        $serviceIds[] = $svc->id;
    }

    $main_class = new ProductsReseller_Main();
    $res = $main_class->get_usage_update([
        'serverhostname' => $params['serverhostname'],
        'serverusername' => $params['serverusername'],
        'serverpassword' => $params['serverpassword'],
        'action'         => 'GetUsageData',
        'service_ids'    => $serviceIds,
    ]);

    if (empty($res['status']) || $res['status'] !== 'success' || empty($res['data'])) {
        return;
    }

    foreach ($res['data'] as $item) {
        $remoteServiceId = (int)($item['remote_service_id'] ?? 0);
        if ($remoteServiceId <= 0) continue;
        Capsule::table('tblhosting')->where('id', $remoteServiceId)->update([
            'diskusage'   => $item['diskusage'],
            'disklimit'   => $item['disklimit'],
            'bwusage'     => $item['bwusage'],
            'bwlimit'     => $item['bwlimit'],
            'lastupdate' => $item['lastupdate'],
        ]);
    }

}

function products_reseller_server_ServiceSingleSignOn($params) {
    try {
        $serviceId = $params['serviceid'] ?? null;

        $app = $params['app'] ?? ($_GET['app'] ?? 'Home'); // prefer $params, fallback to $_GET

        $mainClass = new ProductsReseller_Main();

        $ssoResponse = $mainClass->get_cpanel_sso([
            'serviceid' => $serviceId,
            'serverhostname' => $params['serverhostname'],
            'serverusername' => $params['serverusername'],
            'serverpassword' => $params['serverpassword'],
            'action'    => 'CreateSSOSession',
            'app'       => $app,
        ]);

        if (!empty($ssoResponse['url'])) {
            return [
                'success' => true,
                'redirectTo' => $ssoResponse['url'],
            ];
        }

        return [
            'success' => false,
            'errorMsg' => 'Unable to generate cPanel SSO link',
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'errorMsg' => 'Exception: ' . $e->getMessage(),
        ];
    }
}


function products_reseller_server_CustomActions($params): CustomActionCollection {
    $collection = new CustomActionCollection();

    $serverId = $params['serverid'] ?? $params['server'] ?? null;
    if (empty($serverId)) {
        return $collection;
    }

    $serverType = Capsule::table('tblservers')->where('id', $serverId)->value('type');
    if ($serverType !== 'products_reseller_server') {
        return $collection;
    }

    try {
        $main = new ProductsReseller_Main();
        $res = $main->get_server_name([
            'serviceid' => $params['serviceid'] ?? null,
            'serverhostname' => $params['serverhostname'],
            'serverusername' => $params['serverusername'],
            'serverpassword' => $params['serverpassword'],
            'action'    => 'GetServerName',
        ]);

        if (empty($res['server_name']) || strtolower($res['server_name']) !== 'cpanel') {
            return $collection;
        }
    } catch (\Throwable $e) {
        
        return $collection;
    }

    $collection->add(
        CustomAction::factory(
            'products_reseller_server',                         
            'Log in to cPanel',                                 
            'products_reseller_server_ServiceSingleSignOn',     
            [$params],                                          
            ['productsso'],                                     
            true                                                
        )
    );

    return $collection;
}

function products_reseller_server_ClientArea($params) {
    try {
        $service = Capsule::table('tblhosting')
            ->where('id', $params['serviceid'])
            ->first();

        if (!$service) {
            return 'Service not found.';
        }

        $product = Capsule::table('tblproducts')
            ->where('id', $service->packageid)
            ->first();

        $main_class = new ProductsReseller_Main();

        $res = $main_class->get_server_name([
            'serviceid' => $params['serviceid'],
            'serverhostname' => $params['serverhostname'],
            'serverusername' => $params['serverusername'],
            'serverpassword' => $params['serverpassword'],
            'action'    => 'GetServerName'
        ]);
        
        $status = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('domainstatus');
        
        if($status == 'Active') {
            if (!empty($res['server_name']) && strtolower($res['server_name']) === 'cpanel') {
            
                $diskUsed    = (float)($service->diskusage ?? 0);
                $diskLimit   = (float)($service->disklimit ?? 0);
                $bwUsed      = (float)($service->bwusage ?? 0);
                $bwLimit     = (float)($service->bwlimit ?? 0);
                $lastUpdated = $service->lastupdated ?? date('Y-m-d H:i:s');
                $diskPercent = ($diskLimit > 0) ? round(($diskUsed / $diskLimit) * 100, 2) : 0;
                $bwPercent   = ($bwLimit > 0) ? round(($bwUsed / $bwLimit) * 100, 2) : 0;

                return [
                    'tabOverviewReplacementTemplate' => 'cpanel.tpl',
                    'vars' => [
                        'service'  => $service,
                        'product'  => $product,
                        'shortcuts' => [
                            [
                                'label' => 'Email Accounts',
                                'app'   => 'Email_Accounts',
                                'icon'  => 'modules/servers/products_reseller_server/images/cpanel/email_accounts.png',
                            ],
                            [
                                'label' => 'Forwarders',
                                'app'   => 'Email_Forwarders',
                                'icon'  => 'modules/servers/products_reseller_server/images/cpanel/forwarders.png',
                            ],
                            [
                                'label' => 'Autoresponders',
                                'app'   => 'Email_Autoresponders',
                                'icon'  => 'modules/servers/products_reseller_server/images/cpanel/autoresponders.png',
                            ],
                            [
                                'label' => 'File Manager',
                                'app'   => 'FileManager_Home',
                                'icon'  => 'modules/servers/products_reseller_server/images/cpanel/file_manager.png',
                            ],
                            [
                                'label' => 'Backup',
                                'app'   => 'Backups_Home',
                                'icon'  => 'modules/servers/products_reseller_server/images/cpanel/backup.png',
                            ],
                            [
                                'label' => 'MySQL Databases',
                                'app'   => 'Database_MySQL',
                                'icon'  => 'modules/servers/products_reseller_server/images/cpanel/mysql_databases.png',
                            ],
                            [
                                'label' => 'phpMyAdmin',
                                'app'   => 'Database_phpMyAdmin',
                                'icon'  => 'modules/servers/products_reseller_server/images/cpanel/php_my_admin.png',
                            ],
                            [
                                'label' => 'Domains',
                                'app'   => 'Domains_domains',
                                'icon'  => 'modules/servers/products_reseller_server/images/cpanel/addon_domains.png',
                            ],
                            [
                                'label' => 'Cron Jobs',
                                'app'   => 'Cron_Home',
                                'icon'  => 'modules/servers/products_reseller_server/images/cpanel/cron_jobs.png',
                            ],
                            [
                                'label' => 'Awstats',
                                'app'   => 'Awstats',
                                'icon'  => 'modules/servers/products_reseller_server/images/cpanel/awstats.png',
                            ],
                        ],
                        'usage'     => [
                            'disk_used'       => $diskUsed,
                            'disk_limit'      => $diskLimit == 0 ? 'Unlimited M' : $diskLimit . ' M',
                            'disk_percent'    => $diskPercent,
                            'bw_used'         => $bwUsed,
                            'bw_limit'        => $bwLimit == 0 ? 'Unlimited M' : $bwLimit . ' M',
                            'bw_percent'      => $bwPercent,
                            'last_updated'    => $lastUpdated,
                        ]
                    ],
                ];
    
            } else {
                $is_server_name = $params['configoption2'] === 'on';
                $is_host_name   = $params['configoption3'] === 'on';
                $is_domain      = $params['configoption4'] === 'on';
                $is_ip          = $params['configoption5'] === 'on';
                $is_username    = $params['configoption6'] === 'on';
                $is_password    = $params['configoption7'] === 'on';
                
                $server = Capsule::table('tblservers')
                    ->where('id', $params['serverid'])
                    ->first();
                
                $domainName = $service->domain ?? ($params['domain'] ?? '');
                $usernameVal = $service->username ?? ($params['username'] ?? 'N/A');
                
                $output = '<div class="product-server-details">';
                
                // Server Name
                if ($is_server_name) {
                    $output .= '<div class="row">';
                    $output .= '<div class="col-sm-5 text-right"><strong>Server Name</strong></div>';
                    $output .= '<div class="col-sm-7 text-left">' . ($server->name ?? 'N/A') . '</div>';
                    $output .= '</div>';
                }
                
                // Host Name
                if ($is_host_name) {
                    $output .= '<div class="row">';
                    $output .= '<div class="col-sm-5 text-right"><strong>Host Name</strong></div>';
                    $output .= '<div class="col-sm-7 text-left">' . ($server->hostname ?? 'N/A') . '</div>';
                    $output .= '</div>';
                }
                
                // Domain
                if ($is_domain) {
                    $output .= '<div class="row">';
                    $output .= '<div class="col-sm-5 text-right"><strong>Domain</strong></div>';
                    $output .= '<div class="col-sm-7 text-left">' . ($domainName ?: 'N/A') . '</div>';
                    $output .= '</div>';
                }
                
                // IP Address
                if ($is_ip) {
                    $output .= '<div class="row">';
                    $output .= '<div class="col-sm-5 text-right"><strong>IP Address</strong></div>';
                    $output .= '<div class="col-sm-7 text-left">' . ($server->ipaddress ?? 'N/A') . '</div>';
                    $output .= '</div>';
                }
                
                // Username
                if ($is_username) {
                    $output .= '<div class="row">';
                    $output .= '<div class="col-sm-5 text-right"><strong>Username</strong></div>';
                    $output .= '<div class="col-sm-7 text-left">' . $usernameVal . '</div>';
                    $output .= '</div>';
                }
                
                // Password
                if ($is_password) {
                    $results = localAPI('DecryptPassword', ['password2' => $service->password]);
                    $output .= '<div class="row">';
                    $output .= '<div class="col-sm-5 text-right"><strong>Password</strong></div>';
                    $output .= '<div class="col-sm-7 text-left">' . ($results['password'] ?? 'N/A') . '</div>';
                    $output .= '</div>';
                }
                
                // Visit Website button (only if domain exists)
                if (!empty($domainName) && $is_domain) {
                    $safeDomain = htmlspecialchars($domainName, ENT_QUOTES);
                    $output .= '<br><p><a href="http://' . $safeDomain . '" class="btn btn-default" target="_blank">Visit Website</a></p>';
                }
                
                $output .= '</div>';
                
                $safeOutput = json_encode($output);


                $script = <<<HTML
<script type="text/javascript">
jQuery(function($) {
    try {
        var customHtml = {$safeOutput};

        // Find and modify existing nav tabs
        var \$nav = $('.nav.nav-tabs, .nav.responsive-tabs-sm').first();
        
        if (\$nav.length) {
            // Clear existing tabs
            \$nav.empty();
            
            // Add Service Information tab with proper Bootstrap 4 classes
            var \$li = \$('<li class="nav-item"></li>');
            var \$a = \$('<a href="#serviceinfo" data-toggle="tab" class="nav-link active">' +
                        '<i class="fas fa-info-circle fa-fw"></i> Service Information</a>');
            \$li.append(\$a);
            \$nav.append(\$li);
        } else {
            // Create new nav if not exists
            \$nav = \$('<ul class="nav nav-tabs responsive-tabs-sm"></ul>');
            var \$li = \$('<li class="nav-item"></li>');
            var \$a = \$('<a href="#serviceinfo" data-toggle="tab" class="nav-link active">' +
                        '<i class="fas fa-info-circle fa-fw"></i> Service Information</a>');
            \$li.append(\$a);
            \$nav.append(\$li);
        }

        // Find tab content container
        var \$tabContainer = $('.tab-content.bg-white.product-details-tab-container').first();
        
        if (\$tabContainer.length) {
            // Clear existing content
            \$tabContainer.empty();
            
            // Add new tab pane with proper structure
            var \$newPane = \$('<div class="tab-pane fade show active text-center" role="tabpanel" id="serviceinfo"></div>');
            \$newPane.html(customHtml);
            \$tabContainer.append(\$newPane);
        } else {
            // Create new tab content if not exists
            \$tabContainer = \$('<div class="tab-content bg-white product-details-tab-container"></div>');
            var \$newPane = \$('<div class="tab-pane fade show active text-center" role="tabpanel" id="serviceinfo"></div>');
            \$newPane.html(customHtml);
            \$tabContainer.append(\$newPane);
            
            // Insert after nav
            \$nav.after(\$tabContainer);
        }

        // Ensure nav is visible
        if (!\$nav.parent().length) {
            \$tabContainer.before(\$nav);
        }

        // Remove any duplicate module-client-area blocks
        $('.module-client-area').remove();

    } catch (err) {
        console.error('Service Information error:', err);
    }
});
</script>
HTML;

                return $script;
            }
        }

    } catch (Exception $e) {
        return '<div class="alert alert-danger">Unable to load service information.</div>';
    }
}