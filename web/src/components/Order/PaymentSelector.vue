<script setup lang="ts">
import {useConfigStore} from "@/stores/config";
import {NSpace, NButton} from "naive-ui";
import {useI18n} from "vue-i18n";
import UnifiedPayIcon from "@/components/Icons/UnifiedPay.vue";
import AlipayIcon from "@/components/Icons/Alipay.vue";
import WechatIcon from "@/components/Icons/Wechat.vue";
import WXPayIcon from "@/components/Icons/Wxpay.vue";
import UniPayIcon from "@/components/Icons/Unipay.vue";
import PaypalIcon from "@/components/Icons/Paypal.vue";
import QQIcon from "@/components/Icons/Qqpay.vue";
import USDTIcon from "@/components/Icons/Usdt.vue";
import BankIcon from "@/components/Icons/Bank.vue";
import JDPayIcon from "@/components/Icons/Jdpay.vue";

const configStore = useConfigStore()
const { t } = useI18n()

const value = defineModel('value', {
  type: String,
  required: false,
  default: () => '',
})

const payments: any = {
  unified: {name: t('Unified payment'), icon: UnifiedPayIcon},
  alipay: {name: t('Alipay'), icon: AlipayIcon},
  wechat: {name: t('Wechat'), icon: WechatIcon},
  wxpay: {name: t('Wxpay'), icon: WXPayIcon},
  unipay: {name: t('Unipay'), icon: UniPayIcon},
  paypal: {name: t('Paypal'), icon: PaypalIcon},
  qqpay: {name: t('Qqpay'), icon: QQIcon},
  usdt: {name: t('Usdt'), icon: USDTIcon},
  bank: {name: t('Bank'), icon: BankIcon},
  jdpay: {name: t('Jdpay'), icon: JDPayIcon},
}

const getKey = (platform: string, channel: string): string => {
  return `${platform}-${channel}`
}

const onSelect = (key: string) => {
  value.value = key === value.value ? '' : key
}
</script>

<template>
  <NSpace vertical>
    <div v-for="payment in configStore.group?.payments || []">
      <div class="text-md font-bold mb-2">
        {{ payment.name }}
      </div>

      <NSpace>
        <NButton
          :secondary="value === getKey(payment.platform, channel)"
          :type="value === getKey(payment.platform, channel) ? 'primary' : 'default'"
          v-for="channel in payment.channels"
          @click="onSelect(getKey(payment.platform, channel))"
        >
          <component
            :is="payments[channel].icon"
            class="w-5 h-5 mr-1 fill-current"
          />
          <span>{{ payments[channel].name }}</span>
        </NButton>
      </NSpace>
    </div>
  </NSpace>
</template>