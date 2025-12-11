document.addEventListener("DOMContentLoaded", function () {
  // =====================================================
  // Grab dropdown filters + table rows for Admin filtering
  // =====================================================
  const providerDropdown = document.getElementById(
    "service-provider-username-dropdown"
  ); // Dropdown for filtering by service provider
  const bookedByDropdown = document.getElementById("booked-by-username"); // Dropdown for filtering by user who booked
  const table = document.getElementById("table");
  const rows = table.getElementsByTagName("tr");

  // =====================================================
  // Apply BOTH filters (service provider + booked-by user)
  // =====================================================
  function applyFilters() {
    // Selected provider (default to "any")
    const providerSelected = providerDropdown
      ? providerDropdown.value.toLowerCase()
      : "any";

    // Selected "booked by" username (default to "any")
    const bookedBySelected = bookedByDropdown
      ? bookedByDropdown.value.toLowerCase()
      : "any";

    // Loop through all table rows (skip header → start at index 1)
    for (let i = 1; i < rows.length; i++) {
      const providerText = rows[i].cells[1].textContent.toLowerCase(); // Column #1 = Provider name
      const bookedByText = rows[i].cells[6].textContent.toLowerCase(); // Column #6 = Booked By username

      // Determine whether row matches selected provider
      const matchProvider =
        providerSelected === "any" || providerText === providerSelected;

      // Determine whether row matches selected "booked by" user
      const matchBookedBy =
        bookedBySelected === "any" || bookedByText === bookedBySelected;

      // Show row only if BOTH conditions match
      if (matchProvider && matchBookedBy) {
        rows[i].style.display = "";
      } else {
        rows[i].style.display = "none";
      }
    }
  }

  // =====================================================
  // Attach filter event listeners to dropdowns
  // =====================================================
  if (providerDropdown)
    providerDropdown.addEventListener("change", applyFilters);

  if (bookedByDropdown)
    bookedByDropdown.addEventListener("change", applyFilters);

  // =====================================================
  // Handle "Cancel" button click → Fill hidden form inputs
  // =====================================================
  document.querySelectorAll(".cancel-button").forEach(function (button) {
    button.addEventListener("click", function () {
      const row = this.closest("tr"); // Get the row belonging to clicked button

      // Extract the hidden appointment ID
      document.getElementById("appointment_id").value = row
        .querySelector(".appointment_id")
        .textContent.trim();

      // Extract slot ID
      document.getElementById("slot_id").value = row
        .querySelector(".slot_id")
        .textContent.trim();

      // Extract booked-by username
      document.getElementById("username").value = row
        .querySelector(".booked_by")
        .textContent.trim();

      // Extract notes (reason)
      document.getElementById("notes").value = row
        .querySelector(".notes")
        .textContent.trim();

      // Extract service provider name
      document.getElementById("service_provider").value = row
        .querySelector(".service_provider")
        .textContent.trim();

      // Submit hidden form to backend (cancel.php)
      document.getElementById("myForm").submit();
    });
  });
});
