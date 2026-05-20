// Story Upload JavaScript
document.addEventListener('DOMContentLoaded', function () {
  const storyTypeSelect = document.getElementById('storyType');
  const textContent = document.getElementById('textContent');
  const fileContent = document.getElementById('fileContent');
  const storyFileInput = document.getElementById('storyFileInput');
  const filePreviewContainer = document.getElementById('filePreviewContainer');

  // Saat tipe dipilih
  storyTypeSelect.addEventListener('change', function () {
    const type = this.value;
    if (type === 'text') {
      textContent.classList.remove('d-none');
      fileContent.classList.add('d-none');
    } else {
      textContent.classList.add('d-none');
      fileContent.classList.remove('d-none');
    }
    // Reset preview
    filePreviewContainer.innerHTML = '';
    storyFileInput.value = '';
  });

  // Preview otomatis saat file dipilih
  storyFileInput.addEventListener('change', function () {
    const file = this.files[0];
    filePreviewContainer.innerHTML = '';
    const alertBox = document.getElementById('storyAlert');
    alertBox.classList.add('d-none'); // Sembunyikan alert

    if (!file) return;

    const selectedType = storyTypeSelect.value;
    const fileType = file.type;

    const isValid =
      (selectedType === 'image' && fileType.startsWith('image/')) ||
      (selectedType === 'video' && fileType.startsWith('video/')) ||
      (selectedType === 'music' && fileType.startsWith('audio/'));

    if (!isValid) {
      alertBox.textContent = `File yang kamu pilih tidak sesuai dengan tipe story "${selectedType}". Harap pilih file yang benar.`;
      alertBox.classList.remove('d-none');
      this.value = ''; // Kosongkan file input
      return;
    }

    alertBox.classList.add('d-none');
    alertBox.textContent = '';

    // Tampilkan preview sesuai file
    if (fileType.startsWith('image/')) {
      const img = document.createElement('img');
      img.src = URL.createObjectURL(file);
      img.className = 'img-fluid rounded';
      img.style.maxHeight = '200px';
      filePreviewContainer.appendChild(img);
    } else if (fileType.startsWith('video/')) {
      const video = document.createElement('video');
      video.src = URL.createObjectURL(file);
      video.className = 'w-100 rounded';
      video.controls = true;
      video.style.maxHeight = '200px';
      filePreviewContainer.appendChild(video);

      video.onloadedmetadata = function () {
        window.URL.revokeObjectURL(video.src);
        const duration = video.duration;
        if (duration > 60) {
          alertBox.textContent = 'Video tidak boleh lebih dari 1 menit.';
          alertBox.classList.remove('d-none');
          storyFileInput.value = '';
          filePreviewContainer.innerHTML = '';
        }
      };
    } else if (fileType.startsWith('audio/')) {
      const audio = document.createElement('audio');
      audio.src = URL.createObjectURL(file);
      audio.className = 'w-100 mt-2';
      audio.controls = true;
      filePreviewContainer.appendChild(audio);

      audio.onloadedmetadata = function () {
        window.URL.revokeObjectURL(audio.src);
        const duration = audio.duration;
        if (duration < 15 || duration > 30) {
          alertBox.textContent = 'Musik harus antara 15 – 30 detik.';
          alertBox.classList.remove('d-none');
          storyFileInput.value = '';
          filePreviewContainer.innerHTML = '';
        }
      };
    }
  });
});
