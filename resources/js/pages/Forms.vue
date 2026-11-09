<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { forms as formsRoute } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

// 👇 eliminar registro
const deletingId = ref<number | null>(null);

const deleteForm = (form: any) => {
    if (
        !confirm(
            `¿Eliminar la encuesta de "${form.name}"? Esta acción no se puede deshacer.`,
        )
    )
        return;

    deletingId.value = form.id;
    router.delete(`/forms/${form.id}`, {
        preserveScroll: true,
        onFinish: () => (deletingId.value = null),
    });
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Formularios', href: formsRoute().url },
];
const showDropdown = ref(false);

const props = defineProps<{
    forms: {
        data: any[];
        links: any[];
        current_page: number;
        last_page: number;
    };
    counts: number;
    filters?: {
        search?: string;
    };
}>();

// 👇 estado local del buscador
const search = ref(props.filters?.search ?? '');
const typeFilter = ref(props.filters?.type_form_id ?? null);

const exportExcel = (typeId: number | null) => {
    const params = new URLSearchParams({
        search: search.value || '',
    });

    if (typeId) params.append('type_form_id', typeId.toString());

    // 👇 Abre la descarga directamente
    window.open(`/forms/export?${params.toString()}`, '_blank');
};

const filterByType = (typeId: number | null) => {
    typeFilter.value = typeId; // ✅ cambia el estado local inmediatamente

    router.get(
        formsRoute().url,
        {
            search: search.value,
            type_form_id: typeId,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const exportExcelV2 = () => {
    const params = new URLSearchParams({
        search: search.value || '',
    });

    if (typeFilter.value) {
        params.append('type_form_id', typeFilter.value.toString());
    }

    window.location.href = `/forms/export-v2?${params.toString()}`;
};

// 👇 cada vez que cambia el input, mandamos la búsqueda
watch(search, (value) => {
    router.get(
        formsRoute().url,
        { search: value },
        {
            preserveState: true,
            replace: true,
        },
    );
});
</script>

<template>
    <Head title="Forms" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                <div
                    class="flex-column flex flex-wrap items-center justify-between space-y-4 bg-white pb-4 md:flex-row md:space-y-0 dark:bg-gray-900"
                >
                    <div class="mb-4 flex flex-wrap gap-2">
                        <button
                            class="rounded-lg border px-3 py-1.5 text-sm transition"
                            :class="[
                                !typeFilter
                                    ? 'border-blue-600 bg-blue-600 text-white'
                                    : 'bg-gray-200 text-gray-700 hover:bg-gray-300',
                            ]"
                            @click="filterByType(null)"
                        >
                            Todos ({{
                                props.counts.reduce(
                                    (sum, c) => sum + c.total,
                                    0,
                                )
                            }})
                        </button>

                        <button
                            v-for="count in props.counts"
                            :key="count.id"
                            class="rounded-lg border px-3 py-1.5 text-sm transition"
                            :class="[
                                typeFilter == count.id
                                    ? 'border-blue-600 bg-blue-600 text-white'
                                    : 'bg-gray-200 text-gray-700 hover:bg-gray-300',
                            ]"
                            @click="filterByType(count.id)"
                        >
                            {{ count.name }} ({{ count.total }})
                        </button>
                    </div>

                    <div class="relative">
                        <button
                            id="dropdownActionButton"
                            @click="showDropdown = !showDropdown"
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-500 hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-700"
                            type="button"
                        >
                            Exportar Excel
                            <svg
                                class="ms-2.5 h-2.5 w-2.5"
                                aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 10 6"
                            >
                                <path
                                    stroke="currentColor"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="m1 1 4 4 4-4"
                                />
                            </svg>
                        </button>

                        <button
                            class="rounded-lg border px-3 py-1.5 text-sm transition"
                            @click="exportExcelV2"
                        >
                            Exportar datos v2
                        </button>

                        <button
                            class="rounded-lg border px-3 py-1.5 text-sm transition"
                            @click="exportExcelV2"
                        >
                            Formularios de whatsapp
                        </button>

                        <div
                            v-if="showDropdown"
                            class="absolute right-0 z-50 mt-2 w-56 divide-y divide-gray-100 rounded-lg bg-white shadow-sm dark:divide-gray-600 dark:bg-gray-700"
                        >
                            <ul
                                class="py-1 text-sm text-gray-700 dark:text-gray-200"
                            >
                                <li>
                                    <button
                                        class="block w-full px-4 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white"
                                        @click="exportExcel(null)"
                                    >
                                        Todos ({{
                                            props.counts.reduce(
                                                (sum, c) => sum + c.total,
                                                0,
                                            )
                                        }})
                                    </button>
                                </li>
                                <li
                                    v-for="count in props.counts"
                                    :key="count.id"
                                >
                                    <button
                                        class="block w-full px-4 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white"
                                        @click="exportExcel(count.id)"
                                    >
                                        {{ count.name }} ({{ count.total }})
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <label for="table-search" class="sr-only">Search</label>
                    <div class="relative">
                        <div
                            class="rtl:inset-r-0 pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3"
                        >
                            <svg
                                class="h-4 w-4 text-gray-500 dark:text-gray-400"
                                aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    stroke="currentColor"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"
                                />
                            </svg>
                        </div>
                        <input
                            type="text"
                            id="table-search-users"
                            v-model="search"
                            class="block w-80 rounded-lg border border-gray-300 bg-gray-50 p-2 ps-10 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                            placeholder="buscar por nombre, área de interés"
                        />
                    </div>
                </div>
                <table
                    class="w-full text-left text-sm text-gray-500 rtl:text-right dark:text-gray-400"
                >
                    <thead
                        class="bg-gray-50 text-xs text-gray-700 uppercase dark:bg-gray-700 dark:text-gray-400"
                    >
                        <tr>
                            <th scope="col" class="p-4">
                                <div class="flex items-center">
                                    <input
                                        id="checkbox-all-search"
                                        type="checkbox"
                                        class="h-4 w-4 rounded-sm border-gray-300 bg-gray-100 text-blue-600 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-blue-600 dark:focus:ring-offset-gray-800"
                                    />
                                    <label
                                        for="checkbox-all-search"
                                        class="sr-only"
                                        >checkbox</label
                                    >
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3">Nombre</th>
                            <th scope="col" class="px-6 py-3">Contacto</th>
                            <th scope="col" class="px-6 py-3">Area interes</th>
                            <th scope="col" class="px-6 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="form in props.forms.data"
                            :key="form.id"
                            class="border-b border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-600"
                            :class="{ 'opacity-50': deletingId === form.id }"
                        >
                            <td class="w-4 p-4">
                                <div class="flex items-center">
                                    <input
                                        type="checkbox"
                                        class="h-4 w-4 rounded-sm border-gray-300 bg-gray-100 text-blue-600 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-blue-600 dark:focus:ring-offset-gray-800"
                                    />
                                </div>
                            </td>

                            <th
                                scope="row"
                                class="px-6 py-4 font-medium whitespace-nowrap text-gray-900 dark:text-white"
                            >
                                {{ form.name }}
                            </th>

                            <td class="px-6 py-4">
                                {{ JSON.parse(form.form)?.phone_contact }}
                            </td>
                            <td class="px-6 py-4">{{ form.type }}</td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <Link
                                        :href="`/forms/${form.id}/view`"
                                        class="font-medium text-blue-600 hover:underline dark:text-blue-500"
                                    >
                                        Ver
                                    </Link>
                                    <Link
                                        :href="`/forms/${form.id}/edit`"
                                        class="font-medium text-blue-600 hover:underline dark:text-blue-500"
                                    >
                                        Editar
                                    </Link>
                                    <button
                                        type="button"
                                        @click="deleteForm(form)"
                                        :disabled="deletingId === form.id"
                                        class="font-medium text-red-600 hover:underline disabled:cursor-wait dark:text-red-500"
                                    >
                                        {{
                                            deletingId === form.id
                                                ? 'Eliminando...'
                                                : 'Eliminar'
                                        }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="mt-4 mb-4 flex justify-center space-x-2">
                    <Link
                        v-for="(link, index) in props.forms.links"
                        :key="index"
                        :href="link.url ?? '#'"
                        v-html="link.label"
                        class="rounded px-3 py-1 text-sm"
                        :class="[
                            link.active
                                ? 'bg-blue-600 text-white'
                                : 'bg-gray-200 text-gray-700 hover:bg-gray-300',
                            !link.url && 'cursor-not-allowed opacity-50',
                        ]"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
