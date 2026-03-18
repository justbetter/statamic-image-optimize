<template>
    <div class="max-w-5xl 3xl:max-w-6xl mx-auto" data-max-width-wrapper>
        <Head :title="__('Image Optimize')" />

        <Header :title="__('Image Optimize')" icon="collection">
            <Button
                v-if="canOptimizeAssets"
                :disabled="isCheckingJobs || !hasUnoptimizedAssets"
                variant="primary"
                :text="__('Optimize remaining images')"
                @click="triggerResizeImages(false)"
            />

            <Button
                v-if="canOptimizeAssets"
                :disabled="isCheckingJobs"
                :text="__('Optimize all images')"
                @click="triggerResizeImages(true)"
            />
        </Header>

        <div class="space-y-6">
            <Card v-if="!canOptimizeAssets">
                <div class="text-sm text-gray-700">
                    {{ __('You need an active database connection in order to use the optimize addon.') }}
                </div>
            </Card>

            <Card v-else-if="loadingMessage">
                <div class="flex flex-col items-center gap-3 py-6">
                    <div class="text-sm text-gray-700" v-text="loadingMessage" />
                </div>
            </Card>

            <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <Card>
                    <div class="text-sm text-gray-600 mb-1">{{ __('Total amount of images') }}</div>
                    <div class="text-3xl font-bold" v-text="totalAssets" />
                </Card>

                <Card>
                    <div class="text-sm text-gray-600 mb-1">{{ __('Images to optimize') }}</div>
                    <div class="text-3xl font-bold" v-text="unoptimizedAssets" />
                </Card>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, ref } from 'vue';
import { Head } from '@statamic/cms/inertia';
import { Button, Card, Header } from '@statamic/cms/ui';

const { totalAssets, unoptimizedAssets, canOptimize, startBatchUrl, batchStatusUrlTemplate } = defineProps({
    totalAssets: { type: Number, required: true },
    unoptimizedAssets: { type: Number, required: true },
    canOptimize: { type: Boolean, required: true },
    startBatchUrl: { type: String, required: true },
    batchStatusUrlTemplate: { type: String, required: true },
});

const batchId = ref(null);
const totalJobs = ref(unoptimizedAssets);
const processedJobs = ref(0);
const failedJobs = ref(0);
const pendingJobs = ref(unoptimizedAssets);
const isCheckingJobs = ref(false);
const hasStartedJob = ref(false);
const pollIntervalId = ref(null);

const canOptimizeAssets = computed(() => canOptimize === true);
const hasUnoptimizedAssets = computed(() => unoptimizedAssets > 0);

const loadingMessage = computed(() => {
    if (!hasStartedJob.value) {
        return '';
    }

    const base = __(':done of :total images have been optimized.', {
        done: processedJobs.value,
        total: totalJobs.value,
    });

    if (failedJobs.value > 0) {
        return `${base} ${__('(:failed failed)', { failed: failedJobs.value })}`;
    }

    return base;
});

const getCookie = (name) => {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length !== 2) {
        return null;
    }

    return parts.pop()?.split(';').shift() ?? null;
};

const getCsrfHeaders = () => {
    const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? null;

    // Laravel will also accept the XSRF cookie token via X-XSRF-TOKEN.
    // Statamic CP reliably sets the XSRF-TOKEN cookie, even when no meta tag exists.
    const xsrfCookie = getCookie('XSRF-TOKEN');
    const xsrfToken = xsrfCookie ? decodeURIComponent(xsrfCookie) : null;

    return {
        ...(metaToken ? { 'X-CSRF-TOKEN': metaToken } : {}),
        ...(xsrfToken ? { 'X-XSRF-TOKEN': xsrfToken } : {}),
    };
};

const batchStatusUrl = (id) => batchStatusUrlTemplate.replace('__BATCH_ID__', id);

const startBatch = async (scope) => {
    try {
        const csrfHeaders = getCsrfHeaders();

        const response = await fetch(startBatchUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...csrfHeaders,
            },
            body: JSON.stringify({ scope }),
        });

        const responseData = await response.json().catch(() => null);

        batchId.value = responseData?.batchId ?? null;

        return responseData;
    } catch (error) {
        // eslint-disable-next-line no-console
        console.error(error);
        return null;
    }
};

const fetchBatchStatus = async () => {
    if (!batchId.value) {
        return null;
    }

    try {
        const response = await fetch(batchStatusUrl(batchId.value), {
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            return null;
        }

        return await response.json().catch(() => null);
    } catch (error) {
        // eslint-disable-next-line no-console
        console.error(error);
        return null;
    }
};

const stopPolling = () => {
    if (pollIntervalId.value) {
        window.clearInterval(pollIntervalId.value);
        pollIntervalId.value = null;
    }
};

const triggerResizeImages = async (forceAll = false) => {
    hasStartedJob.value = true;
    isCheckingJobs.value = true;
    stopPolling();

    totalJobs.value = 0;
    processedJobs.value = 0;
    failedJobs.value = 0;
    pendingJobs.value = 0;

    const scope = forceAll ? 'all' : 'remaining';
    const started = await startBatch(scope);

    if (!started?.batchId) {
        isCheckingJobs.value = false;
        return;
    }

    const applyStatus = (status) => {
        totalJobs.value = status?.total ?? totalJobs.value;
        processedJobs.value = status?.processed ?? processedJobs.value;
        failedJobs.value = status?.failed ?? failedJobs.value;
        pendingJobs.value = status?.pending ?? pendingJobs.value;
    };

    const firstStatus = await fetchBatchStatus();
    if (firstStatus) {
        applyStatus(firstStatus);
        if (firstStatus.finished === true) {
            isCheckingJobs.value = false;
            return;
        }
    }

    pollIntervalId.value = window.setInterval(async () => {
        const status = await fetchBatchStatus();

        if (!status) {
            isCheckingJobs.value = false;
            stopPolling();
            return;
        }

        applyStatus(status);

        if (status.finished === true) {
            isCheckingJobs.value = false;
            stopPolling();
        }
    }, 1000);
};

onBeforeUnmount(() => {
    stopPolling();
});
</script>

