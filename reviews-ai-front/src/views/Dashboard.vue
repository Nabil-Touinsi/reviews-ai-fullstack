<script setup>
import { onMounted, ref } from "vue";
import api from "../services/api";

const stats = ref(null);
const loading = ref(true);

onMounted(async () => {
  try {
    const res = await api.get("/dashboard");
    stats.value = res.data;
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <h1>Dashboard</h1>

  <p v-if="loading">Chargement...</p>

  <pre v-else>{{ stats }}</pre>
</template>
