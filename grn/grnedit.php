<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css-grn12.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>GRN</title>
    <style>
        .dropdown-list {
            position: absolute;
            background: #fff;
            border: 1px solid #ccc;
            display: none;
            max-height: 150px;
            overflow-y: auto;
            z-index: 1000;
        }
        .dropdown-item {
            padding: 5px;
            cursor: pointer;
        }
        .dropdown-item:hover {
            background: #f0f0f0;
        }
        .dropdown-container {
            position: relative;
        }
        .delete-btn12 {
            background-color: #f44336;
            color: #ffffff;
            border: none;
            border-radius: 3px;
            padding: 6px 12px;
            font-weight: 500;
            cursor: pointer;
        }
        .delete-btn12:hover {
            background-color: #d32f2f;
        }
        .btn-primary2 {
            padding: 8px 16px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1 class="form-title">GRN</h1>

         <div class="space">
            <a href="./grn.php"><button class="btn btn-primary">Add</button></a>
            <a href="./grnedit.php"><button class="btn btn-primary">Amend</button></a>
            <a href="./disnew.php"><button class="btn btn-primary">Display</button></a>
            <a href="/support"><button class="btn btn-primary">Delete</button></a>
        </div>
        <br>
        <br>


        <!-- Search Form -->
        <form id="searchForm" class="form-container">
            <div class="form-row" style="align-items: flex-end;">
                <div class="form-group" style="flex: 1;">
                    <label for="searchId" class="form-label">Enter GRN No</label>
                    <input type="text" class="form-control2 search-input" id="searchId" name="searchId" autocomplete="off" required>
                    <input type="hidden" class="search-id" name="searchId">
                    <div class="dropdown-list search-list"></div>
                </div>
                <div class="form-group" style="width: auto;">
                    <button type="submit" class="btn btn-primary2">Search</button>
                </div>
            </div>
        </form>

        <!-- Header Form -->
        <form id="headerForm" class="form-container">
            <div class="form-row">
                <div class="form-group">
                    <div class="dropdown-container">
                        <label for="loc">Location</label>
                        <input type="text" class="form-control loc-input" name="loc" autocomplete="off" required>
                        <input type="hidden" class="loc-id" name="locid">
                        <div class="dropdown-list loc-list"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="grnddt">GRN Date</label>
                    <input type="date" class="form-control" id="grnddt" name="grnddt">
                </div>
                <div class="form-group">
                    <label for="grntime">GRN Time</label>
                    <input type="time" class="form-control" id="grntime">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <div class="dropdown-container">
                        <label for="supnam">Supplier</label>
                        <input type="text" class="form-control sup-input" name="supnam" autocomplete="off">
                        <input type="hidden" class="sup-id" name="supid">
                        <div class="dropdown-list sup-list"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="invoiceno">Invoice Number</label>
                    <input type="text" class="form-control" name="invoiceno" id="invoiceno">
                </div>
                <div class="form-group">
                    <label for="inhremarks">Remarks</label>
                    <input type="text" class="form-control" id="inhremarks" name="inhremarks">
                </div>
                <div class="form-group">
                    <input type="hidden" id="grandTotal" name="grandTotal">
                </div>
            </div>
        </form>

        <!-- Summary Form -->
        <form id="summaryForm" style="margin-top:20px; border:1px solid #ccc; padding:10px; width:100%;">
            <h3>GRN Summary</h3>
            <div style="display:flex; gap:20px; flex-wrap:wrap;">
                <div>
                    <label>Total Quantity</label><br>
                    <input type="number" id="sumQuantity" class="form-control" readonly>
                </div>
                <div>
                    <label>Total Cost</label><br>
                    <input type="number" id="sumCost" class="form-control" readonly>
                </div>
                <div>
                    <label>Total VAT</label><br>
                    <input type="number" id="sumVat" class="form-control" readonly>
                </div>
                <div>
                    <label>Total Discount</label><br>
                    <input type="number" id="sumDiscount" class="form-control" readonly>
                </div>
                <div>
                    <label>Grand Total</label><br>
                    <input type="number" id="grandTotal1" name="grandTotal" class="form-control" readonly>
                </div>
            </div>
        </form>

        <!-- GRN Detail Form -->
        <form id="grnForm" class="form-container" style="display:none;">
            <table id="grnTable">
                <thead>
                    <tr>
                        <th>Item Des</th>
                        <th>Unit</th>
                        <th>Quantity</th>
                        <th>Cost</th>
                        <th>VAT (%)</th>
                        <th>Discount</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="dropdown-cell">
                            <div class="dropdown-container">
                                <input type="text" class="form-control1 itemdes-input" autocomplete="off" required>
                                <input type="hidden" class="item-id" name="itemid[]">
                                <div class="dropdown-list item-list"></div>
                            </div>
                        </td>
                        <td>
                            <div class="select-control-wrapper">
                                <select class="select-control unitDropdown" name="unit[]">
                                    <option value="">Select Unit</option>
                                </select>
                            </div>
                        </td>
                        <td><input type="number" name="quantity[]" min="0" required></td>
                        <td><input type="number" name="Cost[]" min="0" step="0.01" class="cost" required></td>
                        <td><input type="number" name="Vat[]" min="0" step="0.01" class="vat"></td>
                        <td><input type="number" name="Dis[]" min="0" step="0.01" class="dis"></td>
                        <td><input type="number" name="total[]" min="0" step="0.01" class="total" readonly></td>
                        <td><button type="button" class="delete-btn12">×</button></td>
                    </tr>
                </tbody>
            </table>
            <button type="button" id="addLineBtn" class="btn btn-primary2">Add Line</button>
            <button type="submit" id="submitButton" class="btn btn-primary2">Submit</button>
        </form>

        <!-- Message Area -->
        <div class="message"></div>
    </div>

    <script>
        $(document).ready(function () {
            console.log("Page loaded at " + new Date().toLocaleString() + ", initializing dropdown and form handlers");

            // Set default date and time
            const now = new Date();
            $("#grnddt").val(now.toISOString().split('T')[0]);
            $("#grntime").val(now.toTimeString().slice(0, 5));

            // Fetch units for dropdowns
            let unitsData = [];
            $.ajax({
                url: "get_unit.php",
                type: "GET",
                dataType: "json",
                success: function (response) {
                    if (response.status === "success") {
                        unitsData = response.units;
                        $(".unitDropdown").each(function () {
                            populateUnitDropdown($(this));
                        });
                        console.log("Units fetched:", unitsData);
                    } else {
                        console.warn("Failed to fetch units:", response.message);
                        $(".message").html('<p style="color:red;">Failed to fetch units: ' + (response.message || "Unknown error") + '</p>');
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Unit fetch error:", { status, error, responseText: xhr.responseText });
                    $(".message").html('<p style="color:red;">Error fetching units: ' + error + '</p>');
                }
            });

            // Populate unit dropdown
            function populateUnitDropdown($select) {
                const selectedValue = $select.val();
                $select.empty().append('<option value="">Select Unit</option>');
                unitsData.forEach(unit => {
                    $select.append(`<option value="${unit.syssno}" ${unit.syssno === selectedValue ? 'selected' : ''}>${unit.sysdes1}</option>`);
                });
            }

            // Initialize item search for default row
            $("#grnTable tbody tr").each(function () {
                initializeItemSearch($(this));
            });

            // Initialize dropdown search for GRN, location, and supplier
            initializeDropdownSearch(".search-input", ".search-id", ".search-list", "searchgrnno.php");
            initializeDropdownSearch(".loc-input", ".loc-id", ".loc-list", "search_loc.php");
            initializeDropdownSearch(".sup-input", ".sup-id", ".sup-list", "search_sup.php");

            // Search form submission
            $("#searchForm").submit(function (e) {
                e.preventDefault();
                var grnId = $(".search-id").val();
                console.log("Search form submitted with GRN ID:", grnId);

                if (!grnId) {
                    $(".message").html('<p style="color:red;">Please select a valid GRN number.</p>');
                    console.warn("No GRN ID provided");
                    return;
                }

                $.ajax({
                    url: "fill.php",
                    type: "POST",
                    data: { id: grnId },
                    dataType: "json",
                    beforeSend: function () {
                        console.log("Sending AJAX request to fill.php with ID:", grnId);
                        $(".message").html('<p style="color:blue;">Loading GRN details...</p>');
                    },
                    success: function (response) {
                        console.log("AJAX response from fill.php:", response);
                        if (response.status === "success") {
                            let data = response.data;

                            // Fill Header fields (all editable)
                            $(".loc-input").val(data.LocationName || "");
                            $(".loc-id").val(data.INHLOCSNO || "");
                            $("#grnddt").val(data.INHDDT || "");
                            $("#grntime").val(data.INHTIME || "00:00");
                            $(".sup-input").val(data.SupplierName || "");
                            $(".sup-id").val(data.INHSUPSNO || "");
                            $("#invoiceno").val(data.INHINVNO || "");
                            $("#inhremarks").val(data.INHREM || "");
                            $("#grandTotal").val(data.INHTOT || "0.00");
                            $("#grnForm").css("display", "block");
                            console.log("Header fields populated:", {
                                INHLOCSNO: data.INHLOCSNO,
                                LocationName: data.LocationName,
                                INHDDT: data.INHDDT,
                                INHTIME: data.INHTIME,
                                INHSUPSNO: data.INHSUPSNO,
                                SupplierName: data.SupplierName,
                                INHINVNO: data.INHINVNO,
                                INHREM: data.INHREM,
                                INHTOT: data.INHTOT
                            });

                            // Fill Line Items
                            let totalQty = 0, totalCost = 0, totalVat = 0, totalDis = 0;
                            let tbody = $("#grnTable tbody");
                            tbody.empty();
                            if (Array.isArray(data.details) && data.details.length > 0) {
                                data.details.forEach(function (item, index) {
                                    totalQty += parseFloat(item.INLQTY || 0);
                                    totalCost += parseFloat(item.INLCOST || 0) * parseFloat(item.INLQTY || 0);
                                    totalVat += parseFloat(item.INLVAT || 0);
                                    totalDis += parseFloat(item.INLDIS || 0);
                                    tbody.append(`
                                        <tr>
                                            <td class="dropdown-cell">
                                                <div class="dropdown-container">
                                                    <input type="text" class="form-control1 itemdes-input" value="${item.ItemName || ''}" autocomplete="off" required>
                                                    <input type="hidden" class="item-id" name="itemid[]" value="${item.INLSTKSNO || ''}">
                                                    <div class="dropdown-list item-list"></div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="select-control-wrapper">
                                                    <select class="select-control unitDropdown" name="unit[]">
                                                        <option value="${item.INLUNTSNO || ''}" selected>${item.UnitName || 'Select Unit'}</option>
                                                    </select>
                                                </div>
                                            </td>
                                            <td><input type="number" name="quantity[]" value="${item.INLQTY || '0'}" min="0" required></td>
                                            <td><input type="number" name="Cost[]" value="${item.INLCOST || '0.00'}" min="0" step="0.01" class="cost" required></td>
                                            <td><input type="number" name="Vat[]" value="${item.INLVAT || '0.00'}" min="0" step="0.01" class="vat"></td>
                                            <td><input type="number" name="Dis[]" value="${item.INLDIS || '0.00'}" min="0" step="0.01" class="dis"></td>
                                            <td><input type="number" name="total[]" value="${item.INLTOTAL || '0.00'}" min="0" step="0.01" class="total" readonly></td>
                                            <td><button type="button" class="delete-btn12">×</button></td>
                                        </tr>
                                    `);
                                    console.log(`Line item ${index + 1} populated:`, {
                                        INLLNO: item.INLLNO,
                                        INLSTKSNO: item.INLSTKSNO,
                                        ItemName: item.ItemName,
                                        INLUNTSNO: item.INLUNTSNO,
                                        UnitName: item.UnitName,
                                        INLQTY: item.INLQTY,
                                        INLCOST: item.INLCOST,
                                        INLVAT: item.INLVAT,
                                        INLDIS: item.INLDIS,
                                        INLTOTAL: item.INLTOTAL
                                    });
                                    initializeItemSearch(tbody.find("tr:last"));
                                    populateUnitDropdown(tbody.find("tr:last .unitDropdown"));
                                });
                            } else {
                                tbody.append(`
                                    <tr>
                                        <td class="dropdown-cell">
                                            <div class="dropdown-container">
                                                <input type="text" class="form-control1 itemdes-input" autocomplete="off" required>
                                                <input type="hidden" class="item-id" name="itemid[]">
                                                <div class="dropdown-list item-list"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="select-control-wrapper">
                                                <select class="select-control unitDropdown" name="unit[]">
                                                    <option value="">Select Unit</option>
                                                </select>
                                            </div>
                                        </td>
                                        <td><input type="number" name="quantity[]" min="0" required></td>
                                        <td><input type="number" name="Cost[]" min="0" step="0.01" class="cost" required></td>
                                        <td><input type="number" name="Vat[]" min="0" step="0.01" class="vat"></td>
                                        <td><input type="number" name="Dis[]" min="0" step="0.01" class="dis"></td>
                                        <td><input type="number" name="total[]" min="0" step="0.01" class="total" readonly></td>
                                        <td><button type="button" class="delete-btn12">×</button></td>
                                    </tr>
                                `);
                                initializeItemSearch(tbody.find("tr:last"));
                                populateUnitDropdown(tbody.find("tr:last .unitDropdown"));
                            }
                            $("#sumQuantity").val(totalQty.toFixed(2));
                            $("#sumCost").val(totalCost.toFixed(2));
                            $("#sumVat").val(totalVat.toFixed(2));
                            $("#sumDiscount").val(totalDis.toFixed(2));
                            $("#grandTotal1").val(data.INHTOT || "0.00");
                            console.log("Summary fields populated:", {
                                totalQty: totalQty.toFixed(2),
                                totalCost: totalCost.toFixed(2),
                                totalVat: totalVat.toFixed(2),
                                totalDis: totalDis.toFixed(2),
                                grandTotal: data.INHTOT
                            });
                            $(".message").html('<p style="color:green;">GRN details loaded successfully. Edit fields and click Submit to save.</p>');
                        } else {
                            $(".message").html(`<p style="color:red;">${response.message || 'GRN not found!'}</p>`);
                            console.warn("GRN not found:", response.message);
                            $("#grnForm").css("display", "none");
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("Fill AJAX error:", { status, error, responseText: xhr.responseText });
                        $(".message").html(`<p style="color:red;">Error fetching GRN details: ${xhr.status} ${error}</p>`);
                        $("#grnForm").css("display", "none");
                    }
                });
            });

            // Submit button handler
            $("#grnForm").submit(function (e) {
                e.preventDefault();
                let grnId = $(".search-id").val();
                if (!grnId) {
                    $(".message").html('<p style="color:red;">Please select a valid GRN number before submitting.</p>');
                    console.warn("No GRN ID provided for submission");
                    return;
                }

                let headerData = {
                    id: grnId,
                    locid: $(".loc-id").val(),
                    invoiceno: $("#invoiceno").val(),
                    grnddt: $("#grnddt").val(),
                    grntime: $("#grntime").val(),
                    inhremarks: $("#inhremarks").val() || null,
                    supid: $(".sup-id").val() || null,
                    grandTotal: $("#grandTotal1").val()
                };

                if (!headerData.locid) {
                    $(".message").html('<p style="color:red;">Please select a valid location.</p>');
                    console.warn("No location selected");
                    return;
                }

                let lineItems = [];
                let validRows = true;
                $("#grnTable tbody tr").each(function (index) {
                    let itemid = $(this).find(".item-id").val();
                    let unit = $(this).find(".unitDropdown").val();
                    let quantity = $(this).find("input[name='quantity[]']").val();
                    let cost = $(this).find(".cost").val();
                    if (itemid && unit && quantity && cost) {
                        lineItems.push({
                            itemid: itemid,
                            unit: unit,
                            quantity: quantity,
                            cost: cost,
                            vat: $(this).find(".vat").val() || "0.00",
                            dis: $(this).find(".dis").val() || "0.00",
                            total: $(this).find(".total").val() || "0.00"
                        });
                    } else {
                        validRows = false;
                        console.warn(`Invalid row ${index}:`, { itemid, unit, quantity, cost });
                    }
                });

                if (!validRows || lineItems.length === 0) {
                    $(".message").html('<p style="color:red;">Please ensure all line items have valid item, unit, quantity, and cost.</p>');
                    console.warn("No valid line items to submit");
                    return;
                }

                let formData = {
                    header: headerData,
                    lines: lineItems
                };

                console.log("Submitting data:", JSON.stringify(formData, null, 2));

                $.ajax({
                    url: "edit.php",
                    type: "POST",
                    data: JSON.stringify(formData),
                    contentType: "application/json",
                    dataType: "json",
                    beforeSend: function () {
                        console.log("Sending AJAX request to edit.php");
                        $(".message").html('<p style="color:blue;">Saving changes...</p>');
                    },
                    success: function (response) {
                        console.log("AJAX response from edit.php:", response);
                        if (response.status === "success") {
                            $(".message").html('<p style="color:green;">GRN updated successfully.</p>');
                            $("#headerForm")[0].reset();
                            $("#grnForm")[0].reset();
                            $("#grnForm").css("display", "none");
                            $("#searchId").val("");
                            $(".search-id").val("");
                            $("#sumQuantity, #sumCost, #sumVat, #sumDiscount, #grandTotal1").val("0.00");
                            console.log("Form reset after successful submission");
                        } else {
                            $(".message").html(`<p style="color:red;">${response.message || 'Failed to save changes!'}</p>`);
                            console.warn("Save failed:", response.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("Submit AJAX error:", { status, error, responseText: xhr.responseText });
                        $(".message").html(`<p style="color:red;">Error saving changes: ${xhr.status} ${error}</p>`);
                    }
                });
            });

            // Add new row
            $("#addLineBtn").click(function () {
                let newRow = `
                    <tr>
                        <td class="dropdown-cell">
                            <div class="dropdown-container">
                                <input type="text" class="form-control1 itemdes-input" autocomplete="off" required>
                                <input type="hidden" class="item-id" name="itemid[]">
                                <div class="dropdown-list item-list"></div>
                            </div>
                        </td>
                        <td>
                            <div class="select-control-wrapper">
                                <select class="select-control unitDropdown" name="unit[]">
                                    <option value="">Select Unit</option>
                                </select>
                            </div>
                        </td>
                        <td><input type="number" name="quantity[]" min="0" required></td>
                        <td><input type="number" name="Cost[]" min="0" step="0.01" class="cost" required></td>
                        <td><input type="number" name="Vat[]" min="0" step="0.01" class="vat"></td>
                        <td><input type="number" name="Dis[]" min="0" step="0.01" class="dis"></td>
                        <td><input type="number" name="total[]" min="0" step="0.01" class="total" readonly></td>
                        <td><button type="button" class="delete-btn12">×</button></td>
                    </tr>`;
                $("#grnTable tbody").append(newRow);
                initializeItemSearch($("#grnTable tbody tr:last"));
                populateUnitDropdown($("#grnTable tbody tr:last .unitDropdown"));
                setTimeout(updateSummaryForm, 50);
                console.log("New line added");
            });

            // Delete row
            $(document).on("click", ".delete-btn12", function () {
                $(this).closest("tr").remove();
                updateSummaryForm();
                console.log("Row deleted");
            });

            // Calculate row total
            function calculateRowTotal(row) {
                let quantity = parseFloat(row.find('input[name="quantity[]"]').val()) || 0;
                let cost = parseFloat(row.find('.cost').val()) || 0;
                let vat = parseFloat(row.find('.vat').val()) || 0;
                let discount = parseFloat(row.find('.dis').val()) || 0;
                let total = quantity * cost;
                if (vat > 0) total += total * (vat / 100);
                total -= discount;
                row.find('.total').val(total.toFixed(2));
            }

            // Update summary form
            function updateSummaryForm() {
                let totalQuantity = 0, totalCost = 0, totalVat = 0, totalDiscount = 0, grandTotal = 0;
                $("#grnTable tbody tr").each(function () {
                    let quantity = parseFloat($(this).find('input[name="quantity[]"]').val()) || 0;
                    let cost = parseFloat($(this).find('.cost').val()) || 0;
                    let vat = parseFloat($(this).find('.vat').val()) || 0;
                    let discount = parseFloat($(this).find('.dis').val()) || 0;
                    let rowTotal = parseFloat($(this).find('.total').val()) || 0;
                    totalQuantity += quantity;
                    totalCost += quantity * cost;
                    totalVat += vat;
                    totalDiscount += discount;
                    grandTotal += rowTotal;
                });
                $("#sumQuantity").val(totalQuantity.toFixed(2));
                $("#sumCost").val(totalCost.toFixed(2));
                $("#sumVat").val(totalVat.toFixed(2));
                $("#sumDiscount").val(totalDiscount.toFixed(2));
                $("#grandTotal1").val(grandTotal.toFixed(2));
                $("#grandTotal").val(grandTotal.toFixed(2));
            }

            // Handle input changes for calculations
            $("#grnTable").on("input", "input[name='quantity[]'], .cost, .vat, .dis", function () {
                let row = $(this).closest("tr");
                calculateRowTotal(row);
                updateSummaryForm();
            });

            // Generic dropdown search
            function initializeDropdownSearch(inputSelector, hiddenSelector, listSelector, url) {
                const input = $(inputSelector);
                const hidden = $(hiddenSelector);
                const list = $(listSelector);

                async function fetchData(query) {
                    try {
                        console.log("Fetching dropdown data from:", url, "with query:", query);
                        const response = await fetch(url + "?q=" + encodeURIComponent(query));
                        if (!response.ok) throw new Error("HTTP error " + response.status);
                        const data = await response.json();
                        console.log("Dropdown data received:", data);
                        return data;
                    } catch (err) {
                        console.error("Dropdown fetch error:", err);
                        $(".message").html('<p style="color:red;">Error fetching data: ' + err.message + '</p>');
                        return [];
                    }
                }

                function showList(results) {
                    list.html("");
                    if (results.length > 0) {
                        list.show();
                        results.forEach(item => {
                            const displayText = item.name || item.INHDNO || (item.name + (item.code ? '-' + item.code : ''));
                            const option = $("<div>").addClass("dropdown-item").text(displayText);
                            option.on("click", function () {
                                input.val(displayText);
                                hidden.val(item.id || item.INHSNO);
                                list.hide();
                                console.log("Selected item:", { name: displayText, id: item.id || item.INHSNO });
                            });
                            list.append(option);
                        });
                    } else {
                        list.html('<div class="dropdown-item">No results found</div>');
                        list.show();
                    }
                }

                input.on("input", async function () {
                    const results = await fetchData($(this).val().trim());
                    showList(results);
                });

                input.on("focus", async function () {
                    if ($(this).val().trim() === "") {
                        console.log("Focus event, fetching all data for:", url);
                        const results = await fetchData("");
                        showList(results);
                    }
                });

                $(document).on("click", function (e) {
                    if (!$(e.target).closest(".dropdown-container").length) {
                        list.hide();
                        console.log("Dropdown hidden due to click outside");
                    }
                });
            }

            // Item search for each row
            function initializeItemSearch(row) {
                const input = row.find(".itemdes-input");
                const hidden = row.find(".item-id");
                const list = row.find(".item-list");

                async function fetchData(query) {
                    try {
                        const response = await fetch("search_item.php?q=" + encodeURIComponent(query));
                        if (!response.ok) throw new Error("HTTP error " + response.status);
                        const data = await response.json();
                        console.log("Item search data received:", data);
                        return data;
                    } catch (err) {
                        console.error("Item fetch error:", err);
                        $(".message").html('<p style="color:red;">Error fetching items: ' + err.message + '</p>');
                        return [];
                    }
                }

                function showList(results) {
                    list.html("");
                    if (results.length > 0) {
                        list.show();
                        results.forEach(item => {
                            const option = $("<div>").addClass("dropdown-item").text(item.name + "-" + item.code);
                            option.on("click", function () {
                                input.val(item.name + "--[" + item.code + "]");
                                hidden.val(item.id);
                                list.hide();
                                console.log("Selected item:", { name: item.name, code: item.code, id: item.id });
                            });
                            list.append(option);
                        });
                    } else {
                        list.html('<div class="dropdown-item">No results found</div>');
                        list.show();
                    }
                }

                input.on("input", async function () {
                    const results = await fetchData($(this).val().trim());
                    showList(results);
                });

                input.on("focus", async function () {
                    if ($(this).val().trim() === "") {
                        const results = await fetchData("");
                        showList(results);
                    }
                });
            }
        });
    </script>
</body>
</html>