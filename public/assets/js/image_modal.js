document.querySelectorAll('.post-image-thumb').forEach(function(img){
  img.addEventListener('click', function(){
    var src = this.getAttribute('data-img');
    document.getElementById('modal-image-preview').src = src;
  });
});
