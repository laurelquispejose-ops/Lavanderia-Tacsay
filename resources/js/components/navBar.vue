<template>
    <nav
        class="navbar navbar-expand-lg bg-dark border-bottom border-body"
        data-bs-theme="dark"
    >
        <div class="container-fluid">
            <a class="navbar-brand" href="/home"
                ><img
                    src="/resources/images/logo.png"
                    class="img-fluid"
                    width="140"
                    alt="Logo Tacsay"
            /></a>
            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a
                            href="/cliente/ordenes"
                            class="nav-link active text-decoration-none link-light"
                            aria-current="page"
                        >
                            <i class="bi bi-house-fill me-1"></i> Inicio</a
                        >
                    </li>
                    <li class="nav-item">
                        <a
                            href="/cliente/historial"
                            class="nav-link text-decoration-none link-light"
                        >
                            <i class="bi bi-clock-history me-1"></i> Historial
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav mb-2 mb-lg-0">
                    <li class="nav-item dropdown">
                        <a
                            class="nav-link dropdown-toggle text-decoration-none link-light d-flex align-items-center"
                            href="#"
                            role="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >
                            <div class="user-avatar me-2 d-flex align-items-center justify-content-center bg-primary rounded-circle">
                                <span v-if="userName && userName.length > 0">{{ userName.charAt(0) }}</span>
                                <i v-else class="bi bi-person"></i>
                            </div>
                            <span class="user-name">{{ userName }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="/cliente/perfil">
                                    <i class="bi bi-person-badge me-2"></i> Mi Perfil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/cliente/ordenes">
                                    <i class="bi bi-bag-check me-2"></i> Mis Órdenes
                                </a>
                            </li>
                            <li><hr class="dropdown-divider" /></li>
                            <li>
                                <a
                                    class="dropdown-item text-danger"
                                    href="#"
                                    @click.prevent="logout"
                                    ><i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión</a
                                >
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const userName = ref("Usuario");
const isLoading = ref(true);

onMounted(async () => {
    try {
        // Verificar si estamos en una página que requiere autenticación
        // Si no lo estamos, no intentaremos cargar el usuario para evitar errores 401
        const pageRequiresAuth = window.location.pathname.includes('/dashboard') || 
                               window.location.pathname.includes('/admin');
                               
        if (pageRequiresAuth) {
            // Realiza la solicitud a la API para obtener el cliente autenticado
            let response = await axios.get("/api/user");
            userName.value = response.data.Nombre || "Usuario"; // Asignamos el nombre del cliente
            console.log("Usuario cargado:", userName.value);
        } else {
            console.log("Página pública, no se cargará usuario");
        }
    } catch (error) {
        // Si hay un error 401, simplemente asumimos que no hay usuario autenticado
        if (error.response && error.response.status === 401) {
            console.log("Usuario no autenticado");
        } else {
            console.error("No se pudo cargar el cliente:", error);
        }
    } finally {
        isLoading.value = false;
    }
});

const logout = async () => {
    try {
        await axios.post("/cliente/logout");
        window.location.href = "/login";
    } catch (error) {
        console.error("Error cerrando sesión", error);
    }
};
</script>

<style scoped>
.user-avatar {
    width: 32px;
    height: 32px;
    font-weight: bold;
    color: white;
    text-transform: uppercase;
}

.user-name {
    max-width: 150px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.dropdown-item {
    padding: 0.5rem 1rem;
    transition: background-color 0.2s ease;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
}

.dropdown-item.text-danger:hover {
    background-color: #f8d7da;
}
</style>
