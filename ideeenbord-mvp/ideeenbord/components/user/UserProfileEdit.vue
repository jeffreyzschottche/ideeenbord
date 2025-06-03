<script setup lang="ts">
/*
  Handles user profile editing.
  Initializes a reactive form with current user data.
  Submits updates to the API, excluding empty or unchanged fields.
  Also handles optional password change.
*/

import { ref, watch } from "vue";
import { useUserAuthStore } from "~/store/useUserAuthStore";
import { apiFetch } from "~/composables/useApi";
import { useResponseDisplay } from "~/composables/useResponseDisplay";

const auth = useUserAuthStore();
const { triggerByKey } = useResponseDisplay();

// Form state and UI flags
const form = ref<Record<string, any>>({});
const saving = ref(false);
const showPassword = ref(false);

/*
  Watch the authenticated user and populate the form fields accordingly.
  Empty strings are used as fallbacks to avoid uncontrolled inputs.
*/
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

/*
  Submit updated profile data.
  Empty password fields are ignored.
  Fields with null/undefined are excluded from the payload.
*/
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

    auth.user = updated.user; // Update local auth state with new user data
    triggerByKey("profile-updated"); // Trigger success message
  } catch (e) {
    triggerByKey("profile-update-failed"); // Trigger error message
  } finally {
    saving.value = false;
  }
}
</script>
