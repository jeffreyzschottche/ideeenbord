<script setup lang="ts">
import { ref, computed } from "vue";
import type { Idea } from "~/types/idea";
import type { Brand } from "~/types/brand";
import { useRuntimeConfig } from "nuxt/app";
import { useUserAuthStore } from "~/store/useUserAuthStore";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";

/* props */
const props = defineProps<{ idea: Idea; brand: Brand | null }>();

/* auth + toast helpers */
const auth = useUserAuthStore();
const { triggerByKey } = useResponseDisplay();

/* like / dislike helpers (scoped op brand-id) */
const vote = ref<"like" | "dislike" | null>(null);

/* status badge helpers */
const statusColor = computed(
  () =>
    ({
      pending: "bg-orange-200 text-orange-800",
      rejected: "bg-red-200 text-red-800",
      in_progress: "bg-blue-200 text-blue-800",
      completed: "bg-green-200 text-green-800",
    }[props.idea.status] ?? "bg-gray-200 text-gray-800")
);
const statusLabel = computed(
  () =>
    ({
      pending: "In afwachting",
      rejected: "Afgewezen",
      in_progress: "In behandeling",
      completed: "Voltooid",
    }[props.idea.status] ?? "Onbekend")
);

/* logo helper */
function logoUrl(p?: string | null) {
  if (!p) return "/img/placeholder-brand.svg";
  if (p.startsWith("http") || p.startsWith("//") || p.startsWith("/img"))
    return p;
  const base = (useRuntimeConfig().public.apiBaseUrl as string).replace(
    "/api",
    "/storage"
  );
  return `${base}/${p.replace(/^\/+/, "")}`;
}
</script>

<template>
  <div
    :class="[
      'group h-full flex flex-col rounded-2xl bg-white border shadow-[0_6px_20px_rgba(31,41,55,0.05)] p-5 transition-all duration-300 hover:shadow-xl hover:-translate-y-1',
      idea.is_pinned ? 'border-[var(--color-brand)]' : 'border-gray-100',
    ]"
  >
    <!-- header: merk + status -->
    <div class="flex items-center justify-between gap-2 mb-3">
      <NuxtLink
        v-if="brand"
        :to="`/brands/${brand.slug}`"
        class="flex items-center gap-2 min-w-0"
      >
        <img
          :src="logoUrl(brand.logo_path)"
          :alt="brand.title"
          class="w-7 h-7 object-contain rounded-lg bg-[var(--color-bg)] p-0.5 shrink-0"
        />
        <span class="text-sm font-semibold text-[var(--color-nav)] truncate group-hover:text-[var(--color-brand)] transition-colors">
          {{ brand.title }}
        </span>
      </NuxtLink>
      <span class="text-xs font-semibold px-2.5 py-1 rounded-full shrink-0" :class="statusColor">
        {{ statusLabel }}
      </span>
    </div>

    <!-- titel & beschrijving -->
    <div class="flex-1">
      <h3 class="text-lg font-bold text-[var(--color-nav)]">
        <span v-if="idea.is_pinned" class="mr-1">📌</span>{{ idea.title }}
      </h3>
      <p class="mt-2 text-sm text-gray-600 line-clamp-3">{{ idea.description }}</p>
    </div>

    <!-- footer -->
    <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
      <div class="flex items-center gap-3 text-sm text-gray-500">
        <span class="text-gray-400">@{{ idea.user.username }}</span>
        <span class="inline-flex items-center gap-1 text-green-600"><i class="fa-solid fa-thumbs-up"></i>{{ idea.likes ?? 0 }}</span>
        <span class="inline-flex items-center gap-1 text-red-500"><i class="fa-solid fa-thumbs-down"></i>{{ idea.dislikes ?? 0 }}</span>
      </div>
      <NuxtLink
        v-if="brand"
        :to="`/brands/${brand.slug}?idea-id=${idea.id}`"
        class="inline-flex items-center gap-1.5 text-sm font-semibold text-[var(--color-brand)]"
      >
        Bekijk
        <i class="fa-solid fa-arrow-right text-xs transition-transform group-hover:translate-x-1"></i>
      </NuxtLink>
    </div>
  </div>
</template>
