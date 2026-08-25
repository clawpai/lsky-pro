import dayjs from 'dayjs';
import 'dayjs/locale/zh-cn';
import 'dayjs/locale/en';
import relativeTime from 'dayjs/plugin/relativeTime'
import localizedFormat from 'dayjs/plugin/localizedFormat'
import duration from 'dayjs/plugin/duration'

// 导入 dayjs 插件
dayjs.extend(relativeTime)
dayjs.extend(localizedFormat)
dayjs.extend(duration)

// 默认语言
dayjs.locale(localStorage.getItem('locale') || 'zh-CN')

export default dayjs