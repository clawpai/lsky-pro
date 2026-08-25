import { h } from 'vue'
import { useModal, useMessage, NImage } from 'naive-ui'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useOrderStore } from '@/stores/order'

export function usePayment() {
  const modal = useModal()
  const message = useMessage()
  const router = useRouter()
  const { t } = useI18n()
  const orderStore = useOrderStore()

  /**
   * 处理支付逻辑
   * @param selectedPayment 选择的支付方式
   * @param tradeNo 订单号
   * @returns Promise<boolean> 支付是否成功发起
   */
  async function handlePayment(selectedPayment: string, tradeNo: string): Promise<boolean> {
    try {
      const { result, platform } = await orderStore.submitPayment(selectedPayment, tradeNo)
      
      if (result.data?.status === 'success') {

        // 显示扫码弹窗
        const showQrcodeModal = (content: string = '') => {
          modal.create({
            title: t('Please use {method} to scan and pay', {method: {'alipay': t('Alipay'), 'wechat': t('WeChat'), 'unipay': t('UnionPay'), 'epay': t('Epay')}[platform]}),
            preset: 'card',
            maskClosable: false,
            style: {
              width: '300px'
            },
            content: () => h('div', {
              class: 'flex justify-center items-center'
            }, h(NImage, {
              // @ts-ignore
              src: `${import.meta.env.VITE_APP_API_URL}/qrcode?content=${content || result.data?.data.content}`,
              width: '100%'
            })),
            onClose: () => router.push(`/user/orders/${tradeNo}`)
          })
        }

        switch (platform) {
          case 'alipay':
            if (result.data?.data.url) {
              window.location.href = result.data?.data.url
            }
            // @ts-ignore
            if (result.data?.data.qr_code) {
              // @ts-ignore
              showQrcodeModal(result.data?.data.qr_code)
            }
            break;
          case 'paypal':
            window.location.href = result.data?.data.url
            break;
          case 'epay':
            // @ts-ignore
            if (result.data?.data?.action === 'jump') {
              window.location.href = result.data?.data.url
            }

            // @ts-ignore
            if (result.data?.data?.action === 'qrcode') {
              showQrcodeModal()
            }
            break;
          case 'wechat':
          case 'unipay':
            showQrcodeModal()
            break;
        }
        
        return true
      } else {
        message.error(result.data?.message || t('Payment failed'))
        return false
      }
    } catch (error) {
      console.error('Payment error:', error)
      message.error(t('Payment failed'))
      return false
    }
  }

  /**
   * 验证支付参数
   * @param selectedPayment 选择的支付方式
   * @param amount 支付金额
   * @returns 验证是否通过
   */
  function validatePayment(selectedPayment: string, amount: number): boolean {
    if (!selectedPayment && amount > 0) {
      message.error(t('Please select a payment method'))
      return false
    }
    return true
  }

  return {
    handlePayment,
    validatePayment
  }
} 