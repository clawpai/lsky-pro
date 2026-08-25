<script setup lang="ts">
import Layout from "@/components/Layout.vue";
import Content from "@/components/Content.vue";
import Back from "@/components/Page/Back.vue";
import {
  NAlert, NButton, NCard, NIcon, NInput, NInputGroup, 
  useMessage, NDivider, NBadge, NSpin, NStatistic
} from "naive-ui";
import {onMounted, ref, watch, computed} from "vue";
import {getPlansById, postUserOrders, postUserOrdersPreview} from "@/api";
import {useRoute, useRouter} from "vue-router";
import {CheckCircleOutlined, ShoppingOutlined, GiftOutlined} from "@vicons/antd";
import {useConfigStore} from "@/stores/config";
import {useOrderStore} from "@/stores/order";
import {useI18n} from "vue-i18n";
import number from "../../../utils/number";
import {useLocaleStore} from "@/stores/locale";
import PaymentSelector from "@/components/Order/PaymentSelector.vue";
import {usePayment} from "@/hooks/usePayment";

const route = useRoute()
const router = useRouter()
const message = useMessage()
const configStore = useConfigStore()
const localeStore = useLocaleStore()
const plan = ref<any>({})
const selectedPrice = ref<any>({})
const selectedPayment = ref<string>('')
const couponCode = ref('')
const amount = ref(0)
const deductAmount = ref(0)
const orderStore = useOrderStore()
const { t } = useI18n()
const loading = ref(false)
const { handlePayment, validatePayment } = usePayment()

const finalAmount = computed(() => amount.value)

const discountPercentage = computed(() => {
  if (!deductAmount.value || typeof selectedPrice.value?.price !== 'number') return 0
  
  if (selectedPrice.value.price === 0) return 100
  
  if (deductAmount.value >= selectedPrice.value.price) return 100
  
  return Math.round((deductAmount.value / selectedPrice.value.price) * 100)
})

watch(selectedPrice, () => previewOrder(), { deep: true })

async function previewOrder() {
  loading.value = true
  try {
    const response = await postUserOrdersPreview({
      body: {
        price_id: selectedPrice.value.id,
        coupon_code: couponCode.value,
      }
    })

    if (response.data?.status === 'error') {
      return message.error(response.data?.message)
    }

    amount.value = response.data?.data.amount || 0
    deductAmount.value = response.data?.data.deduct_amount || 0
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

async function submit() {
  if (!validatePayment(selectedPayment.value, amount.value)) {
    return
  }

  loading.value = true
  try {
    const response = await postUserOrders({
      body: {
        price_id: selectedPrice.value.id,
        coupon_code: couponCode.value,
      }
    })

    if (response.data?.status === 'error') {
      return message.error(response.data?.message)
    }

    const tradeNo = response.data!.data.trade_no

    if (response.data?.data.is_paid) {
      router.push(`/user/orders/${tradeNo}`).then(() => {
        message.success(t('Order Payment Successful'));
      })
    } else {
      await handlePayment(selectedPayment.value, tradeNo)
    }
  } catch (error) {
    console.error(error)
    message.error(t('Order creation failed'))
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  loading.value = true
  try {
    const response = await getPlansById({
      path: {
        id: Number(route.params.id),
      }
    })

    if (response.data?.status === 'error') {
      return message.error(response.data?.message)
    }

    plan.value = response.data?.data
    selectedPrice.value = response.data?.data.prices[0]

    await previewOrder()
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <Layout
    :header-title="$t('Subscription Details')"
    :toggle-header="false"
    :show-footer="false"
  >
    <Content class="mx-auto p-4 md:p-10 space-y-6">
      <NSpin :show="loading">
        <div class="flex flex-col w-full space-y-4">
          <div class="flex items-center justify-between">
            <Back to="/user/plans" class="hover:text-primary transition-colors" />
            <NBadge v-if="plan.badge" :value="plan.badge" type="success" />
          </div>

          <div class="bg-gradient-to-r from-green-50 to-blue-50 dark:from-gray-800 dark:to-gray-700 rounded-lg p-6 mb-4">
            <h1 class="text-2xl md:text-3xl font-bold mb-2 dark:text-white">{{ plan.name }}</h1>
            <p class="text-gray-600 dark:text-gray-300">{{ plan.intro }}</p>
          </div>

          <NAlert type="info" show-icon class="mb-4">
            {{ $t('Note that subscription purchases do not stack packages, only one subscription will be in effect at any one time, and the most recently purchased subscription will be applied immediately upon successful purchase.') }}
          </NAlert>

          <div class="flex w-full flex-col md:flex-row gap-6">
            <div class="w-full md:basis-2/3 space-y-6">
              <NCard :title="$t('Subscription Information')" class="subscription-card" :bordered="false">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div v-for="feature in plan.features" :key="feature" class="flex space-x-2 items-center">
                    <div class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                      <NIcon :size="14" color="#18a058" :component="CheckCircleOutlined"/>
                    </div>
                    <p class="w-full" :title="feature">{{ feature }}</p>
                  </div>
                </div>
              </NCard>
              
              <NCard :title="$t('Payment Cycle')" class="subscription-card" :bordered="false">
                <div class="payment-options">
                  <div
                    v-for="price in plan.prices"
                    :key="price.id"
                    :class="[
                      'payment-option', 
                      { 'payment-option-selected': price.id === selectedPrice.id },
                      { 'payment-option-recommended': price.recommended }
                    ]"
                    @click="selectedPrice = price"
                  >
                    <div class="select-indicator">
                      <div class="radio-outer">
                        <div class="radio-inner" v-if="price.id === selectedPrice.id"></div>
                      </div>
                    </div>
                    
                    <div class="option-content">
                      <div class="option-header">
                        <div class="flex items-center">
                          <span class="option-name">{{ price.name }}</span>
                          <span v-if="price.recommended" class="best-value-badge">{{ $t('Best value') }}</span>
                        </div>
                        <div v-if="price.id === selectedPrice.id" class="selected-badge">
                          {{ $t('Selected') }}
                        </div>
                      </div>
                      
                      <div class="option-price">
                        {{ number.currency(price.price, configStore.configs?.app.currency, localeStore.locale) }}
                      </div>
                      
                      <div v-if="price.description" class="option-description">
                        {{ price.description }}
                      </div>
                      
                      <div v-if="price.features && price.features.length > 0" class="option-features">
                        <div v-for="(feature, index) in price.features" :key="index" class="feature-item">
                          <div class="feature-dot"></div>
                          <span>{{ feature }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </NCard>
            </div>
            
            <div class="flex flex-col gap-4 w-full md:basis-1/3">
              <NCard class="subscription-card" :bordered="false">
                <h3 class="font-medium mb-3 flex items-center gap-2">
                  <NIcon :component="GiftOutlined" />
                  <span>{{ $t('Apply Coupon') }}</span>
                </h3>
                <NInputGroup>
                  <NInput v-model:value="couponCode" :placeholder="$t('Enter coupon code')"/>
                  <NButton type="primary" :disabled="!couponCode" @click="() => {
                    if (couponCode) previewOrder()
                  }">{{ $t('Apply') }}
                  </NButton>
                </NInputGroup>
              </NCard>
              
              <NCard :title="$t('Order Summary')" class="subscription-card checkout-card" :bordered="false">
                <div class="flex flex-col space-y-4">
                  <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">{{ $t('Subscription') }}</span>
                    <span>{{ plan.name }}</span>
                  </div>
                  
                  <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">{{ $t('Plan') }}</span>
                    <span>{{ selectedPrice.name }}</span>
                  </div>
                  
                  <div v-if="deductAmount > 0" class="flex justify-between items-center text-green-600">
                    <span>{{ $t('Coupon Discount') }}</span>
                    <div class="flex items-center gap-2">
                      <span>-{{ number.currency(deductAmount, configStore.configs?.app.currency, localeStore.locale) }}</span>
                      <NBadge v-if="discountPercentage > 0 && discountPercentage <= 100" :value="`-${discountPercentage}%`" type="success" />
                    </div>
                  </div>
                  
                  <NDivider />
                  
                  <div class="flex justify-between items-center">
                    <span class="font-bold">{{ $t('Total') }}</span>
                    <NStatistic :value="finalAmount" class="text-2xl font-bold" style="color: #e53e3e;">
                      <template #prefix>{{ configStore.configs?.app.currency }}</template>
                    </NStatistic>
                  </div>

                  <template v-if="amount > 0">
                    <PaymentSelector v-model:value="selectedPayment" class="mt-2"/>

                    <NButton 
                      type="primary" 
                      size="large"
                      block
                      :disabled="selectedPayment === ''" 
                      @click="submit"
                      class="mt-4"
                    >
                      <template #icon>
                        <NIcon :component="ShoppingOutlined" />
                      </template>
                      {{ $t('Complete Purchase') }}
                    </NButton>
                  </template>

                  <template v-else>
                    <NButton 
                      type="primary" 
                      size="large"
                      block
                      @click="submit"
                      class="mt-4"
                    >
                      {{ $t('Confirm Order') }}
                    </NButton>
                  </template>
                </div>
              </NCard>
            </div>
          </div>
        </div>
      </NSpin>
    </Content>
  </Layout>
</template>

<style scoped>
.checkout-card :deep(.n-card__content) {
  padding: 16px;
}

.subscription-card {
  transition: all 0.2s ease;
  background-color: white;
  border-radius: 12px;
}

.subscription-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  border: none;
}

.payment-options {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.payment-option {
  display: flex;
  padding: 16px;
  border-radius: 10px;
  background-color: white;
  transition: all 0.25s ease;
  border: 1px solid #eee;
  position: relative;
  overflow: hidden;
  cursor: pointer;
}

.payment-option:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
  border-color: #d9f2e4;
}

.payment-option-selected {
  border-color: #18a058;
  background-color: rgba(24, 160, 88, 0.03);
}

.payment-option-recommended {
  border-left: 3px solid #e53e3e;
}

.payment-option-recommended::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 0;
  height: 0;
  border-style: solid;
  border-width: 24px 24px 0 0;
  border-color: #e53e3e transparent transparent transparent;
  z-index: 1;
}

.select-indicator {
  margin-right: 12px;
  display: flex;
  align-items: flex-start;
  margin-top: 2px;
}

.radio-outer {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  border: 2px solid #ccc;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: border-color 0.2s ease;
}

.payment-option-selected .radio-outer {
  border-color: #18a058;
}

.radio-inner {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background-color: #18a058;
}

.option-content {
  flex: 1;
}

.option-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.option-name {
  font-weight: 600;
  font-size: 16px;
}

.selected-badge {
  background-color: rgba(24, 160, 88, 0.1);
  color: #18a058;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 500;
}

.option-price {
  font-size: 20px;
  font-weight: 700;
  color: #18a058;
  margin-bottom: 8px;
}

.option-description {
  font-size: 14px;
  color: #666;
  margin-bottom: 10px;
  line-height: 1.5;
}

.option-features {
  margin-top: 12px;
}

.option-features .feature-item {
  display: flex;
  align-items: center;
  margin-bottom: 6px;
  font-size: 13px;
  color: #555;
}

.feature-dot {
  width: 6px;
  height: 6px;
  background-color: #18a058;
  border-radius: 50%;
  margin-right: 8px;
  flex-shrink: 0;
}

.best-value-badge {
  background-color: #fef2f2;
  color: #e53e3e;
  font-size: 0.7rem;
  border-radius: 12px;
  padding: 2px 8px;
  font-weight: 500;
  margin-left: 8px;
}

.text-primary {
  color: #18a058;
}

.hover\:text-primary:hover {
  color: #18a058;
}

.dark .subscription-card {
  background-color: #1f1f1f;
}

.dark .payment-option {
  background-color: #1f1f1f;
  border-color: #333;
}

.dark .payment-option:hover {
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
  border-color: #2c4c3b;
}

.dark .payment-option-selected {
  border-color: #18a058;
  background-color: rgba(24, 160, 88, 0.15);
}

.dark .option-price {
  color: #4ade80;
}

.dark .option-description {
  color: #aaa;
}

.dark .option-features .feature-item {
  color: #bbb;
}

.dark .feature-dot {
  background-color: #4ade80;
}

.dark .selected-badge {
  background-color: rgba(24, 160, 88, 0.25);
  color: #4ade80;
}

.dark .n-card-header__main {
  color: #f0f0f0 !important;
}

.dark .n-card__content {
  color: #e0e0e0 !important;
}

.dark .text-green-600 {
  color: #4ade80 !important;
}

.dark .bg-green-50 {
  background-color: rgba(24, 160, 88, 0.15) !important;
}
</style>