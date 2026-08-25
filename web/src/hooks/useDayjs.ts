import {computed} from 'vue';
import dayjs from '@/utils/dayjs';
import {useLocaleStore} from "@/stores/locale";

export function useDayjs(date?: dayjs.ConfigType, format?: dayjs.OptionType, locale?: string, strict?: boolean): dayjs.Dayjs {
  const localeStore = useLocaleStore()

  const instance = computed(() => {
    // todo 设置时区
    return dayjs(date, format, locale, strict).locale(localeStore.locale);
  })

  return instance.value as dayjs.Dayjs
}