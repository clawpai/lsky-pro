<script setup lang="ts">
import Content from "@/components/Content.vue";
import Layout from "@/components/Layout.vue";
import { NCard, NTabs, NTab, NButton, NIcon, NEmpty, NBadge, NDivider } from "naive-ui";
import { CheckCircleOutlined, ShoppingCartOutlined, CrownOutlined, CloudUploadOutlined } from "@vicons/antd";
import { computed, onMounted, ref } from "vue";
import { getPlans } from "@/api";
import { useRouter } from "vue-router";

const router = useRouter()
const tab = ref('vip')
const plans = ref<any>([])

onMounted(async () => {
  const response = await getPlans({
    query: {
      page: 1,
      per_page: 999,
    }
  })

  plans.value = response.data?.data.data || []
})

const computedPlans = computed(() => plans.value.filter((item: any) => item.type === tab.value))
const recommendedPlan = computed(() => computedPlans.value.find((plan: any) => plan.badge))
</script>

<template>
  <Layout
    :header-title="$t('Purchase Subscription')"
    :toggle-header="false"
    :show-footer="false"
  >
    <Content class="mx-auto p-4 md:p-10 space-y-8">
      <div class="text-center mb-6">
        <h1 class="text-2xl md:text-3xl font-bold mb-2 dark:text-white">{{ $t('Choose Your Perfect Plan') }}</h1>
        <p class="text-gray-500 dark:text-gray-400">{{ $t('Unlock premium features and storage with our subscription plans') }}</p>
      </div>

      <NTabs animated v-model:value="tab" class="plan-tabs" type="line" justify-content="center" size="large">
        <NTab name="vip">
          <div class="flex items-center gap-2">
            <NIcon :component="CrownOutlined" />
            <span>{{ $t('Membership Package') }}</span>
          </div>
        </NTab>
        <NTab name="storage">
          <div class="flex items-center gap-2">
            <NIcon :component="CloudUploadOutlined" />
            <span>{{ $t('Storage Package') }}</span>
          </div>
        </NTab>
      </NTabs>

      <div v-if="computedPlans.length <= 0" class="flex justify-center py-20">
        <NEmpty description="" />
      </div>

      <div v-else class="grid gap-6 grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3">
        <NCard
          v-for="plan in computedPlans"
          :key="plan.id"
          :class="{'plan-card': true, 'recommended-plan': plan.badge}"
          :bordered="false"
          content-style="padding: 0"
        >
          <div class="card-header">
            <div class="flex justify-between items-start">
              <h3 class="text-xl font-bold dark:text-white">{{ plan.name }}</h3>
              <NBadge v-if="plan.badge" type="success" :value="plan.badge" />
            </div>
            
            <div v-if="plan.intro" class="mt-3 text-gray-500 text-sm dark:text-gray-400">
              {{ plan.intro }}
            </div>
          </div>

          <NDivider class="my-4" />

          <div class="card-content">
            <div class="features-list">
              <div class="feature-item" v-for="feature in plan.features" :key="feature">
                <NIcon :size="16" color="#18a058" :component="CheckCircleOutlined" />
                <span class="dark:text-gray-300">{{ feature }}</span>
              </div>
            </div>
          </div>

          <div class="card-footer">
            <NButton 
              block 
              :type="plan.badge ? 'primary' : 'default'" 
              :ghost="!plan.badge"
              size="large"
              @click="() => router.push(`/user/plans/${plan.id}`)"
            >
              <template #icon>
                <NIcon :component="ShoppingCartOutlined" />
              </template>
              {{ $t('Buy Now') }}
            </NButton>
          </div>
        </NCard>
      </div>
    </Content>
  </Layout>
</template>

<style scoped>
.plan-card {
  transition: all 0.3s ease;
  background-color: white;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.plan-card:hover {
  transform: translateY(-5px);
  box-shadow: none;
  border: none;
}

.recommended-plan {
  position: relative;
  z-index: 1;
  transform: scale(1.02);
  background-color: white;
  box-shadow: none;
}

.recommended-plan:hover {
  transform: translateY(-5px) scale(1.02);
  box-shadow: none;
  border: none;
}

.card-header {
  padding: 20px 20px 10px 20px;
}

.card-content {
  padding: 0 20px 10px 20px;
  flex-grow: 1;
}

.card-footer {
  padding: 20px;
  margin-top: auto;
}

.features-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.feature-item {
  display: flex;
  align-items: flex-start;
  gap: 8px;
}

.feature-item span {
  flex: 1;
  line-height: 1.5;
}

.text-primary {
  color: #18a058;
}

.dark .plan-card {
  background-color: #1f1f1f;
}

.dark .recommended-plan {
  background-color: #1f1f1f;
}

.dark .n-card-header__main {
  color: #f0f0f0 !important;
}

.dark .n-card__content {
  color: #e0e0e0 !important;
}

.plan-tabs :deep(.n-tabs-nav) {
  justify-content: center;
}

.plan-tabs :deep(.n-tabs-tab) {
  padding: 12px 24px;
  font-weight: bold;
}

.plan-tabs :deep(.n-tabs-tab:hover) {
  color: #18a058;
}

.plan-tabs :deep(.n-tabs-tab.n-tabs-tab--active) {
  color: #18a058;
}

.dark .plan-tabs :deep(.n-tabs-tab) {
  color: #e0e0e0;
}

.dark .plan-tabs :deep(.n-tabs-tab.n-tabs-tab--active) {
  color: #4ade80;
}
</style>