const courseContent = document.getElementById("course-content");
const options = document.querySelectorAll(
  ".list-group-item.list-group-item-action.d-flex.align-items-center"
);
const urlParams = new URLSearchParams(window.location.search);
const cid = urlParams.get("cid");

const loadComponent = (componentName, clickedElement) => {
  options.forEach((option) => {
    option.classList.remove("active");
    const useElement = option.querySelector("use");
    if (useElement) {
      let href = useElement.getAttribute("xlink:href");
      if (href) {
        href = href.replace("-fill", "");
        useElement.setAttribute("xlink:href", href);
      }
    }
  });
  clickedElement.classList.add("active");
  const icon = clickedElement.querySelector("use");
  let href = icon.getAttribute("xlink:href");
  href += "-fill";
  icon.setAttribute("xlink:href", href);

  courseContent.innerHTML = "";

  fetch(`../pages/components/${componentName}`)
    .then((response) => response.text())
    .then((data) => {
      courseContent.innerHTML = data;
    })
    .catch((error) => {
      courseContent.innerHTML = "<h3>No se encontro la información</h3>";
      throw new Error(`Error al cargar información: ${error.message}`);
    });
};

options.forEach((option) => {
  option.addEventListener("click", (event) => {
    // Obtener el elemento que disparó el evento
    const clickedElement = event.currentTarget;

    // Determinar qué componente cargar según el elemento clicado
    if (clickedElement === options[0]) {
      loadComponent("course_home.php", clickedElement);
    } else if (clickedElement === options[1]) {
      loadComponent("assignments.php", clickedElement);
    } else if (clickedElement === options[2]) {
      loadComponent("kardex.php", clickedElement);
    } else if (clickedElement === options[3]) {
      loadComponent(`enrolled.php?cid=${cid}`, clickedElement);
    }
  });
});
