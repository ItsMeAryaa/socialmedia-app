document.addEventListener('DOMContentLoaded', function () {
  let zoom = 1, minZoom = 1, maxZoom = 3, isDragging = false;
  let startX = 0, startY = 0, imgX = 0, imgY = 0;

  const imgPreview = document.getElementById('modal-image-preview');
  const imageModal = document.getElementById('imageModal');

  function resetZoom() {
    zoom = 1; imgX = 0; imgY = 0;
    if (imgPreview) {
      imgPreview.style.transform = `translate(0px,0px) scale(1)`;
      imgPreview.style.cursor = "default";
    }
  }

  // Ganti gambar saat thumbnail diklik
  document.querySelectorAll('.post-image-thumb').forEach(function (img) {
    img.addEventListener('click', function () {
      const src = this.getAttribute('data-img');
      if (imgPreview) {
        imgPreview.src = src;
        resetZoom();
      }
    });
  });

  // Zoom pakai scroll mouse
  imageModal.addEventListener('wheel', function (e) {
    if (!imgPreview || !imgPreview.src) return;
    e.preventDefault();
    const delta = e.deltaY || e.detail || e.wheelDelta;
    if (delta < 0 && zoom < maxZoom) zoom += 0.1;
    if (delta > 0 && zoom > minZoom) zoom -= 0.1;
    zoom = Math.max(minZoom, Math.min(maxZoom, zoom));
    imgPreview.style.transform = `translate(${imgX}px,${imgY}px) scale(${zoom})`;
  }, { passive: false });

  // Geser gambar saat di-zoom
  imgPreview.addEventListener('mousedown', function (e) {
    if (zoom <= 1) return;
    isDragging = true;
    startX = e.clientX - imgX;
    startY = e.clientY - imgY;
    imgPreview.style.cursor = "grabbing";
  });

  document.addEventListener('mousemove', function (e) {
    if (isDragging) {
      imgX = e.clientX - startX;
      imgY = e.clientY - startY;
      imgPreview.style.transform = `translate(${imgX}px,${imgY}px) scale(${zoom})`;
    }
  });

  document.addEventListener('mouseup', function () {
    isDragging = false;
    if (zoom > 1) imgPreview.style.cursor = "grab";
    else imgPreview.style.cursor = "default";
  });

  // Reset saat modal ditutup
  imageModal.addEventListener('hidden.bs.modal', resetZoom);

  // Double click untuk reset
  imgPreview.addEventListener('dblclick', resetZoom);
});
