<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use WHMCS\Database\Capsule;
use WHMCS\Module\Server\CustomAction;
use WHMCS\Module\Server\CustomActionCollection;

require_once __DIR__ . '/pr_server_classes.php';
include_once __DIR__ . '/hooks.php';

whmp_prs_copyFileToHooks();

function products_reseller_server_MetaData() {
    return array(
        'DisplayName' => 'Products Reseller for WHMCS',
        'APIVersion' => '1.0',
        'RequiresServer' => true,
        'ServiceSingleSignOnLabel' => "Login to cPanel",
        'AdminSingleSignOnLabel'   => "Login to cPanel",
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
        'success' => (is_array($all_products['data']) && !empty($all_products['data']) ? true : false),
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


function products_reseller_server_CreateAccount($params) {
    
    $billingCycle = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('billingcycle');
    $qty = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('qty');
    $domain = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('domain');
    $username = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('username');
    $ip = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('dedicatedip');
    
    
    $main_class = new ProductsReseller_Main();
    
    return $main_class->create_account([
        'serviceid' => $params['serviceid'],
        'billingcycle' => $billingCycle,
        'qty'       => $qty,
        'domain'    => $domain,
        'username'  => $username,
        'password'  => $params['password'],
        'ip_address' => $ip,
        'selected_product' => $params['configoption1'],
        'server_id'     => $params['serverid'],
        'action'    => 'CreateAccount'
    ]);
}

function products_reseller_server_SuspendAccount($params) {
    $domain = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('domain');
    $username = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('username');
    $ip = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('dedicatedip');
    $main_class = new ProductsReseller_Main();
    
    return $main_class->suspend_account([
        'serviceid' => $params['serviceid'],
        'server_id'     => $params['serverid'],
        'suspendreason' => $params['suspendreason'],
        'action'    => 'SuspendAccount',
        'domain'    => $domain,
        'username'  => $username,
        'password'  => $params['password'],
        'ip_address' => $ip,
    ]);
}

function products_reseller_server_UnsuspendAccount($params) {
    $domain = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('domain');
    $username = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('username');
    $ip = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('dedicatedip');
    
    $main_class = new ProductsReseller_Main();
    
    return $main_class->unsuspend_account([
        'serviceid' => $params['serviceid'],
        'server_id'     => $params['serverid'],
        'action'    => 'UnsuspendAccount',
        'domain'    => $domain,
        'username'  => $username,
        'password'  => $params['password'],
        'ip_address' => $ip,
    ]);
}

function products_reseller_server_TerminateAccount($params) {
    $domain = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('domain');
    $username = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('username');
    $ip = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('dedicatedip');
    
    $main_class = new ProductsReseller_Main();
    
    return $main_class->terminate_account([
        'serviceid' => $params['serviceid'],
        'server_id'     => $params['serverid'],
        'action'    => 'TerminateAccount',
        'domain'    => $domain,
        'username'  => $username,
        'password'  => $params['password'],
        'ip_address' => $ip,
    ]);
}

function products_reseller_server_ChangePackage($params) {
    $domain = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('domain');
    $username = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('username');
    $ip = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('dedicatedip');
    
    $main_class = new ProductsReseller_Main();
    
    return $main_class->change_package([
        'serviceid' => $params['serviceid'],
        'server_id'     => $params['serverid'],
        'action'    => 'ChangePackage',
        'domain'    => $domain,
        'username'  => $username,
        'password'  => $params['password'],
        'ip_address' => $ip,
    ]);
}


function products_reseller_server_ChangePassword($params) {
    $domain = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('domain');
    $username = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('username');
    $ip = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('dedicatedip');
    
    $main_class = new ProductsReseller_Main();
    
    return $main_class->change_password([
        'serviceid' => $params['serviceid'],
        'server_id'     => $params['serverid'],
        'password'      => $params['password'],
        'action'    => 'ChangePassword',
        'domain'    => $domain,
        'username'  => $username,
        'ip_address' => $ip,
    ]);
}

function products_reseller_server_ServiceSingleSignOn($params) {
    try {
        $serviceId = $params['serviceid'] ?? null;
        $serverId  = $params['serverid'] ?? null;

        $app = $params['app'] ?? ($_GET['app'] ?? 'Home'); // prefer $params, fallback to $_GET

        $mainClass = new ProductsReseller_Main();

        $ssoResponse = $mainClass->get_cpanel_sso([
            'serviceid' => $serviceId,
            'server_id' => $serverId,
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
            'server_id' => $serverId,
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
            'server_id' => $params['serverid'],
            'action'    => 'GetServerName'
        ]);
        
        $status = Capsule::table('tblhosting')->where('id', $params['serviceid'])->value('domainstatus');
        
        if($status == 'Active') {
            if (!empty($res['server_name']) && strtolower($res['server_name']) === 'cpanel') {
            
                $usageRes = $main_class->get_usage_deatils([
                    'serviceid' => $params['serviceid'],
                    'server_id' => $params['serverid'],
                    'action'    => 'get_Bandwidth_Disk_Usage'
                ]);
            
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
                            'disk_used'       => $usageRes['disk_usage']['used_mb'] ?? 0,
                            'disk_limit'      => ($usageRes['disk_usage']['limit_mb'] ?? 0) == 0 ? 'Unlimited M' : $usageRes['disk_usage']['limit_mb'] . ' M',
                            'disk_percent'    => $usageRes['disk_usage']['percentage_used'] ?? 0,
                            'bw_used'         => $usageRes['bandwidth_usage']['current_month_mb'] ?? 0,
                            'bw_limit'        => ($usageRes['bandwidth_usage']['limit_mb'] ?? 0) == 0 ? 'Unlimited M' : $usageRes['bandwidth_usage']['limit_mb'] . ' M',
                            'bw_percent'      => $usageRes['bandwidth_usage']['percentage_used'] ?? 0,
                            'last_updated'    => $usageRes['last_updated'] ?? date('Y-m-d H:i:s'),
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