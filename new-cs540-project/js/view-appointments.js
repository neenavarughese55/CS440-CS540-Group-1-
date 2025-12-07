document.addEventListener("DOMContentLoaded", function () {
  const dropdown = document.getElementById("booked-by-username");
  const table = document.getElementById("table");
  const rows = table.getElementsByTagName("tr");

  dropdown.addEventListener("change", () => {
    const selected = dropdown.value.toLowerCase();

    for (let i = 1; i < rows.length; i++) {
      // skip header row (i = 0)
      const bookedByCell = rows[i].cells[5]; // 3rd column: "Booked By"
      const bookedByText = bookedByCell.textContent.toLowerCase();

      if (selected === "any" || bookedByText === selected) {
        rows[i].style.display = "";
      } else {
        rows[i].style.display = "none";
      }
    }
  });

  // Submit form:
  document.querySelectorAll(".cancel-button").forEach(function (button) {
    button.addEventListener("click", function () {
      const row = this.closest("tr");

      document.getElementById("appointment_id").value = row
        .querySelector(".appointment_id")
        .textContent.trim();
      console.log(
        "appointment_id: " + document.getElementById("appointment_id").value
      );

      document.getElementById("slot_id").value = row
        .querySelector(".slot_id")
        .textContent.trim();
      console.log("slot_id: " + document.getElementById("slot_id").value);

      document.getElementById("username").value = row
        .querySelector(".booked_by")
        .textContent.trim();
      console.log("username: " + document.getElementById("username").value);

      document.getElementById("myForm").submit();
    });
  });
});
