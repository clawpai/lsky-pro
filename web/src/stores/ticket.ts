import {defineStore} from "pinia";
import type {DialogOptions} from "naive-ui/es/dialog/src/DialogProvider";
import {putUserTicketsByIssueNoClose} from "@/api";

export const useTicketStore = defineStore('ticket', () => {
  
  /**
   * 关闭工单
   * @param issueNo 工单号
   * @param message 消息实例
   * @param dialog 对话框实例
   * @param t 国际化函数
   * @param options 对话框选项
   */
  async function closeTicket(issueNo: string, message: any, dialog: any, t: any, options: DialogOptions = {}) {
    return new Promise(resolve => {
      const d = dialog.warning({
        ...{
          title: t('Close Ticket'),
          content: t('Once closed, you cannot reply to the ticket. Are you sure you want to close this ticket?'),
          positiveText: t('Confirm'),
          negativeText: t('Cancel'),
          onPositiveClick: () => {
            d.loading = true
            return new Promise((resolve2) => {
              putUserTicketsByIssueNoClose({
                path: {
                  issue_no: issueNo,
                }
              }).then(() => {
                message.success(t('Closed successfully'))
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

  return {
    closeTicket
  }
})