function updateLastActive() {
  fetch('update_last_active.php', { method: 'POST' });
}

// Jalankan setiap 3 detik
setInterval(updateLastActive, 3000);

// Jalankan pertama kali langsung
updateLastActive();
