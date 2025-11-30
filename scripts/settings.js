/* ------------------------------------
   PROFILE PICTURE PREVIEW
------------------------------------ */
document.getElementById("profileFileInput")?.addEventListener("change", event => {
    const file = event.target.files[0];
    if (!file) return;

    const img = new Image();
    const url = URL.createObjectURL(file);
    img.src = url;

    img.onload = function () {
        const maxSize = 800;
        let { width, height } = img;

        if (width > height && width > maxSize) {
            height = Math.round(height * (maxSize / width));
            width = maxSize;
        } else if (height > maxSize) {
            width = Math.round(width * (maxSize / height));
            height = maxSize;
        }

        const canvas = document.createElement("canvas");
        canvas.width = width;
        canvas.height = height;

        canvas.getContext("2d").drawImage(img, 0, 0, width, height);

        document.getElementById("profilePreview").src =
            canvas.toDataURL("image/jpeg", 0.8);

        URL.revokeObjectURL(url);
    };
});

/* ------------------------------------
   DELETE PROFILE PICTURE
------------------------------------ */
const deleteModal = document.getElementById("deleteModal");

document.getElementById("openDeletePicModal")?.addEventListener("click", () => {
    deleteModal.classList.remove("hidden");
});

document.getElementById("cancelDeletePic")?.addEventListener("click", () => {
    deleteModal.classList.add("hidden");
});

document.getElementById("confirmDeletePic")?.addEventListener("click", () => {
    fetch("index.php?action=deleteProfilePicture", { method: "POST" })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById("profilePreview").src = "uploads/default.png";
                deleteModal.classList.add("hidden");
            }
        })
        .catch(console.error);
});

/* ------------------------------------
   UNBLOCK USER
------------------------------------ */
document.querySelectorAll(".unblock-btn").forEach(btn => {
    btn.addEventListener("click", () => {
        const userId = btn.dataset.userId;

        fetch(`index.php?action=unblockUser&user_id=${userId}&ajax=1`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const row = btn.closest(".flex.items-center");
                    row.style.opacity = "0";
                    setTimeout(() => row.remove(), 300);
                }
            })
            .catch(console.error);
    });
});

/* ------------------------------------
   DELETE ACCOUNT MODAL
------------------------------------ */
const deleteAccModal = document.getElementById("deleteAccountModal");
const errorEl = document.getElementById("deleteAccountError");

document.getElementById("openDeleteAccountModal")?.addEventListener("click", () => {
    deleteAccModal.classList.remove("hidden");
    errorEl.textContent = "";
    errorEl.classList.add("hidden");
});

document.getElementById("cancelDeleteAccount")?.addEventListener("click", () => {
    deleteAccModal.classList.add("hidden");
});

document.getElementById("confirmDeleteAccount")?.addEventListener("click", () => {
    const password = document.getElementById("deleteAccountPassword").value.trim();

    if (!password) {
        errorEl.textContent = "Please enter your password.";
        errorEl.classList.remove("hidden");
        return;
    }

    fetch("index.php?action=deleteAccount", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "password=" + encodeURIComponent(password)
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = "index.php?action=user";
            } else {
                errorEl.textContent = data.error ?? "Failed to delete account.";
                errorEl.classList.remove("hidden");
            }
        })
        .catch(err => {
            errorEl.textContent = "Network error.";
            errorEl.classList.remove("hidden");
        });
});