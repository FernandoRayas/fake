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
      loadComponent(`course_home.php?cid=${cid}`, clickedElement);
    } else if (clickedElement === options[1]) {
      loadComponent(`course_assignments.php?cid=${cid}`, clickedElement);
      setTimeout(() => {
        scriptAssignments();
      }, 200);
    } else if (clickedElement === options[2]) {
      loadComponent(`course_kardex.php?cid=${cid}`, clickedElement);
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

        fetch(`../files/create_assignment_file.php`, {
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

// Variables globales
let questionIndex = 1;

// Función para añadir una nueva pregunta
function addQuestion() {
    const container = document.getElementById('questions-container');
    const questionDiv = document.createElement('div');
    questionDiv.className = 'question-container';
    questionDiv.dataset.questionIndex = questionIndex;
    
    questionDiv.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6>Pregunta #${questionIndex + 1}</h6>
            <button type="button" class="btn btn-sm btn-outline-danger remove-question" onclick="removeQuestion(this)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Texto de la pregunta *</label>
            <input type="text" class="form-control" name="questions[${questionIndex}][text]" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Tipo de pregunta *</label>
            <select class="form-select question-type" name="questions[${questionIndex}][type]" onchange="toggleQuestionType(this)" required>
                <option value="open">Abierta (respuesta de texto)</option>
                <option value="closed">Cerrada (opciones múltiples)</option>
            </select>
        </div>
        
        <div class="options-container" style="display: none;">
            <label class="form-label">Opciones de respuesta</label>
            <div class="options-list">
                <div class="option-row d-flex">
                    <input type="text" class="form-control me-2" name="questions[${questionIndex}][options][]" placeholder="Opción">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-option" onclick="removeOption(this)" disabled>
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="option-row d-flex">
                    <input type="text" class="form-control me-2" name="questions[${questionIndex}][options][]" placeholder="Opción">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-option" onclick="removeOption(this)" disabled>
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="addOption(this)">
                <i class="fas fa-plus me-1"></i>Añadir opción
            </button>
        </div>
    `;
    
    container.appendChild(questionDiv);
    questionIndex++;
    
    // Habilitar todos los botones de eliminar si hay más de una pregunta
    const removeButtons = document.querySelectorAll('.remove-question');
    if (removeButtons.length > 1) {
        removeButtons.forEach(btn => btn.disabled = false);
    }
}

// Función para eliminar una pregunta
function removeQuestion(button) {
    const questionContainer = button.closest('.question-container');
    questionContainer.remove();
    
    // Recalcular numeración de preguntas
    const questionHeaders = document.querySelectorAll('.question-container h6');
    questionHeaders.forEach((header, index) => {
        header.textContent = `Pregunta #${index + 1}`;
    });
    
    // Si solo queda una pregunta, deshabilitar su botón de eliminar
    const removeButtons = document.querySelectorAll('.remove-question');
    if (removeButtons.length === 1) {
        removeButtons[0].disabled = true;
    }
}

// Función para cambiar el tipo de pregunta
function toggleQuestionType(select) {
    const questionContainer = select.closest('.question-container');
    const optionsContainer = questionContainer.querySelector('.options-container');
    
    if (select.value === 'closed') {
        optionsContainer.style.display = 'block';
        
        // Validar que haya al menos dos opciones
        const optionInputs = optionsContainer.querySelectorAll('input[type="text"]');
        optionInputs.forEach(input => input.required = true);
    } else {
        optionsContainer.style.display = 'none';
        
        // Quitar la validación de opciones
        const optionInputs = optionsContainer.querySelectorAll('input[type="text"]');
        optionInputs.forEach(input => input.required = false);
    }
}

// Función para añadir una opción de respuesta
function addOption(button) {
    const optionsContainer = button.previousElementSibling;
    const newOption = document.createElement('div');
    newOption.className = 'option-row d-flex';
    newOption.innerHTML = `
        <input type="text" class="form-control me-2" name="${optionsContainer.querySelector('input').name}" placeholder="Opción" required>
        <button type="button" class="btn btn-sm btn-outline-danger remove-option" onclick="removeOption(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    optionsContainer.appendChild(newOption);
    
    // Habilitar todos los botones de eliminar si hay más de dos opciones
    const removeButtons = optionsContainer.querySelectorAll('.remove-option');
    if (removeButtons.length > 2) {
        removeButtons.forEach(btn => btn.disabled = false);
    }
}

// Función para eliminar una opción de respuesta
function removeOption(button) {
    const optionRow = button.closest('.option-row');
    const optionsList = optionRow.closest('.options-list');
    optionRow.remove();
    
    // Si solo quedan dos opciones, deshabilitar los botones de eliminar
    const remainingOptions = optionsList.querySelectorAll('.option-row');
    if (remainingOptions.length <= 2) {
        const removeButtons = optionsList.querySelectorAll('.remove-option');
        removeButtons.forEach(btn => btn.disabled = true);
    }
}

// Función para enviar el formulario de creación/edición
function submitQuizForm() {
    // Validar que todas las preguntas tengan texto
    const questionTexts = document.querySelectorAll('input[name$="[text]"]');
    let valid = true;
    
    questionTexts.forEach(input => {
        if (!input.value.trim()) {
            valid = false;
            input.classList.add('is-invalid');
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    // Validar que las preguntas cerradas tengan al menos dos opciones válidas
    const questionTypes = document.querySelectorAll('.question-type');
    questionTypes.forEach(select => {
        if (select.value === 'closed') {
            const questionContainer = select.closest('.question-container');
            const optionInputs = questionContainer.querySelectorAll('.options-list input[type="text"]');
            
            let validOptions = 0;
            optionInputs.forEach(input => {
                if (input.value.trim()) {
                    validOptions++;
                    input.classList.remove('is-invalid');
                } else {
                    input.classList.add('is-invalid');
                }
            });
            
            if (validOptions < 2) {
                valid = false;
            }
        }
    });
    
    if (valid) {
        document.getElementById('quizForm').submit();
    } else {
        alert('Por favor completa correctamente todos los campos requeridos.');
    }
}

// Función para ver un cuestionario (profesor)
function viewQuizDetails(quizId) {
    // Cargar detalles mediante AJAX
    $.ajax({
        url: '../quizzes/quiz_controller.php',
        type: 'GET',
        data: {
            action: 'get_quiz_details',
            quiz_id: quizId
        },
        success: function(response) {
            $('#quizDetailsContent').html(response);
            $('#quizDetailsModal').modal('show');
        },
        error: function() {
            alert('Error al cargar los detalles del cuestionario.');
        }
    });
}

// Función para editar un cuestionario
function editQuiz(quizId) {
  // Obtener el parámetro 'cid' de la URL actual (ej: "course.php?cid=55")
  const urlParams = new URLSearchParams(window.location.search);
  const courseId = urlParams.get('cid');  // 'cid' es el nombre del parámetro en tu URL
  
  // Redirigir con ambos parámetros
  window.location.href = `edit_quiz.php?quiz_id=${quizId}&cid=${courseId}`;
}

function confirmDeleteQuiz(quizId) {
  // Obtiene el cid de la URL actual
  const urlParams = new URLSearchParams(window.location.search);
  const courseId = urlParams.get('cid');
  
  if (confirm('¿Estás seguro de eliminar este cuestionario?')) {
      window.location.href = `../quizzes/quiz_controller.php?action=delete_quiz&quiz_id=${quizId}&cid=${courseId}`;
  }
}

// Función para cargar estadísticas de un cuestionario
function loadQuizStatistics() {
    const quizId = $('#quizSelector').val();
    
    if (!quizId) {
        $('#statistics-container').html('<div class="alert alert-info"><i class="fas fa-chart-pie me-2"></i>Selecciona un cuestionario para ver sus estadísticas.</div>');
        return;
    }
    
    // Cargar estadísticas mediante AJAX
    $.ajax({
        url: '../quizzes/quiz_controller.php',
        type: 'GET',
        data: {
            action: 'get_quiz_statistics',
            quiz_id: quizId
        },
        success: function(response) {
            $('#statistics-container').html(response);
            
            // Inicializar gráficos si existen
            initializeCharts();
        },
        error: function() {
            alert('Error al cargar las estadísticas del cuestionario.');
        }
    });
}

// Función para inicializar gráficos
function initializeCharts() {
    // Inicializar gráficos si hay elementos canvas para ellos
    const chartElements = document.querySelectorAll('.statistics-chart');
    
    chartElements.forEach(canvas => {
        const ctx = canvas.getContext('2d');
        const chartData = JSON.parse(canvas.dataset.chartData);
        const chartType = canvas.dataset.chartType;
        
        new Chart(ctx, {
            type: chartType,
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: canvas.dataset.chartTitle
                    }
                }
            }
        });
    });
}

// Función para iniciar un cuestionario (estudiantes)
function startQuiz(quizId) {
    // Cargar preguntas mediante AJAX
    $.ajax({
        url: '../quizzes/quiz_controller.php',
        type: 'GET',
        data: {
            action: 'get_quiz_for_student',
            quiz_id: quizId
        },
        success: function(response) {
            $('#answerQuizContent').html(response);
            $('#answerQuizModal').modal('show');
            
            // Configurar el botón de envío
            $('#submitAnswersBtn').off('click').on('click', function() {
                submitAnswers(quizId);
            });
        },
        error: function() {
            alert('Error al cargar el cuestionario.');
        }
    });
}

// Función para enviar respuestas de un cuestionario
function submitAnswers(quizId) {
    // Validar que todas las preguntas estén respondidas
    const form = $('#answerForm');
    let valid = true;
    
    // Validar preguntas cerradas (selección)
    form.find('input[type="radio"]').each(function() {
        const name = $(this).attr('name');
        if ($(`input[name="${name}"]:checked`).length === 0) {
            valid = false;
            $(`input[name="${name}"]`).closest('.form-group').addClass('has-error');
        } else {
            $(`input[name="${name}"]`).closest('.form-group').removeClass('has-error');
        }
    });
    
    // Validar preguntas abiertas (texto)
    form.find('textarea[required]').each(function() {
        if (!$(this).val().trim()) {
            valid = false;
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    if (valid) {
        $.ajax({
            url: '../quizzes/quiz_controller.php',
            type: 'POST',
            data: form.serialize() + '&action=submit_answers&quiz_id=' + quizId,
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    if (result.success) {
                        $('#answerQuizModal').modal('hide');
                        alert('¡Respuestas enviadas con éxito!');
                        // Recargar la página para actualizar la lista de cuestionarios
                        location.reload();
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch(e) {
                    console.error('Error al procesar la respuesta:', e);
                    alert('Error al procesar la respuesta del servidor.');
                }
            },
            error: function() {
                alert('Error al enviar las respuestas.');
            }
        });
    } else {
        alert('Por favor responde todas las preguntas antes de enviar.');
    }
}

// // Inicialización
// $(document).ready(function() {
//     // Tooltips con jQuery (más corto)
//     $('[data-bs-toggle="tooltip"]').tooltip();
// });
