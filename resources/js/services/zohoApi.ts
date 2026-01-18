export type ZohoSubmitPayload = {
    deal_name: string;
    deal_stage: string;
    deal_pipeline?: string | null;

    account_name: string;
    account_website?: string | null;
    account_phone?: string | null;
};

export type ZohoSubmitResponse =
    | { ok: true; account_id: string; deal_id: string }
    | { ok: false; message?: string; error?: string; errors?: Record<string, string[]> };

export async function submitZohoForm(payload: ZohoSubmitPayload): Promise<ZohoSubmitResponse> {
    const resp = await fetch("/api/zoho/submit", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
        },
        body: JSON.stringify(payload),
    });

    const data = (await resp.json().catch(() => ({}))) as ZohoSubmitResponse;

    // Laravel validation: 422 with errors
    if (!resp.ok) return { ok: false, ...(data as any) };

    return data;
}
