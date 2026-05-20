document.addEventListener("DOMContentLoaded", function () {
  const toastEl = document.getElementById('copyToast');
  const toast = new bootstrap.Toast(toastEl);

  document.querySelectorAll('.copy-link').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const link = this.getAttribute('data-link');
      const postId = this.dataset.postid;

      // Salin ke clipboard
      navigator.clipboard.writeText(link).then(() => {
        toast.show(); // ✅ tampilkan toast
        recordShare(postId, 'copy');
      }).catch(err => {
        alert("Gagal menyalin tautan.");
      });
    });
  });

  document.querySelectorAll('.share-track').forEach(btn => {
    btn.addEventListener('click', function () {
      const postId = this.dataset.postid;
      const type = this.dataset.type;
      recordShare(postId, type);
    });
  });

  function recordShare(postId, type) {
    fetch('record_share.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `post_id=${postId}&share_type=${type}`
    })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        // Temukan elemen .share-count yang sesuai dengan post_id
        const postElement = document.querySelector(`[data-post-id='${postId}']`)?.closest('.post-container');
        if (postElement) {
          const shareCountElem = postElement.querySelector('.share-count');
          if (shareCountElem) {
            shareCountElem.textContent = data.shareCount;
          }
        }
      }
    })
    .catch(err => {
      console.error('Share error:', err);
    });
  }
});
