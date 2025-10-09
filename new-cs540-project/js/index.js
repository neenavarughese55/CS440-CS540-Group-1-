$(document).ready(function () {
  // Default hide these fields:
  $("#business-name-container").hide();
  $("#category-container").hide();
  $("#qualifications-container").hide();

  // When dropdown changes:
  $("#role").change(function () {
    if ($("#role").val() === "customer") {
      $("#business-name-container").hide();
      $("#category-container").hide();
      $("#qualifications-container").hide();
    } else if ($("#role").val() === "service-provider") {
      $("#business-name-container").show();
      $("#category-container").show();
      $("#qualifications-container").show();
    }
  });

  $("#sub-btn").click(function () {
    var usernameVal = $("#username").val();
    var usernamePattern = /^\w{5,10}$/;
    if (!usernamePattern.test(usernameVal)) {
      $("span[class='errorMsg']").text(
        "Please enter 5–10 characters or digits"
      );
      return false;
    }

    // var emailVal = $("#registered-email").val();
    // var emailPattern = /^[A-Za-z0-9.%+_]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/;
    // if (!emailPattern.test(emailVal)) {
    //   $("span[class='errorMsg']").text("Please enter a valid email format");
    //   return false;
    // }

    // var phoneNumVal = $("#registered-phonenumber").val();
    // var phoneNumPattern = /^\d{10}$|^\d{12}$/;
    // if (!phoneNumPattern.test(phoneNumVal)) {
    //   $("span[class='errorMsg']").text("Please enter a valid phoneNum format");
    //   return false;
    // }

    var passwordVal = $("#registered-password").val();
    var passwordPattern = /^\w{6,10}$/;
    if (!passwordPattern.test(passwordVal)) {
      $("span[class='errorMsg']").text(
        "Please enter 6–10 characters or digits password"
      );
      return false;
    }

    var password2Val = $("#registered-password-2").val();
    console.log("passwordVal: " + passwordVal);
    console.log("password2Val: " + password2Val);

    if (passwordVal != password2Val) {
      $("span[class='errorMsg']").text(
        "Please enter the same password in the 'Confirm Password' field!"
      );
      return false;
    }

    $("span.errorMsg").text("Validation successful");

    return true;
  });
});

function switchPanel(type) {
  const loginPanel = document.getElementById("loginPanel");
  const regPanel = document.getElementById("regPanel");
  const loginTab = document.getElementById("loginTab");
  const regTab = document.getElementById("regTab");

  if (type === "login") {
    loginPanel.classList.add("active");
    regPanel.classList.remove("active");
    loginTab.classList.add("active");
    regTab.classList.remove("active");
  } else {
    loginPanel.classList.remove("active");
    regPanel.classList.add("active");
    loginTab.classList.remove("active");
    regTab.classList.add("active");
  }
}
