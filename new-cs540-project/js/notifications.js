document.addEventListener("DOMContentLoaded", function () {
  const emails = document.querySelectorAll(".email");
  let currentlySelected = null;

  emails.forEach((email) => {
    email.addEventListener("click", function () {
      // If another email was previously selected
      if (currentlySelected && currentlySelected !== this) {
        // Remove "selected" border from the old one
        currentlySelected.classList.remove("selected");
        // Mark it as read by removing the left blue bar
        currentlySelected.classList.remove("unread");
      }

      // Select the clicked email
      this.classList.add("selected");
      currentlySelected = this;
    });
  });
});
