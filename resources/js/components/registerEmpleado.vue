<template>
    <div class="container mt-5">
        <h2 class="mb-4">Registrar Empleado</h2>
        <form @submit.prevent="registerEmpleado">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input
                    type="text"
                    v-model="form.Nombre"
                    class="form-control"
                    id="nombre"
                    required
                />
            </div>

            <div class="mb-3">
                <label for="correo" class="form-label"
                    >Correo Electrónico</label
                >
                <input
                    type="email"
                    v-model="form.CorreoElectronico"
                    class="form-control"
                    id="correo"
                    required
                />
            </div>

            <div class="mb-3">
                <label for="telefono" class="form-label">Teléfono</label>
                <input
                    type="text"
                    v-model="form.Telefono"
                    class="form-control"
                    id="telefono"
                    required
                />
            </div>

            <div class="mb-3">
                <label for="direccion" class="form-label">Dirección</label>
                <input
                    type="text"
                    v-model="form.Direccion"
                    class="form-control"
                    id="direccion"
                    required
                />
            </div>

            <div class="mb-3">
                <label for="cargo" class="form-label">Cargo</label>
                <select
                    v-model="form.Cargo"
                    class="form-select"
                    id="cargo"
                    required
                >
                    <option value="">Selecciona un cargo</option>
                    <option value="administrador">Administrador</option>
                    <option value="empleado">Empleado</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input
                    type="password"
                    v-model="form.password"
                    class="form-control"
                    id="password"
                    required
                />
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label"
                    >Confirmar Contraseña</label
                >
                <input
                    type="password"
                    v-model="form.password_confirmation"
                    class="form-control"
                    id="password_confirmation"
                    required
                />
            </div>

            <button type="submit" class="btn btn-primary">Registrar</button>
        </form>
    </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";

const form = ref({
    Nombre: "",
    CorreoElectronico: "",
    Telefono: "",
    Direccion: "",
    Cargo: "",
    password: "",
    password_confirmation: "",
});

const registerEmpleado = async () => {
    try {
        await axios.get("/sanctum/csrf-cookie");
        await axios.post("/empleados/register", form.value);
        alert("Empleado registrado correctamente");

        window.location.href = "/admin/dashboard";
    } catch (error) {
        console.error(error);
        alert("Error al registrar empleado");
    }
};
</script>
