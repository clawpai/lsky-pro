<script setup lang="ts">
import Layout from "@/components/Layout.vue";
import Content from "@/components/Content.vue";
import {useRoute} from "vue-router";
import {computed, onMounted, ref} from "vue";
import {getUserOrdersByTradeNo} from "@/api";
import {NCard, NIcon, NDescriptions, NDescriptionsItem, useMessage, NButton, useDialog} from "naive-ui";
import {CheckCircleOutlined, CloseCircleOutlined, WarningOutlined} from "@vicons/antd";
import Back from "@/components/Page/Back.vue";
import {useConfigStore} from "@/stores/config";
import {useOrderStore} from "@/stores/order";
import {useDayjs} from "@/hooks/useDayjs";
import {useI18n} from "vue-i18n";
import number from "@/utils/number";
import {useLocaleStore} from "@/stores/locale";
import PaymentSelector from "@/components/Order/PaymentSelector.vue";
import {usePayment} from "@/hooks/usePayment";

const route = useRoute()
const message = useMessage()
const dialog = useDialog()
const order = ref<any>({})
const configStore = useConfigStore()
const orderStore = useOrderStore()
const localeStore = useLocaleStore()
const { t } = useI18n()
const selectedPayment = ref<string>('')
const { handlePayment, validatePayment } = usePayment()

async function getData() {
  const response = await getUserOrdersByTradeNo({
    path: {
      trade_no: route.params.trade_no.toString(),
    }
  })

  if (response.data?.status === 'error') {
    return message.error(response.data?.message)
  }

  order.value = response.data?.data
}

const statuses = computed(() => ({cancelled: t('Cancelled'), unpaid: t('Unpaid'), paid: t('Paid')}))
const colors = computed(() => ({cancelled: 'red', unpaid: 'yellow', paid: 'green'}))
const icons = {cancelled: CloseCircleOutlined, unpaid: WarningOutlined, paid: CheckCircleOutlined}

async function submit() {
  if (!validatePayment(selectedPayment.value, order.value.amount)) {
    return
  }

  await handlePayment(selectedPayment.value, route.params.trade_no.toString())
}

async function cancel() {
  try {
    await orderStore.cancelOrder(route.params.trade_no.toString(), dialog, t)
    message.success(t('Cancellation successful'))
    getData()
  } catch (error) {
    console.error('Cancel order error:', error)
  }
}

onMounted(() => {
  getData()
})
</script>

<template>
  <Layout
    :header-title="$t('Order Details')"
    :toggle-header="false"
    :show-footer="false"
  >
    <Content class="mx-auto p-4 md:p-10 space-y-5">
      <Back to="/user/orders" />

      <NCard>
        <div class="flex flex-col space-y-3 my-6 items-center justify-center">
          <NIcon
            :size="50"
            :color="colors[order.status as keyof typeof colors]"
            :component="icons[order.status as keyof typeof icons]"
          />

          <p>{{ statuses[order.status as keyof typeof statuses] }}</p>
        </div>
      </NCard>

      <NCard :title="$t('Package Snapshot')">
        <NDescriptions
          content-class="ml-10"
          :columns="1"
          size="large"
          label-placement="left"
          separator=""
        >
          <NDescriptionsItem :label="$t('Name')">
            {{ order.snapshot?.name }}
          </NDescriptionsItem>
          <NDescriptionsItem :label="$t('Intro')">
            {{ order.snapshot?.intro }}
          </NDescriptionsItem>
          <NDescriptionsItem :label="$t('Badge')">
            {{ order.snapshot?.badge }}
          </NDescriptionsItem>
          <NDescriptionsItem :label="$t('Privileges')">
            <div class="flex space-x-1 w-full items-center" v-for="feature in order.snapshot?.features || []">
              <NIcon :size="16" color="green" :component="CheckCircleOutlined"/>
              <p class="w-full truncate" :title="feature">{{ feature }}</p>
            </div>
          </NDescriptionsItem>
        </NDescriptions>
      </NCard>

      <NCard :title="$t('Product Information')">
        <NDescriptions
          content-class="ml-10"
          :columns="1"
          size="large"
          label-placement="left"
          separator=""
        >
          <NDescriptionsItem :label="$t('Name')">
            {{ order.product?.name }}
          </NDescriptionsItem>
          <NDescriptionsItem :label="$t('Duration')">
            {{ $t('{minutes} minutes', {minutes: order.product?.duration}) }}
          </NDescriptionsItem>
          <NDescriptionsItem :label="$t('Price')">
            <p class="text-red-500">
              {{ number.currency(order.product?.price, configStore.configs?.app.currency, localeStore.locale) }}
            </p>
          </NDescriptionsItem>
        </NDescriptions>
      </NCard>

      <NCard :title="$t('Order Information')">
        <NDescriptions
          content-class="ml-10"
          :columns="1"
          size="large"
          label-placement="left"
          separator=""
        >
          <NDescriptionsItem :label="$t('Order Number')">
            {{ order.trade_no }}
          </NDescriptionsItem>
          <NDescriptionsItem :label="$t('Order Status')">
            {{ statuses[order.status as keyof typeof statuses] }}
          </NDescriptionsItem>
          <NDescriptionsItem :label="$t('Payment Method')" v-if="order.pay_method">
            {{ order.pay_method }}
          </NDescriptionsItem>
          <NDescriptionsItem :label="$t('Payment Time')" v-if="order.paid_at">
            {{ useDayjs(order.paid_at).format('LLL') }}
          </NDescriptionsItem>
          <NDescriptionsItem :label="$t('Cancellation Time')" v-if="order.canceled_at">
            {{ useDayjs(order.canceled_at).format('LLL') }}
          </NDescriptionsItem>
          <NDescriptionsItem :label="$t('Creation Time')">
            {{ useDayjs(order.created_at).format('LLL') }}
          </NDescriptionsItem>
          <NDescriptionsItem :label="$t('Total Price')">
            <p class="text-red-500">
              {{ number.currency(order.amount, configStore.configs?.app.currency, localeStore.locale) }}
            </p>
            <span v-if="order.deduct_amount > 0" class="text-xs">{{ $t('Use the coupon to deduct {amount}', {amount: number.currency(order.deduct_amount, configStore.configs?.app.currency, localeStore.locale)}) }}</span>
          </NDescriptionsItem>
        </NDescriptions>
      </NCard>

      <div v-if="order.status === 'unpaid'">
        <NCard :title="$t('Checkout')">
          <PaymentSelector v-if="order.amount > 0" v-model:value="selectedPayment"/>

          <div class="flex justify-end space-x-2 w-full mt-4">
            <NButton v-if="order.amount > 0" type="primary" :disabled="selectedPayment === ''" size="small" @click="submit">{{ $t('Proceed to Payment') }}</NButton>
            <NButton v-else type="primary" size="small" @click="submit">{{ $t('Pay Now') }}</NButton>

            <NButton type="error" size="small" @click="cancel">{{ $t('Cancel Order') }}</NButton>
          </div>
        </NCard>
      </div>
    </Content>
  </Layout>
</template>