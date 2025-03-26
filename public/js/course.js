const courseContent = document.getElementById("course-content");
const options = document.querySelectorAll(
  ".list-group-item.list-group-item-action.d-flex.align-items-center"
);
const urlParams = new URLSearchParams(window.location.search);
const cid = urlParams.get("cid");

const resetCourseModal = (modal) => {
  modal.querySelectorAll("input, textarea, select").forEach((input) => {
    input.value = "";
    input.classList.remove("is-invalid");
    input.querySelectorAll(".invalid-feedback").forEach((element) => {
      element.textContent = "";
    });
  });
};

const closeModal = (modal) => {
  const modalBootstrap = bootstrap.Modal.getInstance(modal);
  modalBootstrap.hide();
};

const reloadCoursework = () => {
  fetch(`../pages/components/course_assignments.php?cid=${cid}`)
    .then((response) => {
      if (!response.ok) {
        throw new Error("Error al obtener la información");
      }
      return response.text();
    })
    .then((data) => {
      courseContent.innerHTML = data;
    })
    .catch((error) => {
      courseContent.innerHTML = "<h3>No se encontro la información</h3>";
      throw new Error(`Error al cargar información: ${error.message}`);
    });
};

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
  const alphanumericSpacesPattern = /^[a-zA-Z0-9\s:áéíóúñÁÉÍÓÚÑ\-\'\.\n]+$/;

  const dropdown = document.getElementById("dropdown");

  if (dropdown) {
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
    const inputAssignmentSubmissionDate = document.getElementById(
      "assignment-submission-date"
    );
    const inputAssignmentSubmissionTime = document.getElementById(
      "assignment-submission-time"
    );
    const inputAssignmentFiles = document.getElementById("assignment-files");
    const assignmentNameFeedback = document.querySelector(
      "#assignment-name + .invalid-feedback"
    );
    const assignmentDescriptionFeedback = document.querySelector(
      "#assignment-description + .invalid-feedback"
    );
    const assignmentMaxScoreFeedback = document.querySelector(
      "#assignment-max-score + .invalid-feedback"
    );
    const assignmentTopicFeedback = document.querySelector(
      "#assignment-topic + .invalid-feedback"
    );
    const assignmentSubmissionDateFeedback = document.querySelector(
      "#assignment-submission-date + .invalid-feedback"
    );
    const assignmentSubmissionTimeFeedback = document.querySelector(
      "#assignment-submission-time + .invalid-feedback"
    );
    const assignmentFilesFeedback = document.querySelector(
      "#assignment-files + .invalid-feedback"
    );

    const createAssignmentButton = document.getElementById(
      "create-assignment-button"
    );
    const createTopicModal = document.getElementById("create-topic-modal");
    const inputTopicName = document.getElementById("topic-name");
    const inputTopicDescription = document.getElementById("topic-description");

    const topicNameFeedback = document.querySelector(
      "#topic-name + .invalid-feedback"
    );
    const topicDescriptionFeedback = document.querySelector(
      "#topic-description + .invalid-feedback"
    );
    const createTopicButton = document.getElementById("create-topic-button");

    const getCurrentDate = () => {
      const now = new Date();
      const year = now.getFullYear();
      const month = String(now.getMonth() + 1).padStart(2, "0");
      const day = String(now.getDate()).padStart(2, "0");

      return `${year}-${month}-${day}`;
    };

    const getCurrentTime = () => {
      const now = new Date();
      const hours = String(now.getHours()).padStart(2, "0");
      const minutes = String(now.getMinutes()).padStart(2, "0");

      return `${hours}:${minutes}`;
    };

    inputAssignmentSubmissionDate.min = getCurrentDate();
    inputAssignmentSubmissionTime.min = getCurrentTime();

    const showValidation = (input, feedback, message, isValid) => {
      if (isValid) {
        input.classList.remove("is-invalid");
        feedback.textContent = "";
      } else {
        input.classList.add("is-invalid");
        feedback.textContent = message;
      }
    };

    const validateAssignmentData = () => {
      const fileExtesions = [
        "pdf",
        "docx",
        "xlsx",
        "csv",
        "pptx",
        "jpg",
        "jpeg",
        "png",
        "mp3",
        "wav",
        "mp4",
        "txt",
        "rft",
        "zip",
      ];

      if (inputAssignmentName.value.trim() === "") {
        showValidation(
          inputAssignmentName,
          assignmentNameFeedback,
          "El nombre de la tarea no puede estar vacío",
          false
        );
        createAssignmentButton.disabled = true;
        return false;
      } else {
        showValidation(inputAssignmentName, assignmentNameFeedback, "", true);
      }

      if (!alphanumericSpacesPattern.test(inputAssignmentName.value.trim())) {
        showValidation(
          inputAssignmentName,
          assignmentNameFeedback,
          "El nombre debe contener letras, números o espacios",
          false
        );
        createAssignmentButton.disabled = true;
        return false;
      } else {
        showValidation(inputAssignmentName, assignmentNameFeedback, "", true);
      }

      if (
        inputAssignmentDescription.value.trim() !== "" &&
        !alphanumericSpacesPattern.test(inputAssignmentDescription.value.trim())
      ) {
        showValidation(
          inputAssignmentDescription,
          assignmentDescriptionFeedback,
          "La descripción debe contener letras, números o espacios",
          false
        );
        createAssignmentButton.disabled = true;
        return false;
      } else {
        showValidation(
          inputAssignmentDescription,
          assignmentDescriptionFeedback,
          "",
          true
        );
      }

      if (
        inputAssignmentMaxScore.value.trim() === "" ||
        isNaN(inputAssignmentMaxScore.value) ||
        parseFloat(inputAssignmentMaxScore.value) <= 0
      ) {
        showValidation(
          inputAssignmentMaxScore,
          assignmentMaxScoreFeedback,
          "La puntuación máxima debe ser mayor a 0",
          false
        );
        createAssignmentButton.disabled = true;
        return false;
      } else {
        showValidation(
          inputAssignmentMaxScore,
          assignmentMaxScoreFeedback,
          "",
          true
        );
      }

      if (parseFloat(inputAssignmentMaxScore.value) > 100) {
        showValidation(
          inputAssignmentMaxScore,
          assignmentMaxScoreFeedback,
          "La puntuación máxima no puede ser mayor a 100",
          false
        );
        createAssignmentButton.disabled = true;
        return false;
      } else {
        showValidation(
          inputAssignmentMaxScore,
          assignmentMaxScoreFeedback,
          "",
          true
        );
      }

      if (
        inputAssignmentTopic.options[inputAssignmentTopic.selectedIndex]
          .value === ""
      ) {
        showValidation(
          inputAssignmentTopic,
          assignmentTopicFeedback,
          "Selecciona el tema de la tarea",
          false
        );
        createAssignmentButton.disabled = true;
        return false;
      } else {
        showValidation(inputAssignmentTopic, assignmentTopicFeedback, "", true);
      }

      if (
        inputAssignmentSubmissionDate.value === "" ||
        inputAssignmentSubmissionDate.value < getCurrentDate()
      ) {
        showValidation(
          inputAssignmentSubmissionDate,
          assignmentSubmissionDateFeedback,
          "Selecciona una fecha válida",
          false
        );
        createAssignmentButton.disabled = true;
        return false;
      } else {
        showValidation(
          inputAssignmentSubmissionDate,
          assignmentSubmissionDateFeedback,
          "",
          true
        );
      }

      if (
        inputAssignmentSubmissionTime.value !== "" &&
        inputAssignmentSubmissionTime.value < getCurrentTime()
      ) {
        showValidation(
          inputAssignmentSubmissionTime,
          assignmentSubmissionTimeFeedback,
          "Selecciona una hora válida",
          false
        );
        createAssignmentButton.disabled = true;
        return false;
      } else {
        showValidation(
          inputAssignmentSubmissionTime,
          assignmentSubmissionTimeFeedback,
          "",
          true
        );
      }

      if (inputAssignmentFiles.files.length > 0) {
        let invalidFiles = [];
        for (
          let fileIndex = 0;
          fileIndex < inputAssignmentFiles.files.length;
          fileIndex++
        ) {
          const file = inputAssignmentFiles.files[fileIndex];
          const fileExtension = file.name.split(".").pop().toLowerCase();
          if (fileExtesions.indexOf(fileExtension) === -1) {
            invalidFiles.push(file.name);
          }
        }

        if (invalidFiles.length > 0) {
          const message = `Los siguientes archivos no son válidos: ${invalidFiles.join(
            ", "
          )}`;

          showValidation(
            inputAssignmentFiles,
            assignmentFilesFeedback,
            message,
            false
          );
          createAssignmentButton.disabled = true;
          return false;
        } else {
          showValidation(
            inputAssignmentFiles,
            assignmentFilesFeedback,
            "",
            true
          );
        }

        return false;
      } else {
        showValidation(inputAssignmentFiles, assignmentFilesFeedback, "", true);
        createAssignmentButton.disabled = false;
      }
    };

    const validateTopicData = () => {
      if (inputTopicName.value.trim() === "") {
        showValidation(
          inputTopicName,
          topicNameFeedback,
          "El nombre del Tema no puede estar vacío",
          false
        );
        createTopicButton.disabled = true;
        return false;
      } else {
        showValidation(inputTopicName, topicNameFeedback, "", true);
        createTopicButton.disabled = false;
      }

      if (!alphanumericSpacesPattern.test(inputTopicName.value.trim())) {
        showValidation(
          inputTopicName,
          topicNameFeedback,
          "El nombre del Tema solo puede contener letras, números o espacios",
          false
        );
        createTopicButton.disabled = true;
        return false;
      } else {
        showValidation(inputTopicName, topicNameFeedback, "", true);
        createTopicButton.disabled = false;
      }

      if (inputTopicName.value.trim().length < 3) {
        showValidation(
          inputTopicName,
          topicNameFeedback,
          "El nombre del Tema debe ser mayor a 3 caracteres",
          false
        );
        createTopicButton.disabled = true;
        return false;
      } else {
        showValidation(inputTopicName, topicNameFeedback, "", true);
        createTopicButton.disabled = false;
      }

      if (inputTopicName.value.trim().length > 100) {
        showValidation(
          inputTopicName,
          topicNameFeedback,
          "El nombre del Tema no puede ser mayor a 100 caracteres",
          false
        );
        createTopicButton.disabled = true;
        return false;
      } else {
        showValidation(inputTopicName, topicNameFeedback, "", true);
        createTopicButton.disabled = false;
      }

      if (
        inputTopicDescription.value.trim() !== "" &&
        !alphanumericSpacesPattern.test(inputTopicDescription.value.trim())
      ) {
        showValidation(
          inputTopicDescription,
          topicDescriptionFeedback,
          "Solo se permiten letras, números o espacios",
          false
        );
        createTopicButton.disabled = true;
        return false;
      } else {
        showValidation(
          inputTopicDescription,
          topicDescriptionFeedback,
          "",
          true
        );
        createTopicButton.disabled = false;
      }
    };

    const createAssignment = (data) => {
      fetch("../assignments/create_assignment.php", {
        body: JSON.stringify(data),
        method: "POST",
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error("Error al crear la tarea");
          }

          return response.json();
        })
        .then((data) => {
          console.log(data);
        })
        .catch((error) => {
          throw new Error(`Error al crear la tarea: ${error.message}`);
        });
    };

    const createTopic = (data) => {
      fetch("../topics/create_topic.php", {
        body: JSON.stringify(data),
        method: "POST",
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error("Error al crear el tema");
          }

          return response.json();
        })
        .then((data) => {
          console.log(data);
        })
        .catch((error) => {
          throw new Error(`Error al crear el tema: ${error.message}`);
        });
    };

    const createFile = () => {
      const files = inputAssignmentFiles.files;
      if (files.length > 0) {
        const formData = new FormData();

        for (let i = 0; i < files.length; i++) {
          formData.append("assignment-files[]", files[i]);
        }

        fetch("../files/create_file.php", {
          method: "POST",
          body: formData,
        })
          .then((response) => {
            if (!response.ok) {
              throw new Error("Error al agregar el archivo");
            }

            return response.json();
          })
          .then((data) => {
            console.log(data);
          })
          .catch((error) => {
            throw new Error(`Error al agregar el archivo: ${error.message}`);
          });
      }
    };

    inputAssignmentName.addEventListener("blur", () => {
      validateAssignmentData();
    });

    inputAssignmentDescription.addEventListener("blur", () => {
      validateAssignmentData();
    });

    inputAssignmentMaxScore.addEventListener("blur", () => {
      validateAssignmentData();
    });

    inputAssignmentTopic.addEventListener("change", () => {
      validateAssignmentData();
    });

    inputAssignmentSubmissionDate.addEventListener("change", () => {
      validateAssignmentData();
    });

    inputAssignmentSubmissionTime.addEventListener("change", () => {
      validateAssignmentData();
    });

    inputAssignmentFiles.addEventListener("change", () => {
      validateAssignmentData();
    });

    inputTopicName.addEventListener("blur", () => {
      validateTopicData();
    });

    inputTopicDescription.addEventListener("blur", () => {
      validateTopicData();
    });

    createAssignmentModal.addEventListener("hidden.bs.modal", () => {
      resetCourseModal(createAssignmentModal);
    });

    createTopicModal.addEventListener("hidden.bs.modal", () => {
      resetCourseModal(createTopicModal);
    });

    createAssignmentButton.addEventListener("click", () => {
      let submissionTime = "";
      if (inputAssignmentSubmissionTime.value == "") {
        submissionTime = "23:59";
      } else {
        submissionTime = inputAssignmentSubmissionTime.value;
      }

      const data = {
        assignmentName: inputAssignmentName.value,
        assignmentDescription: inputAssignmentDescription.value,
        assignmentMaxScore: inputAssignmentMaxScore.value,
        assignmentSubmissionDate: inputAssignmentSubmissionDate.value,
        assignmentSubmissionTime: submissionTime,
        topic: inputAssignmentTopic.value,
      };

      createAssignment(data);

      createFile();

      setTimeout(() => {
        reloadCoursework();
      }, 200);

      closeModal(createAssignmentModal);
    });

    createTopicButton.addEventListener("click", () => {
      const data = {
        topicName: inputTopicName.value,
        topicDescription: inputTopicDescription.value,
        course: cid,
      };

      createTopic(data);

      setTimeout(() => {
        reloadCoursework();
      }, 200);

      closeModal(createTopicModal);
    });
  }
};
