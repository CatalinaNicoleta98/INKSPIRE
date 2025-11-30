// scripts/profile_post.js

// Toggle post settings menu (⚙️)
document.addEventListener('click', (e) => {
  const btn = e.target.closest('.post-options');
  const openMenus = document.querySelectorAll('.post-options-menu:not(.hidden)');

  // Close menus if click outside
  if (!btn && !e.target.closest('.post-options-menu')) {
    openMenus.forEach(m => m.classList.add('hidden'));
    return;
  }

  if (btn) {
    openMenus.forEach(m => m.classList.add('hidden'));
    const menu = btn.nextElementSibling;
    if (menu) {
      menu.classList.toggle('hidden');
      e.stopPropagation();
    }
  }
});

// Inline edit post in Profile
document.addEventListener('click', (e) => {
  const editBtn = e.target.closest('.edit-post');
  if (!editBtn) return;

  const postCard = editBtn.closest('.post-card');
  if (!postCard) return;

  const postId = editBtn.dataset.postId;
  const contentView = postCard.querySelector('.post-content-view');
  const titleEl = contentView?.querySelector('.post-title');
  const descEl  = contentView?.querySelector('.post-desc');

  const oldTitle = titleEl ? titleEl.textContent.trim() : '';
  const oldDesc  = descEl ? descEl.textContent.trim() : '';

  const tagElement = postCard.querySelector('.mt-2.text-sm.text-indigo-500');

  const form = document.createElement('div');
  form.className = 'post-edit-form';
  form.dataset.originalTitle = oldTitle;
  form.dataset.originalDesc  = oldDesc;
  form.dataset.originalTags  = tagElement ? tagElement.textContent : '';

  const existingTagsText = tagElement
    ? tagElement.textContent.replace(/#/g, '').trim().replace(/\s+/g, ', ')
    : '';

  const existingImageEl = postCard.querySelector('img.post-image');
  const imageBlock = existingImageEl
    ? `
      <div class="mb-2">
        <p class="text-sm text-gray-600 mb-1">Current image:</p>
        <img src="${existingImageEl.src}" class="w-32 h-32 object-cover rounded-md shadow border" />
      </div>
      <label class="flex items-center gap-2 text-sm text-red-600 mb-2">
        <input type="checkbox" class="remove-image-checkbox">
        Remove current image
      </label>
    `
    : '';

  form.innerHTML = `
    <input type="text"
           class="edit-title w-full border border-indigo-200 rounded-md p-2 mb-2 font-semibold"
           value="${oldTitle}">
    <textarea class="edit-description w-full border border-indigo-200 rounded-md p-2 mb-2 text-sm">${oldDesc}</textarea>
    <input type="text"
           class="edit-tags w-full border border-indigo-200 rounded-md p-2 mb-3 text-sm"
           placeholder="Tags (comma separated)"
           value="${existingTagsText}">

    <div class="mb-3">
      ${imageBlock}
      <label class="block text-sm text-gray-700 mb-1">Select new image:</label>
      <input type="file" accept="image/*" class="edit-image w-full text-sm">
    </div>

    <div class="flex justify-end gap-2">
      <button class="save-edit bg-indigo-500 text-white px-3 py-1 rounded-md text-sm hover:bg-indigo-600"
              data-post-id="${postId}">
        Save
      </button>
      <button class="cancel-edit bg-gray-300 text-gray-700 px-3 py-1 rounded-md text-sm hover:bg-gray-400">
        Cancel
      </button>
    </div>
  `;

  const menu = editBtn.closest('.post-options-menu');
  if (menu) menu.classList.add('hidden');
  if (contentView) contentView.classList.add('hidden');
  contentView.insertAdjacentElement('beforebegin', form);
});

// Save / Cancel edit
document.addEventListener('click', async (e) => {
  const saveBtn   = e.target.closest('.save-edit');
  const cancelBtn = e.target.closest('.cancel-edit');

  // Cancel
  if (cancelBtn) {
    const form     = cancelBtn.closest('.post-edit-form');
    const postCard = cancelBtn.closest('.post-card');
    if (!form || !postCard) return;

    const contentView = postCard.querySelector('.post-content-view');
    if (contentView) contentView.classList.remove('hidden');

    const originalTitle = form.dataset.originalTitle || '';
    const originalDesc  = form.dataset.originalDesc  || '';
    const originalTags  = form.dataset.originalTags  || '';

    const titleEl = contentView.querySelector('.post-title');
    const descEl  = contentView.querySelector('.post-desc');
    const tagsEl  = postCard.querySelector('.mt-2.text-sm.text-indigo-500');

    if (titleEl) titleEl.textContent = originalTitle;
    if (descEl)  descEl.textContent  = originalDesc;
    if (tagsEl)  tagsEl.textContent  = originalTags;

    form.remove();
    return;
  }

  // Save
  if (saveBtn) {
    const form     = saveBtn.closest('.post-edit-form');
    const postCard = saveBtn.closest('.post-card');
    if (!form || !postCard) return;

    const postId      = saveBtn.dataset.postId;
    const title       = form.querySelector('.edit-title')?.value.trim() || '';
    const description = form.querySelector('.edit-description')?.value.trim() || '';
    const tagsInput   = form.querySelector('.edit-tags')?.value.trim() || '';

    if (!title || !description) {
      alert('Please fill all fields.');
      return;
    }

    try {
      const formData = new FormData();
      formData.append('post_id', postId);
      formData.append('title', title);
      formData.append('description', description);
      formData.append('tags', tagsInput);

      const fileInput = form.querySelector('.edit-image');
      if (fileInput && fileInput.files.length > 0) {
        formData.append('image', fileInput.files[0]);
      }

      const removeCheckbox = form.querySelector('.remove-image-checkbox');
      if (removeCheckbox && removeCheckbox.checked) {
        formData.append('remove_image', '1');
      }

      const res  = await fetch('index.php?action=editPost', { method: 'POST', body: formData });
      const data = await res.json();
      if (data.success) {
        window.location.reload();
      } else {
        alert(data.message || 'Error updating post.');
      }
    } catch (err) {
      console.error(err);
      alert('Request failed.');
    }
  }
});

// Delete post: open modal
document.addEventListener('click', (e) => {
  const delBtn = e.target.closest('.delete-post');
  if (!delBtn) return;

  const postId = delBtn.dataset.postId;
  const modal  = document.getElementById('deletePostModal');

  if (!modal) return;

  modal.classList.remove('hidden');
  window.postToDelete = postId;
});

// Delete post: cancel
document.addEventListener('click', (e) => {
  const cancelBtn = e.target.closest('#cancelDeletePostBtn');
  if (!cancelBtn) return;

  const modal = document.getElementById('deletePostModal');
  if (modal) modal.classList.add('hidden');
  window.postToDelete = null;
});

// Delete post: confirm
document.addEventListener('click', async (e) => {
  const confirmBtn = e.target.closest('#confirmDeletePostBtn');
  if (!confirmBtn) return;

  if (!window.postToDelete) return;
  const postId = window.postToDelete;

  try {
    const res = await fetch('index.php?action=deletePost', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `post_id=${encodeURIComponent(postId)}`
    });
    const data = await res.json();
    if (data.success) {
      window.location.reload();
    } else {
      alert(data.message || 'Error deleting post.');
    }
  } catch (err) {
    console.error(err);
    alert('Error sending delete request.');
  } finally {
    const modal = document.getElementById('deletePostModal');
    if (modal) modal.classList.add('hidden');
    window.postToDelete = null;
  }
});

// Toggle privacy
document.addEventListener('click', async (e) => {
  const privacyBtn = e.target.closest('.privacy-post');
  if (!privacyBtn) return;

  const postId   = privacyBtn.dataset.postId;
  const isPublic = privacyBtn.dataset.public === '1' ? 0 : 1;

  try {
    const res = await fetch('index.php?action=changePrivacy', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `post_id=${encodeURIComponent(postId)}&is_public=${encodeURIComponent(isPublic)}`
    });
    const data = await res.json();
    if (data.success) {
      window.location.reload();
    } else {
      alert(data.message || 'Error changing privacy.');
    }
  } catch (err) {
    console.error(err);
    alert('Error sending privacy request.');
  }
});

// Toggle sticky / pinned post
document.addEventListener('click', async (e) => {
  const stickyBtn = e.target.closest('.sticky-post');
  if (!stickyBtn) return;

  const postId = stickyBtn.dataset.postId;

  try {
    const res = await fetch('index.php?action=toggleSticky', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `post_id=${encodeURIComponent(postId)}`
    });
    const data = await res.json();

    if (data.success) {
      stickyBtn.dataset.sticky = data.sticky ? '1' : '0';
      stickyBtn.innerHTML = data.sticky ? '📌 Unpin from profile' : '📌 Pin on profile';
      window.location.reload();
    } else {
      alert(data.message || 'Failed to update pinned state.');
    }
  } catch (err) {
    console.error(err);
    alert('Error sending sticky request.');
  }
});