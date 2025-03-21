document.addEventListener("DOMContentLoaded", function () {
  document
    .getElementById("linkInputForm")
    .addEventListener("submit", function (e) {
      e.preventDefault();
      const formData = new FormData(this);
      const tabId = new URLSearchParams(window.location.search).get("tab_id");
      formData.append("tab_id", tabId);

      fetch("input.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error("Network issue");
          }
          return response.json();
        })
        .then((data) => {
          if (data.success) {
            alert(data.message || "Link added successfully");
            window.location.reload();
          } else {
            throw new Error(data.message || "Failed to add link");
          }
        })
        .catch((err) => {
          console.error("Error:", err);
          alert(err.message || "An error occurred while adding the link");
        });
    });
});
