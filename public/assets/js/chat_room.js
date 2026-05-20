document.addEventListener('DOMContentLoaded', () => {
  const chatRoom = document.querySelector('.chat-room');
  const friendList = document.querySelector('.friend-list');
  const closeBtn = document.getElementById('closeChat');

  document.querySelectorAll('.friend-item').forEach(item => {
    item.addEventListener('click', () => {
      chatRoom.style.display = 'block';
      friendList.style.display = window.innerWidth <= 768 ? 'none' : 'block';
    });
  });

  closeBtn.addEventListener('click', () => {
    chatRoom.style.display = 'none';
    friendList.style.display = 'block';
  });

  window.addEventListener('resize', () => {
    if (window.innerWidth > 768) {
      friendList.style.display = 'block';
      chatRoom.style.display = 'block';
    }
  });
});
