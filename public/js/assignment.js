const alertContainer = document.getElementById("alert-container");
const inputSubmissionFile = document.getElementById("submission-file");
const submissionFileInvalidFeedback = document.querySelector(
  "#submission-file + .invalid-feedback"
);

const submitButton = document.getElementById("submit");

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
const urlParams = new URLSearchParams(window.location.search);
const aid = urlParams.get("aid");

const showAlert = (message, type) => {
  let svg = "";
  switch (type.toLowerCase()) {
    case "success":
      svg = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                <use xlink:href="#check-circle-fill" />
            </svg>
            `;
      break;
    case "warning":
      svg = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle-fill" viewBox="0 0 16 16">
                <use xlink:href="#exclamation-triangle-fill" />
            </svg>
            `;
      break;
    case "danger":
      svg = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle-fill" viewBox="0 0 16 16">
                <use xlink:href="#exclamation-triangle-fill" />
            </svg>
    `;
      break;
  }

  const alertContent = `
  <div class="alert alert-${type} alert-dismissible fade show d-flex align-items-center" id="alert" role="alert">
    ${svg}
    <div class="ms-3">${message}</div>
    <button type="button" id="btn-close" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>`;

  alertContainer.innerHTML = alertContent;

  setTimeout(() => {
    closeAlert();
  }, 2000);
};

const closeAlert = () => {
  const closeButton = document.getElementById("btn-close");
  closeButton.click();
};

const showValidation = (input, feedback, message, isValid) => {
  if (isValid) {
    input.classList.remove("is-invalid");
    feedback.textContent = "";
  } else {
    input.classList.add("is-invalid");
    feedback.textContent = message;
  }
};

const validateSubmissionFile = () => {
  if (inputSubmissionFile.files.length > 0) {
    let invalidFiles = [];
    for (
      let fileIndex = 0;
      fileIndex < inputSubmissionFile.files.length;
      fileIndex++
    ) {
      const file = inputSubmissionFile.files[fileIndex];
      const fileExtension = file.name.split(".").pop().toLowerCase();
      if (fileExtesions.indexOf(fileExtension) === -1) {
        invalidFiles.push(file.name);
      }
    }

    if (invalidFiles.length > 0) {
      const message = `Los siguientes archivos no son válidos: 
      ${invalidFiles.join("\n")}`;

      showValidation(
        inputSubmissionFile,
        submissionFileInvalidFeedback,
        message,
        false
      );
      submitButton.disabled = true;
      return false;
    } else {
      showValidation(
        inputSubmissionFile,
        submissionFileInvalidFeedback,
        "",
        true
      );
      submitButton.disabled = false;
    }

    return false;
  } else {
    showValidation(
      inputSubmissionFile,
      submissionFileInvalidFeedback,
      "",
      true
    );
    submitButton.disabled = false;
  }
};

const createSubmission = () => {
  const files = inputSubmissionFile.files;
  if (files.length > 0) {
    const formData = new FormData();

    for (let i = 0; i < files.length; i++) {
      formData.append("submission-files[]", files[i]);
    }

    fetch(`../files/create_submission_file.php?aid=${aid}`, {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        if (!response.ok) {
          showAlert("Error al agregar el archivo", "danger");
          throw new Error("Error al agregar el archivo");
        }

        return response.json();
      })
      .then((data) => {
        if (data.response.isFileUploaded) {
          showAlert("Tarea entregada correctamente", "success");
        } else if (data.response.isAlreadySubmitted) {
          showAlert("Ya has entregado la tarea", "warning");
        }
        inputSubmissionFile.disabled = true;
        submitButton.disabled = true;
      })
      .catch((error) => {
        showAlert(`Error al agregar el archivo: ${error.message}`, "danger");
        inputSubmissionFile.disabled = false;
        submitButton.disabled = false;
        throw new Error(`Error al agregar el archivo: ${error.message}`);
      });
  }
};

inputSubmissionFile.addEventListener("change", () => {
  validateSubmissionFile();
});

submitButton.addEventListener("click", (e) => {
  e.preventDefault();

  createSubmission();
});
