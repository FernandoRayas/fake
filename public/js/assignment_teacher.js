document.addEventListener("DOMContentLoaded", function () {
  const scoreInputs = document.querySelectorAll(".input-score");
  const scoreButtons = document.querySelectorAll(".score-btn");
  const urlParams = new URLSearchParams(window.location.search);
  const aid = urlParams.get("aid");

  scoreInputs.forEach((input, index) => {
    input.addEventListener("input", function (event) {
      validateScore(event, scoreButtons[index]);
    });
  });

  scoreButtons.forEach((button) => {
    button.addEventListener("click", sendScore);
  });

  function validateScore(event, button) {
    const input = event.target;
    const value = input.value;
    const feedback = input.nextElementSibling;

    if (value === "") {
      input.classList.remove("is-invalid");
      feedback.textContent = "";
      button.disabled = true; // Deshabilitar el botón si el campo está vacío
      return true;
    }

    if (isNaN(value)) {
      input.classList.add("is-invalid");
      feedback.textContent = "Ingrese un número válido.";
      button.disabled = true; // Deshabilitar el botón si el valor no es un número
      return false;
    }

    const score = parseInt(value);
    const maxScore = parseInt(
      input.closest("tr").querySelector("td:nth-child(6)").textContent
    ); // Obtener la puntuación máxima

    if (score < -1) {
      input.classList.add("is-invalid");
      feedback.textContent = "La puntuación no puede ser menor que -1.";
      button.disabled = true; // Deshabilitar el botón si la puntuación es menor que -1
      return false;
    }

    if (score > maxScore) {
      input.classList.add("is-invalid");
      feedback.textContent =
        "La puntuación no puede ser mayor que la puntuación máxima.";
      button.disabled = true; // Deshabilitar el botón si la puntuación es mayor que la máxima
      return false;
    }

    input.classList.remove("is-invalid");
    feedback.textContent = "";
    button.disabled = false; // Habilitar el botón si el valor es válido
    return true;
  }

  function sendScore(event) {
    const button = event.target;
    const row = button.closest("tr");
    const scoreInput = row.querySelector(".input-score");
    const studentId = row.querySelector("td:nth-child(1)").textContent;
    const score = scoreInput.value;

    fetch("../assignments/score_assignment.php", {
      // Reemplaza '/api/calificar-tarea' con la URL de tu API
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        studentId: studentId,
        score: score,
        aid: aid,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          alert("Calificación enviada correctamente.");
          button.disabled = true; // Deshabilitar el botón después de enviar la calificación
        } else {
          alert("Error al enviar la calificación.");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("Error al enviar la calificación.");
      });
  }
});
