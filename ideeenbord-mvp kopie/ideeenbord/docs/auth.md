# 🔐 Auth Systeem – Documentatie

Deze documentatie beschrijft hoe het authenticatiesysteem in deze Nuxt 3 + Laravel app is opgezet. De structuur volgt een modulaire aanpak gebaseerd op **Clean Architecture** en **Pinia State Management**.

---

## 🌍 Structuur en Bestanden

### **1. `types/auth.ts`**

- **Doel:** Type-definities voor `RegisterForm` en `LoginForm`.
- **Gebruik:** Gebruikt in services en composables voor type-veiligheid.

---

### **2. `services/api/authService.ts`**

- **Doel:** Verzorgt de communicatie met de Laravel backend voor `login` en `register`.
- **Functies:**
  - `register(form: RegisterForm)`
  - `login(form: LoginForm)`
- **Gebruik:** Aangeroepen vanuit `useAuth.ts`.

---

### **3. `composables/useAuth.ts`**

- **Doel:** Biedt logica en state voor authenticatie aan componenten.
- **Functies:**
  - `useRegister()` – registreert nieuwe gebruikers
  - `useLogin()` – logt bestaande gebruikers in en slaat token + user op in Pinia
- **Gebruik:** Gebruikt in `pages/register.vue` en `pages/login.vue`.

---

### **4. `store/auth.ts`**

- **Doel:** Globale state voor authenticatie (met Pinia).
- **State:**
  - `token: string | null`
  - `user: any`
- **Acties:**
  - `setAuth(token, user)` – slaat token en user op (ook in localStorage)
  - `logout()` – verwijdert alles
- **Gebruik:** Aangeroepen via `useAuthStore()` in composables of componenten.

---

### **5. `pages/register.vue`**

- **Doel:** UI voor registratie van nieuwe gebruikers.
- **Logica:** Aangeroepen via `useRegister()` composable.
- **Effect:** Roept `authService.register()` aan → slaat token op → success alert.

---

### **6. `pages/login.vue`**

- **Doel:** UI voor gebruikerslogin.
- **Logica:** Aangeroepen via `useLogin()` composable.
- **Effect:** Roept `authService.login()` aan → update Pinia store → success alert.

---

## 🔁 Data Flow Overzicht

```plaintext
UI → useAuth composable → authService API-call → Laravel backend
                                       ↓
                         Response: token + user
                                       ↓
                          opslaan in Pinia store
```
