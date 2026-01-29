<script setup>
import { computed } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();

const user = computed(() => {
  try {
    return JSON.parse(localStorage.getItem("user"));
  } catch {
    return null;
  }
});

const isAuthed = computed(() => !!localStorage.getItem("token"));
const isAdmin = computed(() => user.value?.role === "admin");

const logout = () => {
  localStorage.removeItem("token");
  localStorage.removeItem("user");
  router.push("/");
};
</script>

<template>
  <div class="nav" v-if="isAuthed">
    <div class="nav__inner">
      <div class="nav__brand">
        Echo Reviews
        <span v-if="isAdmin" style="margin-left:8px; color:#e74c3c; font-weight:bold">
          ADMIN
        </span>
      </div>

      <div class="nav__links">
        <RouterLink
          to="/dashboard"
          class="btn btn--ghost"
          active-class="btn--primary"
        >
          Dashboard
        </RouterLink>

        <RouterLink
          to="/reviews"
          class="btn btn--ghost"
          active-class="btn--primary"
        >
          Reviews
        </RouterLink>

        <RouterLink
          to="/analyze"
          class="btn btn--ghost"
          active-class="btn--primary"
        >
          Analyser
        </RouterLink>

        <!-- Lien admin (visible uniquement si admin) -->
        <RouterLink
          v-if="isAdmin"
          to="/admin"
          class="btn btn--ghost"
          active-class="btn--primary"
        >
          Admin
        </RouterLink>

        <button class="btn" @click="logout">Logout</button>
      </div>
    </div>
  </div>

  <div class="container">
    <RouterView />
  </div>
</template>
