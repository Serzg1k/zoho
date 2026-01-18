<template>
    <div class="page">
        <div class="card">
            <h1>Zoho CRM: Create Account + Deal</h1>
            <p class="muted">
                This form creates an <b>Account</b> and a linked <b>Deal</b> in Zoho CRM.
            </p>

            <div v-if="successMessage" class="alert success">
                {{ successMessage }}
            </div>
            <div v-if="errorMessage" class="alert error">
                {{ errorMessage }}
            </div>

            <form @submit.prevent="onSubmit">
                <fieldset>
                    <legend>Account</legend>

                    <div class="field">
                        <label>Account Name <span class="req">*</span></label>
                        <input
                            v-model.trim="form.account_name"
                            type="text"
                            placeholder="Acme Inc."
                            :class="{ invalid: !!errors.account_name }"
                        />
                        <div v-if="errors.account_name" class="field-error">{{ errors.account_name }}</div>
                    </div>

                    <div class="field">
                        <label>Website</label>
                        <input
                            v-model.trim="form.account_website"
                            type="text"
                            placeholder="https://example.com"
                            :class="{ invalid: !!errors.account_website }"
                        />
                        <div v-if="errors.account_website" class="field-error">{{ errors.account_website }}</div>
                    </div>

                    <div class="field">
                        <label>Phone</label>
                        <input
                            v-model.trim="form.account_phone"
                            type="text"
                            placeholder="+1 555 123 456"
                            :class="{ invalid: !!errors.account_phone }"
                        />
                        <div v-if="errors.account_phone" class="field-error">{{ errors.account_phone }}</div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Deal</legend>

                    <div class="field">
                        <label>Deal Name <span class="req">*</span></label>
                        <input
                            v-model.trim="form.deal_name"
                            type="text"
                            placeholder="New Website Project"
                            :class="{ invalid: !!errors.deal_name }"
                        />
                        <div v-if="errors.deal_name" class="field-error">{{ errors.deal_name }}</div>
                    </div>

                    <div class="field">
                        <label>Deal Stage <span class="req">*</span></label>
                        <select v-model="form.deal_stage" :class="{ invalid: !!errors.deal_stage }">
                            <option value="" disabled>Select stage</option>
                            <option v-for="s in stageOptions" :key="s" :value="s">{{ s }}</option>
                        </select>
                        <div v-if="errors.deal_stage" class="field-error">{{ errors.deal_stage }}</div>
                    </div>

                    <div class="field" v-if="showPipeline">
                        <label>Pipeline <span class="req">*</span></label>
                        <input
                            v-model.trim="form.deal_pipeline"
                            type="text"
                            placeholder="Standard"
                            :class="{ invalid: !!errors.deal_pipeline }"
                        />
                        <div v-if="errors.deal_pipeline" class="field-error">{{ errors.deal_pipeline }}</div>
                        <div class="hint">
                            If your Zoho CRM has Pipelines enabled, Pipeline may be mandatory for Deals.
                        </div>
                    </div>
                </fieldset>

                <div class="actions">
                    <button type="submit" :disabled="loading">
                        {{ loading ? "Submitting..." : "Submit" }}
                    </button>
                    <button type="button" class="secondary" :disabled="loading" @click="resetForm">
                        Reset
                    </button>
                </div>
            </form>

            <div class="footer">
                <small class="muted">
                    Endpoint: <code>POST /api/zoho/submit</code>
                </small>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from "vue";

const loading = ref(false);
const successMessage = ref("");
const errorMessage = ref("");

const showPipeline = ref(false); // switch to true if needed

const stageOptions = ref([
    // You can replace this with dynamic load from backend metadata later
    "Qualification",
    "Needs Analysis",
    "Proposal/Price Quote",
    "Negotiation/Review",
    "Closed Won",
    "Closed Lost",
]);

const form = reactive({
    account_name: "",
    account_website: "",
    account_phone: "",
    deal_name: "",
    deal_stage: "",
    deal_pipeline: "",
});

const errors = reactive({
    account_name: "",
    account_website: "",
    account_phone: "",
    deal_name: "",
    deal_stage: "",
    deal_pipeline: "",
});

function clearMessages() {
    successMessage.value = "";
    errorMessage.value = "";
}

function clearErrors() {
    for (const key of Object.keys(errors)) errors[key] = "";
}

function isValidUrl(value) {
    if (!value) return true;
    try {
        // Basic URL validation
        new URL(value);
        return true;
    } catch {
        return false;
    }
}

function validate() {
    clearErrors();

    if (!form.account_name) errors.account_name = "Account Name is required";

    if (form.account_website && !isValidUrl(form.account_website)) {
        errors.account_website = "Website must be a valid URL (e.g. https://example.com)";
    }

    if (form.account_phone && form.account_phone.length > 30) {
        errors.account_phone = "Phone is too long (max 30 characters)";
    }

    if (!form.deal_name) errors.deal_name = "Deal Name is required";
    if (!form.deal_stage) errors.deal_stage = "Deal Stage is required";

    if (showPipeline.value && !form.deal_pipeline) {
        errors.deal_pipeline = "Pipeline is required";
    }

    return Object.values(errors).every((v) => !v);
}

async function onSubmit() {
    clearMessages();

    if (!validate()) {
        errorMessage.value = "Please fix validation errors";
        return;
    }

    loading.value = true;
    try {
        const resp = await fetch("/api/zoho/submit", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
            },
            body: JSON.stringify(form),
        });

        const data = await resp.json().catch(() => ({}));

        if (!resp.ok || !data.ok) {
            // Laravel validation errors
            if (data?.errors && typeof data.errors === "object") {
                for (const [field, messages] of Object.entries(data.errors)) {
                    if (Array.isArray(messages) && messages[0] && errors[field] !== undefined) {
                        errors[field] = messages[0];
                    }
                }
                errorMessage.value = "Validation failed";
            } else {
                errorMessage.value = data?.error || data?.message || "Failed to create records";
            }
            return;
        }

        successMessage.value = `Created successfully. Account ID: ${data.account_id}, Deal ID: ${data.deal_id}`;
    } catch (e) {
        errorMessage.value = `Submit failed: ${e?.message || e}`;
    } finally {
        loading.value = false;
    }
}

function resetForm() {
    clearMessages();
    clearErrors();
    form.account_name = "";
    form.account_website = "";
    form.account_phone = "";
    form.deal_name = "";
    form.deal_stage = "";
    form.deal_pipeline = "";
}
</script>

<style scoped>
.page {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    padding: 24px;
    background: #fafafa;
}

.card {
    width: 100%;
    max-width: 760px;
    border: 1px solid #e5e5e5;
    border-radius: 10px;
    background: #fff;
    padding: 18px;
}

h1 {
    margin: 0 0 6px;
    font-size: 20px;
}

.muted {
    color: #666;
    margin: 0 0 16px;
}

fieldset {
    border: 1px solid #eee;
    border-radius: 8px;
    padding: 14px;
    margin: 12px 0;
}

legend {
    padding: 0 6px;
    color: #333;
}

.field {
    margin-bottom: 12px;
}

label {
    display: block;
    font-weight: 600;
    margin-bottom: 6px;
}

.req {
    color: #c00;
}

input,
select {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 10px;
    outline: none;
}

input.invalid,
select.invalid {
    border-color: #d33;
}

.field-error {
    margin-top: 6px;
    font-size: 12px;
    color: #b00020;
}

.hint {
    margin-top: 6px;
    font-size: 12px;
    color: #666;
}

.alert {
    border-radius: 8px;
    padding: 10px 12px;
    margin: 10px 0 14px;
    border: 1px solid transparent;
}

.alert.success {
    border-color: #b7e1b7;
    background: #f3fff3;
}

.alert.error {
    border-color: #f1b3b3;
    background: #fff3f3;
}

.actions {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

button {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 10px 14px;
    cursor: pointer;
    background: #fff;
}

button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

button.secondary {
    background: #f7f7f7;
}

.footer {
    margin-top: 14px;
}
</style>
