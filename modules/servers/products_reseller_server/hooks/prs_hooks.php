<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use WHMCS\Database\Capsule;

$path = __DIR__ . '/../../modules/servers/products_reseller_server/pr_server_classes.php';

if (file_exists($path)) {
    require_once $path;
} else {
    unlink(__FILE__);
    exit;
}

if(!Capsule::table('tblservers')->where('type', 'products_reseller_server')->where('active', 1)->exists()) {
    unlink(__FILE__);
    exit;
}

add_hook('AdminAreaHeadOutput', 1, function($vars) {

    if($vars['filename'] == 'configproducts' && !isset($_GET['id'])) {
        return <<<'HTML'
        <style>
            #prs-import-modal-overlay {
                position: fixed;
                top: 0; left: 0;
                width: 100%; height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 10000;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            #prs-import-modal {
                background: #fff;
                border-radius: 8px;
                width: 1166px;
                max-width: 100%;
                max-height: 88vh;
                display: flex;
                flex-direction: column;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            }
            .prs-modal-header {
                background: #1A4D80;
                color: #fff;
                padding: 16px 22px;
                border-radius: 8px 8px 0 0;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .prs-modal-header h3 {
                margin: 0;
                font-size: 17px;
                font-weight: 600;
            }
            .prs-modal-close {
                cursor: pointer;
                font-size: 22px;
                color: #fff;
                opacity: 0.85;
                transition: opacity 0.2s;
            }
            .prs-modal-close:hover { opacity: 1; }
            .prs-modal-body {
                padding: 20px 22px;
                overflow-y: auto;
                flex: 1;
            }
            .prs-modal-footer {
                padding: 14px 22px;
                border-top: 1px solid #e9ecef;
                text-align: right;
                background: #f8f9fa;
                border-radius: 0 0 8px 8px;
            }
            .prs-margin-section {
                margin-bottom: 18px;
                padding: 16px 18px;
                background: #f8f9fa;
                border-radius: 6px;
                border: 1px solid #e0e5ea;
            }
            .prs-margin-section h4 {
                margin: 0 0 12px;
                color: #1A4D80;
                font-size: 14px;
                font-weight: 600;
            }
            .prs-margin-controls {
                display: flex;
                gap: 10px;
                align-items: center;
                flex-wrap: wrap;
            }
            .prs-margin-controls select,
            .prs-margin-controls input {
                padding: 7px 10px;
                border: 1px solid #ced4da;
                border-radius: 4px;
                font-size: 13px;
                outline: none;
            }
            .prs-margin-controls select:focus,
            .prs-margin-controls input:focus {
                border-color: #1A4D80;
                box-shadow: 0 0 0 2px rgba(26,77,128,0.12);
            }
            .prs-margin-controls input[type="number"] { width: 120px; }
            .prs-margin-label {
                font-size: 13px;
                color: #555;
                font-weight: 500;
            }
            #prs-products-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 13px;
                margin-top: 5px;
            }
            #prs-products-table th {
                background: #1A4D80;
                color: #fff;
                padding: 10px 12px;
                text-align: center;
                font-weight: 600;
                font-size: 12px;
                text-transform: uppercase;
                letter-spacing: 0.3px;
            }
            #prs-products-table th:first-child { border-radius: 4px 0 0 0; }
            #prs-products-table th:last-child { border-radius: 0 4px 0 0; }
            #prs-products-table td {
                padding: 9px 12px;
                border-bottom: 1px solid #eef0f2;
            }
            #prs-products-table tbody tr:hover td {
                background: #f0f5fa;
            }
            .prs-status-new {
                display: inline-block;
                padding: 2px 10px;
                background: #28a745;
                color: #fff;
                border-radius: 3px;
                font-size: 11px;
                font-weight: 600;
            }
            .prs-status-imported {
                display: inline-block;
                padding: 2px 10px;
                background: #1A4D80;
                color: #fff;
                border-radius: 3px;
                font-size: 11px;
                font-weight: 600;
            }
            .prs-btn-primary {
                background: #1A4D80;
                color: #fff;
                border: none;
                padding: 9px 22px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 13px;
                font-weight: 600;
                transition: background 0.2s;
            }
            .prs-btn-primary:hover { background: #15406a; color: #fff; }
            .prs-btn-primary:disabled {
                background: #8ba4be;
                cursor: not-allowed;
            }
            .prs-btn-default {
                background: #fff;
                color: #333;
                border: 1px solid #ced4da;
                padding: 9px 22px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 13px;
                margin-left: 10px;
                transition: background 0.2s;
            }
            .prs-btn-default:hover { background: #f1f1f1; }
            .prs-btn-apply {
                background: #1A4D80;
                color: #fff;
                border: none;
                padding: 7px 16px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 13px;
                font-weight: 500;
                transition: background 0.2s;
            }
            .prs-btn-apply:hover { background: #15406a; color: #fff; }
            .prs-loading {
                text-align: center;
                padding: 45px 20px;
                color: #777;
                font-size: 14px;
            }
            .prs-loading i { margin-right: 8px; }
            .prs-success-popup {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 4px;
                border: 1px solid transparent;
                color: #3c763d;
                background-color: #dff0d8;
                border-color: #d6e9c6;
                z-index: 10001;
                opacity: 0;
                animation: slideInOutRight 3s ease-in-out forwards;
            }
            @keyframes slideInOutRight {
                0% {
                    opacity: 0;
                    transform: translateX(50px);
                }
                10% {
                    opacity: 1;
                    transform: translateX(0);
                }
                90% {
                    opacity: 1;
                    transform: translateX(0);
                }
                100% {
                    opacity: 0;
                    transform: translateX(50px);
                }
            }

            .prs-progress-bar {
                width: 100%;
                height: 5px;
                background: #e9ecef;
                border-radius: 3px;
                margin-top: 10px;
                overflow: hidden;
            }
            .prs-progress-fill {
                height: 100%;
                background: #1A4D80;
                width: 0%;
                transition: width 0.4s ease;
                border-radius: 3px;
            }
            .prs-result-success { color: #28a745; }
            .prs-result-synced { color: #1A4D80; }
            .prs-result-error { color: #dc3545; }
            .prs-select-bar {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 8px;
            }
            .prs-select-bar label {
                font-size: 13px;
                color: #333;
                font-weight: 500;
                cursor: pointer;
            }
            .prs-product-count {
                font-size: 12px;
                color: #888;
            }
            .prs-inline-margin select,
            .prs-inline-margin input {
                padding: 4px 7px;
                border: 1px solid #ced4da;
                border-radius: 3px;
                font-size: 12px;
                outline: none;
            }
            .prs-inline-margin select:focus,
            .prs-inline-margin input:focus {
                border-color: #1A4D80;
            }
            .prs-inline-margin input[type="number"] { width: 75px; }
            .prs-results-wrap {
                max-height: 180px;
                overflow-y: auto;
                border: 1px solid #e9ecef;
                border-radius: 4px;
                padding: 10px 14px;
                background: #fafbfc;
            }
            .prs-results-wrap div {
                padding: 3px 0;
                font-size: 13px;
            }
            .prs-product-check, #prs-select-all {
                accent-color: #1A4D80;
            }

            .prs-currency-block {
                display: flex;
                flex-wrap: wrap;
                gap: 4px 8px;
                font-size: 12px;
                line-height: 1.6;
            }
            .prs-price-cycle {
                display: inline-block;
                white-space: nowrap;
                color: #333;
            }
            .prs-setup-fee {
                color: #888;
                font-size: 11px;
            }
            #prs-products-table td:nth-child(8),
            #prs-products-table td:nth-child(9) {
                min-width: 160px;
                max-width: 220px;
            }
            #prs-products-table th:nth-child(8),
            #prs-products-table th:nth-child(9) {
                min-width: 160px;
            }

            #prs-import-modal, #prs-products-table tbody {
                scrollbar-width: auto;
                scrollbar-color: #1a4d80 #f5f5f5;
            }

            #prs-import-modal::-webkit-scrollbar, #prs-products-table tbody::-webkit-scrollbar {
                width: 8px;
                background: #f5f5f5;
                border-radius: 8px;
            }
            #prs-import-modal::-webkit-scrollbar-thumb, #prs-products-table tbody::-webkit-scrollbar-thumb {
                background: #1a4d80;
                border-radius: 8px;
            }
            #prs-import-modal::-webkit-scrollbar-thumb:hover, #prs-products-table tbody::-webkit-scrollbar-thumb:hover {
                background: #153d66;
            }
            #prs-import-modal::-webkit-scrollbar-corner, #prs-products-table tbody::-webkit-scrollbar-corner {
                background: #f5f5f5;
            }
        </style>

        <script>
        (function($) {
            var prsProducts = [];
            var prsAjaxUrl = '../modules/servers/products_reseller_server/ajax_functions.php';

            function buildButton() {
                var contentArea = $('#contentarea');
                var firstButtonGroup = contentArea.find('.btn-group:first');

                if (contentArea.find('.btn-group').length < 2) {
                    var secondButtonGroup = $('<div>', {
                        class: 'btn-group',
                        role: 'group',
                        style: 'margin-left: 15px;',
                        html: '<a id="WHMP-Sync-Products-link" href="#" class="btn btn-default"><i class="fas fa-sync"></i>  Import/Sync Products  <span style="font-size: 0.75em; font-weight: 500; color: #6c757d; margin-top: 2px;">(By Products Reseller)</span></a>'
                    });
                    firstButtonGroup.after(secondButtonGroup);
                } else {
                    var secondButtonGroup = contentArea.find('.btn-group:eq(1)');
                    var syncButton = $('<a>', {
                        id: 'WHMP-Sync-Products-link',
                        href: '#',
                        class: 'btn btn-default',
                        html: '<i class="fas fa-sync"></i>  Import/Sync Products  <span style="font-size: 0.75em; font-weight: 500; color: #6c757d; margin-top: 2px;">(By Products Reseller)</span>'
                    });
                    secondButtonGroup.append(syncButton);
                }
            }

            function buildModal() {
                var html = '<div id="prs-import-modal-overlay" style="display:none;">'
                    + '<div id="prs-import-modal">'
                    + '<div class="prs-modal-header">'
                    + '<h3 style="color: white;"><i class="fas fa-sync-alt"></i>&nbsp; Import / Sync Products</h3>'
                    + '<span class="prs-modal-close">&times;</span>'
                    + '</div>'
                    + '<div class="prs-modal-body">'
                    + '<div class="prs-margin-section">'
                    + '<h4><i class="fas fa-percentage"></i>&nbsp; Global Profit Margin</h4>'
                    + '<div class="prs-margin-controls">'
                    + '<span class="prs-margin-label">Margin Type:</span>'
                    + '<select id="prs-global-margin-type">'
                    + '<option value="percentage">Percentage (%)</option>'
                    + '<option value="fixed">Fixed Amount</option>'
                    + '</select>'
                    + '<span class="prs-margin-label">Value:</span>'
                    + '<input type="number" id="prs-global-margin-value" value="0" min="0" step="0.01" placeholder="0">'
                    + '<button id="prs-apply-global-margin" class="prs-btn-apply"><i class="fas fa-check"></i> Apply to All</button>'
                    + '</div>'
                    + '</div>'
                    + '<div class="prs-products-section">'
                    + '<div id="prs-loading" class="prs-loading"><i class="fas fa-spinner fa-spin"></i> Loading products from provider...</div>'
                    + '<div id="prs-table-wrapper" style="display:none;">'
                    + '<div class="prs-select-bar">'
                    + '<label><input type="checkbox" id="prs-select-all" checked> &nbsp;Select / Deselect All</label>'
                    + '<span class="prs-product-count" id="prs-product-count"></span>'
                    + '</div>'
                    + '<div style="max-height:350px; overflow-y:auto; border:1px solid #e0e5ea; border-radius:4px;">'
                    + '<table id="prs-products-table">'
                    + '<thead><tr>'
                    + '<th style="width:38px;"></th>'
                    + '<th>Product Name</th>'
                    + '<th>Group</th>'
                    + '<th>Type</th>'
                    + '<th style="width:85px;">Status</th>'
                    + '<th style="width:110px;">Margin Type</th>'
                    + '<th style="width:95px;">Margin Value</th>'
                    + '<th>Base Price</th>'
                    + '<th>Final Price (with Margin)</th>'
                    + '</tr></thead>'
                    + '<tbody id="prs-products-tbody"></tbody>'
                    + '</table>'
                    + '</div>'
                    + '</div>'
                    + '<div id="prs-no-products" style="display:none;" class="prs-loading"><i class="fas fa-info-circle"></i> No products available for import.</div>'
                    + '</div>'
                    + '<div id="prs-progress-section" style="display:none; margin-top:16px;">'
                    + '<div style="font-size:13px; color:#555;"><i class="fas fa-spinner fa-spin"></i> <span id="prs-progress-text">Processing...</span></div>'
                    + '<div class="prs-progress-bar"><div class="prs-progress-fill" id="prs-progress-fill"></div></div>'
                    + '</div>'
                    + '<div id="prs-results-section" style="display:none; margin-top:16px;">'
                    + '<h5 style="color:#1A4D80; margin:0 0 8px; font-size:14px;">Results:</h5>'
                    + '<div class="prs-results-wrap" id="prs-results-list"></div>'
                    + '</div>'
                    + '</div>'
                    + '<div class="prs-modal-footer">'
                    + '<button id="prs-import-btn" class="prs-btn-primary" disabled><i class="fas fa-download"></i> Import / Sync Selected</button>'
                    + '<button class="prs-modal-cancel prs-btn-default">Cancel</button>'
                    + '</div>'
                    + '</div>'
                    + '</div>'
                    + '<div class="prs-success-popup" id="prs-success-popup" style="display: none;"></div>';

                $('body').append(html);
            }

            function escapeHtml(text) {
                if (!text) return '';
                var div = document.createElement('div');
                div.appendChild(document.createTextNode(text));
                return div.innerHTML;
            }

            function showSuccessPopup(message) {
                var $p = $('#prs-success-popup');
                $p.html('<i class="fas fa-check-circle"></i>&nbsp; ' + escapeHtml(message)).show();
                setTimeout(function() { $p.hide(); }, 4500);
            }

            function openModal() {
                $('#prs-import-modal-overlay').fadeIn(200);
                loadProducts();
            }

            function closeModal() {
                $('#prs-import-modal-overlay').fadeOut(200);
                prsProducts = [];
                $('#prs-products-tbody').empty();
                $('#prs-loading').show();
                $('#prs-table-wrapper').hide();
                $('#prs-no-products').hide();
                $('#prs-progress-section').hide();
                $('#prs-results-section').hide();
                $('#prs-import-btn').prop('disabled', true).html('<i class="fas fa-download"></i> Import / Sync Selected');
                $('.prs-modal-cancel').prop('disabled', false);
            }

            function loadProducts() {
                $('#prs-loading').show();
                $('#prs-table-wrapper').hide();
                $('#prs-no-products').hide();
                $('#prs-results-section').hide();
                $('#prs-progress-section').hide();

                $.ajax({
                    url: prsAjaxUrl,
                    type: 'POST',
                    data: { prs_doing_ajax: true, action: 'getProductsForImport' },
                    dataType: 'json',
                    success: function(response) {
                        $('#prs-loading').hide();
                        if (response.status === 'success' && response.data && response.data.length > 0) {
                            prsProducts = response.data;
                            renderProducts(prsProducts);
                            $('#prs-table-wrapper').show();
                        } else if (response.status === 'error') {
                            $('#prs-no-products').html('<i class="fas fa-exclamation-triangle"></i> ' + escapeHtml(response.message || 'Error loading products.')).show();
                        } else {
                            $('#prs-no-products').show();
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#prs-loading').hide();
                        $('#prs-no-products').html('<i class="fas fa-exclamation-triangle"></i> Failed to connect to provider. Please check server configuration.').show();
                    }
                });
            }

            function renderProducts(products) {
                var $tbody = $('#prs-products-tbody');
                $tbody.empty();

                var newCount = 0, importedCount = 0;
                $.each(products, function(i, p) {
                    var statusCls = p.is_mapped ? 'prs-status-imported' : 'prs-status-new';
                    var statusTxt = p.is_mapped ? 'Imported' : 'New';
                    if (p.is_mapped) importedCount++; else newCount++;

                    var typeLabel = p.product_type;
                    if (typeLabel === 'hostingaccount') typeLabel = 'Hosting';
                    else if (typeLabel === 'reselleraccount') typeLabel = 'Reseller';
                    else if (typeLabel === 'server') typeLabel = 'Server/VPS';
                    else typeLabel = 'Other';

                    var basePriceHtml = buildPriceHtml(p.pricing, 0, 'percentage');
                    var row = '<tr data-index="' + i + '">'
                        + '<td style="text-align:center;"><input type="checkbox" class="prs-product-check" data-index="' + i + '" checked></td>'
                        + '<td><strong>' + escapeHtml(p.product_name) + '</strong></td>'
                        + '<td>' + escapeHtml(p.product_group_name) + '</td>'
                        + '<td>' + escapeHtml(typeLabel) + '</td>'
                        + '<td><span class="' + statusCls + '">' + statusTxt + '</span></td>'
                        + '<td class="prs-inline-margin"><select class="prs-product-margin-type" data-index="' + i + '">'
                        + '<option value="percentage">Percentage (%)</option>'
                        + '<option value="fixed">Fixed</option>'
                        + '</select></td>'
                        + '<td class="prs-inline-margin"><input type="number" class="prs-product-margin-value" data-index="' + i + '" value="0" min="0" step="0.01"></td>'
                        + '<td class="prs-base-price-cell" data-index="' + i + '">' + basePriceHtml + '</td>'
                        + '<td class="prs-final-price-cell" data-index="' + i + '">' + basePriceHtml + '</td>'
                        + '</tr>';
                    $tbody.append(row);
                });

                updateSelectionCount();
                //$('#prs-select-all').prop('checked', true);
                $('#prs-product-count').text(products.length + ' product(s) — ' + newCount + ' new, ' + importedCount + ' imported');
            }

            function buildPriceHtml(pricing, marginValue, marginType) {
                if (!pricing || pricing.length === 0) return '<span style="color:#aaa;">N/A</span>';

                var cycles = ['monthly','quarterly','semiannually','annually','biennially','triennially'];
                var setupKeys = ['msetupfee','qsetupfee','ssetupfee','asetupfee','bsetupfee','tsetupfee'];
                var cycleLabels = {'monthly':'Mo','quarterly':'Qtr','semiannually':'Semi','annually':'Ann','biennially':'Bi','triennially':'Tri'};

                var html = '';
                $.each(pricing, function(i, cp) {
                    var currency = cp.currency_code || '';
                    var p = cp.pricing || {};
                    var lines = [];

                    $.each(cycles, function(ci, cycle) {
                        var price = parseFloat(p[cycle] ?? -1);
                        var setup = parseFloat(p[setupKeys[ci]] ?? 0);
                        if (price < 0) return; // skip disabled

                        // Apply margin
                        var finalPrice = price;
                        var finalSetup = setup;
                        if (marginType === 'percentage' && marginValue > 0) {
                            finalPrice = price * (1 + marginValue / 100);
                            if (setup > 0) finalSetup = setup * (1 + marginValue / 100);
                        } else if (marginType === 'fixed' && marginValue > 0) {
                            finalPrice = price + marginValue;
                        }

                        var line = '<span class="prs-price-cycle"><b>' + cycleLabels[cycle] + ':</b> '
                            + currency + ' ' + finalPrice.toFixed(2);
                        if (finalSetup > 0) {
                            line += ' <span class="prs-setup-fee">(Setup: ' + currency + ' ' + finalSetup.toFixed(2) + ')</span>';
                        }
                        line += '</span>';
                        lines.push(line);
                    });

                    if (lines.length > 0) {
                        html += '<div class="prs-currency-block">' + lines.join('') + '</div>';
                    }
                });

                return html || '<span style="color:#aaa;">N/A</span>';
            }

            function recalcFinalPrice(idx) {
                var p = prsProducts[idx];
                if (!p) return;
                var marginType = $('.prs-product-margin-type[data-index="' + idx + '"]').val();
                var marginValue = parseFloat($('.prs-product-margin-value[data-index="' + idx + '"]').val()) || 0;
                var finalHtml = buildPriceHtml(p.pricing, marginValue, marginType);
                $('.prs-final-price-cell[data-index="' + idx + '"]').html(finalHtml);
            }

            function updateSelectionCount() {
                var sel = $('.prs-product-check:checked').length;
                var total = $('.prs-product-check').length;
                $('#prs-import-btn').prop('disabled', sel === 0);
                var label = sel > 0 ? 'Import / Sync Selected (' + sel + ')' : 'Import / Sync Selected';
                $('#prs-import-btn').html('<i class="fas fa-download"></i> ' + label);
                $('#prs-select-all').prop('checked', sel === total && total > 0);
            }

            function doImportSync() {
                var selectedProducts = [];

                $('.prs-product-check:checked').each(function() {
                    var idx = $(this).data('index');
                    var p = prsProducts[idx];
                    var mType = $('.prs-product-margin-type[data-index="' + idx + '"]').val();
                    var mVal = parseFloat($('.prs-product-margin-value[data-index="' + idx + '"]').val()) || 0;

                    selectedProducts.push({
                        product_id: p.product_id,
                        product_name: p.product_name,
                        product_type: p.product_type,
                        product_group_name: p.product_group_name,
                        product_description: p.product_description,
                        product_shortdesc: p.product_shortdesc,
                        product_tagline: p.product_tagline,
                        pgroup_slug: p.pgroup_slug,
                        pgroup_headline: p.pgroup_headline,
                        pgroup_tagline: p.pgroup_tagline,
                        paytype: p.paytype,
                        is_mapped: p.is_mapped,
                        reseller_product_id: p.reseller_product_id,
                        margin_type: mType,
                        margin_value: mVal,
                        pricing: p.pricing
                    });
                });

                if (selectedProducts.length === 0) return;

                // Disable controls during processing
                $('#prs-import-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
                $('.prs-modal-cancel').prop('disabled', true);
                $('#prs-progress-section').show();
                $('#prs-results-section').hide();
                $('#prs-progress-text').text('Processing ' + selectedProducts.length + ' product(s)...');
                $('#prs-progress-fill').css('width', '15%');

                $.ajax({
                    url: prsAjaxUrl,
                    type: 'POST',
                    data: {
                        prs_doing_ajax: true,
                        action: 'importSyncProducts',
                        products: JSON.stringify(selectedProducts)
                    },
                    dataType: 'json',
                    timeout: 120000,
                    success: function(response) {
                        $('#prs-progress-fill').css('width', '100%');

                        setTimeout(function() {
                            $('#prs-progress-section').hide();
                            $('.prs-modal-cancel').prop('disabled', false);

                            if (response.results && response.results.length > 0) {
                                var html = '';
                                $.each(response.results, function(i, r) {
                                    var icon, cls;
                                    if (r.status === 'imported') { icon = 'fa-check-circle'; cls = 'prs-result-success'; }
                                    else if (r.status === 'synced') { icon = 'fa-sync-alt'; cls = 'prs-result-synced'; }
                                    else { icon = 'fa-times-circle'; cls = 'prs-result-error'; }
                                    html += '<div class="' + cls + '"><i class="fas ' + icon + '"></i> ' + escapeHtml(r.message) + '</div>';
                                });
                                $('#prs-results-list').html(html);
                                $('#prs-results-section').show();
                            }

                            if (response.status === 'success' || response.status === 'partial') {
                                showSuccessPopup(response.message);
                            }

                            // Reload products list to show updated statuses
                            loadProducts();

                        }, 600);
                    },
                    error: function(xhr, status, error) {
                        $('#prs-progress-section').hide();
                        $('.prs-modal-cancel').prop('disabled', false);
                        var errMsg = 'Request failed';
                        if (status === 'timeout') errMsg = 'Request timed out. Please try with fewer products.';
                        else if (error) errMsg += ': ' + error;
                        $('#prs-results-list').html('<div class="prs-result-error"><i class="fas fa-times-circle"></i> ' + escapeHtml(errMsg) + '</div>');
                        $('#prs-results-section').show();
                        $('#prs-import-btn').prop('disabled', false).html('<i class="fas fa-download"></i> Import / Sync Selected');
                    }
                });
            }

            $(document).ready(function() {
                buildButton();
                buildModal();

                // Open modal
                $(document).on('click', '#WHMP-Sync-Products-link', function(e) {
                    e.preventDefault();
                    openModal();
                });

                // Close modal
                $(document).on('click', '.prs-modal-close, .prs-modal-cancel', function() {
                    closeModal();
                });
                $(document).on('click', '#prs-import-modal-overlay', function(e) {
                    if ($(e.target).is('#prs-import-modal-overlay')) closeModal();
                });

                // Select all
                $(document).on('change', '#prs-select-all', function() {
                    $('.prs-product-check').prop('checked', $(this).is(':checked'));
                    updateSelectionCount();
                });

                // Individual checkbox
                $(document).on('change', '.prs-product-check', function() {
                    updateSelectionCount();
                });

                // Apply global margin
                $(document).on('click', '#prs-apply-global-margin', function() {
                    var t = $('#prs-global-margin-type').val();
                    var v = $('#prs-global-margin-value').val();
                    $('.prs-product-margin-type').val(t);
                    $('.prs-product-margin-value').val(v);
                });

                // Import / Sync button
                $(document).on('click', '#prs-import-btn', function() {
                    if (!$(this).prop('disabled')) doImportSync();
                });

                // Recalc final price on margin type change
                $(document).on('change', '.prs-product-margin-type', function() {
                    recalcFinalPrice($(this).data('index'));
                });

                // Recalc final price on margin value change
                $(document).on('input change', '.prs-product-margin-value', function() {
                    recalcFinalPrice($(this).data('index'));
                });

                // Recalc all final prices when global margin is applied
                $(document).on('click', '#prs-apply-global-margin', function() {
                    setTimeout(function() {
                        $('.prs-product-check').each(function() {
                            recalcFinalPrice($(this).data('index'));
                        });
                    }, 50);
                });
            });
        }(jQuery));
        </script>
        HTML;
    }

});

add_hook('ProductDelete', 1, function($vars) {

    if (!Capsule::table('tblservers')->where('type', 'products_reseller_server')->where('active', 1)->exists()) {
        return;
    }

    $productId = (int)($vars['pid'] ?? 0);
    if ($productId <= 0) return;

    $server_id = Capsule::table('tblservers')
        ->where('type', 'products_reseller_server')
        ->where('active', 1)
        ->value('id');

    if (!$server_id) return;

    try {
        $main_class = new ProductsReseller_Main();
        $main_class->send_request_to_api([
            'action' => 'Delete_Product_Mapping',
            'server_id' => $server_id,
            'reseller_product_id' => $productId,
        ]);
    } catch (\Exception $e) {
        logActivity("Products Reseller Server: Failed to delete product mapping for PID {$productId}: " . $e->getMessage());
    }
});