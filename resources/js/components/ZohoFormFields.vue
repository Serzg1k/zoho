<template>
    <form @submit.prevent="$emit('submit')">
        <fieldset>
            <legend>Account</legend>

            <div class="field">
                <label>Account Name <span class="req">*</span></label>
                <input v-model.trim="form.account_name" type="text" :class="{ invalid: !!errors.account_name }" />
                <div v-if="errors.account_name" class="field-error">{{ errors.account_name }}</div>
            </div>

            <div class="field">
                <label>Website</label>
                <input v-model.trim="form.account_website" type="text" placeholder="https://example.com"
                       :class="{ invalid: !!errors.account_website }" />
                <div v-if="errors.account_website" class="field-error">{{ errors.account_website }}</div>
            </div>

            <div class="field">
                <label>Phone</label>
                <input v-model.trim="form.account_phone" type="text" :class="{ invalid: !!errors.account_phone }" />
                <div v-if="errors.account_phone" class="field-error">{{ errors.account_phone }}</div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Deal</legend>

            <div class="field">
                <label>Deal Name <span class="req">*</span></label>
                <input v-model.trim="form.deal_name" type="text" :class="{ invalid: !!errors.deal_name }" />
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
                <input v-model.trim="form.deal_pipeline" type="text" :class="{ invalid: !!errors.deal_pipeline }" />
                <div v-if="errors.deal_pipeline" class="field-error">{{ errors.deal_pipeline }}</div>
            </div>
        </fieldset>

        <div class="actions">
            <button type="submit" :disabled="loading">
                {{ loading ? "Submitting..." : "Submit" }}
            </button>
            <button type="button" class="secondary" :disabled="loading" @click="$emit('reset')">
                Reset
            </button>
        </div>
    </form>
</template>

<script setup>
defineProps({
    form: { type: Object, required: true },
    errors: { type: Object, required: true },
    stageOptions: { type: Array, required: true },
    loading: { type: Boolean, required: true },
    showPipeline: { type: Boolean, required: true },
});

defineEmits(["submit", "reset"]);
</script>
