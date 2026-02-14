<template>
    <div class="d-flex h-100">
        <nav
            class="bg-dark text-white p-3"
            style="min-width: 220px; height: 100vh"
        >
            <h4>Admin</h4>
            <ul class="nav flex-column mt-4">
                <li class="nav-item" v-for="item in menu" :key="item.name">
                    <a class="nav-link text-white" :href="item.link">{{
                        item.name
                    }}</a>
                </li>
            </ul>
        </nav>
        <main class="ps-4 pe-4 flex-grow-1">
            <!-- Topbar con Perfil y Cerrar sesión -->
            <div class="d-flex justify-content-end align-items-center py-3">
                <div class="me-3 text-end">
                    <div class="small text-muted">{{ empleado?.CorreoElectronico }}</div>
                    <strong>{{ empleado?.Nombre || 'Empleado' }}</strong>
                </div>
                <div class="btn-group">
                    <a href="/admin/empleados" class="btn btn-outline-secondary btn-sm">Perfil</a>
                    <button class="btn btn-outline-danger btn-sm" @click="logout">Cerrar sesión</button>
                </div>
            </div>
            <slot />
        </main>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';

const menu = [
    { name: "Inicio", link: "/admin/dashboard" },
    { name: "Órdenes", link: "/ordenes" },
    { name: "Crear Orden", link: "/ordenes/crear" },
    { name: "Clientes", link: "/admin/clientes" },
    { name: "Empleados", link: "/admin/empleados" },
    { name: "Registrar Empleado", link: "/empleado/register" },
];

const empleado = ref(null);

onMounted(async () => {
    try {
        const res = await fetch('/api/user', { credentials: 'include' });
        if (res.ok) {
            empleado.value = await res.json();
        }
    } catch (e) {
        // noop
    }
});

const logout = async () => {
    try {
        await fetch('/empleado/logout', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            credentials: 'include'
        });
    } catch (e) {
        // ignore network errors and continue
    } finally {
        window.location.href = '/login';
    }
};
</script>
