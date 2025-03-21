document.addEventListener("DOMContentLoaded", function () {
  const permissionsModal = document.getElementById("permissionsModal");
  const closePermissions = document.querySelector(".close-permissions");
  const permissionsForm = document.getElementById("permissionsForm");
  const collaboratorsList = document.getElementById("collaboratorsList");

  // Store original tab user
  const originalTabUser =
    document.querySelector('input[name="tab_user_id"]')?.value ||
    document.querySelector('input[name="ownerUsername"]').value;

  // Add "Share" button only to folders owned by the user
  document.querySelectorAll(".folder-block").forEach((folder) => {
    // Check if this is an owned folder (not a shared one)
    if (!folder.querySelector(".shared-badge")) {
      const shareButton = document.createElement("button");
      shareButton.innerHTML =
        '<img src="../images/icons/share.png" alt="share" class="folder-img">';
      shareButton.className = "share-button";
      folder.querySelector(".folder-icn-wp").prepend(shareButton);

      shareButton.addEventListener("click", (e) => {
        e.stopPropagation();
        const folderName = folder.querySelector("p").textContent;
        openPermissionsModal(folderName);
      });
    }
  });

  function openPermissionsModal(folderName) {
    document.getElementById("currentFolder").value = folderName;
    loadCollaborators(folderName);
    permissionsModal.style.display = "block";
  }

  function loadCollaborators(folderName) {
    const ownerUsername = permissionsForm.querySelector(
      '[name="ownerUsername"]'
    ).value;

    fetch(
      `folder_permissions.php?folderName=${encodeURIComponent(
        folderName
      )}&ownerUsername=${encodeURIComponent(ownerUsername)}`
    )
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          displayCollaborators(data.permissions);
        } else {
          console.error("Failed to load collaborators:", data.message);
        }
      })
      .catch((error) => console.error("Error:", error));
  }

  // Expose loadCollaborators to the global scope so it can be accessed by removeCollaborator
  window.loadCollaborators = loadCollaborators;

  function displayCollaborators(permissions) {
    collaboratorsList.innerHTML = "";
    permissions.forEach((perm) => {
      const div = document.createElement("div");
      div.className = "collaborator-item";
      div.innerHTML = `
                <span>${perm.collaborator_email}</span>
                <span>
                    ${perm.can_write ? "(Can Edit)" : "(Read Only)"}
                    ${perm.can_delete ? "(Can Delete)" : ""}
                </span>
                <button onclick="removeCollaborator('${
                  perm.collaborator_email
                }')">Remove</button>
            `;
      collaboratorsList.appendChild(div);
    });
  }

  permissionsForm.addEventListener("submit", function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append("action", "add");
    formData.append("tab_user_id", originalTabUser);
    console.log(originalTabUser);

    fetch("folder_permissions.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then((data) => {
        if (data.success) {
          loadCollaborators(formData.get("folderName"));
          permissionsForm.reset();
          alert(data.message || "Collaborator added successfully");
        } else {
          alert("Failed to add collaborator: " + data.message);
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("An error occurred while adding the collaborator: " + error);
      });
  });

  closePermissions.addEventListener("click", () => {
    permissionsModal.style.display = "none";
  });

  window.addEventListener("click", (event) => {
    if (event.target === permissionsModal) {
      permissionsModal.style.display = "none";
    }
  });
});

// Global function for removing collaborators
function removeCollaborator(email) {
  const folderName = document.getElementById("currentFolder").value;
  const ownerUsername = document.querySelector('[name="ownerUsername"]').value;
  const originalTabUser =
    document.querySelector('input[name="tab_user_id"]')?.value || ownerUsername;
  const tabId = new URLSearchParams(window.location.search).get("tab_id"); // Get tab_id

  if (confirm("Are you sure you want to remove this collaborator's access?")) {
    fetch("folder_permissions.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `action=remove&folderName=${encodeURIComponent(
        folderName
      )}&ownerUsername=${encodeURIComponent(
        ownerUsername
      )}&collaboratorEmail=${encodeURIComponent(
        email
      )}&tab_user_id=${encodeURIComponent(
        originalTabUser
      )}&tab_id=${encodeURIComponent(tabId)}`, // Include tab_id
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          alert(data.message || "Collaborator removed successfully");
          // Load the updated list of collaborators
          loadCollaborators(folderName);
        } else {
          if (data.redirect) {
            window.location.href = data.redirect;
          } else {
            alert(data.message || "Failed to remove collaborator");
          }
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("An error occurred while removing the collaborator");
      });
  }
}

// Helper function to get current tab user
function getTabUser() {
  return (
    document.querySelector('input[name="tab_user_id"]')?.value ||
    document.querySelector('input[name="ownerUsername"]').value
  );
}

// Function to handle profile redirections
function redirectToProfile(userId = null) {
  const tabUser = userId || getTabUser();
  window.location.href = `profile.php?user=${encodeURIComponent(tabUser)}`;
}

function addCollaborator(formData) {
  const tabId = new URLSearchParams(window.location.search).get("tab_id");
  formData.append("tab_id", tabId);

  fetch("folder_permissions.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        loadCollaborators(formData.get("folderName"));
        permissionsForm.reset();
        alert(data.message || "Collaborator added successfully");
      } else {
        alert("Failed to add collaborator: " + data.message);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("An error occurred while adding the collaborator");
    });
}
