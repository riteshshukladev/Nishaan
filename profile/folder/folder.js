document.addEventListener("DOMContentLoaded", function () {
  document
    .querySelector("#addFolderForm")
    .addEventListener("submit", function (event) {
      event.preventDefault();
      const formData = new FormData(this);
      const currentUserId = formData.get("OriginalUsernameFolder");
      const tabId = new URLSearchParams(window.location.search).get("tab_id");

      formData.append("tab_id", tabId);

      fetch("add_floder.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error("Network Issue");
          }
          return response.json();
        })
        .then((data) => {
          if (data.success) {
            alert("Folder created successfully");
            // Use the current user's ID from the response
            window.location.href = `profile.php?user=${encodeURIComponent(
              data.currentUser || currentUserId
            )}&tab_id=${encodeURIComponent(tabId)}`;
          } else {
            if (
              data.message ===
              "A folder with this name already exists in your account"
            ) {
              alert(data.message);
            }
            else if (data.redirect) {
              alert(data.message || "Session expired. Please login again.");
              window.location.href = data.redirect;
            } else {
              alert(data.message || "Failed to create folder");
            }
          }
        })
        .catch((err) => {
          console.error(err);
          alert("An error occurred, please try again later");
        });
    });
});
