document.querySelectorAll('.like-btn').forEach(function(btn) {
  btn.addEventListener('click', function(e) {
    e.preventDefault();
    var anchor = this;
    var postId = anchor.getAttribute('data-post-id');
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'like_post.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
      if (xhr.status === 200) {
        var res = JSON.parse(xhr.responseText);
        if (res.status === 'liked') {
          anchor.classList.add('liked');
          anchor.querySelector('i').className = 'bi bi-heart-fill text-danger';
        } else {
          anchor.classList.remove('liked');
          anchor.querySelector('i').className = 'bi bi-heart';
        }
        anchor.querySelector('.like-count').textContent = res.likeCount;
      }
    };
    xhr.send('post_id=' + encodeURIComponent(postId));
  });
});
