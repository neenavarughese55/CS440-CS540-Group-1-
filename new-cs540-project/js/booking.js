document.addEventListener("DOMContentLoaded", function () {
  // Get category dropdown element (may not exist on some pages)
  const categoryDropdown = document.getElementById("category-dropdown");

  // Get the table and all rows inside it
  const table = document.getElementById("myTable");
  const rows = table.getElementsByTagName("tr");

  /**
   * ============================
   *  FILTER TABLE BY CATEGORY
   * ============================
   */
  function applyFilters() {
    // Use selected category, or "any" if dropdown doesn't exist
    const categorySelected = categoryDropdown
      ? categoryDropdown.value.toLowerCase()
      : "any";

    // Start from row 1 to skip table header
    for (let i = 1; i < rows.length; i++) {
      // Column 4 contains category text
      const categoryText = rows[i].cells[4].textContent.toLowerCase();

      // Determine if the category matches or "any" is selected
      const matchcategory =
        categorySelected === "any" || categoryText === categorySelected;

      // Show row only if category matches
      rows[i].style.display = matchcategory ? "" : "none";
    }
  }

  // Apply filter when dropdown changes (if dropdown exists)
  if (categoryDropdown)
    categoryDropdown.addEventListener("change", applyFilters);

  /**
   * ============================
   *  BOOK BUTTON CLICK HANDLER
   * ============================
   * Fills hidden inputs with row data and submits the form.
   */
  document.querySelectorAll(".book").forEach(function (button) {
    button.addEventListener("click", function () {
      const row = this.closest("tr"); // Locate the row of the clicked button

      // Fill hidden form inputs with values from the selected row
      document.getElementById("slot_id").value = row
        .querySelector(".slot_id")
        .textContent.trim();

      document.getElementById("provider_id").value = row
        .querySelector(".provider_id")
        .textContent.trim();

      document.getElementById("business_name").value = row
        .querySelector(".business_name")
        .textContent.trim();

      document.getElementById("category_id").value = row
        .querySelector(".category_id")
        .textContent.trim();

      document.getElementById("category_name").value = row
        .querySelector(".category_name")
        .textContent.trim();

      document.getElementById("notes").value = row
        .querySelector(".notes")
        .textContent.trim();

      document.getElementById("start_time").value = row
        .querySelector(".start_time")
        .textContent.trim();

      document.getElementById("end_time").value = row
        .querySelector(".end_time")
        .textContent.trim();

      // Submit the form after the fields have been filled
      document.getElementById("myForm").submit();
    });
  });

  /**
   * ============================
   *  SEARCH FUNCTION FOR TABLE
   * ============================
   * Filters rows based on text typed into searchInput.
   */
  function searchTable() {
    const input = document.getElementById("searchInput").value.toLowerCase();
    const table = document.getElementById("myTable");
    const rows = table.getElementsByTagName("tr");

    // Loop through all rows except header
    for (let i = 1; i < rows.length; i++) {
      const cells = rows[i].getElementsByTagName("td");
      let rowContainsSearch = false;

      // Check each cell to see if it contains the search term
      for (let j = 0; j < cells.length; j++) {
        if (cells[j].textContent.toLowerCase().includes(input)) {
          rowContainsSearch = true;
          break;
        }
      }

      // Show / hide row based on search match
      rows[i].style.display = rowContainsSearch ? "" : "none";
    }
  }

  // Get the search input field (may not exist on some pages)
  const searchInput = document.getElementById("searchInput");

  // If no search input exists, stop script execution safely
  if (!searchInput) return;

  // Run search while user types
  searchInput.addEventListener("keyup", searchTable);
});
