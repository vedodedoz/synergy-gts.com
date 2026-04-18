function toggleMenu(){
  const nav = document.getElementById("nav");
  nav.style.display = nav.style.display === "flex" ? "none" : "flex";
}

const counters = document.querySelectorAll('.counter');

counters.forEach(counter => {
  const update = () => {
    const target = +counter.getAttribute('data-target');
    const count = +counter.innerText;
    const increment = target / 200;

    if(count < target){
      counter.innerText = Math.ceil(count + increment);
      setTimeout(update, 10);
    } else {
      counter.innerText = target;
    }
  };
  update();
});

let isEnglish = true;
function switchLang(){
  const title = document.getElementById("hero-title");
  const sub = document.getElementById("hero-sub");

  if(isEnglish){
    title.innerText = "Transformación Empresarial SAP";
    sub.innerText = "Impulsando Excelencia Digital en Europa";
  } else {
    title.innerText = "Enterprise SAP Transformation";
    sub.innerText = "Driving Digital Excellence Across Europe";
  }
  isEnglish = !isEnglish;
}
const reveals = document.querySelectorAll(".reveal");

window.addEventListener("scroll", () => {
  reveals.forEach(section => {
    const windowHeight = window.innerHeight;
    const elementTop = section.getBoundingClientRect().top;

    if (elementTop < windowHeight - 100) {
      section.classList.add("active");
    }
  });
});

let index = 0;
const testimonials = document.querySelectorAll(".testimonial");

setInterval(() => {
  testimonials[index].classList.remove("active");
  index = (index + 1) % testimonials.length;
  testimonials[index].classList.add("active");
}, 4000);

// Header Shrink Effect
window.addEventListener("scroll", function() {
  const header = document.querySelector("header");
  header.classList.toggle("shrink", window.scrollY > 80);
});