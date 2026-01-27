<script setup>
import { computed } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();

const isAuthed = computed(() => !!localStorage.getItem("token"));

const logout = () => {
  localStorage.removeItem("token");
  router.push("/");
};
</script>

<template>
  <div class="nav" v-if="isAuthed">
    <div class="nav__inner">
      <div class="nav__brand">Echo Reviews</div>

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

        <button class="btn" @click="logout">Logout</button>
      </div>
    </div>
  </div>

  <div class="container">
    <RouterView />
  </div>
</template>
