import {defineStore} from "pinia";
import {postUserOrdersByTradeNoPay, putUserOrdersByTradeNoCancel} from "@/api";
import type {DialogOptions} from "naive-ui/es/dialog/src/DialogProvider";

export const useOrderStore = defineStore('order', () => {

  /**
   * 取消订单
   * @param tradeNo
   * @param dialog 对话框实例
   * @param t 国际化函数
   * @param options 对话框选项
   */
  async function cancelOrder(tradeNo: string, dialog: any, t: any, options: DialogOptions = {}) {
    return new Promise(resolve => {
      const d = dialog.warning({
        ...{
          title: t('Cancel Order'),
          content: t('Are you sure you want to cancel this order?'),
          positiveText: t('Confirm'),
          negativeText: t('Cancel'),
          onPositiveClick: () => {
            d.loading = true
            return new Promise((resolve2) => {
              putUserOrdersByTradeNoCancel({
                path: {
                  trade_no: tradeNo,
                }
              }).then(() => {
                resolve2(true)
              }).finally(() => {
                d.loading = false
                resolve(true)
              })
            })
          },
        },
        ...options
      })
    })
  }

  /**
   * 发起支付
   * @param payment
   * @param tradeNo
   */
  async function submitPayment(payment: string, tradeNo: string) {
    const [platform, channel] = payment.split('-');
    let method: string

    switch (platform) {
      case 'wechat':
      case 'unipay':
        method = 'scan'
        break;
      default:
        method = 'web'
    }

    const orderUrl = `${window.location.origin}/user/orders/${tradeNo}`
    const result = await postUserOrdersByTradeNoPay({
      path: {
        trade_no: tradeNo,
      },
      body: {
        platform: platform as 'alipay' | 'wechat' | 'unipay' | 'paypal' | 'epay',
        channel: channel as 'unified' | 'alipay' | 'wechat' | 'unipay' | 'paypal' | 'wxpay' | 'usdt' | 'qqpay' | 'bank' | 'jdpay',
        method: method as 'web' | 'h5' | 'app' | 'mini' | 'pos' | 'scan' | 'mp',
        return_url: orderUrl,
        cancel_url: orderUrl,
      }
    })

    return {
      result,
      platform,
      tradeNo
    }
  }

  return {
    submitPayment,
    cancelOrder
  }
})