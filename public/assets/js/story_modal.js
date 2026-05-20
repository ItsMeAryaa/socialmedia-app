// Fungsi buat munculin modal hapus story
function showStoryOptions(storyId) {
  console.log('Tombol titik tiga diklik, id:', storyId); // buat debug aja

  // Tutup dulu modal story viewer biar nggak tabrakan
  const viewerModalEl = document.getElementById('storyViewerModal');
  const viewerModal = bootstrap.Modal.getInstance(viewerModalEl);
  if (viewerModal) viewerModal.hide();

  // Tunggu animasi modal viewer selesai dulu
  setTimeout(() => {
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteStoryModal'));
    document.getElementById('storyIdInput').value = storyId;
    deleteModal.show();
  }, 300);
}
