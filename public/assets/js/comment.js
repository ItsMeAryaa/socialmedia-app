// Komentar pada postingan
document.querySelectorAll('.comment-form').forEach(function(form) {
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    var postId = form.getAttribute('data-post-id');
    var textarea = form.querySelector('textarea[name="content"]');
    var content = textarea.value.trim();
    if (!content) return;
    var data = new FormData();
    data.append('post_id', postId);
    data.append('content', content);

    fetch('post_comment.php', { method: 'POST', body: data })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          // Tambahkan komentar ke list
          var list = document.getElementById('comments-list-' + postId);
          var html = `<div class="d-flex align-items-start mb-2">
            <img src="${res.comment.photo}" class="rounded-circle me-2" width="32" height="32" style="object-fit:cover;">
            <div>
              <div class="bg-light rounded px-3 py-2">
                <span class="fw-semibold">${res.comment.name}</span>
                <span class="text-muted small">· ${res.comment.created_at}</span>
                <div>${res.comment.content.replace(/\n/g, '<br>')}</div>
              </div>
            </div>
          </div>`;
          list.insertAdjacentHTML('beforeend', html);
          // Setelah insertAdjacentHTML
          var commentElems = list.querySelectorAll('.d-flex.align-items-start');
          if (commentElems.length > 3) {
            // Sembunyikan komentar paling atas, kecuali 3 terakhir
            for (var i = 0; i < commentElems.length - 2; i++) {
              commentElems[i].classList.add('d-none', 'comment-hidden');
            }
            // Jika tombol "lihat semua" belum muncul, tambahkan manual (opsional, tapi disarankan reload page agar konsisten)
          }
          textarea.value = '';
        }
      });
  });
});

// Tampilkan/ sembunyikan komentar
document.querySelectorAll('.show-comments-link').forEach(function(link) {
  link.addEventListener('click', function(e) {
    e.preventDefault();
    var postId = this.getAttribute('data-post-id');
    document.querySelectorAll('#comments-list-' + postId + ' .comment-hidden').forEach(function(el) {
      el.classList.remove('d-none');
    });
    this.classList.add('d-none');
    document.querySelector('.hide-comments-link[data-post-id="'+postId+'"]').classList.remove('d-none');
  });
});

document.querySelectorAll('.hide-comments-link').forEach(function(link) {
  link.addEventListener('click', function(e) {
    e.preventDefault();
    var postId = this.getAttribute('data-post-id');
    document.querySelectorAll('#comments-list-' + postId + ' .comment-hidden').forEach(function(el) {
      el.classList.add('d-none');
    });
    this.classList.add('d-none');
    document.querySelector('.show-comments-link[data-post-id="'+postId+'"]').classList.remove('d-none');
    // Scroll ke komentar terbaru
    document.getElementById('comments-list-' + postId).scrollIntoView({ behavior: "smooth", block: "end" });
  });
});

// Hapus Komentar
document.addEventListener('click', function(e){
  if(e.target.classList.contains('delete-comment-btn')){
    if (!confirm('Hapus komentar ini?')) return;
    var commentId = e.target.getAttribute('data-comment-id');
    fetch('delete_comment.php', {
      method: 'POST',
      body: new URLSearchParams({comment_id: commentId})
    }).then(r => r.json()).then(res => {
      if (res.success) {
        document.querySelector('[data-comment-id="'+commentId+'"]').remove();
      } else {
        alert(res.error || 'Gagal menghapus komentar');
      }
    });
  }
});

// Edit Komentar: buka modal dan isi data
document.addEventListener('click', function(e){
  if(e.target.classList.contains('edit-comment-btn')){
    var commentId = e.target.getAttribute('data-comment-id');
    var commentElem = document.querySelector('[data-comment-id="'+commentId+'"] .comment-content');
    document.getElementById('edit-comment-id').value = commentId;
    document.getElementById('edit-comment-content').value = commentElem.innerText.trim();
    var modal = new bootstrap.Modal(document.getElementById('editCommentModal'));
    modal.show();
  }
});

// Submit edit komentar
document.getElementById('editCommentForm').addEventListener('submit', function(e) {
  e.preventDefault();
  var commentId = document.getElementById('edit-comment-id').value;
  var content = document.getElementById('edit-comment-content').value.trim();
  fetch('edit_comment.php', {
    method: 'POST',
    body: new URLSearchParams({comment_id: commentId, content: content})
  }).then(r => r.json()).then(res => {
    if (res.success) {
      var commentElem = document.querySelector('[data-comment-id="'+commentId+'"] .comment-content');
      commentElem.innerHTML = res.new_content.replace(/\n/g, '<br>');
      bootstrap.Modal.getInstance(document.getElementById('editCommentModal')).hide();
    } else {
      alert(res.error || 'Gagal mengedit komentar');
    }
  });
});

// Like Komentar
document.addEventListener('click', function(e){
  if(e.target.closest('.comment-like-btn')){
    e.preventDefault();
    var btn = e.target.closest('.comment-like-btn');
    var commentId = btn.getAttribute('data-comment-id');
    fetch('like_comment.php', {
      method: 'POST',
      body: new URLSearchParams({comment_id: commentId})
    }).then(r=>r.json()).then(res=>{
      if(res.status==='liked'){
        btn.classList.add('text-danger');
        btn.classList.remove('text-primary');
        btn.querySelector('i').className = 'bi bi-heart-fill';
      }else{
        btn.classList.remove('text-danger');
        btn.classList.add('text-primary');
        btn.querySelector('i').className = 'bi bi-heart';
      }
      btn.querySelector('.comment-like-count').textContent = res.likeCount;
    });
  }
});

// Reply Komentar
document.addEventListener('click', function(e){
  if(e.target.classList.contains('reply-link')){
    e.preventDefault();
    var commentId = e.target.getAttribute('data-comment-id');
    var form = document.querySelector('.reply-form[data-parent-id="'+commentId+'"]');
    if(form) form.classList.toggle('d-none');
  }
});

// Reply Komentar: submit form
document.querySelectorAll('.reply-form').forEach(function(form){
  form.addEventListener('submit', function(e){
    e.preventDefault();
    var postId = form.getAttribute('data-post-id');
    var parentId = form.getAttribute('data-parent-id');
    var textarea = form.querySelector('textarea[name="content"]');
    var content = textarea.value.trim();
    if(!content) return;
    var data = new FormData();
    data.append('post_id', postId);
    data.append('content', content);
    data.append('parent_id', parentId);

    fetch('post_comment.php', { method: 'POST', body: data })
      .then(r=>r.json()).then(res=>{
        if(res.success){
          var html = `<div class="d-flex align-items-start mt-2 ms-4" data-comment-id="${res.comment.id}">
            <img src="${res.comment.photo}" class="rounded-circle me-2" width="28" height="28" style="object-fit:cover;">
            <div>
              <div class="bg-light rounded px-3 py-2 position-relative">
                <span class="fw-semibold">${res.comment.name}</span>
                <span class="text-muted small">· ${res.comment.created_at}</span>
                <div class="comment-content">${res.comment.content.replace(/\n/g, '<br>')}</div>
                <a href="#" class="comment-like-btn d-inline-flex align-items-center text-decoration-none text-primary"
                  data-comment-id="${res.comment.id}">
                  <i class="bi bi-heart"></i>
                  <span class="comment-like-count ms-1">0</span>
                  <span class="ms-1">Like</span>
                </a>
              </div>
            </div>
          </div>`;
          form.insertAdjacentHTML('beforebegin', html);
          textarea.value = '';
          form.classList.add('d-none');
        }
      });
  });
});
