<div id="addChildModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-lg w-full">
        <h3 class="text-xl font-semibold mb-4">Add Child Items</h3>
        <form action="{{ route('production.bom.child.store', $bomParent) }}" method="POST">
            @csrf

            <!-- Manual Child Form Fields -->
            <div class="mb-4 relative">
                <label class="block text-sm font-medium">Item Code</label>
                <input type="text" name="child[0][item_code]" class="w-full border rounded px-4 py-2" required>
            </div>

            <div class="mb-4 ">
                <label class="block text-sm font-medium">Item Description</label>
                <input type="text" name="child[0][item_description]" class="w-full border rounded px-4 py-2"
                    required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Quantity</label>
                <input type="number" name="child[0][quantity]" class="w-full border rounded px-4 py-2" required>
            </div>

            <div class="mb-4 ">
                <label class="block text-sm font-medium">Measure</label>
                <input type="text" name="child[0][measure]" class="w-full border rounded px-4 py-2" required>
            </div>

            <div class="flex justify-end mt-6">
                <button type="button" onclick="toggleModal('addChildModal', false)"
                    class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mr-2">
                    Cancel
                </button>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Add Child
                </button>
            </div>
        </form>

        <!-- Excel File Upload Form -->
        <!-- <form action="{{ route('production.bom.child.upload', $bomParent) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <div class="mt-6">
                <label class="block text-sm font-medium">Upload Excel File</label>
                <input type="file" name="excel_file" class="w-full border rounded px-4 py-2" accept=".xlsx,.xls,.csv"
                    required>
            </div>

            <div class="flex justify-end mt-6">
                <button type="button" onclick="toggleModal('addChildModal', false)"
                    class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mr-2">
                    Cancel
                </button>
                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    Upload Excel
                </button>
            </div>
        </form> -->
    </div>
</div>

<script>

document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("addChildModal");

    // Debounce function to limit fetch calls
    function debounce(fn, delay) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => fn(...args), delay);
        };
    }

    // Event Listener for Input in Item Code Field
    modal.addEventListener("input", debounce(function (event) {
        if (event.target.name && event.target.name.includes("item_code")) {
            const inputField = event.target;
            const dropdown = getOrCreateDropdown(inputField);
            const query = inputField.value;

            if (query.length > 1) {
                fetchFilteredItemCodes(query, dropdown);
            } else {
                dropdown.classList.add("hidden");
            }
        }
    }, 300)); // Debounce delay of 300ms

    // Fetch Filtered Item Codes
    function fetchFilteredItemCodes(query, dropdown) {
        fetch(`/get-item-codes?query=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => updateDropdown(data, dropdown))
            .catch(error => console.error("Error fetching item codes:", error));
    }

    // Update Dropdown with Results
    function updateDropdown(items, dropdown) {
        dropdown.innerHTML = ""; // Clear existing dropdown items
        if (items.length > 0) {
            items.forEach(item => {
                const option = document.createElement("div");
                option.className = "dropdown-item p-2 hover:bg-gray-200 cursor-pointer";
                option.textContent = `${item.item_code} - ${item.item_description}`;
                option.dataset.itemCode = item.item_code;
                option.dataset.itemDescription = item.item_description;
                option.dataset.itemUom = item.uom;
                dropdown.appendChild(option);
            });
            dropdown.classList.remove("hidden");
        } else {
            dropdown.classList.add("hidden");
        }
    }

    // Handle Dropdown Item Click
    document.addEventListener("click", function (event) {
        if (event.target.classList.contains("dropdown-item")) {
            const selectedItem = event.target;
            const row = selectedItem.closest('.mb-4');  // Get the row that the item code belongs to

            // Populate the respective fields inside the row
            const itemCodeField = row.querySelector("input[name*='item_code']");
            itemCodeField.value = selectedItem.dataset.itemCode;

            // Get the next row for item_description
            const nextRow1 = row.nextElementSibling;
            const itemDescriptionField = nextRow1.querySelector("input[name*='item_description']");
            itemDescriptionField.value = selectedItem.dataset.itemDescription;

            // Get the row after that for measure
            const nextRow2 = nextRow1.nextElementSibling;
            const nextRow3 = nextRow2.nextElementSibling;
            const measureField = nextRow3.querySelector("input[name*='measure']");
            measureField.value = selectedItem.dataset.itemUom;

            // Close the dropdown
            const dropdown = row.querySelector(".dropdown");
            dropdown.classList.add("hidden");
        }
    });

    // Utility to Create or Fetch Dropdown
    function getOrCreateDropdown(inputField) {
        let dropdown = inputField.nextElementSibling;

        if (!dropdown || !dropdown.classList.contains("dropdown")) {
            dropdown = document.createElement("div");
            dropdown.className = "dropdown hidden absolute bg-white border rounded shadow-lg w-full z-10";
            inputField.parentElement.appendChild(dropdown);
        }
        return dropdown;
    }

    // Close dropdown when clicking outside
    document.addEventListener("click", function (event) {
        if (!event.target.closest(".relative")) {
            document.querySelectorAll(".dropdown").forEach(dropdown => dropdown.classList.add("hidden"));
        }
    });
});



</script>
