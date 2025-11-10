<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import { forms as formsRoute } from "@/routes";
import type { BreadcrumbItem } from "@/types";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";

const breadcrumbs: BreadcrumbItem[] = [{ title: "Formularios", href: formsRoute().url }];

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
const search = ref(props.filters?.search ?? "");
const typeFilter = ref(props.filters?.type_form_id ?? "");

const filterByType = (typeId: number | null) => {
  router.get(
    formsRoute().url,
    {
      search: search.value,
      type_form_id: typeId,
    },
    {
      preserveState: true,
      replace: true,
    }
  );
};

// 👇 cada vez que cambia el input, mandamos la búsqueda
watch(search, (value) => {
  router.get(
    formsRoute().url,
    { search: value },
    {
      preserveState: true,
      replace: true,
    }
  );
});
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
      <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <div
          class="flex items-center justify-between flex-column flex-wrap md:flex-row space-y-4 md:space-y-0 pb-4 bg-white dark:bg-gray-900"
        >
          <div class="flex flex-wrap gap-2 mb-4">
            <button
              class="px-3 py-1.5 text-sm rounded-lg border"
              :class="[
                !props.filters?.type_form_id
                  ? 'bg-blue-600 text-white border-blue-600'
                  : 'bg-gray-200 text-gray-700 hover:bg-gray-300',
              ]"
              @click="filterByType(null)"
            >
              Todos ({{ props.counts.reduce((sum, c) => sum + c.total, 0) }})
            </button>

            <button
              v-for="count in props.counts"
              :key="count.id"
              class="px-3 py-1.5 text-sm rounded-lg border"
              :class="[
                props.filters?.type_form_id == count.id
                  ? 'bg-blue-600 text-white border-blue-600'
                  : 'bg-gray-200 text-gray-700 hover:bg-gray-300',
              ]"
              @click="filterByType(count.id)"
            >
              {{ count.name }} ({{ count.total }})
            </button>
          </div>

          <div>
            <button
              id="dropdownActionButton"
              data-dropdown-toggle="dropdownAction"
              class="inline-flex items-center text-gray-500 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-1.5 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700"
              type="button"
            >
              <span class="sr-only">Action button</span>
              Action
              <svg
                class="w-2.5 h-2.5 ms-2.5"
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
            <!-- Dropdown menu -->
            <div
              id="dropdownAction"
              class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44 dark:bg-gray-700 dark:divide-gray-600"
            >
              <ul
                class="py-1 text-sm text-gray-700 dark:text-gray-200"
                aria-labelledby="dropdownActionButton"
              >
                <li>
                  <a
                    href="#"
                    class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white"
                    >Reward</a
                  >
                </li>
                <li>
                  <a
                    href="#"
                    class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white"
                    >Promote</a
                  >
                </li>
                <li>
                  <a
                    href="#"
                    class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white"
                    >Activate account</a
                  >
                </li>
              </ul>
              <div class="py-1">
                <a
                  href="#"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white"
                  >Delete User</a
                >
              </div>
            </div>
          </div>
          <label for="table-search" class="sr-only">Search</label>
          <div class="relative">
            <div
              class="absolute inset-y-0 rtl:inset-r-0 start-0 flex items-center ps-3 pointer-events-none"
            >
              <svg
                class="w-4 h-4 text-gray-500 dark:text-gray-400"
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
              class="block p-2 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg w-80 bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
              placeholder="buscar por nombre, área de interés"
            />
          </div>
        </div>
        <table
          class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400"
        >
          <thead
            class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400"
          >
            <tr>
              <th scope="col" class="p-4">
                <div class="flex items-center">
                  <input
                    id="checkbox-all-search"
                    type="checkbox"
                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                  />
                  <label for="checkbox-all-search" class="sr-only">checkbox</label>
                </div>
              </th>
              <th scope="col" class="px-6 py-3">Nombre</th>
              <th scope="col" class="px-6 py-3">Contacto</th>
              <th scope="col" class="px-6 py-3">Area interes</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="form in props.forms.data"
              :key="form.id"
              class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600"
            >
              <td class="w-4 p-4">
                <div class="flex items-center">
                  <input
                    type="checkbox"
                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                  />
                </div>
              </td>

              <th
                scope="row"
                class="px-6 py-4 text-gray-900 whitespace-nowrap dark:text-white font-medium"
              >
                {{ form.name }}
              </th>

              <td class="px-6 py-4">
                {{ JSON.parse(form.form)?.phone_contact }}
              </td>
              <td class="px-6 py-4">{{ form.type }}</td>

              <td class="px-6 py-4">
                <Link
                  :href="`/forms/${form.id}/edit`"
                  class="font-medium text-blue-600 dark:text-blue-500 hover:underline"
                >
                  Editar
                </Link>
              </td>
            </tr>
          </tbody>
        </table>

        <div class="flex justify-center mt-4 space-x-2 mb-4">
          <Link
            v-for="(link, index) in props.forms.links"
            :key="index"
            :href="link.url ?? '#'"
            v-html="link.label"
            class="px-3 py-1 rounded text-sm"
            :class="[
              link.active
                ? 'bg-blue-600 text-white'
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300',
              !link.url && 'opacity-50 cursor-not-allowed',
            ]"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
