<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use WHMCS\Database\Capsule;

whmp_prs_copyFileToHooks();

add_hook('AdminAreaHeadOutput', 1, function($vars) {

    if ($vars['filename'] == 'configservers' && isset($_GET['action']) && $_GET['action'] === 'manage') {
        
        return <<<HTML
        <script>
        $(document).ready(function() {

            var originalLabels = {
                username: null,
                addUsername: null,
                password: null,
                addPassword: null,
            };
            var originalsCaptured = false;
            function updateLabelsForProductsReseller() {
                var selectedType = $('#inputServerType').val();
                
                if (!originalsCaptured) {

                    var inputUsername = $('#inputUsername');
                    if (inputUsername.length) {
                        var tr = inputUsername.parent().parent();
                        if (tr.is('tr')) {
                            var labelTd = tr.children('td.fieldlabel');
                            if (labelTd.length) {
                                var txt = labelTd.text().trim();
                                if (txt !== 'API Endpoint') {
                                    originalLabels.username = txt;
                                }
                            }
                        }
                    }
                    // addUsername label
                    var addLabel = $('label[for="addUsername"]');
                    if (addLabel.length) {
                        var txt = addLabel.text().trim();
                        if (txt !== 'API Endpoint') {
                            originalLabels.addUsername = txt;
                        }
                    }
                    
                    var inputPassword = $('#inputPassword');
                    if (inputPassword.length) {
                        var trPass = inputPassword.parent().parent();
                        if (trPass.is('tr')) {
                            var labelTdPass = trPass.children('td.fieldlabel');
                            if (labelTdPass.length) {
                                var txt = labelTdPass.text().trim();
                                if (txt !== 'API Key') {
                                    originalLabels.password = txt;
                                }
                            }
                        }
                    }
                    
                    var addPassword = $('label[for="addPassword"]');
                    if (addPassword.length) {
                        var txt = addPassword.text().trim();
                        if (txt !== 'API Key') {
                            originalLabels.addPassword = txt;
                        }
                    }
                
                    originalsCaptured = true;
                }
                if (selectedType === 'products_reseller_server') {
                    // Change Username to Client ID
                    var inputUsername = $('#inputUsername');
                    if (inputUsername.length) {
                        var tr = inputUsername.parent().parent();
                        if (tr.is('tr')) {
                            var labelTd = tr.children('td.fieldlabel');
                            if (labelTd.length) {
                                labelTd.text('API Endpoint');
                            }
                        }
                    }
                    // Change label for addUsername to Client ID
                    var addLabel = $('label[for="addUsername"]');
                    if (addLabel.length) {
                        addLabel.text('API Endpoint');
                    }
                    // Change Password to Client Secret
                    var inputPassword = $('#inputPassword');
                    if (inputPassword.length) {
                        var trPass = inputPassword.parent().parent();
                        if (trPass.is('tr')) {
                            var labelTdPass = trPass.children('td.fieldlabel');
                            if (labelTdPass.length) {
                                labelTdPass.text('API Key');
                            }
                        }
                    }
                    var addPassword = $('label[for="addPassword"]');
                    if (addPassword.length) {
                        addPassword.text('API Key');
                    }
                    
                } else {
                    // Revert to original labels when not Contabo
                    var inputUsername = $('#inputUsername');
                    if (inputUsername.length) {
                        var tr = inputUsername.parent().parent();
                        if (tr.is('tr')) {
                            var labelTd = tr.children('td.fieldlabel');
                            if (labelTd.length && originalLabels.username) {
                                labelTd.text(originalLabels.username);
                            }
                        }
                    }
                    var addUsernameLabel = $('label[for="addUsername"]');
                    if (addUsernameLabel.length && originalLabels.addUsername) {
                        addUsernameLabel.text(originalLabels.addUsername);
                    }

                    var inputPassword = $('#inputPassword');
                    if (inputPassword.length) {
                        var trPass = inputPassword.parent().parent();
                        if (trPass.is('tr')) {
                            var labelTdPass = trPass.children('td.fieldlabel');
                            if (labelTdPass.length && originalLabels.password) {
                                labelTdPass.text(originalLabels.password);
                            }
                        }
                    }

                    var addPasswordLabel = $('label[for="addPassword"]');
                    if (addPasswordLabel.length && originalLabels.addPassword) {
                        addPasswordLabel.text(originalLabels.addPassword);
                    }
                    
                }
            }

            updateLabelsForProductsReseller();

            $('#inputServerType,#addType').on('change', function() {
                updateLabelsForProductsReseller();
            });
        });
        </script>
        HTML;
        
    }
    
    if ($vars['filename'] == 'clientsservices') {
        $serviceId = 0;
        if(!isset($_GET['id'])) {
            $userId = (int)$_GET['userid'];
            $fullCode = $vars['jscode'] . ' ' . $vars['jquerycode'];
            
            if (preg_match('/userid=' . $userId . '&id=(\d+)/', $fullCode, $matches)) {
                $serviceId = (int) $matches[1];
            }
        } else {
            $serviceId = (int)$_GET['id'];
        }

        if(!$serviceId) {
            $serviceId = Capsule::table('tblhosting')
                ->where('userid', (int) $_GET['userid'])
                ->orderBy('id', 'asc')
                ->value('id');
        }

        if(!$serviceId) {
            return;
        }
        

        $service = Capsule::table('tblhosting')
            ->join('tblservers', 'tblhosting.server', '=', 'tblservers.id')
            ->where('tblhosting.id', $serviceId)
            ->where('tblservers.type', 'products_reseller_server')
            ->select('tblhosting.id', 'tblhosting.server')
            ->first();

        if ($service) {
            $main_class = new ProductsReseller_Main();
            try {
                $res = $main_class->get_server_name([
                    'serviceid' => $service->id,
                    'server_id' => $service->server,
                    'action'    => 'GetServerName'
                ]);

                // If it's NOT 'cpanel', hide the login button
                if (empty($res['server_name']) || strtolower($res['server_name']) !== 'cpanel') {
                    return <<<HTML
                    <script>
                    $(document).ready(function() {
                        $('button[onclick*="runModuleCommand"][onclick*="singlesignon"]').closest('.btn-group').find('button:first').hide();
                    });
                    </script>
                    HTML;
                }
            } catch (Exception $e) {
                return <<<HTML
                <script>
                $(document).ready(function() {
                    $('button[onclick*="runModuleCommand"][onclick*="singlesignon"]').closest('.btn-group').find('button:first').hide();
                });
                </script>
                HTML;
            }
        }
    }

});

function whmp_prs_copyFileToHooks() {
    $sourcePath = __DIR__ . '/hooks/';
    $fileName = 'prs_hooks.php';
    $destinationPath = __DIR__ . '/../../../includes/hooks/';
    
    $sourceFile = $sourcePath . $fileName;
    $destinationFile = $destinationPath . $fileName;
    
    if (!file_exists($sourceFile)) {
        return [
            'success' => false,
            'message' => "Source file does not exist: {$sourceFile}"
        ];
    }
    
    if (!is_readable($sourceFile)) {
        return [
            'success' => false,
            'message' => "Source file is not readable: {$sourceFile}"
        ];
    }
    
    if (!is_dir($destinationPath)) {
        if (!mkdir($destinationPath, 0755, true)) {
            return [
                'success' => false,
                'message' => "Failed to create destination directory: {$destinationPath}"
            ];
        }
    }
    
    if (!is_writable($destinationPath)) {
        return [
            'success' => false,
            'message' => "Destination directory is not writable: {$destinationPath}"
        ];
    }
    
    if (file_exists($destinationFile)) {
        return [
            'success' => false,
            'message' => "Hooks File is Already Added."
        ];
    }

    if(!Capsule::table('tblservers')->where('type', 'products_reseller_server')->where('active', 1)->exists()) {
        
        if (file_exists($destinationFile)) {
            unlink($destinationFile);
        }

        return [
            'success' => false,
            'message' => "Server is not Activated!"
        ];
    }
    
    if (!copy($sourceFile, $destinationFile)) {
        
        return [
            'success' => false,
            'message' => "Failed to copy file from {$sourceFile} to {$destinationFile}"
        ];
    }
    
    return [
        'success' => true,
        'message' => "File copied successfully. "
    ];
}