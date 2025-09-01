<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="grn.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <title>Document</title>
</head>

<body>
    <div class="form-container">
        <h1 class="form-title">GRN</h1>

        <div class="space">

            <a href="/dashboard" class="nav-button">
                <button class="btn btn-primary">Add</button>
            </a>
            <a href="/reports" class="nav-button">
                <button class="btn btn-primary">Amend</button>
            </a>
            <a href="/settings" class="nav-button">
                <button class="btn btn-primary">Display</button>
            </a>
            <a href="/support" class="nav-button">
                <button class="btn btn-primary">Delete</button>
            </a>
        </div>

        <form id="headerForm" action="" method="post">
            <div class="form-row">
                <div class="form-group">
                    <div class="dropdown-container">
                        <label for="loc" class="form-label">Location</label>
                        <input type="text" class="form-control" id="loc" name="loc" autocomplete="off" required >
                        <input type="hidden" class="form-control" id="locid" name="locid" >
                        <div id="dropdownList" class="dropdown-list"></div>
                        <!-- Search input -->


                    </div>

                </div>
                <div class="form-group">
                    <label for="grnno" class="form-label">GRN NO</label>
                    <input type="text" class="form-control" id="grnno" required>
                </div>

                <div class="form-group">
                    <label for="grnddt" class="form-label">GRN DATE</label>
                    <input type="date" class="form-control" id="grnddt">
                </div>

                <div class="form-group">
                    <label for="grntime" class="form-label">GRN TIME</label>
                    <input type="time" class="form-control" id="grntime">
                </div>


            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="supnam" class="form-label">SUPPLIER NAME</label>
                    <input type="text" class="form-control" id="supnam">
                </div>
                <div class="form-group">
                    <label for="invoiceno" class="form-label">INVOICE NUMBER</label>
                    <input type="text" class="form-control" id="invoiceno">
                </div>


            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary2">Submit Header</button>
                <button type="submit" id="editHeaderBtn" style="display:none;">Edit Header</button>
        </form>
    </div>

    </form>
    <br>

    <form action="" method="post" id="grnForm">
        <table id="grnTable">
            <thead>
                <tr>
                    <th>Item Des</th>
                    <th>Unit</th>
                    <th>Quantity</th>

                    <th>Cost</th>
                    <th>vat</th>
                    <th>dis</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="text" name="item_code[]" required></td>
                    <td><input type="text" name="item_unit[]" required></td>
                    <td><input type="number" name="quantity[]" min="0" required></td>
                    <td><input type="number" name="Cost[]" min="0" step="0.01" required></td>
                    <td><input type="number" name="Vat[]" min="0" step="0.01" required></td>
                    <td><input type="number" name="Dis[]" min="0" step="0.01" required></td>
                    <td><input type="number" name="total[]" min="0" step="0.01" readonly></td>
                    <td><button type="button" class="delete-btn">×</button></td>
                </tr>
            </tbody>
        </table>

        <button type="button" id="addLineBtn">Add Line</button>
        <button type="submit">Submit GRN</button>
    </form>
    </div>

</body>
<script>
    //---------------------------------get loc---------------------------------
    $(document).ready(function () {
        $.ajax({
            url: "get_location.php",
            type: "GET",
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    $("#loc").val(response.locname);
                    $("#locid").val(response.locsno);

                }
            }
        });

    });
  //--------------------------------------------loc search
    const input = document.getElementById("loc");
    const list = document.getElementById("dropdownList");
    const hiddenId = document.getElementById("locid");

    async function fetchData(query) {
        try {
            const response = await fetch("loc_search.php?q=" + encodeURIComponent(query));
            if (!response.ok) throw new Error("HTTP error " + response.status);
            return await response.json();
        } catch (err) {
            console.error("Fetch error:", err);
            return [];
        }
    }

    // 🔹 show results in dropdown
    function showList(results) {
        list.innerHTML = "";
        if (results.length > 0) {
            list.style.display = "block";
            results.forEach(item => {
                const option = document.createElement("div");
                option.textContent = item.name;
                option.onclick = function () {
                    input.value = item.name;   // set name in input
                    hiddenId.value = item.id;  // set id in hidden field
                    list.style.display = "none";
                };
                list.appendChild(option);
            });
        } else {
            list.style.display = "none";
        }
    }

    // 🔹 when typing
    input.addEventListener("input", async function () {
        const value = this.value.trim();
        const results = await fetchData(value); // empty = all, text = filtered
        showList(results);
    });

    // 🔹 when clicking inside (show all if empty)
    input.addEventListener("focus", async function () {
        if (this.value.trim() === "") {
            const results = await fetchData(""); // fetch all
            showList(results);
        }
    });

    // 🔹 hide if click outside
    document.addEventListener("click", function (e) {
        if (!e.target.closest(".dropdown-container")) list.style.display = "none";
    });





    // // ---------------- Header / GRN toggle ---------------- //
    // document.addEventListener('DOMContentLoaded', function () {
    //     const grnForm = document.getElementById('grnForm');
    //     const submitHeaderBtn = document.querySelector('.btn-primary2');

    //     // Create Edit Header button dynamically
    //     const editHeaderBtn = document.createElement('button');
    //     editHeaderBtn.type = 'button';
    //     editHeaderBtn.textContent = 'Edit Header';
    //     editHeaderBtn.className = 'btn btn-primary1';
    //     editHeaderBtn.style.marginLeft = '10px';
    //     submitHeaderBtn.parentNode.appendChild(editHeaderBtn);

    //     // Initially hide GRN form and edit button
    //     grnForm.style.display = 'none';
    //     editHeaderBtn.style.display = 'none';

    //     function setHeaderReadonly(readonly) {
    //         const headerInputs = document.querySelectorAll('#grnno, #grnddt, #grntime, #invoiceno, #supnam');
    //         headerInputs.forEach(input => input.readOnly = readonly);
    //     }

    //     function headerFieldsFilled() {
    //         const headerInputs = document.querySelectorAll('#grnno, #grnddt, #grntime, #invoiceno, #supnam');
    //         return Array.from(headerInputs).every(input => input.value.trim() !== "");
    //     }


    // });




    // // ------------------------------------------------searchdropdown--------------------

    // const input = document.getElementById("dropdownInput");
    // const list = document.getElementById("dropdownList");
    // const hiddenId = document.getElementById("dropdownId");

    // async function fetchData(query) {
    //     try {
    //         const response = await fetch("search.php?q=" + encodeURIComponent(query));
    //         if (!response.ok) throw new Error("HTTP error " + response.status);
    //         return await response.json();
    //     } catch (err) {
    //         console.error("Fetch error:", err);
    //         return [];
    //     }
    // }

    // // 🔹 show results in dropdown
    // function showList(results) {
    //     list.innerHTML = "";
    //     if (results.length > 0) {
    //         list.style.display = "block";
    //         results.forEach(item => {
    //             const option = document.createElement("div");
    //             option.textContent = item.name;
    //             option.onclick = function () {
    //                 input.value = item.name;   // set name in input
    //                 hiddenId.value = item.id;  // set id in hidden field
    //                 list.style.display = "none";
    //             };
    //             list.appendChild(option);
    //         });
    //     } else {
    //         list.style.display = "none";
    //     }
    // }

    // // 🔹 when typing
    // input.addEventListener("input", async function () {
    //     const value = this.value.trim();
    //     const results = await fetchData(value); // empty = all, text = filtered
    //     showList(results);
    // });

    // // 🔹 when clicking inside (show all if empty)
    // input.addEventListener("focus", async function () {
    //     if (this.value.trim() === "") {
    //         const results = await fetchData(""); // fetch all
    //         showList(results);
    //     }
    // });

    // // 🔹 hide if click outside
    // document.addEventListener("click", function (e) {
    //     if (!e.target.closest(".dropdown-container")) list.style.display = "none";
    // });



    // // -----------------------------DATE AND TIME----------------



    // document.addEventListener("DOMContentLoaded", function () {
    //     // Get today's date and time
    //     const now = new Date();

    //     // Format date (YYYY-MM-DD)
    //     const date = now.toISOString().split('T')[0];

    //     // Format time (HH:MM)
    //     const time = now.toTimeString().split(' ')[0].slice(0, 5);

    //     // Set values into your inputs
    //     document.getElementById("grnddt").value = date;
    //     document.getElementById("grntime").value = time;
    // });


</script>



</html>


















































<!-- 






submitHeaderBtn.addEventListener('click', function (e) {
    e.preventDefault(); // stop page refresh

    if (headerFieldsFilled()) {
        const formData = new FormData(document.getElementById('headerForm'));

        fetch('save_header.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(data => {
            console.log("Header saved:", data);

            // Lock header fields
            setHeaderReadonly(true);

            // Show GRN lines
            grnForm.style.display = 'block';
            submitHeaderBtn.style.display = 'none';
            editHeaderBtn.style.display = 'inline-block';
        })
        .catch(err => console.error(err));
    } else {
        alert("⚠ Please fill in all header fields before proceeding.");
    }
}); -->