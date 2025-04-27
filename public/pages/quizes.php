<!-- courses.html (sección de cuestionarios) -->
<div class="container mt-4" id="quizzes-section">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Cuestionarios del Curso</h3>
        <button class="btn btn-primary" @click="showCreateQuizForm" v-if="userRole === 'teacher'">
            <i class="fas fa-plus"></i> Crear Cuestionario
        </button>
    </div>

    <div class="list-group">
        <a href="#" class="list-group-item list-group-item-action" v-for="quiz in quizzes" :key="quiz.quiz_id" 
            @click.prevent="viewQuiz(quiz.quiz_id)">
            <div class="d-flex w-100 justify-content-between">
                <h5 class="mb-1">{{ quiz.title }}</h5>
                <small>{{ formatDate(quiz.created_at) }}</small>
            </div>
            <p class="mb-1">{{ quiz.description }}</p>
            <small v-if="userRole === 'teacher'">{{ quiz.response_count }} respuestas</small>
        </a>
    </div>
</div>

<!-- Modal para crear cuestionario -->
<div class="modal fade" id="quizModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ editingQuiz ? 'Editar' : 'Crear' }} Cuestionario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <quiz-editor 
                    v-if="showQuizEditor" 
                    :quiz-data="editingQuiz"
                    @save="handleSaveQuiz"
                    @cancel="hideQuizModal">
                </quiz-editor>
            </div>
        </div>
    </div>
</div>