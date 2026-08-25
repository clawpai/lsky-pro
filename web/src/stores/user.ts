import {ref} from "vue";
import {defineStore} from "pinia";
import {getUserProfile, type GetUserProfileResponse} from "@/api";

export const useUserStore = defineStore('user', () => {
  const token = ref(localStorage.getItem('token') || null)
  const profile = ref<GetUserProfileResponse['data']>()
  const isLoggedIn = ref(false)

  function setToken(newToken: string) {
    token.value = newToken
    localStorage.setItem('token', newToken)
  }

  function removeToken() {
    token.value = null;
    localStorage.removeItem('token')
  }

  function setUserProfile(data: GetUserProfileResponse['data'] | any) {
    profile.value = data
  }

  function setIsLoggedIn(status: boolean) {
    isLoggedIn.value = status
  }

  function setOptions(options: any) {
    profile.value!.options = options;
  }

  /**
   * 设置用户信息
   */
  async function fetchUserProfile() {
    const userResult = await getUserProfile()
    if (userResult.data?.status === 'success') {
      setUserProfile(userResult.data?.data as GetUserProfileResponse['data'])
      setIsLoggedIn(true)
    } else {
      removeToken()
      throw new Error(userResult.data?.message)
    }
  }

  return {
    token,
    profile,
    setToken,
    removeToken,
    isLoggedIn,
    setUserProfile,
    setIsLoggedIn,
    fetchUserProfile,
    setOptions,
  }
})