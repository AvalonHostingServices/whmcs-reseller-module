<?php

use WHMCS\Database\Capsule;

if ($_POST['prs_doing_ajax']) {
    require_once __DIR__ . '/../../../init.php';
}

require_once __DIR__ . '/pr_server_classes.php';

if ($_POST['prs_doing_ajax']) {
    $st_action = $_POST['action'];
    switch ($st_action) {

        case 'getProductsForImport':
            try {
                $server_id = Capsule::table('tblservers')
                    ->where('type', 'products_reseller_server')
                    ->where('active', 1)
                    ->value('id');

                if (!$server_id) {
                    $ajax_requests = ['status' => 'error', 'message' => 'No active Products Reseller server found.'];
                    break;
                }

                $main_class = new ProductsReseller_Main();
                $result = $main_class->send_request_to_api([
                    'action' => 'Get_Products_For_Import',
                    'server_id' => $server_id
                ]);

                $ajax_requests = $result;
            } catch (\Exception $e) {
                $ajax_requests = ['status' => 'error', 'message' => 'Error: ' . $e->getMessage()];
            }
            break;

        case 'importSyncProducts':
            try {
                $productsJson = $_POST['products'] ?? '[]';
                $productsJson = html_entity_decode($_POST['products'], ENT_QUOTES | ENT_HTML5);
                $products = json_decode($productsJson, true);

                if (empty($products) || !is_array($products)) {
                    $ajax_requests = ['status' => 'error', 'message' => 'No products provided for import/sync.'];
                    break;
                }

                $server_id = Capsule::table('tblservers')
                    ->where('type', 'products_reseller_server')
                    ->where('active', 1)
                    ->value('id');

                if (!$server_id) {
                    $ajax_requests = ['status' => 'error', 'message' => 'No active Products Reseller server found.'];
                    break;
                }

                // Find server group containing the products_reseller_server
                $serverGroupId = 0;
                $sgRel = Capsule::table('tblservergroupsrel')
                    ->where('serverid', $server_id)
                    ->value('groupid');
                if ($sgRel) {
                    $serverGroupId = (int)$sgRel;
                }

                $main_class = new ProductsReseller_Main();

                $results = [];
                $successCount = 0;
                $errorCount = 0;

                foreach ($products as $product) {
                    $productId = (int)($product['product_id'] ?? 0);
                    $productName = trim($product['product_name'] ?? '');
                    $productType = trim($product['product_type'] ?? 'other');
                    $productGroupName = trim($product['product_group_name'] ?? 'General');
                    $groupSlug = trim($product['pgroup_slug'] ?? '');
                    $groupHeadline = trim($product['pgroup_headline'] ?? '');
                    $groupTagline = trim($product['pgroup_tagline'] ?? '');
                    $productDescription = $product['product_description'] ?? '';
                    $productShortDesc = $product['product_shortdesc'] ?? '';
                    $productTagline = $product['product_tagline'] ?? '';
                    $paytype = trim($product['paytype'] ?? 'recurring');
                    $isMapped = (bool)($product['is_mapped'] ?? false);
                    $resellerProductId = (int)($product['reseller_product_id'] ?? 0);
                    $marginType = trim($product['margin_type'] ?? 'percentage');
                    $marginValue = (float)($product['margin_value'] ?? 0);
                    $pricingData = $product['pricing'] ?? [];

                    if ($productId <= 0 || empty($productName)) {
                        $results[] = ['product_id' => $productId, 'status' => 'error', 'message' => 'Invalid product data.'];
                        $errorCount++;
                        continue;
                    }

                    try {
                        // Calculate adjusted pricing with margin applied
                        $adjustedPricing = [];
                        foreach ($pricingData as $currencyPricing) {
                            $providerCurrencyCode = $currencyPricing['currency_code'] ?? '';
                            if (empty($providerCurrencyCode)) continue;

                            // Match provider currency to local currency by code
                            $localCurrency = Capsule::table('tblcurrencies')
                                ->where('code', $providerCurrencyCode)
                                ->first();
                            if (!$localCurrency) continue;

                            $localCurrencyId = $localCurrency->id;
                            $pricing = $currencyPricing['pricing'] ?? [];

                            $adjustedPricing[$localCurrencyId] = [];

                            $cycles = ['monthly', 'quarterly', 'semiannually', 'annually', 'biennially', 'triennially'];
                            $setupFees = ['msetupfee', 'qsetupfee', 'ssetupfee', 'asetupfee', 'bsetupfee', 'tsetupfee'];

                            foreach ($cycles as $i => $cycle) {
                                $price = (float)($pricing[$cycle] ?? -1);
                                $setupfee = (float)($pricing[$setupFees[$i]] ?? 0);

                                if ($price >= 0) {
                                    // Apply profit margin
                                    if ($marginType === 'percentage' && $marginValue > 0) {
                                        $price = $price * (1 + ($marginValue / 100.0));
                                        if ($setupfee > 0) {
                                            $setupfee = $setupfee * (1 + ($marginValue / 100.0));
                                        }
                                    } elseif ($marginType === 'fixed' && $marginValue > 0) {
                                        $price = $price + $marginValue;
                                    }
                                    $adjustedPricing[$localCurrencyId][$cycle] = round(max(0, $price), 2);
                                } else {
                                    // Keep disabled cycles as -1
                                    $adjustedPricing[$localCurrencyId][$cycle] = -1.00;
                                }

                                $adjustedPricing[$localCurrencyId][$setupFees[$i]] = round(max(0, $setupfee), 2);
                            }
                        }

                        if (empty($adjustedPricing)) {
                            throw new \Exception('No matching currencies found between provider and local system.');
                        }

                        if ($isMapped && $resellerProductId > 0) {
                            // ---- SYNC: Update existing product ----
                            $existingProduct = Capsule::table('tblproducts')
                                ->where('id', $resellerProductId)
                                ->first();

                            if (!$existingProduct) {
                                // Product was deleted locally, treat as new import
                                $isMapped = false;
                                $resellerProductId = 0;
                            } else {
                                // Update pricing for each matched currency
                                foreach ($adjustedPricing as $currencyId => $pricingValues) {
                                    $existingPricing = Capsule::table('tblpricing')
                                        ->where('type', 'product')
                                        ->where('relid', $resellerProductId)
                                        ->where('currency', $currencyId)
                                        ->first();

                                    if ($existingPricing) {
                                        Capsule::table('tblpricing')
                                            ->where('id', $existingPricing->id)
                                            ->update($pricingValues);
                                    } else {
                                        Capsule::table('tblpricing')->insert(array_merge([
                                            'type' => 'product',
                                            'relid' => $resellerProductId,
                                            'currency' => $currencyId,
                                        ], $pricingValues));
                                    }
                                }

                                // Update product name and description
                                Capsule::table('tblproducts')
                                    ->where('id', $resellerProductId)
                                    ->update([
                                        'name' => $productName,
                                        'description' => $productDescription,
                                    ]);

                                $results[] = [
                                    'product_id' => $productId,
                                    'reseller_product_id' => $resellerProductId,
                                    'status' => 'synced',
                                    'message' => "'" . $productName . "' synced successfully."
                                ];
                                $successCount++;
                                continue;
                            }
                        }

                        if (!$isMapped || $resellerProductId <= 0) {
                            // ---- IMPORT: Create new product ----

                            // Find or create product group by name
                            $existingGroup = Capsule::table('tblproductgroups')
                                ->where('name', $productGroupName)
                                ->first();

                            $gid = null;
                            if ($existingGroup) {
                                $gid = $existingGroup->id;
                            } else {
                                // Create product group with the same name
                                $maxOrder = (int) Capsule::table('tblproductgroups')->max('order');
                                $gid = Capsule::table('tblproductgroups')->insertGetId([
                                    'name' => $productGroupName,
                                    'slug' => $groupSlug,
                                    'headline' => $groupHeadline,
                                    'tagline' => $groupTagline,
                                    'orderfrmtpl' => '',
                                    'disabledgateways' => '',
                                    'hidden' => 0,
                                    'order' => $maxOrder + 1,
                                ]);
                            }

                            // Build AddProduct API params
                            $addProductParams = [
                                'name' => $productName,
                                'gid' => $gid,
                                'type' => $productType,
                                'description' => $productDescription,
                                'short_description' => $productShortDesc,
                                'tagline' => $productTagline,
                                'paytype' => $paytype,
                                'hidden' => false,
                                'module' => 'products_reseller_server',
                                'configoption1' => $productId,
                                'pricing' => $adjustedPricing,
                                'autosetup' => 'payment',
                            ];

                            if ($serverGroupId > 0) {
                                $addProductParams['servergroupid'] = $serverGroupId;
                            }

                            $addProductResult = localAPI('AddProduct', $addProductParams);

                            if (($addProductResult['result'] ?? '') !== 'success') {
                                throw new \Exception('Failed to create product: ' . ($addProductResult['message'] ?? 'Unknown error'));
                            }

                            $newProductId = (int)$addProductResult['pid'];

                            // Save mapping via provider API
                            $mappingResult = $main_class->send_request_to_api([
                                'action' => 'Save_Product_Mapping',
                                'server_id' => $server_id,
                                'product_id' => $productId,
                                'reseller_product_id' => $newProductId,
                            ]);

                            if (($mappingResult['status'] ?? '') !== 'success') {
                                logModuleCall('products_reseller_server', 'Save_Product_Mapping_Warning',
                                    ['product_id' => $productId, 'reseller_product_id' => $newProductId],
                                    $mappingResult
                                );
                            }

                            $results[] = [
                                'product_id' => $productId,
                                'reseller_product_id' => $newProductId,
                                'status' => 'imported',
                                'message' => "'" . $productName . "' imported successfully."
                            ];
                            $successCount++;
                        }

                    } catch (\Exception $e) {
                        $results[] = [
                            'product_id' => $productId,
                            'status' => 'error',
                            'message' => $productName . ': ' . $e->getMessage()
                        ];
                        $errorCount++;
                    }
                }

                $statusText = ($errorCount === 0) ? 'success' : (($successCount > 0) ? 'partial' : 'error');
                $messageText = $successCount . ' product(s) processed successfully';
                if ($errorCount > 0) {
                    $messageText .= ', ' . $errorCount . ' failed';
                }

                $ajax_requests = [
                    'status' => $statusText,
                    'message' => $messageText,
                    'results' => $results,
                    'success_count' => $successCount,
                    'error_count' => $errorCount
                ];
            } catch (\Exception $e) {
                $ajax_requests = ['status' => 'error', 'message' => 'Fatal error: ' . $e->getMessage()];
            }
            break;

        default:
            $ajax_requests['status'] = 'error';
            $ajax_requests['message'] = 'No case is match';
    }

    echo json_encode($ajax_requests);
    exit();
}