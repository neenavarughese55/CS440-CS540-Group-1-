window.addEventListener("DOMContentLoaded", () => {
  const startDate = document.getElementById("start-date");
  const startTime = document.getElementById("start-time");
  const endDate = document.getElementById("end-date");
  const endTime = document.getElementById("end-time");

  // --- Set minimum date = today ---
  const now = new Date();
  const todayStr = now.toISOString().split("T")[0];
  startDate.min = todayStr;
  endDate.min = todayStr;

  // Convert date + time input to JS Date
  function toDate(dateInput, timeInput) {
    if (!dateInput.value || !timeInput.value) return null;
    return new Date(`${dateInput.value}T${timeInput.value}:00`);
  }

  // Prevent selecting past time for the selected date
  function enforceTodayTime(dateInput, timeInput) {
    if (dateInput.value === todayStr) {
      const hours = now.getHours().toString().padStart(2, "0");
      const minutes = now.getMinutes().toString().padStart(2, "0");
      timeInput.min = `${hours}:${minutes}`;
    } else {
      timeInput.removeAttribute("min");
    }
  }

  // Validate Start Date + Time
  function validateStart() {
    enforceTodayTime(startDate, startTime);

    const start = toDate(startDate, startTime);
    if (!start) return;

    if (start < now) {
      alert("Start time cannot be in the past.");
      startTime.value = "";
    }

    // Update minimum end date to match start date
    endDate.min = startDate.value;

    // If same day, enforce end time >= start time
    if (endDate.value === startDate.value) {
      endTime.min = startTime.value;
    } else {
      endTime.removeAttribute("min");
    }
  }

  // Validate End Date + Time
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

  // --- Event listeners ---
  startDate.addEventListener("change", validateStart);
  startTime.addEventListener("change", validateStart);

  endDate.addEventListener("change", validateEnd);
  endTime.addEventListener("change", validateEnd);
});
