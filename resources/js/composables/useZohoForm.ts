import { reactive, ref } from "vue";
import { submitZohoForm, type ZohoSubmitPayload } from "../services/zohoApi";

export type FieldErrors = Record<string, string>;

export function useZohoForm() {
    const loading = ref(false);
    const successMessage = ref("");
    const errorMessage = ref("");

    const stageOptions = [
        "Qualification",
        "Needs Analysis",
        "Proposal/Price Quote",
        "Negotiation/Review",
        "Closed Won",
        "Closed Lost",
    ];

    const form = reactive<ZohoSubmitPayload>({
        account_name: "",
        account_website: "",
        account_phone: "",
        deal_name: "",
        deal_stage: "",
        deal_pipeline: "",
    });

    const errors = reactive<FieldErrors>({
        account_name: "",
        account_website: "",
        account_phone: "",
        deal_name: "",
        deal_stage: "",
        deal_pipeline: "",
    });

    const showPipeline = ref(false);

    function clearMessages() {
        successMessage.value = "";
        errorMessage.value = "";
    }

    function clearErrors() {
        Object.keys(errors).forEach((k) => (errors[k] = ""));
    }

    function isValidUrl(value?: string | null) {
        if (!value) return true;
        try {
            new URL(value);
            return true;
        } catch {
            return false;
        }
    }

    function validate(): boolean {
        clearErrors();

        if (!form.account_name?.trim()) errors.account_name = "Account Name is required";
        if (form.account_website && !isValidUrl(form.account_website))
            errors.account_website = "Website must be a valid URL (e.g. https://example.com)";
        if (form.account_phone && form.account_phone.length > 30)
            errors.account_phone = "Phone is too long (max 30 characters)";

        if (!form.deal_name?.trim()) errors.deal_name = "Deal Name is required";
        if (!form.deal_stage) errors.deal_stage = "Deal Stage is required";

        if (showPipeline.value && !form.deal_pipeline?.trim())
            errors.deal_pipeline = "Pipeline is required";

        return Object.values(errors).every((v) => !v);
    }

    async function submit() {
        clearMessages();

        if (!validate()) {
            errorMessage.value = "Please fix validation errors";
            return;
        }

        loading.value = true;
        try {
            const res = await submitZohoForm({
                ...form,
                // normalize empties to null (backend проще)
                account_website: form.account_website?.trim() ? form.account_website.trim() : null,
                account_phone: form.account_phone?.trim() ? form.account_phone.trim() : null,
                deal_pipeline: form.deal_pipeline?.trim() ? form.deal_pipeline.trim() : null,
            });

            if (!res.ok) {
                // Map Laravel validation errors into single strings
                const maybeErrors = (res as any).errors;
                if (maybeErrors && typeof maybeErrors === "object") {
                    for (const [field, msgs] of Object.entries(maybeErrors)) {
                        if (errors[field] !== undefined && Array.isArray(msgs) && msgs[0]) {
                            errors[field] = String(msgs[0]);
                        }
                    }
                    errorMessage.value = "Validation failed";
                } else {
                    errorMessage.value = (res as any).message || (res as any).error || "Failed to create records";
                }
                return;
            }

            successMessage.value = `Created successfully. Account ID: ${res.account_id}, Deal ID: ${res.deal_id}`;
        } catch (e: any) {
            errorMessage.value = `Submit failed: ${e?.message || e}`;
        } finally {
            loading.value = false;
        }
    }

    function reset() {
        clearMessages();
        clearErrors();
        form.account_name = "";
        form.account_website = "";
        form.account_phone = "";
        form.deal_name = "";
        form.deal_stage = "";
        form.deal_pipeline = "";
    }

    return {
        form,
        errors,
        loading,
        successMessage,
        errorMessage,
        stageOptions,
        showPipeline,
        submit,
        reset,
    };
}
