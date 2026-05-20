// Story Viewer JavaScript
document.addEventListener('DOMContentLoaded', () => {
  let storyTimeout = null; // 👈 buat nyimpen timeout
  const viewerModal = new bootstrap.Modal(document.getElementById('storyViewerModal'));

  // Bersihkan timeout ketika modal ditutup
  document.getElementById('storyViewerModal').addEventListener('hidden.bs.modal', () => {
    if (storyTimeout) {
      clearTimeout(storyTimeout);
      storyTimeout = null;
    }
  });

  const viewerContent = document.getElementById('storyContentViewer');

  document.getElementById('storyContentViewer').addEventListener('click', function (e) {
    e.stopPropagation();
  });

  const userId = STORY_DATA.userId;
  const allStories = STORY_DATA.groupedStories;

  function renderProgressBars(count) {
    const wrapper = document.getElementById('storyProgressWrapper');
    wrapper.innerHTML = '';
    for (let i = 0; i < count; i++) {
      const bar = document.createElement('div');
      bar.className = 'story-progress-bar'; // sesuai class CSS
      const fill = document.createElement('div');
      fill.className = 'progress-fill';
      bar.appendChild(fill);
      wrapper.appendChild(bar);
    }
  }

  // Reset semua progress bar
  document.querySelectorAll('.progress-fill').forEach(el => {
    el.style.animation = 'none';
    el.offsetHeight; // trigger reflow
    el.style.animation = '';
  });

  function animateProgress(index, duration) {
    const bars = document.querySelectorAll('.progress-fill');
    bars.forEach((bar, i) => {
      bar.style.animation = 'none';
      bar.style.width = (i < index) ? '100%' : '0%';
      bar.offsetHeight; // 🔁 Trigger reflow
    });

    const bar = bars[index];
    if (bar) {
      bar.style.animation = `story-progress ${duration}s linear forwards`;
    }
  }

  document.querySelectorAll('.story-box[data-user-id]').forEach(box => {
    box.addEventListener('click', () => {
      const uid = box.dataset.userId;
      const storyGroup = allStories[uid];
      if (!storyGroup) return;

      const stories = storyGroup.stories;
      let currentIndex = 0;

      const setupNavigation = () => {
        document.getElementById('storyLeftClick').onclick = (e) => {
          if (e.target.closest('.story-options-btn')) return;

          if (currentIndex > 0) {
            currentIndex--;
            show(currentIndex);
          }
        };

        document.getElementById('storyRightClick').onclick = (e) => {
          if (e.target.closest('.story-options-btn')) return;

          currentIndex++;
          if (currentIndex < stories.length) {
            show(currentIndex);
          } else {
            viewerModal.hide();
          }
        };
      };

      function show(index) {
        if (storyTimeout) {
          clearTimeout(storyTimeout);
          storyTimeout = null;
        }

        const story = stories[index];
        if (!story) {
          viewerModal.hide();
          return;
        }

        if (!viewerModal._isShown) {
          viewerModal.show() // hanya panggil show() sekali
          setupNavigation(); // setup navigation setelah modal ditampilkan
        }

        fetch('mark_story_viewed.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `story_id=${story.id}`
        });

        if (index === 0) {
          renderProgressBars(stories.length);
        }
        animateProgress(index, 10);

        const optionsContainer = document.getElementById('storyOptionsButtonContainer');
        optionsContainer.innerHTML = '';

        // Hanya tampilkan tombol untuk story milik sendiri
        if (parseInt(story.user_id) === parseInt(userId)) {
          const deleteButton = document.createElement('button');
          deleteButton.className = 'btn btn-sm btn-dark rounded-circle story-options-btn';
          deleteButton.innerHTML = '<i class="bi bi-three-dots-vertical"></i>';
          deleteButton.onclick = (e) => {
            e.stopPropagation();
            showStoryOptions(story.id);
          };
          optionsContainer.appendChild(deleteButton);
        }

        const header = `
          <div class="position-relative">
            <div class="d-flex align-items-center gap-2 mb-2">
              <img src="${story.photo}" class="rounded-circle" width="40" height="40" style="object-fit:cover;">
              <div class="text-start">
                <div class="fw-semibold">
                  ${(parseInt(story.user_id) === parseInt(userId)) ? 'Cerita saya' : story.name}
                </div>
                <div class="small text-secondary">
                  ${new Date(story.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                </div>
              </div>
            </div>
          </div>
        `;

        let body = '';
        if (story.type === 'text') {
          const duration = 10;
          body = `<div class="p-4 fs-5">${story.content ? story.content.replace(/\n/g, "<br>") : "<em>(tidak ada isi)</em>"}</div>`;
          viewerContent.innerHTML = header + body;
          viewerModal.show();
          animateProgress(index, duration); // ✅ tambahkan ini
          storyTimeout = setTimeout(next, duration * 1000); // ✅ gunakan durasi yang sama
        }
        else if (story.type === 'image') {
          const duration = 10;
          body = `<img src="${story.file_path}" class="img-fluid rounded" style="height: 528px;">`;
          if (story.content) body += `<div class="text-start p-2 fs-6">${story.content}</div>`;
          viewerContent.innerHTML = header + body;
          viewerModal.show();
          animateProgress(index, duration); // ✅
          storyTimeout = setTimeout(next, duration * 1000); // ✅
        }
        else if (story.type === 'video') {
          body = `
            <video src="${story.file_path}"
              class="w-100 rounded story-video"
              autoplay
              muted
              playsinline
              style="pointer-events: none;">
            </video>
          `;
          if (story.content) body += `<div class="text-start p-2 fs-6">${story.content}</div>`;
          viewerContent.innerHTML = header + body;
          viewerModal.show();

          const video = viewerContent.querySelector('video');
          video.onloadedmetadata = () => {
            const dur = video.duration || 10;
            animateProgress(index, dur); // ✅
          };
          video.onended = next;
        }
        else if (story.type === 'music') {
          body = `
            <audio src="${story.file_path}"
              class="w-100 story-audio"
              autoplay
              muted
              playsinline
              style="display: none;">
            </audio>
          `;
          if (story.content) body += `<div class="text-start mt-2 fs-6">${story.content}</div>`;
          viewerContent.innerHTML = header + `<div class="p-3">${body}</div>`;
          viewerModal.show();

          const audio = viewerContent.querySelector('audio');
          audio.onloadedmetadata = () => {
            const dur = audio.duration || 10;
            animateProgress(index, dur); // ✅
          };
          audio.onended = next;
        }
      }

      function showStoryOptions(storyId) {
        document.getElementById('storyIdInput').value = storyId;
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteStoryModal'));
        deleteModal.show();
      }

      function next() {
        currentIndex++;
        if (currentIndex < stories.length) {
          show(currentIndex);
        } else {
          viewerModal.hide();

          // ✅ Update tampilan card agar tampak sudah dilihat
          const card = document.querySelector(`.story-box[data-user-id="${uid}"]`);
          if (card) {
            card.classList.add('viewed');
            const profileImg = card.querySelector('img.rounded-circle');
            if (profileImg) {
              profileImg.classList.remove('border-info');
            }

            // ✅ Pindahkan elemen ke paling kanan
            const container = document.querySelector('.story-container');
            if (container && card.parentNode === container) {
              container.removeChild(card);
              container.appendChild(card);
            }
          }
        }
      }

      show(currentIndex);
    });
  });
});
