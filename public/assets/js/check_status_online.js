// Check status online
function checkOnlineStatus() {
  const ids = Array.from(document.querySelectorAll('.friend-photo'))
    .map(img => img.getAttribute('data-user-id'));

  fetch('check_online_status.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ ids })
  })
  .then(res => res.json())
  .then(statuses => {
    for (const id in statuses) {
      const dot = document.getElementById(`status-dot-${id}`);
      if (dot) {
        dot.style.backgroundColor = statuses[id] === 'online' ? 'green' : 'gray';
      }
    }
  });
}

// Cek awal dan ulangi setiap 10 detik
checkOnlineStatus();
setInterval(checkOnlineStatus, 10000);
