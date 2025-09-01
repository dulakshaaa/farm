<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="grn.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>GRN</title>
    <style>
        /* Dropdown basic styles */
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
    </style>
</head>

<body>
    <div class="form-container">
        <h1 class="form-title">GRN</h1>

        <!-- Navigation buttons -->
        <div class="space">
            <a href="/dashboard"><button class="btn btn-primary">Add</button></a>
            <a href="/reports"><button class="btn btn-primary">Amend</button></a>
            <a href="/settings"><button class="btn btn-primary">Display</button></a>
            <a href="/support"><button class="btn btn-primary">Delete</button></a>
        </div>

        <!-- HEADER FORM -->
        <form id="headerForm" action="" method="post">
            <div class="form-row">
                <!-- Location with dropdown -->
                <div class="form-group">
                    <div class="dropdown-container">
                        <label for="loc">Location</label>
                        <input type="text" class="form-control loc-input" name="loc" autocomplete="off" required>
                        <input type="hidden" class="loc-id" name="locid">
                        <div class="dropdown-list loc-list"></div>
                    </div>
                </div>

                <!-- GRN number -->
                <div class="form-group">
                    <label for="grnno">GRN NO</label>
                    <input type="text" class="form-control" id="grnno" required>
                </div>

                <!-- GRN date -->
                <div class="form-group">
                    <label for="grnddt">GRN DATE</label>
                    <input type="date" class="form-control" id="grnddt">
                </div>

                <!-- GRN time -->
                <div class="form-group">
                    <label for="grntime">GRN TIME</label>
                    <input type="time" class="form-control" id="grntime">
                </div>
            </div>

            <div class="form-row">
                <!-- Supplier with dropdown -->
                <div class="form-group">
                    <div class="dropdown-container">
                        <label for="supnam">Supplier</label>
                        <input type="text" class="form-control sup-input" name="supnam" autocomplete="off" required>
                        <input type="hidden" class="sup-id" name="supid">
                        <div class="dropdown-list sup-list"></div>
                    </div>
                </div>


                <!-- Invoice number -->
                <div class="form-group">
                    <label for="invoiceno">INVOICE NUMBER</label>
                    <input type="text" class="form-control" id="invoiceno">
                </div>

                <!-- Remarks -->
                <div class="form-group">
                    <label for="inhremarks">Remarks</label>
                    <input type="text" class="form-control" id="inhremarks">
                </div>
                <div class="form-group">
                    
                    <input type="hidden" id="grandTotal" name="grandTotal" readonly>
                </div>
            </div>

            <!-- Buttons for locking/unlocking header -->
            <div class="form-actions">
                <button type="button" id="okHeaderBtn" class="btn btn-primary2">OK</button>
                <button type="button" id="editHeaderBtn" class="btn btn-primary2" style="display:none;">Edit
                    Header</button>
            </div>
        </form>
        <form id="summaryForm" style="margin-top:20px; border:1px solid #ccc; padding:10px; width:100%;">
            <h3>GRN Summary</h3>
            <div style="display:flex; gap:20px; flex-wrap:wrap;">
                <div>
                    <label>Total Quantity</label><br>
                    <input type="number" id="sumQuantity" readonly>
                </div>
                <div>
                    <label>Total Cost</label><br>
                    <input type="number" id="sumCost" readonly>
                </div>
                <div>
                    <label>Total VAT</label><br>
                    <input type="number" id="sumVat" readonly>
                </div>
                <div>
                    <label>Total Discount</label><br>
                    <input type="number" id="sumDiscount" readonly>
                </div>
                <div>
                    <label>Grand Total</label><br>
                    <input type="number" id="grandTotal" name="grandTotal" readonly>
                </div>
            </div>
        </form>




        <br>

        <!-- GRN DETAIL FORM (hidden initially) -->
        <form action="" method="post" id="grnForm" style="display:none;">
            <table id="grnTable">
                <thead>
                    <tr>
                        <th>Item Des</th>
                        <th>Unit</th>
                        <th>Quantity</th>
                        <th>Cost</th>
                        <th>VAT</th>
                        <th>Discount</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Default first row -->
                    <tr>
                        <td class="dropdown-cell">
                            <div class="dropdown-container">
                                <input type="text" class="form-control itemdes-input" autocomplete="off" required>
                                <input type="hidden" class="item-id">
                                <div class="dropdown-list item-list"></div>
                            </div>
                        </td>
                        <td><input type="text" name="item_unit[]" required></td>
                        <td><input type="number" name="quantity[]" min="0" required></td>
                        <td><input type="number" name="Cost[]" min="0" step="0.01" class="cost" required></td>
                        <td><input type="number" name="Vat[]" min="0" step="0.01" class="vat" required></td>
                        <td><input type="number" name="Dis[]" min="0" step="0.01" class="dis" required></td>
                        <td><input type="number" name="total[]" min="0" step="0.01" class="total" readonly></td>
                        <td><button type="button" class="delete-btn">×</button></td>
                    </tr>
                </tbody>
            </table>
            <!-- Add line + submit -->
            <button type="button" id="addLineBtn">Add Line</button>
            <button type="submit">Submit GRN</button>
        </form>

        <!-- Message area -->
        <div class="message"></div>
    </div>

    <script>
        $(document).ready(function () {
            // ------------------ SET DATE & TIME ------------------
            const now = new Date();
            $("#grnddt").val(now.toISOString().split('T')[0]); // YYYY-MM-DD
            $("#grntime").val(now.toTimeString().slice(0, 5));  // HH:MM

            // ------------------ GET LOCATION ------------------
            $.getJSON("get_location.php", function (response) {
                if (response.status === "success") {
                    $(".loc-input").val(response.locname);
                    $(".loc-id").val(response.locsno);
                }
            });

            $.ajax({
                url: "get_units.php",
                type: "GET",
                dataType: "json",
                success: function (response) {
                    if (response.status === "success") {
                        unitsData = response.data;
                    }
                }
            });

            // When a new row is added → fill the dropdown
            function populateUnitDropdown($select) {
                $select.empty().append('<option value="">-- Select Unit --</option>');
                unitsData.forEach(unit => {
                    $select.append(`<option value="${unit.syssno}">${unit.sysname}</option>`);
                });
            }

            // ------------------ INIT SEARCH FOR DEFAULT ROW ------------------
            $("#grnTable tbody tr").each(function () {
                initializeItemSearch($(this));
            });

            // ------------------ ADD NEW ROW ------------------
            $("#addLineBtn").click(function () {
                let newRow = `<tr>
                    <td>
                        <div class="dropdown-container">
                            <input type="text" class="form-control1 itemdes-input" autocomplete="off" required>
                            <input type="hidden" class="item-id">
                            <div class="dropdown-list item-list"></div>
                        </div>
                    </td>
                    <td><input type="text" name="item_unit[]" class="unt-input" required>
                    <input type="hidden" class="unt-id"></td>
                    <td><input type="number" name="quantity[]" min="0" required></td>
                    <td><input type="number" name="Cost[]" min="0" step="0.01" class="cost" required></td>
                    <td><input type="number" name="Vat[]" min="0" step="0.01" class="vat" required></td>
                    <td><input type="number" name="Dis[]" min="0" step="0.01" class="dis" required></td>
                    <td><input type="number" name="total[]" min="0" step="0.01" class="total" readonly></td>
                    <td><button type="button" class="delete-btn">×</button></td>
                </tr>`;
                $("#grnTable tbody").append(newRow);
                // Enable search on new row
                initializeItemSearch($("#grnTable tbody tr:last"));
            });

            // ------------------ DELETE ROW ------------------
            $(document).on("click", ".delete-btn", function () {
                $(this).closest("tr").remove();
            });

            // ------------------ LOCK HEADER ------------------
            $("#okHeaderBtn").click(function () {
                let isValid = true;
                $("#headerForm input").each(function () {
                    if ($(this).val().trim() === "") {
                        isValid = false;
                        $(this).focus();
                        return false;
                    }
                });
                if (!isValid) {
                    alert("Please fill all header fields before proceeding.");
                    return;
                }
                // Make header readonly
                $("#headerForm input").prop("readonly", true);
                $("#headerForm select").prop("disabled", true);
                $("#okHeaderBtn").hide();
                $("#editHeaderBtn").show();
                $("#grnForm").show();
            });

            // ------------------ EDIT HEADER ------------------
            $("#editHeaderBtn").click(function () {
                $("#headerForm input").prop("readonly", false);
                $("#headerForm select").prop("disabled", false);
                $("#okHeaderBtn").show();
                $(this).hide();
            });

            // ------------------ SUBMIT GRN ------------------
            $("#grnForm").submit(async function (e) {
                e.preventDefault();
                $(".message").html("");

                try {
                    // Save header first
                    let headerData = $("#headerForm").serialize();
                    let headerResponse = await $.ajax({
                        url: 'save_header.php',
                        method: 'POST',
                        data: headerData,
                        dataType: 'json'
                    });

                    if (headerResponse.status !== 'success') {
                        $(".message").html("Failed to save header!");
                        return;
                    }

                    // Save GRN lines
                    let grnData = $(this).serialize() + "&header_id=" + headerResponse.id;
                    let grnResponse = await $.ajax({
                        url: 'save_grn.php',
                        method: 'POST',
                        data: grnData,
                        dataType: 'json'
                    });

                    if (grnResponse.status === 'success') {
                        $(".message").html("GRN and header saved successfully!");
                        $("#headerForm")[0].reset();
                        $("#grnForm")[0].reset();
                        $("#grnForm").hide();
                        $("#okHeaderBtn").show();
                        $("#editHeaderBtn").hide();
                        $("#headerForm input").prop("readonly", false);
                        $("#headerForm select").prop("disabled", false);
                    } else {
                        $(".message").html("Failed to save GRN lines!");
                    }

                } catch (err) {
                    console.error(err);
                    $(".message").html("Error submitting forms!");
                }
            });

            // ------------------ INIT DROPDOWNS FOR HEADER ------------------
            initializeDropdownSearch(".loc-input", ".loc-id", ".loc-list", "search_loc.php");
            initializeDropdownSearch(".sup-input", ".sup-id", ".sup-list", "search_sup.php");
        });

        // ------------------ GENERIC DROPDOWN SEARCH FUNCTION ------------------
        function initializeDropdownSearch(inputSelector, hiddenSelector, listSelector, url) {
            const input = $(inputSelector);
            const hidden = $(hiddenSelector);
            const list = $(listSelector);

            async function fetchData(query) {
                try {
                    const response = await fetch(url + "?q=" + encodeURIComponent(query));
                    if (!response.ok) throw new Error("HTTP error " + response.status);
                    return await response.json();
                } catch (err) {
                    console.error("Fetch error:", err);
                    return [];
                }
            }

            function showList(results) {
                list.html("");
                if (results.length > 0) {
                    list.show();
                    results.forEach(item => {
                        const option = $("<div>").addClass("dropdown-item").text(item.name + item.code);
                        option.on("click", function () {
                            input.val(item.name + item.code);
                            hidden.val(item.id);
                            list.hide();
                        });
                        list.append(option);
                    });
                } else list.hide();
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

            $(document).on("click", function (e) {
                if (!$(e.target).closest(".dropdown-container").length) list.hide();
            });
        }

        //----------------- ITEM SEARCH FOR EACH ROW ------------------
        function initializeItemSearch(row) {
            const input = row.find(".itemdes-input");
            const hidden = row.find(".item-id");
            const list = row.find(".item-list");

            async function fetchData(query) {
                try {
                    const response = await fetch("search_item.php?q=" + encodeURIComponent(query));
                    if (!response.ok) throw new Error("HTTP error " + response.status);
                    return await response.json();
                } catch (err) {
                    console.error("Fetch error:", err);
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
                            input.val(item.name + "--" + "[" + item.code + "]");
                            hidden.val(item.id);
                            list.hide();
                        });
                        list.append(option);
                    });
                } else list.hide();
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

            $(document).on("click", function (e) {
                if (!$(e.target).closest(".dropdown-container").length) list.hide();
            });
        }
        // ------------------ CALCULATE TOTAL FOR EACH ROW ------------------
        function calculateRowTotal(row) {
            let quantity = parseFloat(row.find('input[name="quantity[]"]').val()) || 0;
            let cost = parseFloat(row.find('.cost').val()) || 0;
            let vat = parseFloat(row.find('.vat').val()) || 0;
            let discount = parseFloat(row.find('.dis').val()) || 0;

            let total = quantity * cost;

            if (vat > 0) total += total * (vat / 100); // VAT still percentage
            total -= discount; // Discount is now a fixed amount

            row.find('.total').val(total.toFixed(2));
        }

        // Event delegation for input changes in GRN lines
        $('#grnTable').on('input', 'input[name="quantity[]"], .cost, .vat, .dis', function () {
            let row = $(this).closest('tr');
            calculateRowTotal(row);
        });



        //----------------------------------external table update
        function updateSummaryForm() {
            let totalQuantity = 0;
            let totalCost = 0;
            let totalVat = 0;
            let totalDiscount = 0;
            let grandTotal = 0;

            $('#grnTable tbody tr').each(function () {
                let quantity = parseFloat($(this).find('input[name="quantity[]"]').val()) || 0;
                let cost = parseFloat($(this).find('.cost').val()) || 0;
                let vat = parseFloat($(this).find('.vat').val()) || 0;
                let discount = parseFloat($(this).find('.dis').val()) || 0;
                let rowTotal = parseFloat($(this).find('.total').val()) || 0;

                totalQuantity += quantity;
                totalCost += quantity * cost;   // cost before VAT/discount
                totalVat += vat;
                totalDiscount += discount;
                grandTotal += rowTotal;         // after VAT/discount
            });

            $('#sumQuantity').val(totalQuantity);
            $('#sumCost').val(totalCost.toFixed(2));
            $('#sumVat').val(totalVat.toFixed(2));
            $('#sumDiscount').val(totalDiscount.toFixed(2));
            $('#grandTotal').val(grandTotal.toFixed(2));
        }

        // Update summary when row values change
        $('#grnTable').on('input', 'input[name="quantity[]"], .cost, .vat, .dis', function () {
            let row = $(this).closest('tr');
            calculateRowTotal(row);
            updateSummaryForm();
        });

        // Update summary when row is added or deleted
        $('#addLineBtn').click(function () { setTimeout(updateSummaryForm, 50); });
        $('#grnTable').on('click', '.delete-btn', function () { setTimeout(updateSummaryForm, 50); });

        // Initialize summary on page load
        updateSummaryForm();




    </script>
</body>

</html>