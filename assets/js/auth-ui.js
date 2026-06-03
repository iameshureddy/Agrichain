const cards = document.querySelectorAll('.reveal');

cards.forEach(card => {
  setTimeout(() => {
    card.classList.add('active');
  }, 200);
});
