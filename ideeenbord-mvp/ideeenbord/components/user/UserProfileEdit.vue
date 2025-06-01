<script setup lang="ts">
import { ref, watch } from "vue";
import { useUserAuthStore } from "~/store/useUserAuthStore";
import { apiFetch } from "~/composables/useApi";
import { useResponseDisplay } from "~/composables/useResponseDisplay";

const auth = useUserAuthStore();
const { triggerByKey } = useResponseDisplay();

const form = ref<Record<string, any>>({});
const saving = ref(false);
const showPassword = ref(false);

watch(
  () => auth.user,
  (newUser) => {
    if (newUser) {
      form.value = {
        name: newUser.name ?? "",
        email: newUser.email ?? "",
        username: newUser.username ?? "",
        gender: newUser.gender ?? "",
        education_level: newUser.education_level ?? "",
        education: newUser.education ?? "",
        job: newUser.job ?? "",
        sector: newUser.sector ?? "",
        city: newUser.city ?? "",
        relationship_status: newUser.relationship_status ?? "",
        postal_code: newUser.postal_code ?? "",
        password: "",
      };
    }
  },
  { immediate: true }
);

async function updateProfile() {
  saving.value = true;

  try {
    const filteredBody = Object.fromEntries(
      Object.entries(form.value).filter(([key, value]) => {
        if (key === "password" && value === "") return false;
        return value !== null && value !== undefined;
      })
    );

    const updated: any = await apiFetch(`/users/${auth.user.username}`, {
      method: "PATCH",
      body: filteredBody,
    });

    auth.user = updated.user;
    triggerByKey("profile-updated");
  } catch (e) {
    triggerByKey("profile-update-failed");
  } finally {
    saving.value = false;
  }
}
</script>
