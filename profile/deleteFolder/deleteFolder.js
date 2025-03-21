document.addEventListener("DOMContentLoaded", function () {
  const deleteButtons = document.querySelectorAll(".folder-button");
  const modal = document.getElementById("deleteFolderModal");
  const closeModal = modal.querySelector(".folder-close");
  const confirmDeleteBtn = modal.querySelector(".confirmdelete");
  let currentFolderName;
  let currentUID;

  deleteButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      e.stopPropagation();
      currentUID = this.getAttribute("data-userid");
      currentFolderName = this.getAttribute("data-foldername");
      showModal(currentUID, currentFolderName);
    });
  });

  function showModal(userID, folderName) {
    currentUID = userID;
    currentFolderName = folderName;
    document.querySelector(".deletefoldername").textContent = folderName;
    modal.style.display = "block";
  }

  closeModal.addEventListener("click", () => {
    modal.style.display = "none";
  });

  confirmDeleteBtn.addEventListener("click", function () {
    const tabId = new URLSearchParams(window.location.search).get("tab_id");
    console.log("tabId being sent:", tabId); // Add this line
    console.log(currentUID);
    console.log(currentFolderName);

    fetch("delete_folder.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `userID=${currentUID}&folderName=${encodeURIComponent(
        currentFolderName
      )}&tab_id=${encodeURIComponent(tabId)}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Display the alert box and wait for it to be acknowledged
          alert("Folder Deleted Successfully");
          if (data.redirectTo) {
            window.location.href = data.redirectTo; // Use the absolute URL directly
          } else {
            console.error("Redirect URL is missing");
            alert("An error occurred: Missing redirect URL");
          }
        } else {
          if (data.redirectTo) {
            // window.location.href = data.redirectTo;
            console.log(data);
            console.log("in the else chamber");
          } else {
            throw new Error(data.message || "Error deleting folder");
          }
        }
      })
      .catch((error) => {
        console.error(error);
        alert("An Error occurred: " + error.message);
        modal.style.display = "none";
      });
  });

  window.addEventListener("click", (event) => {
    if (event.target === modal) {
      modal.style.display = "none";
    }
  });
});
