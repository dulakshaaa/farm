<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css-grn12.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>GRN Display</title>
</head>
<body>
    <div class="form-container">
        <h1 class="form-title">GRN Details</h1>

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
                    <input type="text" class="form-control2 search-input" id="searchId" name="searchId" required>
                    <input type="hidden" class="search-id" name="searchId">
                    <div class="dropdown-list search-list"></div>
                </div>
                <div class="form-group" style="width: auto;">
                    <button type="submit" class="btn btn-primary1">Search</button>
                </div>
            </div>
        </form>

        <!-- Header Display -->
        <form id="headerForm" class="form-container">
            <div class="form-row">
                <div class="form-group">
                    <label for="loc">Location</label>
                    <input type="text" class="form-control loc-input" name="loc" readonly>
                    <input type="hidden" class="loc-id" name="locid">
                </div>
                <div class="form-group">
                    <label for="grnddt">GRN Date</label>
                    <input type="date" class="form-control" id="grnddt" name="grnddt" readonly>
                </div>
                <div class="form-group">
                    <label for="grntime">GRN Time</label>
                    <input type="time" class="form-control" id="grntime" readonly>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="supnam">Supplier</label>
                    <input type="text" class="form-control sup-input" name="supnam" readonly>
                    <input type="hidden" class="sup-id" name="supid">
                </div>
                <div class="form-group">
                    <label for="invoiceno">Invoice Number</label>
                    <input type="text" class="form-control" name="invoiceno" id="invoiceno" readonly>
                </div>
                <div class="form-group">
                    <label for="inhremarks">Remarks</label>
                    <input type="text" class="form-control" id="inhremarks" name="inhremarks" readonly>
                </div>
            </div>
        </form>

        <!-- Summary Display -->
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
                    <input type="number" id="grandTotal" name="grandTotal" class="form-control" readonly>
                </div>
            </div>
        </form>

        <!-- Line Items Table -->
        <form id="grnForm" class="form-container">
            <table id="grnTable">
                <thead>
                    <tr>
                        <th>Item Description</th>
                        <th>Unit</th>
                        <th>Quantity</th>
                        <th>Cost</th>
                        <th>VAT</th>
                        <th>Discount</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </form>

        <!-- Message Area -->
        <div class="message"></div>
    </div>

    <script>
        $(document).ready(function () {
            console.log("Page loaded, initializing dropdown and form handlers");

            initializeDropdownSearch(".search-input", ".search-id", ".search-list", "searchgrnno.php");

            $("#searchForm").submit(function (e) {
                e.preventDefault();
                var grnId = $(".search-id").val();
                console.log("Form submitted with GRN ID:", grnId);

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

                            $(".loc-input").val(data.LocationName || "N/A");
                            $(".loc-id").val(data.INHLOCSNO || "");
                            $("#grnddt").val(data.INHDDT || "");
                            $("#grntime").val(data.INHTIME || "00:00");
                            $(".sup-input").val(data.SupplierName || "N/A");
                            $(".sup-id").val(data.INHTYPSNO || "");
                            $("#invoiceno").val(data.INHINVNO || "");
                            $("#inhremarks").val(data.INHREM || "");
                            $("#grandTotal").val(data.INHTOT || "0.00");
                            console.log("Header fields populated:", {
                                LocationName: data.LocationName,
                                INHDDT: data.INHDDT,
                                INHTIME: data.INHTIME,
                                SupplierName: data.SupplierName,
                                INHINVNO: data.INHINVNO,
                                INHREM: data.INHREM,
                                INHTOT: data.INHTOT
                            });

                            let totalQty = 0, totalCost = 0, totalVat = 0, totalDis = 0;
                            if (Array.isArray(data.details)) {
                                data.details.forEach(function (item) {
                                    totalQty += parseFloat(item.INLQTY || 0);
                                    totalCost += parseFloat(item.INLCOST || 0) * parseFloat(item.INLQTY || 0);
                                    totalVat += parseFloat(item.INLVAT || 0);
                                    totalDis += parseFloat(item.INLDIS || 0);
                                });
                            }
                            $("#sumQuantity").val(totalQty.toFixed(2));
                            $("#sumCost").val(totalCost.toFixed(2));
                            $("#sumVat").val(totalVat.toFixed(2));
                            $("#sumDiscount").val(totalDis.toFixed(2));
                            console.log("Summary fields populated:", {
                                totalQty: totalQty.toFixed(2),
                                totalCost: totalCost.toFixed(2),
                                totalVat: totalVat.toFixed(2),
                                totalDis: totalDis.toFixed(2)
                            });

                            let tbody = $("#grnTable tbody");
                            tbody.empty();
                            if (Array.isArray(data.details) && data.details.length > 0) {
                                data.details.forEach(function (item, index) {
                                    tbody.append(`
                                        <tr>
                                            <td><input type="text" class="form-control1" value="${item.ItemName || 'N/A'}" readonly></td>
                                            <td><input type="text" class="form-control1" value="${item.UnitName || 'N/A'}" readonly></td>
                                            <td><input type="number" value="${item.INLQTY || '0.00'}" readonly></td>
                                            <td><input type="number" value="${item.INLCOST || '0.00'}" readonly></td>
                                            <td><input type="number" value="${item.INLVAT || '0.00'}" readonly></td>
                                            <td><input type="number" value="${item.INLDIS || '0.00'}" readonly></td>
                                            <td><input type="number" value="${item.INLTOTAL || '0.00'}" readonly></td>
                                        </tr>
                                    `);
                                    console.log(`Line item ${index + 1} populated:`, item);
                                });
                            } else {
                                tbody.append('<tr><td colspan="7">No line items found.</td></tr>');
                                console.warn("No line items found in response");
                            }
                            $(".message").html('<p style="color:green;">GRN details loaded successfully.</p>');
                        } else {
                            $(".message").html(`<p style="color:red;">${response.message || 'GRN not found!'}</p>`);
                            console.warn("GRN not found:", response.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX error:", {
                            status: status,
                            error: error,
                            responseText: xhr.responseText
                        });
                        $(".message").html(`<p style="color:red;">Error fetching GRN details: ${xhr.status} ${error}</p>`);
                    }
                });
            });
        });

        function initializeDropdownSearch(inputSelector, hiddenSelector, listSelector, url) {
            const input = $(inputSelector);
            const hidden = $(hiddenSelector);
            const list = $(listSelector);

            async function fetchData(query) {
                try {
                    console.log("Fetching dropdown data from:", url, "with query:", query);
                    const response = await fetch(url + "?q=" + encodeURIComponent(query));
                    if (!response.ok) {
                        throw new Error("HTTP error " + response.status);
                    }
                    const data = await response.json();
                    console.log("Dropdown data received:", data);
                    return data;
                } catch (err) {
                    console.error("Dropdown fetch error:", err);
                    $(".message").html('<p style="color:red;">Error fetching GRN numbers: ' + err.message + '</p>');
                    return [];
                }
            }

            function showList(results) {
                list.html("");
                if (results.length > 0) {
                    list.show();
                    results.forEach(item => {
                        const option = $("<div>").addClass("dropdown-item").text(item.name || item.INHDNO);
                        option.on("click", function () {
                            input.val(item.name || item.INHDNO);
                            hidden.val(item.id || item.INHSNO);
                            list.hide();
                            console.log("Selected GRN:", {
                                name: item.name || item.INHDNO,
                                id: item.id || item.INHSNO
                            });
                        });
                        list.append(option);
                    });
                } else {
                    list.html('<div class="dropdown-item">No results found</div>');
                    list.show();
                    console.warn("No dropdown results found");
                }
            }

            input.on("input", async function () {
                const query = $(this).val().trim();
                console.log("Input event, query:", query);
                const results = await fetchData(query);
                showList(results);
            });

            input.on("focus", async function () {
                if ($(this).val().trim() === "") {
                    console.log("Focus event, fetching all GRNs");
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
    </script>
</body>
</html>