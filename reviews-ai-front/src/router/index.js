import { createRouter, createWebHistory } from "vue-router";
import Login from "../views/Login.vue";
import Dashboard from "../views/Dashboard.vue";
import Reviews from "../views/Reviews.vue";
import Analyze from "../views/Analyze.vue";

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: "/", component: Login, meta: { guest: true } },

    { path: "/dashboard", component: Dashboard, meta: { requiresAuth: true } },
    { path: "/reviews", component: Reviews, meta: { requiresAuth: true } },
    { path: "/analyze", component: Analyze, meta: { requiresAuth: true } },
  ],
});

router.beforeEach((to) => {
  const token = localStorage.getItem("token");
  const isAuth = !!token;

  if (to.meta.requiresAuth && !isAuth) return "/";
  if (to.meta.guest && isAuth) return "/dashboard";
});

export default router;
