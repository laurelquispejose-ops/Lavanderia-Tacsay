<template>
    <div class="container py-5 d-flex justify-content-center">
        <form @submit.prevent="register" class="w-100" style="max-width: 560px">
            <!-- DNI + botón: usa grid para mejor ajuste en 100% zoom -->
            <div class="mb-4">
                <div class="row g-2 align-items-end flex-wrap">
                    <div class="col-12 col-sm">
                        <label class="form-label" for="registerDNI">DNI</label>
                        <input
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]{8}"
                            maxlength="8"
                            id="registerDNI"
                            class="form-control"
                            v-model="dni"
                            placeholder="Número de DNI"
                        />
                    </div>
                    <div class="col-12 col-sm-auto">
                        <button
                            type="button"
                            class="btn btn-outline-secondary w-100"
                            @click="buscarPorDNI"
                        >
                            Buscar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Nombres -->
            <div class="form-outline mb-4">
                <label class="form-label" for="registerFirstName"
                    >Nombres</label
                >
                <input
                    type="text"
                    id="registerFirstName"
                    class="form-control"
                    v-model="firstName"
                    placeholder="Tus nombres"
                />
            </div>

            <!-- Apellidos -->
            <div class="form-outline mb-4">
                <label class="form-label" for="registerLastName"
                    >Apellidos</label
                >
                <input
                    type="text"
                    id="registerLastName"
                    class="form-control"
                    v-model="lastName"
                    placeholder="Tus apellidos"
                />
            </div>

            <!-- Email -->
            <div class="form-outline mb-4">
                <label class="form-label" for="registerEmail"
                    >Correo Electrónico</label
                >
                <input
                    type="email"
                    id="registerEmail"
                    class="form-control"
                    v-model="email"
                    placeholder="usuario@correo.com"
                />
            </div>

            <!-- Password -->
            <div class="form-outline mb-4">
                <label class="form-label" for="registerPassword"
                    >Contraseña</label
                >
                <input
                    type="password"
                    id="registerPassword"
                    class="form-control"
                    :class="{'is-invalid': passwordErrors.length > 0, 'is-valid': password.length >= 8 && passwordErrors.length === 0}"
                    v-model="password"
                    @input="validatePassword"
                    placeholder="Crea una contraseña"
                />
                <div class="password-strength mt-1" v-if="password">
                    <div class="progress" style="height: 5px">
                        <div class="progress-bar" :class="passwordStrengthClass" :style="{width: passwordStrength + '%'}"></div>
                    </div>
                    <small class="text-muted">Fortaleza: {{ passwordStrengthText }}</small>
                </div>
                <div class="invalid-feedback" v-if="passwordErrors.length > 0">
                    <ul class="mb-0 ps-3">
                        <li v-for="(error, index) in passwordErrors" :key="index">{{ error }}</li>
                    </ul>
                </div>
                <small class="form-text text-muted">La contraseña debe tener al menos 8 caracteres.</small>
            </div>

            <!-- Confirm Password -->
            <div class="form-outline mb-4">
                <label class="form-label" for="registerPasswordConfirm"
                    >Confirmar Contraseña</label
                >
                <input
                    type="password"
                    id="registerPasswordConfirm"
                    class="form-control"
                    :class="{'is-invalid': password_confirmation && password !== password_confirmation, 'is-valid': password_confirmation && password === password_confirmation}"
                    v-model="password_confirmation"
                    placeholder="Repite la contraseña"
                />
                <div class="invalid-feedback" v-if="password_confirmation && password !== password_confirmation">
                    Las contraseñas no coinciden.
                </div>
            </div>

            <!-- Teléfono -->
            <div class="form-outline mb-4">
                <label class="form-label" for="registerPhone">Teléfono</label>
                <input
                    type="text"
                    id="registerPhone"
                    class="form-control"
                    v-model="phone"
                    placeholder="Tu número de teléfono"
                />
            </div>

            <!-- Dirección -->
            <div class="form-outline mb-4">
                <label class="form-label" for="registerAddress"
                    >Dirección</label
                >
                <textarea
                    id="registerAddress"
                    class="form-control"
                    v-model="address"
                    placeholder="Tu dirección completa"
                ></textarea>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-secondary mb-4" @click="goBack">
                    Atrás
                </button>
                <button type="submit" class="btn btn-primary mb-4">
                    Registrarse
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import axios from "axios";

const dni = ref("");
const firstName = ref("");
const lastName = ref("");
const email = ref("");
const password = ref("");
const password_confirmation = ref("");
const phone = ref("");
const address = ref("");
const passwordErrors = ref([]);

// Validación de contraseña
const validatePassword = () => {
    const errors = [];
    
    if (password.value.length < 8) {
        errors.push("La contraseña debe tener al menos 8 caracteres");
    }
    
    if (!/[A-Z]/.test(password.value)) {
        errors.push("Debe incluir al menos una letra mayúscula");
    }
    
    if (!/[a-z]/.test(password.value)) {
        errors.push("Debe incluir al menos una letra minúscula");
    }
    
    if (!/[0-9]/.test(password.value)) {
        errors.push("Debe incluir al menos un número");
    }
    
    if (!/[^A-Za-z0-9]/.test(password.value)) {
        errors.push("Debe incluir al menos un carácter especial");
    }
    
    passwordErrors.value = errors;
};

// Cálculo de la fortaleza de la contraseña
const passwordStrength = computed(() => {
    if (!password.value) return 0;
    
    let strength = 0;
    
    // Longitud
    if (password.value.length >= 8) strength += 20;
    if (password.value.length >= 12) strength += 10;
    
    // Complejidad
    if (/[A-Z]/.test(password.value)) strength += 20;
    if (/[a-z]/.test(password.value)) strength += 20;
    if (/[0-9]/.test(password.value)) strength += 20;
    if (/[^A-Za-z0-9]/.test(password.value)) strength += 20;
    
    return Math.min(strength, 100);
});

const passwordStrengthText = computed(() => {
    const strength = passwordStrength.value;
    if (strength < 40) return "Débil";
    if (strength < 70) return "Media";
    return "Fuerte";
});

const passwordStrengthClass = computed(() => {
    const strength = passwordStrength.value;
    if (strength < 40) return "bg-danger";
    if (strength < 70) return "bg-warning";
    return "bg-success";
});

// Ya no necesitamos el token aquí porque lo manejaremos en el backend

const register = async () => {
    // Validar contraseña antes de enviar
    validatePassword();
    
    // Verificar si hay errores de validación
    if (passwordErrors.value.length > 0) {
        alert("Por favor, corrige los errores en la contraseña antes de continuar.");
        return;
    }
    
    // Verificar que las contraseñas coincidan
    if (password.value !== password_confirmation.value) {
        alert("Las contraseñas no coinciden. Por favor, verifica.");
        return;
    }
    
    try {
        await axios.post("/register", {
            dni: dni.value,
            nombre: firstName.value + " " + lastName.value, // Combinamos nombres y apellidos como espera el backend
            correoElectronico: email.value,
            password: password.value,
            password_confirmation: password_confirmation.value,
            telefono: phone.value,
            direccion: address.value,
        });

        window.location.href = "/cliente/ordenes";
    } catch (error) {
        console.error(error);
        alert(
            "Error al registrarse: " +
                (error.response?.data?.message || "Error desconocido")
        );
    }
};

const buscarPorDNI = async () => {
    if (dni.value.length !== 8) {
        alert("El DNI debe tener 8 dígitos");
        return;
    }

    try {
        // Usamos nuestro propio endpoint de Laravel que actúa como proxy
        const response = await axios.get(`/api/consultar-dni?numero=${dni.value}`);
        const data = response.data;

        firstName.value = data.nombres;
        lastName.value = `${data.apellidoPaterno} ${data.apellidoMaterno}`;
    } catch (error) {
        console.error("Error al buscar DNI:", error);
        alert("No se pudo encontrar información con ese DNI: " + 
              (error.response?.data?.error || error.message || "Error desconocido"));
    }
};

// Botón Atrás
const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = "/login";
    }
};
</script>
