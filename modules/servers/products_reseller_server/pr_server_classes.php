<?php

use WHMCS\Database\Capsule;

class ProductsReseller_Main {

    public function send_request_to_api($data) {
        
        if (empty($data['server_id']) || $data['server_id'] < 1){
            return [
                'status' => 'error',
                'message' => 'Serverid is required',
            ];
        }
        $server = Capsule::table('tblservers')
            ->where('id', $data['server_id'])
            ->select('hostname', 'username', 'password')
            ->first();
        
        $baseHost = rtrim($server->hostname, '/');

        $restPart = trim($server->username ?? '', '/');
    
        if ($restPart !== '') {
            $api_end_point = "https://{$baseHost}/{$restPart}/modules/addons/products_reseller/api.php";
        } else {
            $api_end_point = "https://{$baseHost}/modules/addons/products_reseller/api.php";
        }
    
        $decryptResponse = localAPI('DecryptPassword', ['password2' => $server->password]);
        $api_key = $decryptResponse['password'] ?? '';
        $action = $data['action'];
    
        $req_data = $data;
        unset($data['server_id']);
        unset($data['action']);

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
        
        if($res['status'] == 'error') {
            return $res['message'];
        }
        
        return 'success';
    }
    
    public function suspend_account($data) {
        
        $res = $this->send_request_to_api($data);
        
        if($res['status'] == 'error') {
            return $res['message'];
        }
        
        return 'success';
    }
    
    public function unsuspend_account($data) {
        
        $res = $this->send_request_to_api($data);
        
        if($res['status'] == 'error') {
            return $res['message'];
        }
        
        return 'success';
    }
    
    public function terminate_account($data) {
        
        $res = $this->send_request_to_api($data);
        
        if($res['status'] == 'error') {
            return $res['message'];
        }
        
        return 'success';
    }
    
    public function change_package($data) {
        
        $res = $this->send_request_to_api($data);
        
        if($res['status'] == 'error') {
            return $res['message'];
        }
        
        return 'success';
    }
    
    public function change_password($data) {
        
        $res = $this->send_request_to_api($data);
        
        if($res['status'] == 'error') {
            return $res['message'];
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

}