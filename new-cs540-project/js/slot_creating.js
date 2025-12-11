window.addEventListener("DOMContentLoaded", () => {
  // ================================
  // Grab form elements for date/time
  // ================================
  const startDate = document.getElementById("start-date");
  const startTime = document.getElementById("start-time");
  const endDate = document.getElementById("end-date");
  const endTime = document.getElementById("end-time");

  // ================================
  // Set the minimum date (start + end)
  // Users cannot select a past date
  // ================================
  const now = new Date();
  const todayStr = now.toISOString().split("T")[0];
  startDate.min = todayStr;
  endDate.min = todayStr;

  // ==========================================================
  // Convert date input + time input → JavaScript Date object
  // Returns null if incomplete
  // ==========================================================
  function toDate(dateInput, timeInput) {
    if (!dateInput.value || !timeInput.value) return null;
    return new Date(`${dateInput.value}T${timeInput.value}:00`);
  }

  // ==================================================================
  // Prevent selecting past time when user selects today's date
  // Enforces minimum time equal to current time (for today only)
  // ==================================================================
  function enforceTodayTime(dateInput, timeInput) {
    if (dateInput.value === todayStr) {
      const hours = now.getHours().toString().padStart(2, "0");
      const minutes = now.getMinutes().toString().padStart(2, "0");
      timeInput.min = `${hours}:${minutes}`;
    } else {
      timeInput.removeAttribute("min");
    }
  }

  // ==========================================================
  // Validate Start Date + Time
  // - Start cannot be in the past
  // - Set minimum for end date based on start date
  // ==========================================================
  function validateStart() {
    enforceTodayTime(startDate, startTime);

    const start = toDate(startDate, startTime);
    if (!start) return;

    // Prevent selecting a past start time
    if (start < now) {
      alert("Start time cannot be in the past.");
      startTime.value = "";
    }

    // Ensure user cannot set an end date before the start date
    endDate.min = startDate.value;

    // If both dates match, force end time >= start time
    if (endDate.value === startDate.value) {
      endTime.min = startTime.value;
    } else {
      endTime.removeAttribute("min");
    }
  }

  // ==========================================================
  // Validate End Date + Time
  // - End must not be earlier than Start
  // ==========================================================
  function validateEnd() {
    enforceTodayTime(endDate, endTime);

    const start = toDate(startDate, startTime);
    const end = toDate(endDate, endTime);
    if (!start || !end) return;

    if (end < start) {
      alert("End time cannot be earlier than Start time.");
      endTime.value = "";
    }
  }

  // ================================
  // Attach validation listeners
  // ================================
  startDate.addEventListener("change", validateStart);
  startTime.addEventListener("change", validateStart);

  endDate.addEventListener("change", validateEnd);
  endTime.addEventListener("change", validateEnd);

  // ==========================================================
  // Convert <input type="time"> into <select> with 15-min steps
  // This ensures consistent input on all browsers
  // ==========================================================
  document.querySelectorAll('input[type="time"]').forEach(function (input) {
    // Create replacement <select>
    const select = document.createElement("select");

    // Copy original attributes
    select.name = input.name;
    select.id = input.id;
    select.className = input.className;
    if (input.required) select.required = true;

    select.classList.add("time-select");

    // Helper to format hours/minutes
    const pad = (n) => String(n).padStart(2, "0");

    // Generate time slots: every 15 minutes in 24h format
    for (let h = 0; h < 24; h++) {
      for (let m = 0; m < 60; m += 15) {
        const value = `${pad(h)}:${pad(m)}`; // Example: "09:15"
        const option = document.createElement("option");

        option.value = value;
        option.textContent = value; // Display format

        // Keep selected value if original input had one
        if (input.value && input.value.slice(0, 5) === value) {
          option.selected = true;
        }

        select.appendChild(option);
      }
    }

    // Replace <input type="time"> with the new <select>
    input.replaceWith(select);
  });
});
