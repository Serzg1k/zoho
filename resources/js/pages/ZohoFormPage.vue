<template>
    <div class="page">
        <div class="card">
            <h1>Zoho CRM: Create Account + Deal</h1>
            <p class="muted">Creates an <b>Account</b> and linked <b>Deal</b> in Zoho CRM.</p>

            <div v-if="successMessage" class="alert success">{{ successMessage }}</div>
            <div v-if="errorMessage" class="alert error">{{ errorMessage }}</div>

            <ZohoFormFields
                :form="form"
                :errors="errors"
                :stage-options="stageOptions"
                :loading="loading"
                :show-pipeline="showPipeline"
                @submit="submit"
                @reset="reset"
            />

            <div class="footer">
                <small class="muted">Endpoint: <code>POST /api/zoho/submit</code></small>
            </div>
        </div>
    </div>
</template>

<script setup>
import ZohoFormFields from "../components/ZohoFormFields.vue";
import { useZohoForm } from "../composables/useZohoForm";

const {
    form,
    errors,
    loading,
    successMessage,
    errorMessage,
    stageOptions,
    showPipeline,
    submit,
    reset,
} = useZohoForm();
</script>
