// 3-dot post management menu for feed posts (edit, delete, toggle privacy)
document.addEventListener('click', (e) => {
  const btn = e.target.closest('.post-options');
  const openMenus = document.querySelectorAll('.post-options-menu:not(.hidden)');
  openMenus.forEach(m => m.classList.add('hidden'));
  if (btn) {
    const menu = btn.nextElementSibling;
    menu.classList.toggle('hidden');
  }
});

// inline edit post in feed
document.addEventListener('click', (e) => {
  const editBtn = e.target.closest('.edit-post');
  if (!editBtn) return;
  const postCard = editBtn.closest('.post');
  const postId = editBtn.dataset.postId;
  const titleEl = postCard.querySelector('h3');
  const descEl = postCard.querySelector('p.text-gray-600');
  const oldTitle = titleEl ? titleEl.textContent.trim() : '';
  const oldDesc = descEl ? descEl.textContent.trim() : '';

  const form = document.createElement('div');
  form.className = 'post-edit-form';
  // persist originals so Cancel can restore without reload
  form.dataset.originalTitle = oldTitle;
  form.dataset.originalDesc = oldDesc;
  const tagElement = postCard.querySelector('.mt-2.text-sm.text-indigo-500');
  form.dataset.originalTags = tagElement ? tagElement.textContent : '';
  form.innerHTML = `
    <input type="text" class="edit-title w-full border border-indigo-200 rounded-md p-2 mb-2 font-semibold" value="${oldTitle}">
    <textarea class="edit-description w-full border border-indigo-200 rounded-md p-2 mb-2 text-sm">${oldDesc}</textarea>
    <input type="text" class="edit-tags w-full border border-indigo-200 rounded-md p-2 mb-3 text-sm" placeholder="Tags (comma separated)" value="${ postCard.querySelector('.mt-2.text-sm.text-indigo-500')
      ? postCard.querySelector('.mt-2.text-sm.text-indigo-500')
          .textContent.replace(/#/g, '')
          .trim()
          .replace(/\s+/g, ', ')
      : '' }">

    <div class="mb-3">
      ${postCard.querySelector('.post-image') ? `
        <div class="mb-2">
          <p class="text-sm text-gray-600 mb-1">Current image:</p>
          <img src="${postCard.querySelector('.post-image').src}" class="w-32 h-32 object-cover rounded-md shadow border" />
        </div>
        <label class="flex items-center gap-2 text-sm text-red-600 mb-2">
          <input type="checkbox" class="remove-image-checkbox">
          Remove current image
        </label>
      ` : ''}

      <label class="block text-sm text-gray-700 mb-1">Select new image:</label>
      <input type="file" accept="image/*" class="edit-image w-full text-sm">
    </div>

    <div class="flex justify-end gap-2">
      <button class="save-edit bg-indigo-500 text-white px-3 py-1 rounded-md text-sm hover:bg-indigo-600" data-post-id="${postId}">Save</button>
      <button class="cancel-edit bg-gray-300 text-gray-700 px-3 py-1 rounded-md text-sm hover:bg-gray-400">Cancel</button>
    </div>
  `;
  postCard.querySelector('.post-options-menu').classList.add('hidden');
  if (descEl) descEl.style.display = 'none';
  titleEl.replaceWith(form);
});

document.addEventListener('click', async (e) => {
  const saveBtn = e.target.closest('.save-edit');
  const cancelBtn = e.target.closest('.cancel-edit');

  if (cancelBtn) {
    const form = cancelBtn.closest('.post-edit-form');
    const postCard = cancelBtn.closest('.post');
    if (!form || !postCard) return;

    const originalTitle = form.dataset.originalTitle || '';
    const originalDesc = form.dataset.originalDesc || '';

    // Remove any existing description paragraph to prevent duplicates
    const existingDesc = postCard.querySelector('p.text-gray-600.text-sm.mt-1');
    if (existingDesc) existingDesc.remove();

    const titleEl = document.createElement('h3');
    titleEl.className = 'text-lg font-semibold text-gray-800';
    titleEl.textContent = originalTitle;

    const descEl = document.createElement('p');
    descEl.className = 'text-gray-600 text-sm mt-1';
    descEl.textContent = originalDesc;
    descEl.style.display = '';

    form.replaceWith(titleEl);
    titleEl.insertAdjacentElement('afterend', descEl);

    // Restore tags
    const originalTags = form.dataset.originalTags || '';
    if (originalTags) {
      const existingTagsEl = postCard.querySelector('.mt-2.text-sm.text-indigo-500');
      if (existingTagsEl) existingTagsEl.textContent = originalTags;
    }
    return;
  }

  if (saveBtn) {
    const postId = saveBtn.dataset.postId;
    const postCard = saveBtn.closest('.post');
    const title = postCard.querySelector('.edit-title').value.trim();
    const description = postCard.querySelector('.edit-description').value.trim();
    if (!title || !description) return alert('Please fill all fields.');

    try {
      const formData = new FormData();
      formData.append('post_id', postId);
      formData.append('title', title);
      formData.append('description', description);
      const tagsInput = postCard.querySelector('.edit-tags').value.trim();
      formData.append('tags', tagsInput);

      const fileInput = postCard.querySelector('.edit-image');
      if (fileInput && fileInput.files.length > 0) {
          formData.append('image', fileInput.files[0]);
      }

      const removeCheckbox = postCard.querySelector('.remove-image-checkbox');
      if (removeCheckbox && removeCheckbox.checked) {
          formData.append('remove_image', '1');
      }

      const res = await fetch('index.php?action=editPost', {
          method: 'POST',
          body: formData
      });
      const data = await res.json();
      if (data.success) {
        window.location.reload();
      } else {
        alert('Error updating post.');
      }
    } catch {
      alert('Request failed.');
    }
  }
});

// delete post confirmation modal
document.addEventListener('click', async (e) => {
  const delBtn = e.target.closest('.delete-post');
  if (!delBtn) return;
  const postId = delBtn.dataset.postId;

  const overlay = document.createElement('div');
  overlay.className = "fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center z-50";
  overlay.innerHTML = `
    <div class="bg-white rounded-xl p-6 text-center shadow-xl max-w-sm w-full">
      <p class="text-gray-700 mb-5 text-base font-medium">🗑️ Are you sure you want to delete this post?</p>
      <div class="flex justify-center gap-4">
        <button class="cancel-del bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition">Cancel</button>
        <button class="confirm-del-post bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition" data-post-id="${postId}">Delete</button>
      </div>
    </div>
  `;
  document.body.appendChild(overlay);
});

document.addEventListener('click', async (e) => {
  const cancel = e.target.closest('.cancel-del');
  const confirm = e.target.closest('.confirm-del-post');
  const overlay = document.querySelector('.fixed.inset-0.bg-black');
  if (cancel && overlay) overlay.remove();
  if (confirm) {
    const postId = confirm.dataset.postId;
    try {
      const res = await fetch('index.php?action=deletePost', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `post_id=${encodeURIComponent(postId)}`
      });
      const data = await res.json();
      if (data.success) {
        overlay.remove();
        window.location.reload();
      } else {
        alert('Error deleting post.');
      }
    } catch {
      alert('Delete request failed.');
    }
  }
});

// toggle post privacy
document.addEventListener('click', async (e) => {
  const privacyBtn = e.target.closest('.toggle-privacy');
  if (!privacyBtn) return;
  const postId = privacyBtn.dataset.postId;
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
      console.error('Error updating privacy.');
    }
  } catch {
    console.error('Privacy request failed.');
  }
});