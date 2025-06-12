<template>
  <div class="p-6">
    <h1 class="text-3xl font-bold mb-6">Content Management (CMS)</h1>

    <!-- Create new page -->
    <form @submit.prevent="createPage" class="mb-8">
      <div class="flex items-center gap-4">
        <input
          v-model="newPage.title"
          type="text"
          placeholder="Page title"
          class="px-4 py-2 border rounded w-64"
        />
        <button
          type="submit"
          class="bg-green-600 text-white px-4 py-2 rounded font-bold"
        >
          + Page
        </button>
      </div>
    </form>

    <!-- List of pages -->
    <div v-if="pages.length" class="space-y-6">
      <div
        v-for="page in pages"
        :key="page.id"
        class="border border-gray-300 rounded-lg p-4"
      >
        <h2 class="text-xl font-bold mb-4">{{ page.title }}</h2>

        <!-- Add field -->
        <form
          v-if="fieldForms[page.id]"
          @submit.prevent="addField(page.id)"
          class="grid grid-cols-6 gap-4 mb-4"
        >
          <input
            v-model="fieldForms[page.id].label"
            type="text"
            placeholder="Label"
            class="col-span-1 px-2 py-1 border rounded"
          />
          <input
            v-model="fieldForms[page.id].key"
            type="text"
            placeholder="Key"
            class="col-span-1 px-2 py-1 border rounded"
          />
          <select
            v-model="fieldForms[page.id].type"
            class="col-span-1 px-2 py-1 border rounded"
          >
            <option value="text">text</option>
            <option value="html">html</option>
            <option value="image">image</option>
            <option value="link">link</option>
          </select>
          <div class="col-span-1">
            <input
              v-if="fieldForms[page.id].type !== 'image'"
              v-model="fieldForms[page.id].value"
              type="text"
              placeholder="Value"
              class="w-full px-2 py-1 border rounded"
            />
            <input
              v-else
              type="file"
              accept="image/*"
              @change="handleImageUpload($event, page.id)"
              class="w-full px-2 py-1 border rounded"
            />
          </div>
          <button
            type="submit"
            class="col-span-1 bg-blue-600 text-white px-3 py-2 rounded font-bold"
          >
            + Field
          </button>
        </form>

        <!-- Field list -->
        <div v-if="page.fields?.length">
          <div
            v-for="field in page.fields"
            :key="field.id"
            class="text-sm border-t pt-2 mt-2 space-y-1"
          >
            <div class="flex items-center justify-between">
              <div>
                <strong>{{ field.label }}</strong>
                <span class="text-gray-500 text-xs">
                  ({{ field.key }} | {{ field.type }})
                </span>
              </div>
              <div class="flex gap-2">
                <button @click="startEditing(field)">✏️</button>
                <button @click="deleteField(field.id, page.id)">🗑</button>
              </div>
            </div>

            <div v-if="editingFieldId === field.id" class="mt-2">
              <input
                v-model="editingValue"
                class="border rounded px-2 py-1 w-full"
                :placeholder="field.label"
              />
              <div class="flex gap-2 mt-2">
                <button
                  @click="saveEdit(field, page.id)"
                  class="bg-blue-600 text-white px-3 py-1 rounded"
                >
                  Opslaan
                </button>
                <button
                  @click="cancelEdit"
                  class="bg-gray-400 text-white px-3 py-1 rounded"
                >
                  Annuleer
                </button>
              </div>
            </div>

            <div v-else>
              <div v-if="field.type === 'image'">
                <img
                  :src="getFullImageUrl(field.value)"
                  alt="Image preview"
                  class="max-h-32 border rounded shadow mt-1"
                />
              </div>
              <div v-else>
                <code
                  class="block bg-gray-100 px-2 py-1 rounded text-sm text-gray-700"
                >
                  {{ field.value }}
                </code>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="text-gray-500 mt-10">No pages created yet.</div>
  </div>
</template>
<script setup lang="ts">
definePageMeta({ middleware: "admin" });

import { ref, onMounted } from "vue";
import { useCms } from "~/composables/admin/useCms";
import type { CmsPage } from "~/types/cms-page";
import type { CmsField } from "~/types/cms-field";

const {
  pages,
  fetchPages,
  createPage: createPageApi,
  createField,
  removeField,
  updateField,
  uploadImage,
} = useCms();

const newPage = ref({ title: "" });
const fieldForms = ref<Record<number, CmsField>>({});
const editingFieldId = ref<number | null>(null);
const editingValue = ref<string>("");

function startEditing(field: CmsField) {
  editingFieldId.value = field.id;
  editingValue.value = field.value;
}

function cancelEdit() {
  editingFieldId.value = null;
  editingValue.value = "";
}

async function saveEdit(field: CmsField, pageId: number) {
  field.value = editingValue.value;
  await updateField(pageId, field);
  editingFieldId.value = null;
  await fetchPages();
}

async function deleteField(fieldId: number, pageId: number) {
  if (!confirm("Weet je zeker dat je dit veld wilt verwijderen?")) return;
  await removeField(pageId, fieldId);
  await fetchPages();
}

const getFullImageUrl = (path: string) => {
  const apiBase = useRuntimeConfig().public.apiBaseUrl;
  const base = apiBase.replace(/\/api$/, "");
  return `${base}${path.startsWith("/") ? "" : "/"}${path}`;
};

function initFieldForm(pageId: number) {
  if (!fieldForms.value[pageId]) {
    fieldForms.value[pageId] = {
      page_id: pageId,
      label: "",
      key: "",
      type: "text",
      value: "",
    };
  }
}

onMounted(async () => {
  await fetchPages();
  pages.value.forEach((page) => initFieldForm(page.id));
});

async function createPage() {
  await createPageApi(newPage.value.title);
  newPage.value = { title: "" };
  await fetchPages();
}

async function addField(pageId: number) {
  const field = fieldForms.value[pageId];
  await createField(field);
  fieldForms.value[pageId] = {
    page_id: pageId,
    label: "",
    key: "",
    type: "text",
    value: "",
  };
  await fetchPages();
}

async function handleImageUpload(event: Event, pageId: number) {
  const input = event.target as HTMLInputElement;
  if (!input.files?.length) return;

  const file = input.files[0];
  try {
    const result = await uploadImage(file);
    fieldForms.value[pageId].value = result;
  } catch (err) {
    console.error("Image upload failed", err);
  }
}
</script>
