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
                    'action'    => 'Get_Products_For_Import',
                    'server_id' => $server_id,
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

                $results     = [];
                $successCount = 0;
                $errorCount   = 0;

                foreach ($products as $product) {
                    $productId          = (int)($product['product_id'] ?? 0);
                    $productName        = trim($product['product_name'] ?? '');
                    $productType        = trim($product['product_type'] ?? 'other');
                    $productGroupName   = trim($product['product_group_name'] ?? 'General');
                    $groupSlug          = trim($product['pgroup_slug'] ?? '');
                    $groupHeadline      = trim($product['pgroup_headline'] ?? '');
                    $groupTagline       = trim($product['pgroup_tagline'] ?? '');
                    $productDescription = $product['product_description'] ?? '';
                    $productShortDesc   = $product['product_shortdesc'] ?? '';
                    $productTagline     = $product['product_tagline'] ?? '';
                    $paytype            = trim($product['paytype'] ?? 'recurring');
                    $isMapped           = (bool)($product['is_mapped'] ?? false);
                    $resellerProductId  = (int)($product['reseller_product_id'] ?? 0);
                    $marginType         = trim($product['margin_type'] ?? 'percentage');
                    $marginValue        = (float)($product['margin_value'] ?? 0);
                    $pricingData        = $product['pricing'] ?? [];
                    $incomingFields     = $product['custom_fields'] ?? [];
                    $incomingGroups     = $product['config_option_groups'] ?? [];

                    if ($productId <= 0 || empty($productName)) {
                        $results[] = ['product_id' => $productId, 'status' => 'error', 'message' => 'Invalid product data.'];
                        $errorCount++;
                        continue;
                    }

                    if(!$isMapped || $resellerProductId <= 0) {
                        $orphanedProduct = Capsule::table('tblproducts')
                            ->where('servertype', 'products_reseller_server')
                            ->where('configoption1', (string)$productId)
                            ->first();

                        if ($orphanedProduct) {
                            $isMapped = true;
                            $resellerProductId = (int)$orphanedProduct->id;
                        }
                    }

                    try {
                        // ---- build adjusted product pricing with margin ----
                        $adjustedPricing = [];
                        foreach ($pricingData as $currencyPricing) {
                            $providerCurrencyCode = $currencyPricing['currency_code'] ?? '';
                            if (empty($providerCurrencyCode)) continue;

                            $localCurrency = Capsule::table('tblcurrencies')
                                ->where('code', $providerCurrencyCode)
                                ->first();
                            if (!$localCurrency) continue;

                            $localCurrencyId = $localCurrency->id;
                            $pricing = $currencyPricing['pricing'] ?? [];

                            $adjustedPricing[$localCurrencyId] = [];

                            $cycles    = ['monthly','quarterly','semiannually','annually','biennially','triennially'];
                            $setupFees = ['msetupfee','qsetupfee','ssetupfee','asetupfee','bsetupfee','tsetupfee'];

                            foreach ($cycles as $i => $cycle) {
                                $price    = (float)($pricing[$cycle]        ?? -1);
                                $setupfee = (float)($pricing[$setupFees[$i]] ?? 0);

                                if ($price >= 0) {
                                    if ($marginType === 'percentage' && $marginValue > 0) {
                                        $price    = $price    * (1 + ($marginValue / 100.0));
                                        if ($setupfee > 0) {
                                            $setupfee = $setupfee * (1 + ($marginValue / 100.0));
                                        }
                                    } elseif ($marginType === 'fixed' && $marginValue > 0) {
                                        $price = $price + $marginValue;
                                    }
                                    $adjustedPricing[$localCurrencyId][$cycle] = round(max(0, $price), 2);
                                } else {
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
                                $isMapped          = false;
                                $resellerProductId = 0;
                            } else {
                                // Update pricing
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
                                            'type'     => 'product',
                                            'relid'    => $resellerProductId,
                                            'currency' => $currencyId,
                                        ], $pricingValues));
                                    }
                                }

                                // Update product name and description
                                Capsule::table('tblproducts')
                                    ->where('id', $resellerProductId)
                                    ->update([
                                        'name'              => $productName,
                                        'type'              => $productType,
                                        'description'       => $productDescription,
                                        'short_description' => $productShortDesc,
                                        'tagline'           => $productTagline,
                                        'paytype'           => $paytype,
                                        'servertype'        => 'products_reseller_server',
                                        'configoption1'     => $productId
                                    ]);

                                // ---- sync custom fields ----
                                prs_syncCustomFields($resellerProductId, $incomingFields);

                                // ---- sync config option groups ----
                                prs_syncConfigOptionGroups($resellerProductId, $incomingGroups, $marginType, $marginValue);

                                $results[] = [
                                    'product_id'         => $productId,
                                    'reseller_product_id'=> $resellerProductId,
                                    'status'             => 'synced',
                                    'message'            => "'" . $productName . "' synced successfully.",
                                ];
                                $successCount++;
                                continue;
                            }
                        }

                        if (!$isMapped || $resellerProductId <= 0) {
                            // ---- IMPORT: Create new product ----
                            
                            // Find or create product group
                            $existingGroup = Capsule::table('tblproductgroups')
                                ->where('name', $productGroupName)
                                ->first();

                            if ($existingGroup) {
                                $gid = $existingGroup->id;
                            } else {
                                $maxOrder = (int)Capsule::table('tblproductgroups')->max('order');
                                $gid = Capsule::table('tblproductgroups')->insertGetId([
                                    'name'              => $productGroupName,
                                    'slug'              => $groupSlug,
                                    'headline'          => $groupHeadline,
                                    'tagline'           => $groupTagline,
                                    'orderfrmtpl'       => '',
                                    'disabledgateways'  => '',
                                    'hidden'            => 0,
                                    'order'             => $maxOrder + 1,
                                ]);
                            }

                            $addProductParams = [
                                'name'              => $productName,
                                'gid'               => $gid,
                                'type'              => $productType,
                                'description'       => $productDescription,
                                'short_description' => $productShortDesc,
                                'tagline'           => $productTagline,
                                'paytype'           => $paytype,
                                'hidden'            => false,
                                'module'            => 'products_reseller_server',
                                'configoption1'     => $productId,
                                'configoption2'     => 'on',
                                'configoption3'     => 'on',
                                'configoption4'     => 'on',
                                'configoption5'     => 'on',
                                'configoption6'     => 'on',
                                'configoption7'     => 'on',
                                'pricing'           => $adjustedPricing,
                                'autosetup'         => 'payment',
                            ];

                            if ($serverGroupId > 0) {
                                $addProductParams['servergroupid'] = $serverGroupId;
                            }

                            $addProductResult = localAPI('AddProduct', $addProductParams);

                            if (($addProductResult['result'] ?? '') !== 'success') {
                                throw new \Exception('Failed to create product: ' . ($addProductResult['message'] ?? 'Unknown error'));
                            }

                            $newProductId = (int)$addProductResult['pid'];

                            // ---- import custom fields ----
                            prs_syncCustomFields($newProductId, $incomingFields);

                            // ---- import config option groups ----
                            prs_syncConfigOptionGroups($newProductId, $incomingGroups, $marginType, $marginValue);

                            // Save mapping via provider API
                            $mappingResult = $main_class->send_request_to_api([
                                'action'              => 'Save_Product_Mapping',
                                'server_id'           => $server_id,
                                'product_id'          => $productId,
                                'reseller_product_id' => $newProductId,
                            ]);

                            if (($mappingResult['status'] ?? '') !== 'success') {
                                logModuleCall('products_reseller_server', 'Save_Product_Mapping_Warning',
                                    ['product_id' => $productId, 'reseller_product_id' => $newProductId],
                                    $mappingResult
                                );
                            }

                            $results[] = [
                                'product_id'          => $productId,
                                'reseller_product_id' => $newProductId,
                                'status'              => 'imported',
                                'message'             => "'" . $productName . "' imported successfully.",
                            ];
                            $successCount++;
                        }

                    } catch (\Exception $e) {
                        $results[] = [
                            'product_id' => $productId,
                            'status'     => 'error',
                            'message'    => $productName . ': ' . $e->getMessage(),
                        ];
                        $errorCount++;
                    }
                }

                $statusText  = ($errorCount === 0) ? 'success' : (($successCount > 0) ? 'partial' : 'error');
                $messageText = $successCount . ' product(s) processed successfully';
                if ($errorCount > 0) {
                    $messageText .= ', ' . $errorCount . ' failed';
                }

                $ajax_requests = [
                    'status'        => $statusText,
                    'message'       => $messageText,
                    'results'       => $results,
                    'success_count' => $successCount,
                    'error_count'   => $errorCount,
                ];
            } catch (\Exception $e) {
                $ajax_requests = ['status' => 'error', 'message' => 'Fatal error: ' . $e->getMessage()];
            }
            break;

        default:
            $ajax_requests['status']  = 'error';
            $ajax_requests['message'] = 'No case is match';
    }

    echo json_encode($ajax_requests);
    exit();
}

function prs_syncCustomFields($resellerProductId, array $incomingFields)
{
    logActivity("Ran prs_syncCustomFields : " . print_r($incomingFields, true));
    foreach ($incomingFields as $field) {
        $fieldname = $field['fieldname'] ?? '';
        if (empty($fieldname)) continue;

        $exists = Capsule::table('tblcustomfields')
            ->where('type', 'product')
            ->where('relid', $resellerProductId)
            ->where('fieldname', $fieldname)
            ->exists();

        logActivity("prs_syncCustomFields Exists : {$exists}");

        if (!$exists) {
            Capsule::table('tblcustomfields')->insert([
                'type'        => 'product',
                'relid'       => $resellerProductId,
                'fieldname'   => $fieldname,
                'fieldtype'   => $field['fieldtype']    ?? 'text',
                'description' => $field['description']  ?? '',
                'fieldoptions'=> $field['fieldoptions'] ?? '',
                'regexpr'     => $field['regexpr']      ?? '',
                'adminonly'   => $field['adminonly']     ?? '',
                'required'    => $field['required']      ?? '',
                'showorder'   => $field['showorder']     ?? '',
                'showinvoice' => $field['showinvoice']   ?? '',
                'sortorder'   => $field['sortorder']     ?? 0,
            ]);
        }
    }
}

function prs_syncConfigOptionGroups($resellerProductId, array $incomingGroups, $marginType, $marginValue)
{
    foreach ($incomingGroups as $group) {
        $groupName = $group['name'] ?? '';
        if (empty($groupName)) continue;

        $localGroup = Capsule::table('tblproductconfiggroups')
            ->where('name', $groupName)
            ->first();

        if ($localGroup) {
            $localGroupId = $localGroup->id;
        } else {
            $localGroupId = Capsule::table('tblproductconfiggroups')->insertGetId([
                'name'        => $groupName,
                'description' => '',
            ]);
        }

        $alreadyLinked = Capsule::table('tblproductconfiglinks')
            ->where('pid', $resellerProductId)
            ->where('gid', $localGroupId)
            ->exists();

        if (!$alreadyLinked) {
            Capsule::table('tblproductconfiglinks')->insert([
                'pid' => $resellerProductId,
                'gid' => $localGroupId,
            ]);
        }

        foreach ($group['options'] ?? [] as $option) {
            $optionname = $option['optionname'] ?? '';
            if (empty($optionname)) continue;

            $optiontype  = (int)($option['optiontype']  ?? 1);
            $qtyminimum  = (int)($option['qtyminimum']  ?? 0);
            $qtymaximum  = (int)($option['qtymaximum']  ?? 0);
            $optHidden   = (int)($option['hidden']       ?? 0);
            $optOrder    = (int)($option['sortorder']    ?? 0);

            $localOption = Capsule::table('tblproductconfigoptions')
                ->where('gid', $localGroupId)
                ->where('optionname', $optionname)
                ->first();

            if ($localOption) {
                $localOptionId = $localOption->id;
                // Keep qtymin/max in sync for quantity options
                if ($optiontype === 4) {
                    Capsule::table('tblproductconfigoptions')
                        ->where('id', $localOptionId)
                        ->update([
                            'qtyminimum' => $qtyminimum,
                            'qtymaximum' => $qtymaximum,
                        ]);
                }
            } else {
                $localOptionId = Capsule::table('tblproductconfigoptions')->insertGetId([
                    'gid'        => $localGroupId,
                    'optionname' => $optionname,
                    'optiontype' => $optiontype,
                    'qtyminimum' => $qtyminimum,
                    'qtymaximum' => $qtymaximum,
                    'order'      => $optOrder,
                    'hidden'     => $optHidden,
                ]);
            }

            foreach ($option['suboptions'] ?? [] as $sub) {
                $subOptionName = $sub['optionname'] ?? '';
                if (empty($subOptionName)) continue;

                $subHidden    = (int)($sub['hidden']    ?? 0);
                $subSortorder = (int)($sub['sortorder'] ?? 0);

                $localSub = Capsule::table('tblproductconfigoptionssub')
                    ->where('configid', $localOptionId)
                    ->where('optionname', $subOptionName)
                    ->first();

                if ($localSub) {
                    $localSubId = $localSub->id;
                } else {
                    $localSubId = Capsule::table('tblproductconfigoptionssub')->insertGetId([
                        'configid'   => $localOptionId,
                        'optionname' => $subOptionName,
                        'sortorder'  => $subSortorder,
                        'hidden'     => $subHidden,
                    ]);
                }

                foreach ($sub['pricing'] ?? [] as $optCurrencyBlock) {
                    $currencyCode = $optCurrencyBlock['currency_code'] ?? '';
                    if (empty($currencyCode)) continue;

                    $localCurrency = Capsule::table('tblcurrencies')
                        ->where('code', $currencyCode)
                        ->first();
                    if (!$localCurrency) continue;

                    $localCurrencyId = $localCurrency->id;
                    $rawCyclePricing = $optCurrencyBlock['pricing'] ?? [];

                    $cycles    = ['monthly', 'quarterly', 'semiannually', 'annually', 'biennially', 'triennially'];
                    $setupFees = ['msetupfee', 'qsetupfee', 'ssetupfee', 'asetupfee', 'bsetupfee', 'tsetupfee'];

                    $pricingRow = [];
                    foreach ($cycles as $i => $cycle) {
                        $price    = (float)($rawCyclePricing[$cycle]         ?? -1);
                        $setupfee = (float)($rawCyclePricing[$setupFees[$i]] ?? 0);

                        if ($price >= 0) {
                            if ($marginType === 'percentage' && $marginValue > 0) {
                                $price = $price * (1 + ($marginValue / 100.0));
                                if ($setupfee > 0) {
                                    $setupfee = $setupfee * (1 + ($marginValue / 100.0));
                                }
                            } elseif ($marginType === 'fixed' && $marginValue > 0) {
                                $price = $price + $marginValue;
                            }
                            $pricingRow[$cycle] = round(max(0, $price), 2);
                        } else {
                            $pricingRow[$cycle] = -1.00;
                        }
                        $pricingRow[$setupFees[$i]] = round(max(0, $setupfee), 2);
                    }

                    $existingOptPricing = Capsule::table('tblpricing')
                        ->where('type', 'configoptions')
                        ->where('relid', $localSubId)
                        ->where('currency', $localCurrencyId)
                        ->first();

                    if ($existingOptPricing) {
                        Capsule::table('tblpricing')
                            ->where('id', $existingOptPricing->id)
                            ->update($pricingRow);
                    } else {
                        Capsule::table('tblpricing')->insert(array_merge([
                            'type'     => 'configoptions',
                            'relid'    => $localSubId,
                            'currency' => $localCurrencyId,
                        ], $pricingRow));
                    }
                }
            }
        }
    }
}