document.addEventListener("DOMContentLoaded", function () {
  // ======================================================
  // ADD CLICK HANDLER FOR USER REPORT "RUN REPORT" BUTTON
  // ======================================================
  document.querySelectorAll("#user-report-run-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      // Call run() and tell it we are running a user report
      run.call(this, "user-report");
    });
  });

  // ============================================================
  // ADD CLICK HANDLER FOR APPOINTMENT REPORT "RUN REPORT" BUTTON
  // ============================================================
  document.querySelectorAll("#appointment-report-run-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      // Call run() and indicate appointment report
      run.call(this, "appointment-report");
    });
  });

  // ======================================================
  // MAIN FUNCTION TO RUN EITHER REPORT TYPE
  // ======================================================
  function run(reportType) {
    // Pick the correct PHP file based on report type
    let fileName =
      reportType === "user-report"
        ? "user_report.php"
        : "appointment_report.php";

    // Get filter elements for User Report by default
    let categoryEl = document.getElementById(
      "appointment-report-category-dropdown"
    );
    let usernameEl = document.getElementById("user-report-username-dropdown");
    let fromEl = document.getElementById("user-report-from");
    let toEl = document.getElementById("user-report-to");

    // Override filters if Appointment Report
    if (fileName == "appointment_report.php") {
      usernameEl = document.getElementById(
        "appointment-report-username-dropdown"
      );

      fromEl = document.getElementById("appointment-report-from");
      toEl = document.getElementById("appointment-report-to");
    }

    // Read selected filter values (fallbacks included)
    const category = categoryEl ? categoryEl.value : "ALL";
    const username = usernameEl ? usernameEl.value : "ALL";
    const fromDate = fromEl ? fromEl.value : "";
    const toDate = toEl ? toEl.value : "";

    // ================================================
    // BUILD HTTP POST BODY
    // Using URLSearchParams ensures proper formatting
    // ================================================
    const params = new URLSearchParams();
    params.append("reportType", reportType);
    params.append("username", username);
    params.append("from", fromDate);
    params.append("to", toDate);

    if (fileName == "appointment_report.php") {
      params.append("category", category);
    }

    // ===========================
    // SEND AJAX REQUEST TO API
    // ===========================
    fetch("/cs540project/api/" + fileName, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: params.toString(),
    })
      .then((res) => res.text())
      .then((msg) => {
        // Debug: raw PHP output before JSON parsing
        console.log("DEBUG RAW RESPONSE:", msg);

        let data;
        try {
          data = JSON.parse(msg); // Attempt JSON decode
        } catch (e) {
          console.error("JSON ERROR:", e, msg);
          return; // Stop if PHP output is invalid JSON
        }

        // If debugging info exists, display it in console
        if (data.debug) {
          console.log("SQL DEBUG:", data.debug);
        }

        // Actual result rows
        data = data.rows;

        const table = document.getElementById("table");
        table.innerHTML = "<thead></thead><tbody></tbody>";

        const thead = table.querySelector("thead");
        const tbody = table.querySelector("tbody");

        // If no rows returned → inform user
        if (!Array.isArray(data) || data.length === 0) {
          const tr = document.createElement("tr");
          const td = document.createElement("td");
          td.textContent = "No data found.";
          tr.appendChild(td);
          tbody.appendChild(tr);
          return;
        }

        // Get column names dynamically from JSON object
        const columns = Object.keys(data[0]);

        // ===========================
        // BUILD TABLE HEADER
        // ===========================
        const headerRow = document.createElement("tr");
        columns.forEach((col) => {
          const th = document.createElement("th");

          // Convert snake_case → HUMAN READABLE
          let column = col.replace(/_/g, " ").toUpperCase();
          th.textContent = column;

          headerRow.appendChild(th);
        });
        thead.appendChild(headerRow);

        // ===========================
        // BUILD TABLE BODY
        // ===========================
        data.forEach((row) => {
          const tr = document.createElement("tr");

          columns.forEach((col) => {
            const td = document.createElement("td");

            if (row[col] !== null && row[col] !== undefined) {
              let cellData = row[col];

              // Clean up special values
              if (col == "is_active") {
                cellData = cellData == "1" ? "Yes" : "No";
              } else if (col == "role") {
                if (cellData == "customer") cellData = "Customer";
                else if (cellData == "service-provider")
                  cellData = "Service Provider";
                else if (cellData == "admin") cellData = "Admin";
              } else if (col == "created_at") {
                cellData = formatDateTime(cellData);
              }

              td.textContent = cellData;
            } else {
              td.textContent = ""; // Empty cell
            }

            tr.appendChild(td);
          });

          tbody.appendChild(tr);
        });
      })
      .catch((err) => {
        console.error(err);
        alert("Error running report.");
      });
  }

  // ======================================================
  // HELPER: FORMAT DATETIME INTO FRIENDLY FORMAT
  // Fixes Safari bugs and ensures AM/PM formatting
  // ======================================================
  function formatDateTime(datetimeString) {
    if (!datetimeString || typeof datetimeString !== "string") {
      return "";
    }

    // Convert "YYYY-MM-DD HH:MM:SS" → "YYYY-MM-DDTHH:MM:SS"
    const cleaned = datetimeString.replace(" ", "T");

    const date = new Date(cleaned);

    if (isNaN(date.getTime())) {
      return "";
    }

    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");

    let hours = date.getHours();
    const minutes = String(date.getMinutes()).padStart(2, "0");

    const ampm = hours >= 12 ? "PM" : "AM";

    hours = hours % 12;
    if (hours === 0) hours = 12;
    hours = String(hours).padStart(2, "0");

    return `${year}-${month}-${day} ${hours}:${minutes} ${ampm}`;
  }
});
