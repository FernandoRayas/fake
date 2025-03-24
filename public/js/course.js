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
      loadComponent(`course_assignments.php?cid=${cid}`, clickedElement);
      setTimeout(() => {
        scriptAssignments();
      }, 200);
    } else if (clickedElement === options[2]) {
      loadComponent("course_kardex.php", clickedElement);
    } else if (clickedElement === options[3]) {
      loadComponent(`course_enrolled.php?cid=${cid}`, clickedElement);
    } else if (clickedElement === options[4]) {
      loadComponent(`course_settings.php?cid=${cid}`, clickedElement);
    }
  });
});

const scriptAssignments = () => {
  const createAssignmentButton = document.getElementById(
    "create-assignment-button"
  );
  const createAssignmentModal = document.getElementById(
    "create-assignment-modal"
  );
  const inputAssignmentName = document.getElementById("assignment-name");
  const inputAssignmentDescription = document.getElementById(
    "assignment-description"
  );
  const inputAssignmentMaxScore = document.getElementById(
    "assignment-max-score"
  );
  const inputAssignmentTopic = document.getElementById("assignment-topic");
  const inputAssignmentFiles = document.getElementById("assignment-files");

  const createTopicModal = document.getElementById("create-topic-modal");
  const inputTopicName = document.getElementById("topic-name");
  const inputTopicDescription = document.getElementById("topic-description");

  const validateAssignmentData = () => {
    if (
      inputAssignmentName.value === "" ||
      inputAssignmentMaxScore.value === "" ||
      inputAssignmentTopic.options[selectedElement.selectedIndex].value ===
        "" ||
      inputAssignmentFiles.files.length === 0
    ) {
    }
  };
};
