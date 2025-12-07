document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".deactivate-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      activate.call(this, "0");
    });
  });

  function activate(isActive) {
    // Step 1: get the row
    const row = this.closest("tr");

    // Step 2: get the username text inside that row
    const username = row.querySelector(".username").textContent.trim();

    // Step 3: send it to the API path
    fetch("../api/activate_user.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body:
        "username=" +
        encodeURIComponent(username) +
        "&isActive=" +
        encodeURIComponent(isActive),
    })
      .then((res) => res.text())
      .then((msg) => {
        alert(msg);
      });
  }
});
