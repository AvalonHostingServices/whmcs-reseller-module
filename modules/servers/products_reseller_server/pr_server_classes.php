<?php

use WHMCS\Database\Capsule;

class ProductsReseller_Main {

    public function send_request_to_api($data) {
        
        if (!empty($data['serverhostname'])) {
            $baseHost  = rtrim($data['serverhostname'], '/');
            $restPart  = trim($data['serverusername'] ?? '', '/');
            $api_key   = $data['serverpassword'] ?? '';
        } else {
            if (empty($data['server_id']) || $data['server_id'] < 1) {
                return [
                    'status'  => 'error',
                    'message' => 'Server credentials or server_id is required',
                ];
            }
            $server = Capsule::table('tblservers')
                ->where('id', $data['server_id'])
                ->select('hostname', 'username', 'password')
                ->first();

            $baseHost = rtrim($server->hostname, '/');
            $restPart = trim($server->username ?? '', '/');

            $decryptResponse = localAPI('DecryptPassword', ['password2' => $server->password]);
            $api_key = $decryptResponse['password'] ?? '';
        }

        if ($restPart !== '') {
            $api_end_point = "https://{$baseHost}/{$restPart}/modules/addons/products_reseller/api.php";
        } else {
            $api_end_point = "https://{$baseHost}/modules/addons/products_reseller/api.php";
        }

        $action   = $data['action'];
        $req_data = $data;

        $data['reseller_url'] = rtrim(Capsule::table('tblconfiguration')->where('setting', 'SystemURL')->value('value') ?? '', '/');

        unset($data['server_id']);
        unset($data['action']);
        unset($data['serverhostname']);
        unset($data['serverusername']);
        unset($data['serverpassword']);

        ob_start();
    
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $api_end_point);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'api_key' => $api_key,
            'action' => $action,
            'params' => $data,
        ]));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        ob_end_clean();

        if ($response === false) {
            return ['status' => 'error', 'message' => 'cURL Error: ' . $curlError];
        }

        $decodedResponse = json_decode($response, true);

        logModuleCall('products_reseller_server', $action, ['endpoint' => $api_end_point, 'request_params' => $req_data], ['http_code' => $httpCode, 'response' => $decodedResponse]);
        

        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['status' => 'error', 'message' => 'Invalid JSON response from API: ' . $response];
        }

        return $decodedResponse;
    }
    
    
    public function create_account($data) {
        $res = $this->send_request_to_api($data);

        if ($res['status'] === 'error') {
            return $res['message'];
        }

        if (!empty($res['service_data'])) {
            prs_writeBackServiceData($data['serviceid'], $res['service_data']);
        }

        return 'success';
    }

    public function suspend_account($data) {
        $res = $this->send_request_to_api($data);

        if ($res['status'] === 'error') {
            return $res['message'];
        }

        if (!empty($res['service_data'])) {
            prs_writeBackServiceData($data['serviceid'], $res['service_data']);
        }

        return 'success';
    }

    public function unsuspend_account($data) {
        $res = $this->send_request_to_api($data);

        if ($res['status'] === 'error') {
            return $res['message'];
        }

        if (!empty($res['service_data'])) {
            prs_writeBackServiceData($data['serviceid'], $res['service_data']);
        }

        return 'success';
    }

    public function terminate_account($data) {
        $res = $this->send_request_to_api($data);

        if ($res['status'] === 'error') {
            return $res['message'];
        }

        if (!empty($res['service_data'])) {
            prs_writeBackServiceData($data['serviceid'], $res['service_data']);
        }

        return 'success';
    }

    public function change_package($data) {
        $res = $this->send_request_to_api($data);

        if ($res['status'] === 'error') {
            return $res['message'];
        }

        if (!empty($res['service_data'])) {
            prs_writeBackServiceData($data['serviceid'], $res['service_data']);
        }

        return 'success';
    }

    public function change_password($data) {
        $res = $this->send_request_to_api($data);

        if ($res['status'] === 'error') {
            return $res['message'];
        }

        if (!empty($res['service_data'])) {
            prs_writeBackServiceData($data['serviceid'], $res['service_data']);
        }

        return 'success';
    }
    
    public function get_server_name($data) {
        
        return $this->send_request_to_api($data);
        
    }
    
    public function get_cpanel_sso($data) {
        
        return $this->send_request_to_api($data);
        
    }
    
    public function get_usage_deatils($data) {
        return $this->send_request_to_api($data);
    }

    public function get_usage_update($data) {
        return $this->send_request_to_api($data);
    }

}

function prs_writeBackServiceData($serviceId, array $serviceData)
{
    // --- credentials ---
    $encryptResult = localAPI('EncryptPassword', ['password2' => $serviceData['password'] ?? '']);

    Capsule::table('tblhosting')->where('id', $serviceId)->update([
        'username'    => $serviceData['username']  ?? '',
        'dedicatedip' => $serviceData['ip']        ?? '',
        'domain'      => $serviceData['domain']    ?? '',
        'password'    => $encryptResult['password'] ?? '',
    ]);

    $service = Capsule::table('tblhosting')->where('id', $serviceId)->first();
    if (!$service) return;

    $localProductId = $service->packageid;

    // --- custom fields ---
    foreach ($serviceData['custom_fields'] ?? [] as $fieldname => $value) {
        $fieldId = Capsule::table('tblcustomfields')
            ->where('type', 'product')
            ->where('relid', $localProductId)
            ->where('fieldname', $fieldname)
            ->value('id');

        if (!$fieldId) continue;

        $exists = Capsule::table('tblcustomfieldsvalues')
            ->where('relid', $serviceId)
            ->where('fieldid', $fieldId)
            ->exists();

        if ($exists) {
            Capsule::table('tblcustomfieldsvalues')
                ->where('relid', $serviceId)
                ->where('fieldid', $fieldId)
                ->update(['value' => $value]);
        } else {
            Capsule::table('tblcustomfieldsvalues')->insert([
                'relid'   => $serviceId,
                'fieldid' => $fieldId,
                'value'   => $value,
            ]);
        }
    }

    // --- config options ---
    $assignedGroupIds = Capsule::table('tblproductconfiglinks')
        ->where('pid', $localProductId)
        ->pluck('gid')
        ->toArray();

    foreach ($serviceData['config_options'] ?? [] as $optionname => $optionValue) {
        $option = Capsule::table('tblproductconfigoptions')
            ->whereIn('gid', $assignedGroupIds)
            ->where('optionname', $optionname)
            ->first();

        if (!$option) continue;

        $optiontype = (int)$option->optiontype;

        $exists = Capsule::table('tblhostingconfigoptions')
            ->where('relid', $serviceId)
            ->where('configid', $option->id)
            ->exists();

        if ($optiontype === 4) {
            // Quantity type — optionValue is ['type'=>'quantity','qty'=>N]
            $qty = is_array($optionValue) ? (int)($optionValue['qty'] ?? 0) : (int)$optionValue;

            if ($exists) {
                Capsule::table('tblhostingconfigoptions')
                    ->where('relid', $serviceId)
                    ->where('configid', $option->id)
                    ->update(['qty' => $qty, 'optionid' => 0]);
            } else {
                Capsule::table('tblhostingconfigoptions')->insert([
                    'relid'    => $serviceId,
                    'configid' => $option->id,
                    'optionid' => 0,
                    'qty'      => $qty,
                ]);
            }
        } else {
            // Standard option — optionValue is ['type'=>'option','value'=>'sub option name']
            $subOptionName = is_array($optionValue) ? ($optionValue['value'] ?? '') : (string)$optionValue;

            $subOptionId = Capsule::table('tblproductconfigoptionssub')
                ->where('configid', $option->id)
                ->where('optionname', $subOptionName)
                ->value('id');

            if (!$subOptionId) continue;

            if ($exists) {
                Capsule::table('tblhostingconfigoptions')
                    ->where('relid', $serviceId)
                    ->where('configid', $option->id)
                    ->update(['optionid' => $subOptionId, 'qty' => 0]);
            } else {
                Capsule::table('tblhostingconfigoptions')->insert([
                    'relid'    => $serviceId,
                    'configid' => $option->id,
                    'optionid' => $subOptionId,
                    'qty'      => 0,
                ]);
            }
        }
    }
}