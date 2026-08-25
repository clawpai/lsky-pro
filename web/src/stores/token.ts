import {defineStore} from "pinia";
import type {DialogOptions} from "naive-ui/es/dialog/src/DialogProvider";
import {deleteUserTokensById} from "@/api";
import {useI18n} from "vue-i18n";

export interface TokenStoreOptions {
  message: any; // 通用消息 API 类型
  dialog: any; // 通用对话框 API 类型
}

export const useTokenStore = defineStore('token', () => {
  const { t } = useI18n()

  /**
   * 删除 token
   * @param id token id
   * @param options dialog配置
   * @param storeOptions store配置，包含message和dialog实例
   */
  async function deleteToken(id: string, options: DialogOptions = {}, storeOptions: TokenStoreOptions) {
    const { message, dialog } = storeOptions;
    return new Promise(resolve => {
      const d = dialog.warning({
        ...{
          title: t('Delete Token'),
          content: t('Once deleted, the login status of the client using this token will become invalid. Are you sure you want to delete it?'),
          positiveText: t('Confirm'),
          negativeText: t('Cancel'),
          onPositiveClick: () => {
            d.loading = true
            return new Promise((resolve2) => {
              deleteUserTokensById({
                path: {
                  id: id,
                }
              }).then(() => {
                message.success(t('Delete successfully'))
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
    deleteToken
  }
})