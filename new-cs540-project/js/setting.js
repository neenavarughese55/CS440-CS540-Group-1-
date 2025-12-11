document.addEventListener("DOMContentLoaded", function () {
  // ================================
  // Grab elements used for role-based UI control
  // ================================
  const roleSelect = document.getElementById("role");
  const businessNamePanel = document.getElementById("business-name-panel");
  const categoryPanel = document.getElementById("category-panel");

  // ======================================================
  // Show/Hide Business Name & Category panels depending
  // on whether the user selects "service-provider"
  // ======================================================
  function togglePanel() {
    if (roleSelect.value === "service-provider") {
      // Show provider-specific fields
      businessNamePanel.style.display = "block";
      categoryPanel.style.display = "block";
    } else {
      // Hide them for customers/admins
      businessNamePanel.style.display = "none";
      categoryPanel.style.display = "none";
    }
  }

  // Run visibility logic once when page loads
  togglePanel();

  // Run visibility logic again whenever role dropdown changes
  roleSelect.addEventListener("change", togglePanel);

  // ======================================================
  //  SAVE BUTTON — FORM VALIDATION BEFORE SUBMITTING
  // ======================================================
  document.getElementById("save-btn").addEventListener("click", function (e) {
    e.preventDefault(); // Prevent <a> link default navigation

    // Get input fields
    const username = document.getElementById("username");
    const email = document.getElementById("email");

    // Validate username not empty
    if (!username.value.trim()) {
      alert("Username cannot be empty.");
      username.focus();
      return; // do NOT submit form
    }

    // Validate email not empty
    if (!email.value.trim()) {
      alert("Email cannot be empty.");
      email.focus();
      return;
    }

    // Simple email validation requiring '@'
    if (!email.value.includes("@")) {
      alert("Email must contain '@'.");
      email.focus();
      return;
    }

    // If all checks pass → submit form
    document.getElementById("myForm").submit();
  });
});
