export default defineNuxtRouteMiddleware((to) => {
  // als er geen route matched → fallback naar jouw 404-pagina
  if (to.matched.length === 0) {
    return navigateTo("/404", { replace: true });
  }
});
