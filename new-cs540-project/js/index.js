$(document).ready(function () {
  // ===============================
  // INITIAL HIDE FOR REGISTER FIELDS
  // These fields should only show when role = service-provider
  // ===============================
  $("#business-name-container").hide();
  $("#category-container").hide();
  $("#qualifications-container").hide();

  // ===============================
  // ROLE DROPDOWN — SHOW/HIDE FIELDS
  // ===============================
  $("#role").change(function () {
    // If user chooses "customer", hide provider-specific fields
    if ($("#role").val() === "customer") {
      $("#business-name-container").hide();
      $("#category-container").hide();
      $("#qualifications-container").hide();

      // If user chooses "service-provider", show additional fields
    } else if ($("#role").val() === "service-provider") {
      $("#business-name-container").show();
      $("#category-container").show();
      $("#qualifications-container").show();
    }
  });

  // ===============================
  // REGISTRATION FORM VALIDATION
  // ===============================
  $("#sub-btn").click(function () {
    // --- Validate username ---
    var usernameVal = $("#username").val();
    var usernamePattern = /^\w{5,10}$/; // 5–10 letters/digits/underscore

    if (!usernamePattern.test(usernameVal)) {
      $("span[class='errorMsg']").text(
        "Please enter 5–10 characters or digits"
      );
      return false; // prevent form submission
    }

    // --- Validate first password ---
    var passwordVal = $("#registered-password").val();
    var passwordPattern = /^\w{6,10}$/; // 6–10 characters

    if (!passwordPattern.test(passwordVal)) {
      $("span[class='errorMsg']").text(
        "Please enter 6–10 characters or digits password"
      );
      return false; // stop submission
    }

    // --- Validate confirm password ---
    var password2Val = $("#registered-password-2").val();
    console.log("passwordVal: " + passwordVal);
    console.log("password2Val: " + password2Val);

    if (passwordVal != password2Val) {
      $("span[class='errorMsg']").text(
        "Please enter the same password in the 'Confirm Password' field!"
      );
      return false; // stop submission
    }

    // Success message (before submitting)
    $("span.errorMsg").text("Validation successful");

    return true; // allow form submission
  });
});

// ========================================
// FUNCTION: SWITCH BETWEEN LOGIN/REGISTER PANELS
// Called when clicking “Login” or “Register” tabs
// ========================================
function switchPanel(type) {
  const loginPanel = document.getElementById("loginPanel");
  const regPanel = document.getElementById("regPanel");
  const loginTab = document.getElementById("loginTab");
  const regTab = document.getElementById("regTab");

  if (type === "login") {
    // Show login panel, hide registration panel
    loginPanel.classList.add("active");
    regPanel.classList.remove("active");

    // Highlight login tab
    loginTab.classList.add("active");
    regTab.classList.remove("active");
  } else {
    // Show registration panel, hide login panel
    loginPanel.classList.remove("active");
    regPanel.classList.add("active");

    // Highlight registration tab
    loginTab.classList.remove("active");
    regTab.classList.add("active");
  }
}
