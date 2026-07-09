<template>
    <div class="container-fluid min-vh-100 py-5 px-md-5" style="background-color: #F8FAFC;">

        <header
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-5 gap-3">
            <div>
                <h1 class="fw-bold tracking-tight mb-1" style="font-size: 1.75rem; color: #0F172A;">Administración de
                    Cuestionarios</h1>
                <p class="text-muted mb-0" style="color: #64748B;">Monitorea y edita las secciones principales de tus
                    cuestionarios</p>
            </div>
            <button @click="openCreateModal"
                class="btn btn-primary px-4 py-2 fw-semibold shadow-sm d-flex align-items-center gap-2"
                style="background-color: #2563EB; border: none; border-radius: 0.5rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Nueva Pregunta Base
            </button>
        </header>

        <div class="row g-3 mb-4 align-items-center justify-content-between">
            <div class="col-md-4">
                <div class="input-group bg-white rounded-3 shadow-sm border">
                    <span class="input-group-text bg-transparent border-0 pe-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24"
                            stroke="#64748B" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" v-model="searchQuery" class="form-control border-0 form-control-lg"
                        placeholder="Buscar por título o descripción..." style="font-size: 0.95rem;">
                </div>
            </div>
            <div class="col-md-auto text-muted small">
                Mostrando <strong>{{ filteredPreguntasBase.length }}</strong> cuestionarios base
            </div>
        </div>

        <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 0.75rem;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="background: white;">
                    <thead style="background-color: #F8FAFC; border-bottom: 1px solid #E2E8F0;">
                        <tr>
                            <th scope="col" class="text-muted px-4 py-3 small fw-semibold" style="width: 35%;">Pregunta
                                Principal / Base</th>
                            <th scope="col" class="text-muted px-4 py-3 small fw-semibold" style="width: 30%;">
                                Descripción</th>
                            <th scope="col" class="text-muted px-4 py-3 small fw-semibold text-center"
                                style="width: 15%;">Preguntas asociadas</th>
                            <th scope="col" class="text-muted px-4 py-3 small fw-semibold" style="width: 10%;">F.
                                Creación</th>
                            <th scope="col" class="text-muted px-4 py-3 small fw-semibold text-end" style="width: 10%;">
                                Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="base in filteredPreguntasBase" :key="base.id" class="align-middle">
                            <td class="px-4 py-3.5">
                                <span class="fw-bold text-slate-900 d-block mb-0"
                                    style="color: #0F172A; font-size: 0.95rem;">
                                    {{ base.title }}
                                </span>
                            </td>

                            <td class="px-4 py-3.5 text-muted small">
                                {{ base.description || 'Sin descripción' }}
                            </td>

                            <td class="px-4 py-3.5 text-center">
                                <span class="badge rounded-pill px-2.5 py-1"
                                    style="background-color: #EFF6FF; color: #2563EB; font-size: 0.8rem; font-weight: 600;">
                                    {{ base.total_preguntas }} ítems
                                </span>
                            </td>

                            <td class="px-4 py-3.5 text-muted small">
                                {{ base.created_at }}
                            </td>

                            <td class="px-4 py-3.5 text-end">
                                <div class="d-flex justify-content-end align-items-center gap-2">
                                    <button @click="navigateToDetail(base.id)"
                                        class="btn btn-sm btn-light text-primary fw-semibold d-flex align-items-center gap-1 border-0"
                                        style="background-color: #F1F5F9; border-radius: 0.375rem;"
                                        title="Ver preguntas internas">
                                        <span>Ver más</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>

                                    <div class="dropdown">
                                        <button class="btn btn-link text-muted p-1 border-0 rounded" type="button"
                                            data-bs-toggle="dropdown">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                            </svg>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-2 rounded-3"
                                            style="font-size: 0.85rem;">
                                            <li><a class="dropdown-item rounded-2" href="#"
                                                    @click.prevent="editBase(base)">Editar detalles</a></li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li><a class="dropdown-item text-danger rounded-2" href="#"
                                                    @click.prevent="deleteBase(base.id)">Eliminar sección</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="filteredPreguntasBase.length === 0">
                            <td colspan="5" class="text-center py-5 text-muted">
                                <svg class="mb-2 text-slate-300" xmlns="http://www.w3.org/2000/svg" width="32"
                                    height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="mb-0 small">No se encontraron cuestionarios que coincidan con la búsqueda.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <ModalPreguntaBase v-if="showModal" @close="showModal = false" @save="savePreguntaBase" />
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import ModalPreguntaBase from './ModalPreguntaBase.vue';

// Si usas Vue Router descomenta la siguiente línea:
// import { useRouter } from 'vue-router';
// const router = useRouter();

const searchQuery = ref('');
const showModal = ref(false);

const preguntasBase = ref([
    { id: 1, title: '¿Cómo te sientes hoy?', description: 'Preguntas relacionadas con el estado emocional, fatiga y estrés del usuario.', total_preguntas: 12, created_at: '15/06/2026' },
    { id: 2, title: 'Evaluación de Entorno Laboral', description: 'Medición del clima organizacional, herramientas y ergonomía de la estación.', total_preguntas: 8, created_at: '18/06/2026' },
    { id: 3, title: 'Satisfacción de Onboarding', description: 'Encuesta corta para nuevos ingresos durante sus primeras 2 semanas.', total_preguntas: 5, created_at: '19/06/2026' }
]);

const filteredPreguntasBase = computed(() => {
    return preguntasBase.value.filter(item =>
        item.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        item.description.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const openCreateModal = () => { showModal.value = true; };
const savePreguntaBase = (data) => { showModal.value = false; };
const editBase = (base) => { alert(`Editar: ${base.title}`); };

const deleteBase = (id) => {
    if (confirm('¿Estás seguro de eliminar esta pregunta principal? Se borrarán todas las preguntas internas relacionadas.')) {
        preguntasBase.value = preguntasBase.value.filter(item => item.id !== id);
    }
};

const navigateToDetail = (id) => {
    // Redirección con tu Router favorito, por ejemplo:
    // router.push({ name: 'preguntas-detalle', params: { id } });
    alert(`Redirigiendo a las preguntas internas del cuestionario con ID: ${id}`);
};
</script>

<style scoped>
/* Estilo limpio para los renglones */
.table tbody tr {
    transition: background-color 0.15s ease;
}

.table-hover tbody tr:hover {
    background-color: #F8FAFC !important;
    /* Resaltado sutil al pasar el mouse */
}

.btn-light:hover {
    background-color: #E2E8F0 !important;
}
</style>
