document.addEventListener('DOMContentLoaded', function () {
  window.toggleStoryInput = function (type) {
    const textInput = document.getElementById('story-text');
    const fileInput = document.getElementById('story-file');
    if (type === 'text') {
      textInput.style.display = 'block';
      fileInput.style.display = 'none';
    } else {
      textInput.style.display = 'none';
      fileInput.style.display = 'block';
    }
  };
});
