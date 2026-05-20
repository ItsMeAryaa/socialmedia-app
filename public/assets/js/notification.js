document.addEventListener("DOMContentLoaded", function () {
  const notifiModal = document.getElementById('notificationModal');

  if (notifiModal) {
    notifiModal.addEventListener('show.bs.modal', function () {
      fetch('notifications.php')
        .then(res => res.text())
        .then(html => {
          document.getElementById('notifList').innerHTML = html;

          // Hapus hanya notif-dot
          const notifDot = document.getElementById('notif-dot');
          if (notifDot) notifDot.remove();
        });
    });
  }
});
