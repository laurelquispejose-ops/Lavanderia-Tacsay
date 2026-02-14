<template>
    <div
        class="login-container d-flex justify-content-center h-100 align-items-center"
    >
        <form @submit.prevent="login">
            <!-- Email input -->
            <div class="form-outline mb-4">
                <label class="form-label d-block">Es usted:</label>
                <div class="form-check form-check-inline">
                    <input
                        class="form-check-input"
                        type="radio"
                        id="cliente"
                        value="cliente"
                        v-model="tipoUsuario"
                    />
                    <label class="form-check-label" for="cliente"
                        >Cliente</label
                    >
                </div>
                <div class="form-check form-check-inline">
                    <input
                        class="form-check-input"
                        type="radio"
                        id="empleado"
                        value="empleado"
                        v-model="tipoUsuario"
                    />
                    <label class="form-check-label" for="empleado"
                        >Empleado</label
                    >
                </div>
            </div>

            <div class="form-outline mb-4">
                <label class="form-label" for="emailInput"
                    >Correo Electrónico</label
                >
                <input
                    v-model="email"
                    type="email"
                    id="emailInput"
                    class="form-control"
                    placeholder="usuario@correo.com"
                />
            </div>

            <!-- Password input -->
            <div class="form-outline mb-4">
                <label class="form-label" for="passwordInput">Contraseña</label>
                <input
                    v-model="password"
                    type="password"
                    id="passwordInput"
                    class="form-control"
                    placeholder="Ingrese su contraseña"
                />
            </div>

            <!-- Recordarme y olvidar contraseña -->
            <div class="row mb-4 text-center">
                <div
                    class="col d-flex justify-content-center align-items-center"
                >
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="checkboxLogin"
                            checked
                        />
                        <label class="form-check-label" for="checkboxLogin">
                            Recordarme
                        </label>
                    </div>
                </div>
                <div class="col">
                    <a href="#!">¿Has olvidado la contraseña?</a>
                </div>
            </div>

            <!-- Submit button -->
            <div class="d-flex justify-content-center">
                <button type="submit" class="btn btn-primary btn-block mb-4">
                    Iniciar Sesión
                </button>
            </div>

            <div class="text-center">
                <p>
                    ¿No tienes cuenta? Registrate <a href="/register">Aqui</a>
                </p>
            </div>
        </form>
    </div>
</template>

<style scoped>
.login-container {
    background-image: url('/images/lavanderia-bg.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    min-height: 100vh;
}

form {
    background-color: rgba(255, 255, 255, 0.9);
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
    max-width: 450px;
    width: 100%;
}
</style>

<script setup>
import { ref } from "vue";
import axios from "axios";

const email = ref("");
const password = ref("");
const tipoUsuario = ref(""); // cliente o empleado

axios.defaults.withCredentials = true;

const login = async () => {
    if (!tipoUsuario.value) {
        alert("Por favor, selecciona si eres cliente o empleado.");
        return;
    }

    try {
        await axios.get("/sanctum/csrf-cookie");

        let url = "/login";
        if (tipoUsuario.value === "empleado") {
            url = "/empleado/login";
        } else if (tipoUsuario.value === "cliente") {
            url = "/cliente/login";
        }

        await axios.post(
            url,
            {
                correoElectronico: email.value,
                password: password.value,
            },
            { withCredentials: true }
        );

        // Redirige según tipo de usuario
        if (tipoUsuario.value === "empleado") {
            window.location.href = "/admin/dashboard";
        } else {
            window.location.href = "/cliente/ordenes";
        }
    } catch (error) {
        console.error(error);
        alert(
            "Error al iniciar sesión: " +
                (error.response?.data?.message || "Error desconocido")
        );
    }
};
</script>
