const modal = document.getElementById("create-course-modal");
const enrollModal = document.getElementById("enroll-course-modal");
const alertElement = document.getElementById("alert");

const displayAlert = (message, type) => {
  const alertMessage = document.getElementById("alert-message");

  alertElement.classList.add("d-flex");
  alertElement.classList.add(`alert-${type}`);
  alertMessage.textContent = message;

  setTimeout(() => {
    closeAlert();
  }, 2000);
};

const closeAlert = () => {
  const closeButton = alertElement.querySelector('[data-bs-dismiss="alert"]');

  closeButton.click();
};

if (modal !== null) {
  const inputCourseName = document.getElementById("course-name");
  const inputCourseDescription = document.getElementById("course-description");
  const createCourseButton = document.getElementById("create-course-btn");
  const courseNameFeedback = document.querySelector(
    "#course-name + .invalid-feedback"
  );
  const courseDescriptionFeedback = document.querySelector(
    "#course-description + .invalid-feedback"
  );

  const validateCourseData = () => {
    if (inputCourseName.value === "" && inputCourseDescription.value === "") {
      createCourseButton.disabled = true;
      inputCourseName.classList.add("is-invalid");
      inputCourseDescription.classList.add("is-invalid");
      courseNameFeedback.textContent = "El nombre del curso es requerido";
      courseDescriptionFeedback.textContent =
        "La descripción del curso es requerido";
      return false;
    }

    if (inputCourseName.value === "") {
      createCourseButton.disabled = true;
      inputCourseName.classList.add("is-invalid");
      courseNameFeedback.textContent = "El nombre del curso es requerido";
      return false;
    } else {
      inputCourseName.classList.remove("is-invalid");
      courseNameFeedback.textContent = "";
    }

    if (
      inputCourseName.value.length < 3 ||
      inputCourseName.value.length > 100
    ) {
      createCourseButton.disabled = true;
      inputCourseName.classList.add("is-invalid");
      courseNameFeedback.textContent =
        "El nombre del curso debe tener entre 3 y 100 caractéres";
      return false;
    } else {
      inputCourseName.classList.remove("is-invalid");
      courseNameFeedback.textContent = "";
    }

    if (inputCourseDescription.value === "") {
      createCourseButton.disabled = true;
      inputCourseDescription.classList.add("is-invalid");
      courseDescriptionFeedback.textContent =
        "La descripción del curso es requerido";
      return false;
    } else {
      inputCourseDescription.classList.remove("is-invalid");
      courseDescriptionFeedback.textContent = "";
    }

    if (
      inputCourseDescription.value.length < 3 ||
      inputCourseDescription.value.length > 250
    ) {
      createCourseButton.disabled = true;
      inputCourseDescription.classList.add("is-invalid");
      courseDescriptionFeedback.textContent =
        "La descripción del curso debe tener entre 3 y 250 caractéres";
      return false;
    } else {
      inputCourseDescription.classList.remove("is-invalid");
      courseDescriptionFeedback.textContent = "";
    }

    createCourseButton.disabled = false;
    return true;
  };

  const resetCourseModal = () => {
    modal.querySelectorAll("input, textarea, select").forEach((input) => {
      input.value = "";
    });

    createCourseButton.disabled = true;
    inputCourseName.classList.remove("is-invalid");
    inputCourseDescription.classList.remove("is-invalid");
    courseNameFeedback.textContent = "";
    courseDescriptionFeedback.textContent = "";
  };

  const createCourse = (data) => {
    fetch("../courses/add_course.php", {
      method: "POST",
      body: JSON.stringify(data),
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Error al agregar el curso");
        }
        return response.json();
      })
      .then((data) => {
        if (!data.isCourseCreated) {
          displayAlert(data.msg, "danger");
        } else {
          displayAlert(data.msg, "success");
        }
      })
      .catch((error) => console.error(`Ocurrio un error: ${error}`));
  };

  const reloadCourses = () => {
    const coursesContainer = document.querySelector(".courses-container");

    coursesContainer.innerHTML = "";

    fetch("../courses/get_courses.php")
      .then((response) => {
        if (!response.ok) {
          throw new Error(`Error al obtener los cursos`);
        }

        return response.json();
      })
      .then((data) => {
        data.courses.forEach((course) => {
          coursesContainer.innerHTML += `
          <div class="col">
            <div class="card mb-3" style="width: 18rem;">
              <div class="card-body">
                <h5 class="card-title row">
                  <a href="course.php?cid=${course.course_id}" class="link-secondary link-underline-secondary link-underline-opacity-0 link-underline-opacity-100-hover text-truncate">
                    ${course.course_name}
                  </a>
                </h5>
                <p class="card-text">${course.course_description}</p>
              </div>
              <div class="card-footer"></div>
            </div>
          </div>`;
        });
      })
      .catch((error) => {
        throw new Error(`Error al obtener los cursos: ${error.message}`);
      });
  };

  createCourseButton.addEventListener("click", () => {
    const data = {
      courseName: inputCourseName.value,
      courseDescription: inputCourseDescription.value,
    };

    createCourse(data);

    setTimeout(() => {
      reloadCourses();
    }, 100);

    const modalBootstrap = bootstrap.Modal.getInstance(modal);
    modalBootstrap.hide();
  });

  modal.addEventListener("hidden.bs.modal", () => {
    resetCourseModal();
  });

  inputCourseName.addEventListener("input", () => {
    validateCourseData();
  });

  inputCourseDescription.addEventListener("input", () => {
    validateCourseData();
  });
} else {
  const inputCourseCode = document.getElementById("course-code");
  const enrollCourseButton = document.getElementById("enroll-course-btn");
  const courseCodeFeedback = document.querySelector(
    "#course-code + .invalid-feedback"
  );

  const validateCourseCode = () => {
    if (inputCourseCode.value === "") {
      enrollCourseButton.disabled = true;
      inputCourseCode.classList.add("is-invalid");
      courseCodeFeedback.textContent = "El código es requerido";
      return false;
    }

    const pattern = /^[a-zA-Z0-9]+$/;

    if (!pattern.test(inputCourseCode.value)) {
      enrollCourseButton.disabled = true;
      inputCourseCode.classList.add("is-invalid");
      courseCodeFeedback.textContent = "El código debe ser alfanumerico";
      return false;
    } else {
      inputCourseCode.classList.remove("is-invalid");
      courseCodeFeedback.textContent = "";
    }

    enrollCourseButton.disabled = false;
    return true;
  };

  const resetEnrollModal = () => {
    enrollModal.querySelectorAll("input, textarea, select").forEach((input) => {
      input.value = "";
    });

    enrollCourseButton.disabled = true;
    inputCourseCode.classList.remove("is-invalid");
    courseCodeFeedback.textContent = "";
  };

  const reloadCourses = () => {};

  const enrollCourse = (data) => {
    fetch("../courses/enroll_course.php", {
      body: JSON.stringify(data),
      method: "POST",
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Error al inscribirse al curso");
        }

        return response.json();
      })
      .then((data) => console.log(data))
      .catch((error) =>
        console.log(`Error al inscribirse al curso: ${error.message}`)
      );
  };

  enrollCourseButton.addEventListener("click", () => {
    const data = {
      code: inputCourseCode.value,
    };

    enrollCourse(data);

    // setTimeout(() => {
    //   reloadCourses();
    // }, 100);

    const modalBootstrap = bootstrap.Modal.getInstance(enrollModal);
    modalBootstrap.hide();
  });

  enrollModal.addEventListener("hidden.bs.modal", () => {
    resetEnrollModal();
  });

  inputCourseCode.addEventListener("input", () => {
    validateCourseCode();
  });
}
