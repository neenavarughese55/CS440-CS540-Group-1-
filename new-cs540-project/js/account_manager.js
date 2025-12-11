document.addEventListener("DOMContentLoaded", function () {
  // "Activate Button" event:
  document.querySelectorAll(".activate-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      activate.call(this, "1");
    });
  });

  // "Deactivate Button" event:
  document.querySelectorAll(".deactivate-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      activate.call(this, "0");
    });
  });

  function activate(isActive) {
    // Step 1: get the row
    const row = this.closest("tr");

    // Step 2: get the "username" text and "role" text inside that row
    const username = row.querySelector(".username").textContent.trim();
    const role = row.querySelector(".role").textContent.trim();
    const userid = row.querySelector(".id").textContent.trim();

    // Step 3: send it to the API path
    fetch("/cs540project/api/activate_user.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body:
        "username=" +
        encodeURIComponent(username) +
        "&role=" +
        encodeURIComponent(role) +
        "&userid=" +
        encodeURIComponent(userid) +
        "&isActive=" +
        encodeURIComponent(isActive),
    })
      .then((res) => res.text())
      .then((msg) => {
        alert(msg);
        location.reload(); // reload page
      });
  }
});
