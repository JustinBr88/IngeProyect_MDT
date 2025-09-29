// JavaScript para la página Home - sin dependencias externas

const equiposContainer = document.getElementById("equipos-container");
const slides = document.querySelectorAll('#header-carousel .carousel-item');

document.addEventListener('DOMContentLoaded', async function () {
  // Carrusel
  let slideIndex = 0;

  const showSlide = function (index) {
    slides.forEach((slide, i) => {
      slide.style.display = i === index ? 'block' : 'none';
      slide.classList.toggle('active', i === index);
    });
  };

  const nextSlide = function () {
    slideIndex = (slideIndex + 1) % slides.length;
    showSlide(slideIndex);
  };
  setInterval(nextSlide, 3000);
  showSlide(slideIndex);

  // 8 productos preview
  // Funcionalidad de productos removida - no se usa API
  console.log('Productos se cargan directamente desde PHP');
});