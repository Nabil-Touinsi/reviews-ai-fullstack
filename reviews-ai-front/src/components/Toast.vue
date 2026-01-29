<!-- src/components/Toast.vue -->
<script setup>
import { onBeforeUnmount, onMounted, reactive } from "vue";
import { subscribeNotify } from "../services/notify";

const state = reactive({
  visible: false,
  type: "info", // success | error | info
  message: "",
  duration: 3000,
});

let timer = null;
let unsubscribe = null;

const close = () => {
  state.visible = false;
  state.message = "";
  if (timer) {
    clearTimeout(timer);
    timer = null;
  }
};

const show = ({ type, message, duration }) => {
  state.type = type || "info";
  state.message = message || "";
  state.duration = duration ?? 3000;
  state.visible = true;

  if (timer) clearTimeout(timer);
  timer = setTimeout(() => close(), state.duration);
};

onMounted(() => {
  unsubscribe = subscribeNotify(show);
});

onBeforeUnmount(() => {
  if (unsubscribe) unsubscribe();
  if (timer) clearTimeout(timer);
});
</script>

<template>
  <transition name="toast">
    <div v-if="state.visible" class="toast" :class="`toast--${state.type}`">
      <div class="toast__content">
        <strong class="toast__title">
          {{ state.type === "success" ? "Succès" : state.type === "error" ? "Erreur" : "Info" }}
        </strong>
        <div class="toast__message">{{ state.message }}</div>
      </div>

      <button class="toast__close" aria-label="Fermer" @click="close">×</button>
    </div>
  </transition>
</template>

<style scoped>
.toast {
  position: fixed;
  top: 16px;
  right: 16px;
  z-index: 9999;

  min-width: 280px;
  max-width: 420px;

  padding: 12px 14px;
  border-radius: 12px;

  color: #fff;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.18);

  display: flex;
  gap: 12px;
  align-items: flex-start;
}

.toast--success { background: #2ecc71; }
.toast--error   { background: #e74c3c; }
.toast--info    { background: #3498db; }

.toast__content {
  flex: 1;
  min-width: 0;
}

.toast__title {
  display: block;
  font-size: 0.95rem;
  margin-bottom: 2px;
}

.toast__message {
  font-size: 0.92rem;
  line-height: 1.25rem;
  word-break: break-word;
}

.toast__close {
  background: transparent;
  border: none;
  color: #fff;
  font-size: 20px;
  line-height: 18px;
  cursor: pointer;
  padding: 2px 6px;
  opacity: 0.9;
}
.toast__close:hover { opacity: 1; }

/* Transition */
.toast-enter-active,
.toast-leave-active {
  transition: all 0.2s ease;
}
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
