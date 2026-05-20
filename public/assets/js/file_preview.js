// File Preview untuk foto dan video

// Preview foto
document.getElementById('photo-input').addEventListener('change', function(e){
  const preview = document.getElementById('photo-preview');
  preview.innerHTML = '';
  for (const file of e.target.files) {
    const url = URL.createObjectURL(file);
    const img = document.createElement('img');
    img.src = url;
    img.style = "max-width:180px;max-height:180px;margin:30px 20px 20px 20px;object-fit:cover;border-radius:8px;box-shadow:0 1px 4px #0003;";
    preview.appendChild(img);
  }
});

// Preview video
document.getElementById('video-input').addEventListener('change', function(e){
  const preview = document.getElementById('video-preview');
  preview.innerHTML = '';
  for (const file of e.target.files) {
    const url = URL.createObjectURL(file);
    const video = document.createElement('video');
    video.src = url;
    video.controls = true;
    video.style = "max-width:220px;max-height:180px;margin:30px 20px 20px 20px;border-radius:8px;box-shadow:0 1px 4px #0003;";
    preview.appendChild(video);
  }
});
