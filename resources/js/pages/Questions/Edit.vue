<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import { forms as formsRoute } from "@/routes";
import type { BreadcrumbItem } from "@/types";
import { Head, Link } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import Draggable from "vuedraggable";
// TypeScript interfaces for structure alignment
interface Question {
    id: number;
    text: string;
    type: 'text' | 'multiple' | 'single' | 'scale' | 'boolean';
    order: number;
}

// 1. DUMMY DATA FOR THE MAIN SURVEY CONTEXT
const mockBaseSurvey = ref({
    id: 1,
    title: "Proceso/Congelado/Deshidratado",
    description: "Sin Descripción"
});

// 2. DUMMY DATA FOR RELATED INTERNAL QUESTIONS
const localQuestions = ref<Question[]>([
    { id: 101, text: "1.- ¡Hola! Platícanos un poco de ti ¿Cuál es tu nombre?", type: "Texto", order: 1 },
    { id: 102, text: "2.- Teléfono / WhatsApp", type: "Texto", order: 2 },
    { id: 103, text: "3.- Ciudad / Estado", type: "Seleccionar", order: 3 },
    { id: 104, text: "4.- Tipo de fruta", type: "Seleccionar", order: 4 },
    { id: 105, text: "5.- Volumen", type: "Seleccionar", order: 5 },

    { id: 106, text: "11.- Variedades de interés", type: "Seleccionar", order: 6 },
    { id: 107, text: "12.- Presentación deseada", type: "Seleccionar", order: 7 },
    { id: 108, text: "13.- ¿Qué certificaciones requiere?", type: "Seleccionar", order: 8 },
    { id: 109, text: "14.- ¿Qué documentos solicita?", type: "Seleccionar", order: 9 },
    { id: 110, text: "15.- Método de pago", type: "Seleccionar", order: 10 },
    { id: 111, text: "16.- Tiempo de pago", type: "Seleccionar", order: 11 },
    { id: 112, text: "17.- ¿Busca proveedor permanente?", type: "Booleano", order: 12 },
]);

// Breadcrumbs tracking back using the forms route helper
const breadcrumbs: BreadcrumbItem[] = [
    { title: "Forms", href: formsRoute().url },
    { title: mockBaseSurvey.value.title, href: "#" }
];

const searchQuery = ref("");
const showModal = ref(false);

// Live frontend search filter
const filteredQuestions = computed(() => {
    return localQuestions.value.filter(question =>
        question.text.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

// Helper layout mapping for the badge design

// Local mock delete action
const deleteQuestion = (questionId: number) => {
    if (confirm("Are you sure you want to delete this mock question?")) {
        localQuestions.value = localQuestions.value.filter(q => q.id !== questionId);
    }
};


const handleDragEnd = () => {
    localQuestions.value = localQuestions.value.map((question, index) => ({
        ...question,
        order: index + 1
    }));

    console.log(localQuestions.value);

    // Aquí puedes enviar al backend
    // router.post('/questions/reorder', {
    //     questions: localQuestions.value.map(q => ({
    //         id: q.id,
    //         order: q.order
    //     }))
    // });
};
</script>

<template>

    <Head :title="`Mockup Edit - ${mockBaseSurvey.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6 bg-[#F8FAFC] min-h-screen">

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm border-l-4 border-l-blue-600">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Base Question /
                            Context</span>
                        <h2 class="text-xl font-bold text-slate-900 mt-0.5">{{ mockBaseSurvey.title }}</h2>
                        <p class="text-sm text-slate-500 mt-1">{{ mockBaseSurvey.description }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="showModal = true"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Question
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="relative w-full sm:max-w-xs">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                                viewBox="0 0 24 24" stroke="#64748B" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input v-model="searchQuery" type="text" placeholder="Buscar pregunta..."
                            class="w-full pl-9 pr-4 py-1.5 bg-white text-xs text-slate-900 placeholder-slate-400 border border-slate-200 rounded-lg shadow-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                    </div>
                    <p class="text-xs text-slate-400">
                        Consejo: Arrastra las filas desde el icono  <span class="font-bold">☰</span> para cambiar rápidamente el orden de presentación.
                        order.
                    </p>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
                    <table class="w-full table-auto border-collapse text-left">
                        <thead>
                            <tr
                                class="border-b border-slate-200 bg-slate-50/70 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <th class="px-6 py-3 w-[80px]">Orden</th>
                                <th class="px-6 py-3">Pregunta</th>
                                <th class="px-6 py-3 w-[180px]">Tipo de pregunta</th>
                                <th class="px-6 py-3 w-[100px] text-end">Acciones</th>
                            </tr>
                        </thead>
                        <Draggable v-model="localQuestions" item-key="id" tag="tbody" handle=".drag-handle"
                            @end="handleDragEnd" class="divide-y divide-slate-200 text-sm">
                            <template #item="{ element, index }">
                                <tr class="hover:bg-slate-50/40 transition-colors">

                                    <td class="px-6 py-3.5">
                                        <div class="flex items-center gap-2">
                                            <svg class="drag-handle cursor-move text-slate-400 hover:text-slate-600"
                                                xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4 6h16M4 12h16M4 18h16" />
                                            </svg>

                                            <span class="text-xs font-medium text-slate-600">
                                                {{ index + 1 }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-3.5 font-medium text-slate-900">
                                        {{ element.text }}
                                    </td>

                                    <td class="px-6 py-3.5">
                                        <span
                                            class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-medium text-slate-600">
                                            {{ element.type }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-3.5 text-end">
                                        <!-- botones -->
                                         <!-- Botón Eliminar Rápido -->
                                    <button
                                        class="text-slate-400 hover:text-red-600 p-1 rounded transition-colors"
                                        title="Eliminar cuestionario">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                    </td>

                                </tr>
                            </template>
                        </Draggable>

                        <tr v-if="filteredQuestions.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="text-slate-300" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                    </svg>
                                    <p class="text-sm font-medium">No questions match your filter criteria.</p>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
