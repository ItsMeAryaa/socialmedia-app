document.addEventListener("DOMContentLoaded", () => {
  const chatBox = document.getElementById("chatBox");
  const chatForm = document.getElementById("chatForm");
  const toUserInput = document.getElementById("toUserInput");
  const chatWithName = document.getElementById("chatWithName");
  const chatPlaceholder = document.getElementById("chatPlaceholder");
  const fileInput = document.getElementById("fileInput");
  const filePreview = document.getElementById("filePreview");
  const btnDeleteAll = document.getElementById("btnDeleteAll");

  // Tombol kembali (untuk mobile)
  const closeChatBtn = document.getElementById("closeChatBtn");
  if (closeChatBtn) {
    closeChatBtn.addEventListener("click", () => {
      document.querySelector(".chat-room").classList.remove("active");
      document.querySelector(".friend-list").classList.remove("hide");
    });
  }

  document.querySelectorAll(".friend-item").forEach((item) => {
    item.addEventListener("click", () => {
      const toUserId = item.dataset.userId;
      const toUserName = item.dataset.username;

      // munculkan tombol Hapus Semua
      btnDeleteAll.classList.remove('d-none');
      btnDeleteAll.onclick = () => {
        if (!confirm(`Hapus seluruh riwayat chat dengan ${toUserName}?`)) return;

        fetch('delete_conversation.php', {
          method: 'POST',
          headers: {'Content-Type':'application/x-www-form-urlencoded'},
          body: 'peer_id=' + toUserId
        })
        .then(r=>r.json())
        .then(j=>{
          if(j.success){
            // Kosongkan UI
            chatBox.innerHTML = `
              <div class="text-secondary text-center">
                <div class="fs-5">Belum ada pesan...</div>
              </div>`;
            btnDeleteAll.classList.add('d-none');
            chatForm.classList.add('d-none');
          }
        });
      };

      toUserInput.value = toUserId;
      chatForm.classList.remove("d-none");
      chatWithName.textContent = toUserName;
      if (chatPlaceholder) chatPlaceholder.remove();

      // Mobile: sembunyikan daftar teman
      if (window.innerWidth <= 768) {
        document.querySelector(".chat-room").classList.add("active");
        document.querySelector(".friend-list").classList.add("hide");
      }

      // 1) munculkan tombol "Hapus Semua"
      btnDeleteAll.classList.remove("d-none");
      btnDeleteAll.onclick = () => {
        if (!confirm(`Hapus seluruh riwayat chat dengan ${toUserName}?`))
          return;
        fetch("delete_conversation.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: "peer_id=" + toUserId,
        })
          .then((r) => r.json())
          .then((json) => {
            if (json.success) {
              // Kosongkan chatBox & tampilkan placeholder
              chatBox.innerHTML = `
              <div class="text-secondary text-center">
                <div class="fs-5">Belum ada pesan...</div>
              </div>`;
            }
          });
      };

      // 2) sekarang baru load pesan
      fetch("load_messages.php?to_user=" + toUserId)
        .then((res) => res.json())
        .then((data) => {
          chatBox.innerHTML = "";

          // Jika tidak ada pesan aktif sama sekali
          if (data.length === 0) {
            // Pure kosong (chat pertama)
            chatBox.innerHTML = `
              <div id="chatPlaceholder" class="text-secondary text-center">
                <div class="fs-5">Belum ada pesan...</div>
              </div>`;
            btnDeleteAll.classList.add('d-none');
            return;
          }

          data.forEach(msg => {
            const div    = document.createElement('div');
            div.className = 'chat-message ' +
              (msg.from_user == currentUserId ? 'me' : 'them');

            const bubble = document.createElement('div');
            bubble.className = 'chat-bubble ' +
              (msg.from_user == currentUserId ? 'me' : 'them');

            if (msg.is_deleted == 1) {
              // 1) Pesan sudah dihapus → hanya tampil teks dan styling deleted
              bubble.textContent = '— Pesan telah dihapus —';
              bubble.classList.add('deleted');

            } else {
              // 2) Pesan masih aktif → render teks atau media
              if (msg.message.includes('[file]')) {
                const [text, fileUrl] = msg.message.split('[file]');
                const ext = fileUrl.split('.').pop().toLowerCase();
                let media = '';

                if (['jpg','jpeg','png','gif','webp'].includes(ext)) {
                  media = `<img src="${fileUrl}" class="img-fluid rounded" style="max-width:200px;">`;
                } else if (['mp4','webm'].includes(ext)) {
                  media = `<video controls style="max-width:200px;"><source src="${fileUrl}"></video>`;
                } else if (['mp3','wav'].includes(ext)) {
                  media = `<audio controls><source src="${fileUrl}"></audio>`;
                } else {
                  media = `<a href="${fileUrl}" download>${fileUrl}</a>`;
                }
                bubble.innerHTML = `<div>${text}</div>${media}`;
              } else {
                bubble.textContent = msg.message;
              }

              // 3) Hanya bubble milik saya yang aktif dapat di‐delete lagi
              if (msg.from_user == currentUserId) {
                const trash = document.createElement('i');
                trash.className    = 'bi bi-three-dots-vertical delete-msg ms-2 text-dark';
                trash.style.cursor = 'pointer';
                trash.title        = 'Hapus pesan ini';
                trash.dataset.id   = msg.id;

                trash.addEventListener('click', () => {
                  if (!confirm('Hapus pesan ini?')) return;
                  fetch('delete_message.php', {
                    method: 'POST',
                    headers: {'Content-Type':'application/x-www-form-urlencoded'},
                    body: 'message_id=' + msg.id
                  })
                  .then(r=>r.json())
                  .then(j=>{
                    if (j.success) {
                      bubble.textContent = '— Pesan telah dihapus —';
                      bubble.classList.add('deleted');
                      trash.remove();
                    }
                  });
                });

                bubble.appendChild(trash);
              }
            }

            div.appendChild(bubble);
            chatBox.appendChild(div);
          });
          chatBox.scrollTop = chatBox.scrollHeight;

          // Setelah data pesan di-render:
          fetch("mark_read.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "peer_id=" + toUserId,
          }).then(() => {
            // refresh list teman agar badge hilang
            document
              .querySelector(`.friend-item[data-user-id="${toUserId}"]`)
              .querySelector(".badge")
              ?.remove();
            // juga bisa reload navbar partial jika ingin update badge total
          });
        });
    });
  });

  fileInput.addEventListener("change", () => {
    filePreview.innerHTML = "";
    const file = fileInput.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (e) => {
      let preview = "";
      if (file.type.startsWith("image/")) {
        preview = `<img src="${e.target.result}" class="img-fluid rounded" style="max-width: 150px;">`;
      } else if (file.type.startsWith("video/")) {
        preview = `<video controls style="max-width:150px;"><source src="${e.target.result}"></video>`;
      } else if (file.type.startsWith("audio/")) {
        preview = `<audio controls><source src="${e.target.result}"></audio>`;
      } else {
        preview = `<p class="text-secondary">${file.name}</p>`;
      }
      filePreview.innerHTML = preview;
    };
    reader.readAsDataURL(file);
  });

  chatForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const message = chatForm
      .querySelector('input[name="message"]')
      .value.trim();
    const file = fileInput.files[0];
    if (!message && !file) {
      alert("Isi pesan atau pilih file untuk dikirim!");
      return;
    }

    const formData = new FormData(chatForm);

    fetch("send_message.php", {
      method: "POST",
      body: formData,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          chatForm.querySelector('input[name="message"]').value = "";
          fileInput.value = "";
          filePreview.innerHTML = "";
          document
            .querySelector(`.friend-item[data-user-id="${toUserInput.value}"]`)
            .click(); // reload chat
        }
      });
  });
});
